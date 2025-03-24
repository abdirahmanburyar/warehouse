<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpiredResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ExpiredController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'all');
        $warehouseId = $request->query('warehouse_id');
        
        // Base query - with eager loading for better performance
        $inventoryQuery = Inventory::with(['product', 'warehouse'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->select('inventories.*', 'products.name as product_name');
        
        // Apply warehouse filter if provided
        if ($warehouseId) {
            $inventoryQuery->where('warehouse_id', $warehouseId);
        }
        
        // Apply tab-specific filters
        switch ($tab) {
            case 'near':
                // Items expiring in the next 30 days
                $inventoryQuery->whereNotNull('inventories.expiry_date')
                    ->whereDate('inventories.expiry_date', '>', Carbon::now())
                    ->whereDate('inventories.expiry_date', '<=', Carbon::now()->addDays(30))
                    ->where('inventories.is_active', true);
                break;
                
            case 'expired':
                // Items that have already expired but not marked as disposed
                $inventoryQuery->whereNotNull('inventories.expiry_date')
                    ->whereDate('inventories.expiry_date', '<', Carbon::now())
                    ->where('inventories.is_active', true);
                break;
                
            case 'disposed':
                // Items marked as inactive (assuming this indicates disposal)
                $inventoryQuery->whereNotNull('inventories.expiry_date')
                    ->where('inventories.is_active', false);
                break;
                
            default: // 'all'
                // All items with issues (near expiry, expired, or disposed)
                $inventoryQuery->whereNotNull('inventories.expiry_date')
                    ->where(function($query) {
                        $query->where(function($q) {
                            // Near expiry items
                            $q->whereDate('inventories.expiry_date', '>', Carbon::now())
                              ->whereDate('inventories.expiry_date', '<=', Carbon::now()->addDays(30))
                              ->where('inventories.is_active', true);
                        })->orWhere(function($q) {
                            // Expired items
                            $q->whereDate('inventories.expiry_date', '<', Carbon::now())
                              ->where('inventories.is_active', true);
                        })->orWhere(function($q) {
                            // Disposed items
                            $q->where('inventories.is_active', false);
                        })->orWhere(function($q) {
                            // Low stock items
                            $q->where('inventories.quantity', '<=', DB::raw('inventories.reorder_level'))
                              ->where('inventories.quantity', '>', 0);
                        });
                    });
                break;
        }
        
        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $inventoryQuery->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('inventories.batch_number', 'like', "%{$search}%")
                  ->orWhere('inventories.location', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting if provided
        if ($request->filled('sort_field')) {
            $sortField = $request->input('sort_field', 'expiry_date');
            $sortDirection = $request->input('sort_direction', 'asc');
            
            // Properly qualify the sort field if it's a common column name
            if (in_array($sortField, ['is_active', 'expiry_date', 'manufacturing_date', 'quantity', 'batch_number', 'location'])) {
                $sortField = 'inventories.' . $sortField;
            } elseif ($sortField === 'product_name') {
                $sortField = 'products.name';
            }
            
            $inventoryQuery->orderBy($sortField, $sortDirection);
        } else {
            // Default sorting by expiry date
            $inventoryQuery->orderBy('inventories.expiry_date', 'asc');
        }
        
        // Paginate the results
        $inventories = $inventoryQuery->paginate(10)->withQueryString();
        
        // Extract non-empty filters
        $filters = [];
        if ($request->filled('search')) $filters['search'] = $request->input('search');
        if ($request->filled('sort_field')) $filters['sort_field'] = $request->input('sort_field');
        if ($request->filled('sort_direction')) $filters['sort_direction'] = $request->input('sort_direction');
        if ($warehouseId) $filters['warehouse_id'] = $warehouseId;
        
        // Count expired items for dashboard metrics (using more efficient queries)
        $nearExpiryCount = Inventory::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>', Carbon::now())
            ->whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('is_active', true)
            ->when($warehouseId, function($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->count();
            
        $expiredCount = Inventory::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', Carbon::now())
            ->where('is_active', true)
            ->when($warehouseId, function($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->count();
            
        $disposedCount = Inventory::whereNotNull('expiry_date')
            ->where('is_active', false)
            ->when($warehouseId, function($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            })
            ->count();
        
        return Inertia::render('Expired/Index', [
            'inventories' => ExpiredResource::collection($inventories),
            'activeTab' => $tab,
            'filters' => $filters,
            'counts' => [
                'near' => $nearExpiryCount,
                'expired' => $expiredCount,
                'disposed' => $disposedCount,
                'all' => $nearExpiryCount + $expiredCount + $disposedCount
            ],
            'warehouses' => Warehouse::where('is_active', true)->get(['id', 'name']),
        ]);
    }
    
    // Mark expired items as disposed (inactive)
    public function markAsDisposed(Request $request)
    {
        $request->validate([
            'inventory_ids' => 'required|array',
            'inventory_ids.*' => 'exists:inventories,id',
            'notes' => 'nullable|string',
        ]);
        
        $count = Inventory::whereIn('id', $request->inventory_ids)
            ->update([
                'is_active' => false,
                'notes' => $request->notes ? $request->notes : 'Marked as disposed due to expiration'
            ]);
        
        return redirect()->back()->with('success', $count . ' items marked as disposed');
    }
}
