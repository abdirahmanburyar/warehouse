<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Events\InventoryUpdated;
use App\Models\Product;
use App\Models\Supply;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use App\Models\Inventory;
use App\Models\SupplyItem;
use App\Models\PackingList;
use App\Models\PackingListDifference;
use App\Models\PurchaseOrderItem;
use App\Http\Resources\SupplierResource;
use Inertia\Inertia;
use Carbon\Carbon;

class SupplyController extends Controller
{
    /**
     * Display a listing of the supplies.
     */
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.product'])
            ->when($request->filled('search'), function($query) use ($request) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('po_number', 'like', "%{$search}%")
                      ->orWhereHas('supplier', function($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->filled('supplier'), function($query) use ($request) {
                $query->where('supplier_id', $request->supplier);
            })
            ->when($request->filled('status'), function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('po_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 10))
            ->withQueryString();

        // Get statistics for the cards
        $stats = [
            'total_items' => PurchaseOrder::count(),
            'total_cost' => PurchaseOrder::join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                ->sum(DB::raw('quantity * unit_cost')),
            'avg_lead_time' => '4 Months', // You can calculate this based on your needs
            'pending_orders' => PurchaseOrder::where('status', 'pending')->count()
        ];

        return Inertia::render('Supplies/Index', [
            'purchaseOrders' => [
                'data' => collect($purchaseOrders->items())->map(function($po) {
                    return [
                        'id' => $po->id,
                        'po_number' => $po->po_number,
                        'po_date' => $po->po_date,
                        'supplier' => $po->supplier ? [
                            'id' => $po->supplier->id,
                            'name' => $po->supplier->name
                        ] : null,
                        'items' => $po->items->map(function($item) {
                            return [
                                'id' => $item->id,
                                'product_name' => $item->product->name,
                                'quantity' => $item->quantity,
                                'unit_cost' => $item->unit_cost,
                                'total_cost' => $item->quantity * $item->unit_cost
                            ];
                        }),
                        'status' => $po->status ?? 'pending',
                        'created_at' => $po->created_at->format('Y-m-d')
                    ];
                }),
                'meta' => [
                    'current_page' => $purchaseOrders->currentPage(),
                    'from' => $purchaseOrders->firstItem(),
                    'last_page' => $purchaseOrders->lastPage(),
                    'links' => $purchaseOrders->linkCollection()->toArray(),
                    'path' => $purchaseOrders->path(),
                    'per_page' => $purchaseOrders->perPage(),
                    'to' => $purchaseOrders->lastItem(),
                    'total' => $purchaseOrders->total(),
                ]
            ],
            'filters' => $request->only('search', 'page','per_page', 'supplier','status'),
            'suppliers' => Supplier::get(),
            'stats' => $stats
        ]);
    }

    public function create(Request $request){
        return inertia('Supplies/Create');
    }

    public function showPO(Request $request, $id){
        $po = PurchaseOrder::where('id', $id)->with('items.product','supplier')->first();
        return inertia("Supplies/Show", [
            'po' => $po
        ]);
    }

    public function newPackingList(Request $request){
        $purchaseOrders = PurchaseOrder::select('id', 'po_number')->get();
        $warehouses = Warehouse::select('id', 'name')->get();
        return inertia("Supplies/PackingList", [
            'purchaseOrders' => $purchaseOrders,
            'warehouses' => $warehouses,
        ]);
    }

    public function getBackOrder(Request $request, $id){
        try {
            logger()->info($id);
            $purchaseOrders = PackingListDifference::whereHas('packingList', function($query) use($id){
                $query->where('purchase_order_id', $id);
            })->get();
            return response()->json($purchaseOrders, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }


    public function backOrder(Request $request){
        $purchaseOrders = PurchaseOrder::select('id','po_number')->get();
        return inertia("Supplies/BackOrder", [
            'purchaseOrders' => $purchaseOrders
        ]);
    }



    public function getPO(Request $request, $id) {
        try {
            // Get PO items with remaining quantities using joins
            $items = DB::table('purchase_order_items as poi')
                ->select([
                    'poi.id',
                    'poi.product_id',
                    'poi.unit_cost',
                    'poi.quantity as original_quantity',
                    'p.name as product_name',
                    'p.barcode',
                    DB::raw('COALESCE(SUM(pl.quantity), 0) as packed_quantity'),
                    DB::raw('poi.quantity - COALESCE(SUM(pl.quantity), 0) as remaining_quantity')
                ])
                ->join('products as p', 'p.id', '=', 'poi.product_id')
                ->leftJoin('packing_lists as pl', function($join) {
                    $join->on('pl.product_id', '=', 'poi.product_id')
                         ->on('pl.purchase_order_id', '=', 'poi.purchase_order_id');
                })
                ->where('poi.purchase_order_id', $id)
                ->groupBy('poi.id', 'poi.product_id', 'poi.unit_cost', 'poi.quantity', 'p.name', 'p.barcode')
                ->having(DB::raw('poi.quantity - COALESCE(SUM(pl.quantity), 0)'), '>', 0)
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->remaining_quantity,
                        // 'remaining_quantity' => $item->remaining_quantity,
                        'unit_cost' => $item->unit_cost,
                        'total_cost' => $item->remaining_quantity * $item->unit_cost,
                        'searchQuery' => $item->product_name,
                        'barcode' => $item->barcode,
                        'warehouse_id' => null,
                        'expired_date' => '',
                        'batch_number' => '',
                        'location' => '',
                        'original_quantity' => $item->original_quantity,
                        'packed_quantity' => $item->packed_quantity,
                        'received_quantity' => 0,
                        'product' => [
                            'id' => $item->product_id,
                            'name' => $item->product_name,
                            'barcode' => $item->barcode
                        ]
                    ];
                })->toArray();

            // Get PO with supplier
            $po = PurchaseOrder::with('supplier')->find($id);
            if (!$po) {
                return response()->json(['error' => 'Purchase order not found'], 404);
            }

            // Get PO number before converting to array
            $poNumber = $po->po_number;
            
            // Convert to array and add items
            $po = $po->toArray();
            $po['items'] = $items;
            
            // Generate packing list number
            $lastPacking = PackingList::where('purchase_order_id', $id)
                ->where('packing_list_number', 'like', "PKL-" . substr($poNumber, 3) . "-%")
                ->orderBy('packing_list_number', 'desc')
                ->first();
            
            $sequence = 1;
            if ($lastPacking) {
                $parts = explode('-', $lastPacking->packing_list_number);
                if (count($parts) === 3) { // PKL-number-sequence
                    $sequence = intval($parts[2]) + 1;
                }
            }
            
            $po['packing_list_number'] = sprintf("PKL-%s-%03d", substr($poNumber, 3), $sequence);
            return response()->json($po, 200);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }        
    }

    public function storePK(Request $request)
    {
        try {
            return DB::transaction(function() use ($request){

                $request->validate([
                    'purchase_order_id' => 'required',
                    'packing_list_number' => 'required',
                    'pk_date' => 'required',
                    'items' => 'required|array',
                    'items.*.product_id' => 'required',
                    'items.*.warehouse_id' => 'required',
                    'items.*.quantity' => 'required|numeric',
                    'items.*.batch_number' => 'required',
                    'items.*.expire_date' => 'required',
                    'items.*.location' => 'required',
                    'items.*.unit_cost' => 'required|numeric',
                    'items.*.total_cost' => 'required|numeric',
                ]);

                // Create packing list for each item
                foreach($request->items as $item){
                    if($item['quantity'] == 0){
                        continue;
                    }
                    $packingList = PackingList::create([
                        'packing_list_number' => $request->packing_list_number,
                        'purchase_order_id' => $request->purchase_order_id,
                        'pk_date' => $request->pk_date,
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'quantity' => $item['quantity'],
                        'batch_number' => $item['batch_number'],
                        'expire_date' => $item['expire_date'],
                        'location' => $item['location'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost']
                    ]);

                    // Handle differences if they exist
                    if (!empty($item['differences'])) {
                        foreach ($item['differences'] as $difference) {
                            $packingList->differences()::updateOrCreate([
                                'purchase_orderid' => $request->purchase_order_id
                            ],[
                                'quantity' => $difference['quantity'],
                                'status' => $difference['status']
                            ]);
                        }
                    }
                }

                return response()->json('Packing list created successfully', 200);
            });
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }

    }
        

    public function newPO()
    {
        $products = Product::get();
        $suppliers = Supplier::get();
        
        // Get the last PO number and increment it
        $lastPO = PurchaseOrder::latest()->first();
        $nextPONumber = $lastPO ? 'PO-' . str_pad((intval(substr($lastPO->po_number, 3)) + 1), 6, '0', STR_PAD_LEFT) : 'PO-000001';

        return inertia('Supplies/NewPo', [
            'products' => $products,
            'suppliers' => $suppliers,
            'po_number' => $nextPONumber
        ]);
    }

    public function editPO($id)
    {
        try {
            $products = Product::get();
            $suppliers = Supplier::get();
            
            return inertia('Supplies/EditPo', [
                'products' => $products,
                'suppliers' => $suppliers,
                'po_id' => $id
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Failed to load purchase order');
        }
    }

    public function storePO(Request $request)
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'po_number' => 'required',
                'po_date' => 'required',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.unit_cost' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.total_cost' => 'required|numeric|min:0',
            ]);

            return DB::transaction(function () use ($validated) {
                $po = PurchaseOrder::create([
                    'po_number' => $validated['po_number'],
                    'supplier_id' => $validated['supplier_id'],
                    'po_date' => $validated['po_date'],
                    'created_by' => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    if (!isset($item['product_id'])) continue;
                    
                    $po->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost'],
                    ]);
                }

                return response()->json('Purchase order created successfully', 200);
            });

        } catch (\Throwable $th) {
            return response()->json( $th->getMessage(), 500);
        }
    }


