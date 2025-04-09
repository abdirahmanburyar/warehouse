<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\BackOrder;
use App\Models\PackingList;
use App\Models\Warehouse;
use App\Imports\PurchaseOrderItemsImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Http\Resources\PurchaseOrderResource;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::query()
            ->with(['supplier', 'po_items', 'creator', 'updater']);

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('po_items', function ($q) use ($search) {
                        $q->where('item_description', 'like', "%{$search}%");
                        $q->orWhere('item_code', 'like', "%{$search}%");
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
                    'notes' => 'nullable|string',
                ]);

                DB::beginTransaction();

                $purchaseOrder = PurchaseOrder::updateOrCreate(
                    ['id' => $request->input('id')],
                    [
                        'po_number' => $request->input('po_number'),
                        'supplier_id' => $request->input('supplier_id'),
                        'po_date' => $request->input('po_date'),    
                        'notes' => $request->input('notes'),
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
        $purchaseOrder = PurchaseOrder::with(['supplier', 'items.product', 'creator', 'updater', 'packingLists.purchaseOrderItems.warehouse', 'packingLists.purchaseOrderItems.product', 'packingLists.creator',  'po_items' => function($q) {
            $q->where('quantity', '>', 0)
              ->addSelect([
                'po_items.*',
                'products.id as product_id',
                'products.name as product_name'
              ])
              ->leftJoin('products', function($join) {
                  $join->on(function($query) {
                      $query->whereColumn('products.name', 'po_items.item_description')
                            ->orWhereColumn('products.barcode', 'po_items.item_code');
                  });
              });
        }])->findOrFail($id);
        
        $purchaseOrder['po_items'] = $purchaseOrder->po_items->transform(function($item) use ($purchaseOrder) {
            return [
                'purchase_order_id' => $purchaseOrder->id,
                'packing_list_id' => null,
                'damage_quantity' => 0,
                'product_id' => $item->product_id,
                'warehouse_id' => null,
                'location' => '',
                'expiry_date' => null,
                'batch_number' => null,
                'generic_name' => null,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'received_quantity' => 0,
                'unit_cost' => $item->unit_cost,
                'total_cost' => $item->total_cost
            ];
        })->values()->toArray();

        // Flatten all purchase order items from packing lists into one array
        $flattenedItems = collect();
        foreach ($purchaseOrder->packingLists as $packingList) {
            $items = collect($packingList->purchaseOrderItems)->map(function($item) use ($packingList) {
                return array_merge($item->toArray(), [
                    'packing_list_number' => $packingList->packing_list_number,
                    'packing_list_date' => $packingList->created_at,
                    'packing_list_status' => $packingList->status,
                    'created_by' => $packingList->creator->name
                ]); 
            });
            $flattenedItems = $flattenedItems->concat($items);
        }

        return Inertia::render('PurchaseOrder/PackingList', [
            'purchase_order' => $purchaseOrder,
            'packingLists' => $flattenedItems->values()->all(),
            'warehouses' => Warehouse::get(),
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

    public function packingListStore(Request $request, PurchaseOrder $purchaseOrder)
    {
        try {
            DB::beginTransaction();
            logger()->info($request->items);
            
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.packing_list_id' => 'nullable|exists:packing_lists,id',
                'items.*.purchase_order_id' => 'required|exists:purchase_orders,id',
                'items.*.id' => 'nullable|exists:purchase_order_items,id',
                'items.*.quantity' => 'nullable|numeric|min:0',
                'items.*.received_quantity' => 'nullable|numeric|min:0',
                'items.*.warehouse_id' => 'nullable|exists:warehouses,id',
                'items.*.product_name' => 'required',
                'items.*.generic_name' => 'nullable|string',
                'items.*.location' => 'nullable|string',
                'items.*.batch_number' => 'nullable|string',
                'items.*.expiry_date' => 'nullable|date',
                'items.*.unit_cost' => 'nullable|numeric|min:0',
                'items.*.total_cost' => 'nullable|numeric|min:0',
                'items.*.damage_quantity' => 'nullable|numeric|min:0',
            ]);
            
            foreach ($request->items as $item) {
                // Find or create PurchaseOrderItem
                $purchaseOrderItem = PurchaseOrderItem::updateOrCreate(
                    ['id' => $item['id']],
                    [
                        'packing_list_id' => $item['packing_list_id'],
                        'purchase_order_id' => $item['purchase_order_id'],
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'location' => $item['location'],
                        'batch_number' => $item['batch_number'],
                        'expiry_date' => $item['expiry_date'],
                        'generic_name' => $item['generic_name'],
                        'product_name' => $item['product_name'],
                        'quantity' => $item['quantity'],
                        'received_quantity' => $item['received_quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost'],
                        'damage_quantity' => $item['damage_quantity'],
                    ]
                );

                // Find and update corresponding PoItem
                $poItem = PoItem::where('item_description', $item['product_name'])
                    ->where('purchase_order_id', $item['purchase_order_id'])
                    ->first();

                if ($poItem) {
                    // Update po_items quantity: original quantity - received quantity
                    $poItem->quantity = $poItem->quantity - $item['received_quantity'] - $item['damage_quantity'];
                    $poItem->save();
                }

                if($item['damage_quantity'] > 0){
                    BackOrder::updateOrCreate([
                        'purchase_order_id' => $item['purchase_order_id'],
                        'product_id' => $item['product_id'],
                    ], [
                        'quantity' => $item['damage_quantity'],
                        'type' => 'damaged',
                        'created_by' => Auth::id(),
                        'status' => 'pending'
                    ]);
                }
            }

            DB::commit();
            return response()->json('Items updated successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
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
                'packing_date' => Carbon::now()->toDateString(),
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

    public function importItems(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx',
                'purchase_order_id' => 'required|exists:purchase_orders,id'
            ]);

            $file = $request->file('file');
            $purchaseOrderId = $request->purchase_order_id;

            DB::beginTransaction();

            Excel::import(new PurchaseOrderItemsImport($purchaseOrderId), $file);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Items imported successfully'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createPackingList(Request $request, $id)
    {
        try {
            // Generate a unique packing list number
            $packingListNumber = 'PL-' . now()->format('Ymd') . '-' . rand(1000, 9999);

            // Create the packing list
            $packingList = PackingList::create([
                'purchase_order_id' => $id,
                'created_by' => Auth::id(),
                'packing_list_number' => $packingListNumber,
                'packing_date' => Carbon::now()->toDateString(),
            ]);
            
            return response()->json([
                'message' => 'Packing list created successfully',
                'packing_list' => $packingList
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create packing list',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkApprove(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array',
                'items.*' => 'exists:purchase_order_items,id',
                'purchase_order_id' => 'required|exists:purchase_orders,id'
            ]);

            DB::beginTransaction();
            try {
                // Get the purchase order items with their details
                $items = DB::table('purchase_order_items')
                    ->whereIn('id', $request->items)
                    ->get();

                foreach ($items as $item) {
                    // Update purchase order item status
                    DB::table('purchase_order_items')
                        ->where('id', $item->id)
                        ->update(['status' => 'approved']);

                    // Check if inventory record exists
                    $inventory = DB::table('inventories')
                        ->where('product_id', $item->product_id)
                        ->where('warehouse_id', $item->warehouse_id)
                        ->first();

                    if ($inventory) {
                        // Update existing inventory
                        DB::table('inventories')
                            ->where('id', $inventory->id)
                            ->update([
                                'quantity' => DB::raw('quantity + ' . $item->received_quantity),
                                'batch_number' => $item->batch_number,
                                'expiry_date' => $item->expiry_date,
                                'unit_cost' => $item->unit_cost,
                                'updated_at' => now()
                            ]);
                    } else {
                        // Create new inventory record
                        DB::table('inventories')->insert([
                            'product_id' => $item->product_id,
                            'warehouse_id' => $item->warehouse_id,
                            'quantity' => $item->received_quantity,
                            'batch_number' => $item->batch_number,
                            'expiry_date' => $item->expiry_date,
                            'unit_cost' => $item->unit_cost,
                            'location' => $item->location,
                            'is_active' => true,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }

                DB::commit();
                return response()->json(['message' => 'Items approved and inventory updated']);
            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateItem(Request $request, $purchaseOrder)
    {
        try {
            $request->validate([
                'received_quantity' => 'required|integer|min:0',
                'damage_quantity' => 'nullable|integer|min:0',
                'batch_number' => 'nullable|string',
                'expiry_date' => 'nullable|date',
                'location' => 'nullable|string'
            ]);

            DB::table('purchase_order_items')
                ->where('id', $request->id)
                ->where('purchase_order_id', $purchaseOrder)
                ->update([
                    'received_quantity' => $request->received_quantity,
                    'damage_quantity' => $request->damage_quantity,
                    'batch_number' => $request->batch_number,
                    'expiry_date' => $request->expiry_date,
                    'location' => $request->location,
                    'updated_at' => now()
                ]);

            return response()->json(['message' => 'Item updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}