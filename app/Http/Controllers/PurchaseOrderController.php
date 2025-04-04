<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseOrderResource;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::query()
            ->with(['supplier', 'items.product', 'creator', 'updater']);

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items.product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Apply date range filter
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('po_date', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('po_date', '<=', $endDate);
        }

        // Apply sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $purchaseOrders = $query->paginate($request->input('per_page', 10))
            ->withQueryString();

        return Inertia::render('PurchaseOrder/Index', [
            'purchase_orders' => PurchaseOrderResource::collection($purchaseOrders),
            'suppliers' => Supplier::select('id', 'name')->get(),
            'products' => Product::select('id', 'name')->get(),
            'filters' => $request->only(['search', 'status', 'start_date', 'end_date', 'per_page']),
        ]);
    }

    public function store(Request $request)
    {
        try {
                $request->validate([
                    'po_number' => 'required|string|unique:purchase_orders,po_number,' . $request->input('id'),
                    'supplier_id' => 'required|exists:suppliers,id',
                    'po_date' => 'required|date',
                    'total_amount' => 'required|numeric|min:0',
                    'notes' => 'nullable|string',
                    'status' => 'required|in:draft,pending,approved,rejected,completed',
                    'items' => 'nullable|array',
                    'items.*.id' => 'nullable|exists:purchase_order_items,id',
                    'items.*.product_id' => 'nullable|exists:products,id',
                    'items.*.quantity' => 'nullable|integer|min:1',
                    'items.*.unit_cost' => 'nullable|numeric|min:0.001',
                    'items.*.total_cost' => 'nullable|numeric|min:0.001',
                ]);

                DB::beginTransaction();

                $purchaseOrder = PurchaseOrder::updateOrCreate(
                    ['id' => $request->input('id')],
                    [
                        'po_number' => $request->input('po_number'),
                        'supplier_id' => $request->input('supplier_id'),
                        'po_date' => $request->input('po_date'),    
                        'total_amount' => $request->input('total_amount'),
                        'notes' => $request->input('notes'),
                        'status' => $request->input('status'),
                        'created_by' => $request->input('id') ? Auth::id() : Auth::id(),
                        'updated_by' => $request->input('id') ? Auth::id() : null,
                ]
            );

            // Handle items
            $currentItemIds = [];
            foreach ($request->input('items') as $item) {
                $itemData = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['total_cost'],
                ];

                $purchaseOrderItem = $purchaseOrder->items()->updateOrCreate(
                    [
                        'id' => $item['id'] ?? null,
                        'purchase_order_id' => $purchaseOrder->id
                    ],
                    $itemData
                );
                
                $currentItemIds[] = $purchaseOrderItem->id;
            }

            // Soft delete items that are no longer in the list
            if ($request->input('id')) {
                $purchaseOrder->items()
                    ->whereNotIn('id', $currentItemIds)
                    ->delete();
            }

            DB::commit();

            return response()->json( $request->input('id') ? 'Purchase order updated successfully' : 'Purchase order created successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        try {
            $purchaseOrder->delete();
            return response()->json('Purchase order deleted successfully', 200);
        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }
}
