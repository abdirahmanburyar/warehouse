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
use App\Models\PackingList;
use App\Models\Warehouse;

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
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.product', 'creator', 'updater', 'packingLists'])->findOrFail($id);

        return Inertia::render('PurchaseOrder/PackingList', [
            'purchase_order' => $purchaseOrder,
            'warehouses' => Warehouse::select('id', 'name')->get()
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
                'items' => 'required|array',
                'items.*.id' => 'nullable|exists:purchase_order_items,id',
                'items.*.packing_list_id' => 'required|exists:packing_lists,id',
                'items.*.purchase_order_id' => 'required|exists:purchase_orders,id',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:0',
                'items.*.warehouse_id' => 'required|exists:warehouses,id',
                'items.*.location' => 'required|string',
                'items.*.expiry_date' => 'nullable|date',
                'items.*.batch_number' => 'nullable|string',
                'items.*.generic_name' => 'nullable|string',
                'items.*.received_quantity' => 'required|integer|min:0|lte:items.*.quantity',
                'items.*.unit_cost' => 'required|numeric|min:0',
                'items.*.total_cost' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            foreach ($request->items as $item) {
                $purchaseOrderItem = PurchaseOrderItem::findOrNew($item['id'] ?? null);
                $purchaseOrderItem->fill([
                    'packing_list_id' => $item['packing_list_id'],
                    'purchase_order_id' => $item['purchase_order_id'],
                    'product_id' => $item['product_id'],
                    'warehouse_id' => $item['warehouse_id'],
                    'quantity' => $item['quantity'],
                    'location' => $item['location'],
                    'received_quantity' => $item['received_quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $item['total_cost'],
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'batch_number' => $item['batch_number'] ?? null,
                    'generic_name' => $item['generic_name'] ?? null,
                ]);
                $purchaseOrderItem->save();

                // Handle back orders based on type
                if ($item['received_quantity'] > 0) {
                    // Get missing type back order
                    $missingBackOrder = BackOrder::where([
                        'product_id' => $item['product_id'],
                        'purchase_order_id' => $item['purchase_order_id'],
                        'type' => 'missing'
                    ])->first();

                    if ($missingBackOrder) {
                        // Deduct received quantity from missing back order
                        $newQuantity = $missingBackOrder->quantity - $item['received_quantity'];
                        if ($newQuantity <= 0) {
                            $missingBackOrder->delete();
                        } else {
                            $missingBackOrder->update(['quantity' => $newQuantity]);
                        }
                    }

                    // Note: We don't touch 'damaged' type back orders as they need to be handled separately
                }
            }

            DB::commit();
            return response()->json('Packing list created successfully', 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            logger()->error('Error creating packing list: ' . $e->getMessage());
            return response()->json($e->getMessage(), 500);
        }
    }

    public function getBackOrders(PurchaseOrder $purchaseOrder, $productId)
    {
        $backOrders = BackOrder::where('purchase_order_id', $purchaseOrder->id)
            ->where('product_id', $productId)
            ->get();

        return response()->json($backOrders);
    }

    public function generatePackingList(Request $request){
        try {
            $request->validate([
                'purchase_order_id' => 'required|exists:purchase_orders,id',
            ]);

            // Get today's date in YYYYMMDD format
            $today = now()->format('Ymd');
            
            // Find the last packing list number for today
            $lastPackingList = PackingList::where('packing_list_number', 'like', "PL-{$today}-%")
                ->orderBy('packing_list_number', 'desc')
                ->first();

            // Extract sequence number and increment
            $sequence = 1;
            if ($lastPackingList) {
                $parts = explode('-', $lastPackingList->packing_list_number);
                $sequence = intval(end($parts)) + 1;
            }

            // Generate new packing list number with sequence
            $timestamp = now()->format('His');
            $packingListNumber = sprintf("PL-%s-%s-%s-%03d", 
                $today,
                str_pad($request->purchase_order_id, 3, '0', STR_PAD_LEFT),
                $timestamp,
                $sequence
            );

            $packingList = PackingList::create([
                'purchase_order_id' => $request->purchase_order_id,
                'created_by' => Auth::id(),
                'packing_list_number' => $packingListNumber,
                'packing_date' => now()->toDateTimeString(),
                'status' => 'pending'
            ]);
            
            return response()->json([
                'message' => 'Packing list generated successfully',
                'packing_list' => $packingList
            ], 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function getPackingListItems($id)
    {
        try {
            $items = PurchaseOrderItem::where('packing_list_id', $id)
                ->with('product:id,name')
                ->get()
                ->map(function ($item) {
                    $item['product_name'] = $item->product->name;
                    $item['product_id'] = $item->product->id;
                    return $item;
                });
            return response()->json($items, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    
}