    public function searchsupplier(Request $request, $search){
        try {
            $supplier = Supplier::where('name', 'like', "%{$search}%")->first();
            return response()->json($supplier, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    
    public function getSupplier(Request $request, $id){
        try {
            $supplier = Supplier::find($id);
            $supplier['po_number'] = 98887;
            return response()->json($supplier, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function searchProduct(Request $request, $id){
        try {
            $product = Product::find($id);
            return response()->json($product, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Store a newly created supply in storage.
     */
    public function store(Request $request)
    {
        
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'invoice_number' => 'required|string',
                'supply_date' => 'required|date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.id' => 'nullable|exists:supply_items,id',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.product_name' => 'required|string',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.batch_number' => 'nullable|string',
                'items.*.manufacturing_date' => 'nullable|date',
                'items.*.expiry_date' => 'nullable|date|after:manufacturing_date',
            ]);
            DB::beginTransaction();

            // Create or update supply
            $supply = Supply::updateOrCreate(
                ['id' => $request->id],
                [
                    'supplier_id' => $validated['supplier_id'],
                    'invoice_number' => $validated['invoice_number'],
                    'supply_date' => $validated['supply_date'],
                    'notes' => $validated['notes'],
                    'warehouse_id' => auth()->user()->warehouse_id,
                ]
            );

            // Get current item IDs
            $currentItemIds = collect($validated['items'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete items that are not in the request (only pending items can be deleted)
            if ($request->id) {
                $supply->items()
                    ->where('status', 'pending')
                    ->whereNotIn('id', $currentItemIds)
                    ->delete();
            }

            // Process each item
            foreach ($validated['items'] as $itemData) {
                // If item has ID, update it, otherwise create new
                if (!empty($itemData['id'])) {
                    // Only update if item is still pending
                    SupplyItem::where('id', $itemData['id'])
                        ->where('status', 'pending')
                        ->update([
                            'product_id' => $itemData['product_id'],
                            'product_name' => $itemData['product_name'],
                            'quantity' => $itemData['quantity'],
                            'batch_number' => $itemData['batch_number'] ?? null,
                            'manufacturing_date' => $itemData['manufacturing_date'] ?? null,
                            'expiry_date' => $itemData['expiry_date'] ?? null,
                        ]);
                } else {
                    // Create new item
                    $supply->items()->create([
                        'product_id' => $itemData['product_id'],
                        'product_name' => $itemData['product_name'],
                        'quantity' => $itemData['quantity'],
                        'batch_number' => $itemData['batch_number'] ?? null,
                        'manufacturing_date' => $itemData['manufacturing_date'] ?? null,
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'status' => 'pending'
                    ]);
                }
            }

            DB::commit();
            return response()->json('Supply saved successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified supply.
     */
    public function show(Supply $supply)
    {
        $supply->load(['items.product', 'warehouse', 'supplier']);
        
        return Inertia::render('Supplies/Show', [
            'supply' => $supply,
        ]);
    }

    /**
     * Remove the specified supply from storage.
     */
    public function destroy(Supply $supply)
    {
        DB::beginTransaction();

        try {
            // Adjust inventory
            $inventory = Inventory::where([
                'product_id' => $supply->product_id,
                'warehouse_id' => $supply->warehouse_id,
                'batch_number' => $supply->batch_number,
            ])->first();
            
            if ($inventory) {
                $inventory->quantity -= $supply->quantity;
                $inventory->save();
            }
            
            // Delete the supply
            $supply->delete();

            DB::commit();

            return redirect()->route('supplies.index')
                ->with('success', 'Supply deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete supply: ' . $e->getMessage());
        }
    }

    /**
     * Store multiple supplies in a batch operation.
     */
    public function storeBatch(Request $request)
    {
        // Validate common fields
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'supply_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.batch_number' => 'nullable|string|max:255',
            'products.*.manufacturing_date' => 'nullable|date',
            'products.*.expiry_date' => 'nullable|date|after_or_equal:products.*.manufacturing_date',
        ]);

        DB::beginTransaction();

        try {
            $createdSupplies = [];

            // Process each product in the batch
            foreach ($validated['products'] as $productData) {
                // Calculate total price for this product
                $totalPrice = $productData['quantity'] * $productData['unit_price'];

                // Create supply record
                $supply = Supply::create([
                    'product_id' => $productData['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'supplier_id' => $validated['supplier_id'],
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['unit_price'],
                    'total_price' => $totalPrice,
                    'supply_date' => $validated['supply_date'],
                    'invoice_number' => $validated['invoice_number'],
                    'batch_number' => $productData['batch_number'] ?? null,
                    'manufacturing_date' => $productData['manufacturing_date'] ?? null,
                    'expiry_date' => $productData['expiry_date'] ?? null,
                    'notes' => $validated['notes'],
                ]);

                $createdSupplies[] = $supply;

                // Update inventory
                $inventory = Inventory::firstOrNew([
                    'product_id' => $productData['product_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'batch_number' => $productData['batch_number'] ?? null,
                ]);

                // If it's a new inventory item, set these properties
                if (!$inventory->exists) {
                    $inventory->manufacturing_date = $productData['manufacturing_date'] ?? null;
                    $inventory->expiry_date = $productData['expiry_date'] ?? null;
                    $inventory->quantity = 0;
                }

                // Increase the quantity
                $inventory->quantity += $productData['quantity'];
                $inventory->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Supplies added successfully',
                'supplies' => $createdSupplies
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add supplies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get items for a specific supply
     */
    public function getItems(Supply $supply)
    {
        return $supply->items()->with('product')->get();
    }

    /**
     * Update the status of a supply item.
     */
    public function updateItemStatus(Request $request, SupplyItem $item)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'status' => 'required|in:pending,approved,rejected'
            ]);

            $item->update([
                'status' => $validated['status']
            ]);

            // If approved, update inventory
            if ($validated['status'] === 'approved' && $item->status !== 'approved') {
                $inventory = Inventory::firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $item->supply->warehouse_id,
                        'batch_number' => $item->batch_number,
                    ],
                    [
                        'quantity' => 0,
                        'manufacturing_date' => $item->manufacturing_date,
                        'expiry_date' => $item->expiry_date,
                    ]
                );

                $inventory->quantity += $item->quantity;
                $inventory->save();

                // Fire inventory updated event
                event(new InventoryUpdated($inventory));
            }

            DB::commit();
            return response()->json(['message' => 'Item status updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update item status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve or reject a supply item
     */
    public function approveItem(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validate([
                'status' => 'required|in:approved,rejected',
            ]);

            $item = SupplyItem::with(['supply', 'product'])->findOrFail($id);
            
            $item->update([
                'status' => $validated['status'],
                'approved_at' => now(),
                'approved_by' => auth()->id()
            ]);

            // If approved, update inventory
            if ($validated['status'] === 'approved') {
                // Check for existing inventory with same product and expiry date
                $inventory = Inventory::where('product_id', $item->product_id)
                    ->where('expiry_date', $item->expiry_date)
                    ->first();

                if ($inventory) {
                    // Update existing inventory quantity
                    $inventory->increment('quantity', $item->quantity);
                } else {
                    // Create new inventory record
                    Inventory::create([
                        'product_id' => $item->product_id,
                        'batch_number' => $item->batch_number,
                        'quantity' => $item->quantity,
                        'expiry_date' => $item->expiry_date,
                        'manufacturing_date' => $item->manufacturing_date,
                        'warehouse_id' => $item->supply->warehouse_id,
                        'created_by' => auth()->id()
                    ]);
                }
            }

            logger()->info('Inventory updated for supply item ' . $item->id);

            event(new InventoryUpdated());

            DB::commit();
            return response()->json(['message' => 'Item status updated successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update item status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Approve or reject all pending items in a supply.
     */
    public function approveBulk(Request $request, Supply $supply)
    {
        try {
            return DB::transaction(function () use ($request, $supply) {
                $validated = $request->validate([
                    'status' => 'required|in:approved,rejected',
                    'notes' => 'nullable|string',
                ]);

                $items = $supply->items()->where('status', 'pending')->get();

                foreach ($items as $item) {
                    // Update the item with approval info
                    $item->update([
                        'status' => $validated['status'],
                        'approval_notes' => $validated['notes'],
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                    // If approved, update inventory
                    if ($validated['status'] === 'approved') {
                        $inventory = Inventory::firstOrNew([
                            'product_id' => $item->product_id,
                            'warehouse_id' => $supply->warehouse_id,
                            'batch_number' => $item->batch_number,
                        ]);

                        if (!$inventory->exists) {
                            $inventory->manufacturing_date = $item->manufacturing_date;
                            $inventory->expiry_date = $item->expiry_date;
                            $inventory->quantity = 0;
                        }

                        $inventory->quantity += $item->quantity;
                        $inventory->save();
                    }
                }

                return response()->json('Supply items ' . $validated['status'] . ' successfully', 200);
            });
        } catch (\Throwable $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Delete multiple supplies at once
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:supplies,id'
        ]);

        try {
            DB::beginTransaction();

            // Delete supplies that have no approved items
            $supplies = Supply::whereIn('id', $request->ids)
                ->whereDoesntHave('items', function ($query) {
                    $query->where('status', 'approved');
                })
                ->get();

            if ($supplies->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Cannot delete supplies because they have approved items.'
                ], 422);
            }

            // Delete the supply items first
            foreach ($supplies as $supply) {
                $supply->items()->delete();
                $supply->delete();
            }

            DB::commit();
            return response()->json([
                'message' => 'Supplies deleted successfully',
                'deleted_count' => $supplies->count()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete supplies: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete supplies after checking if they can be deleted
     */
    public function bulkDeleteSupplies(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:supplies,id'
        ]);

        $supplies = Supply::whereIn('id', $request->ids)
            ->with('items')
            ->get();

        // Check if any supply has approved items
        foreach ($supplies as $supply) {
            if ($supply->items->contains('is_approved', true)) {
                return response()->json([
                    'message' => 'Cannot delete supplies that have approved items.',
                    'supply_id' => $supply->id
                ], 500);
            }
        }

        // If we get here, none of the supplies have approved items
        try {
            DB::beginTransaction();
            
            // Delete all supply items first
            SupplyItem::whereIn('supply_id', $request->ids)->delete();
            
            // Then delete the supplies
            Supply::whereIn('id', $request->ids)->delete();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Supplies deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete supplies: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchSuppliers(Request $request)
    {
        $query = $request->input('query');
        $suppliers = Supplier::query()
            ->select('id', 'name')
            ->when($query, function ($q) use ($query) {
                return $q->where('name', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(function ($supplier) {
                return [
                    'value' => $supplier->id,
                    'label' => $supplier->name
                ];
            });

        return response()->json($suppliers);
    }

    public function getPurchaseOrder($id)
    {
        $purchaseOrder = PurchaseOrder::with(['items.product', 'supplier'])->findOrFail($id);
        return response()->json([
            'id' => $purchaseOrder->id,
            'supplier_id' => $purchaseOrder->supplier_id,
            'po_number' => $purchaseOrder->po_number,
            'items' => $purchaseOrder->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'searchQuery' => $item->product->name,
                    'barcode' => $item->product->barcode,
                    'dose' => $item->product->dose,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_cost,
                    'total_cost' => $item->quantity * $item->unit_cost
                ];
            })
        ]);
    }

    public function updatePurchaseOrder(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'po_number' => 'required',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.unit_cost' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.total_cost' => 'required|numeric|min:0',
            ]);

            return DB::transaction(function () use ($validated, $id) {
                $po = PurchaseOrder::findOrFail($id);
                
                // Update PO details
                $po->update([
                    'po_number' => $validated['po_number'],
                    'supplier_id' => $validated['supplier_id'],
                ]);

                // Delete existing items
                $po->items()->delete();

                // Create new items
                foreach ($validated['items'] as $item) {
                    if (!isset($item['product_id'])) continue;
                    
                    $po->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost'],
                    ]);
                }

                return response()->json('Purchase order updated successfully', 200);
            });

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }


    public function deletePurchaseOrder(Request $request, $id){
        try {
            $po = PurchaseOrder::find($id);
            logger()->info($po);
            if($po->status != 'pending'){
                return response()->json("This $po->po_number already approved and it can not be deleted", 500);
            }
            $po->items()->delete();
            $po->delete();
            return response()->json("Deleted succefyully", 200);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }


    public function deletePurchaseOrderItem($id)
    {
        try {
            $item = \App\Models\PurchaseOrderItem::findOrFail($id);
            $item->delete();
            return response()->json('Item removed successfully', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}
