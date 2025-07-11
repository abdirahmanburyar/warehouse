<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\InventoryItem;
use App\Models\FacilityInventoryItem;
use App\Models\Facility;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TracertableItemsController extends Controller
{
    /**
     * Display the tracertable items dashboard.
     */
    public function dashboard()
    {
        // Get tracertable products
        $tracertableProducts = Product::whereNotNull('tracert_type')
            ->where('tracert_type', '!=', '')
            ->with(['category', 'dosage'])
            ->get();

        // Get warehouse inventory items for tracertable products
        $warehouseItems = InventoryItem::whereHas('inventory', function ($query) use ($tracertableProducts) {
            $query->whereIn('product_id', $tracertableProducts->pluck('id'));
        })
        ->with(['inventory.product.category'])
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'product_name' => $item->inventory->product->name,
                'product_id' => $item->inventory->product->productID,
                'category_name' => $item->inventory->product->category->name ?? 'N/A',
                'available_quantity' => $item->available_quantity ?? 0,
                'tracert_type' => $item->inventory->product->tracert_type
            ];
        });

        // Get facility inventory items for tracertable products
        $facilityItems = FacilityInventoryItem::whereHas('facilityInventory', function ($query) use ($tracertableProducts) {
            $query->whereIn('product_id', $tracertableProducts->pluck('id'));
        })
        ->with(['facilityInventory.product', 'facilityInventory.facility'])
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'product_name' => $item->facilityInventory->product->name,
                'product_id' => $item->facilityInventory->product->productID,
                'facility_name' => $item->facilityInventory->facility->name ?? 'N/A',
                'available_quantity' => $item->available_quantity ?? 0,
                'tracert_type' => $item->facilityInventory->product->tracert_type
            ];
        });

        // Calculate summary statistics
        $summary = [
            'totalItems' => $tracertableProducts->count(),
            'warehouseItems' => $warehouseItems->count(),
            'facilityItems' => $facilityItems->count(),
            'totalQuantity' => $warehouseItems->sum('available_quantity') + $facilityItems->sum('available_quantity')
        ];

        // Prepare warehouse chart data
        $warehouseChartData = $this->prepareWarehouseChartData($warehouseItems);

        // Prepare facility chart data
        $facilityChartData = $this->prepareFacilityChartData($facilityItems);

        // Get all facilities for filter
        $facilities = Facility::select('id', 'name')->get();

        return Inertia::render('TracertableItems/Dashboard', [
            'summary' => $summary,
            'warehouseItems' => $warehouseItems,
            'facilityItems' => $facilityItems,
            'facilities' => $facilities,
            'warehouseChartData' => $warehouseChartData,
            'facilityChartData' => $facilityChartData
        ]);
    }

    /**
     * Prepare warehouse chart data.
     */
    private function prepareWarehouseChartData($warehouseItems)
    {
        $inStock = $warehouseItems->where('available_quantity', '>', 10)->count();
        $lowStock = $warehouseItems->where('available_quantity', '>', 0)->where('available_quantity', '<=', 10)->count();
        $outOfStock = $warehouseItems->where('available_quantity', '<=', 0)->count();

        return [
            'labels' => ['In Stock', 'Low Stock', 'Out of Stock'],
            'data' => [$inStock, $lowStock, $outOfStock]
        ];
    }

    /**
     * Prepare facility chart data.
     */
    private function prepareFacilityChartData($facilityItems)
    {
        $facilityData = $facilityItems->groupBy('facility_name')
            ->map(function ($items) {
                return $items->sum('available_quantity');
            });

        return [
            'labels' => $facilityData->keys()->toArray(),
            'data' => $facilityData->values()->toArray()
        ];
    }

    /**
     * Get facility-specific data for AJAX requests.
     */
    public function getFacilityData(Request $request)
    {
        $facilityId = $request->input('facility_id');
        
        $query = FacilityInventoryItem::whereHas('facilityInventory', function ($query) {
            $query->whereHas('product', function ($q) {
                $q->whereNotNull('tracert_type')->where('tracert_type', '!=', '');
            });
        })
        ->with(['facilityInventory.product', 'facilityInventory.facility']);

        if ($facilityId) {
            $query->whereHas('facilityInventory', function ($q) use ($facilityId) {
                $q->where('facility_id', $facilityId);
            });
        }

        $facilityItems = $query->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->facilityInventory->product->name,
                    'product_id' => $item->facilityInventory->product->productID,
                    'facility_name' => $item->facilityInventory->facility->name ?? 'N/A',
                    'available_quantity' => $item->available_quantity ?? 0,
                    'tracert_type' => $item->facilityInventory->product->tracert_type
                ];
            });

        $facilityChartData = $this->prepareFacilityChartData($facilityItems);

        return response()->json([
            'facilityItems' => $facilityItems,
            'facilityChartData' => $facilityChartData
        ]);
    }
} 