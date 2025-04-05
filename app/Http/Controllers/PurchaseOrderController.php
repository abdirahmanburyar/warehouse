<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchaseOrderResource;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\BackOrder;
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

    public function packingList($id)
    {
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.product', 'creator', 'updater'])->findOrFail($id);

        return Inertia::render('PurchaseOrder/PackingList', [
            'purchase_order' => $purchaseOrder,
        ]);
    }

    public function createBackOrder(Request $request, PurchaseOrder $purchaseOrder)
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'nullable|exists:back_orders,id',
                'items.*.purchase_order_id' => 'nullable|exists:purchase_orders,id',
                'items.*.type' => 'required|string|in:damaged,missing',
                'items.*.quantity' => 'required|integer|min:0',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.notes' => 'nullable|string'
            ]);

            logger()->info($request->items);
    
            foreach ($request->items as $item) {
                // Find existing back order
                $backOrder = BackOrder::where([
                    'product_id' => $item['product_id'],
                    'purchase_order_id' => $item['purchase_order_id'],
                    'type' => $item['type'],
                ])->first();

                if ($item['quantity'] == 0) {
                    // Delete if exists and quantity is 0
                    if ($backOrder) {
                        $backOrder->delete();
                    }
                } else {
                    // Update or create with new quantity
                    if ($backOrder) {
                        $backOrder->update([
                            'quantity' => $item['quantity'],
                            'notes' => $item['notes'] ?? null,
                        ]);
                    } else {
                        BackOrder::create([
                            'product_id' => $item['product_id'],
                            'purchase_order_id' => $item['purchase_order_id'],
                            'type' => $item['type'],
                            'quantity' => $item['quantity'],
                            'notes' => $item['notes'] ?? null,
                            'created_by' => auth()->id(),
                            'status' => 'pending'
                        ]);
                    }
                }
            }
    
            return response()->json('Back orders updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function packingListStore(Request $request)
    {
        try {
            $request->validate([
                'total_cost' => 'required|numeric|min:0',
                'purchase_order_id' => 'nullable|exists:purchase_orders,id',
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:0',
                'items.*.received_quantity' => 'required|integer|min:0',
                'items.*.unit_cost' => 'required|numeric|min:0',
            ]);

            $purchaseOrder = PurchaseOrder::find($request->purchase_order_id);
            
            // Get all current product IDs in the request
            $requestProductIds = collect($request->items)->pluck('product_id')->toArray();
            
            // Delete items and their back orders that are not in the request
            $purchaseOrder->items()
                ->whereNotIn('product_id', $requestProductIds)
                ->get()
                ->each(function ($item) use ($request) {
                    // Delete associated back orders first
                    BackOrder::where([
                        'product_id' => $item->product_id,
                        'purchase_order_id' => $request->purchase_order_id,
                    ])->delete();
                    
                    // Then delete the item itself
                    $item->delete();
                });

            // Update or create new items
            foreach ($request->items as $item) {
                $purchaseOrder->items()->updateOrCreate(
                    [
                        'product_id' => $item['product_id'],
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'received_quantity' => $item['received_quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['unit_cost'] * $item['quantity']
                    ]
                );

                // If received quantity equals ordered quantity, delete any back orders
                if($item['received_quantity'] == $item['quantity']){
                    BackOrder::where([
                        'product_id' => $item['product_id'],
                        'purchase_order_id' => $request->purchase_order_id,
                    ])->delete();
                }
            }

            logger()->info($request->total_cost);

            $purchaseOrder->update([
                'total_amount' => $request->total_cost
            ]);

            return response()->json('Packing list updated successfully', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function getBackOrders(PurchaseOrder $purchaseOrder, $productId)
    {
        $backOrders = BackOrder::where('purchase_order_id', $purchaseOrder->id)
            ->where('product_id', $productId)
            ->get();

        return response()->json($backOrders);
    }
}