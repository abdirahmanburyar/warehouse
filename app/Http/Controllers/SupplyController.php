<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Events\InventoryUpdated;
use App\Models\Product;
use App\Models\Supply;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Disposal;
use App\Models\PurchaseOrder;
use App\Models\Location;
use App\Models\Inventory;
use App\Models\SupplyItem;
use App\Models\PackingList;
use App\Models\IssuedQuantity;
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
            'locations' => Location::get(),
        ]);
    }

    public function getBackOrder(Request $request, $id){
        try {
            $purchaseOrders = PackingListDifference::withWhereHas('packingList', function($query) use($id){
                $query->where('purchase_order_id', $id);
            })->with('product', 'packingList:id,packing_list_number')->get();
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
            // Get PO items with latest packing list data
            $items = DB::table('purchase_order_items as poi')
                ->select([
                    'poi.id',
                    'poi.product_id',
                    'poi.unit_cost',
                    'poi.quantity',
                    'p.name as product_name',
                    'p.barcode',
                    'pl.id as packing_list_id',
                    'pl.quantity as received_quantity',
                    'pl.expire_date as expire_date',
                    'pl.warehouse_id',
                    'pl.batch_number',
                    'pl.location',
                    'pl.packing_list_number'
                ])
                ->join('products as p', 'p.id', '=', 'poi.product_id')
                ->leftJoin('packing_lists as pl', function($join) {
                    $join->on('pl.product_id', '=', 'poi.product_id')
                         ->on('pl.purchase_order_id', '=', 'poi.purchase_order_id');
                })
                ->where('poi.purchase_order_id', $id)
                ->get()
                ->map(function($item) {
                    $total = $item->received_quantity ? ($item->received_quantity * $item->unit_cost) : ($item->quantity * $item->unit_cost);
                    
                    // Get differences if packing list exists
                    $differences = [];
                    if ($item->packing_list_id) {
                        $differences = DB::table('packing_list_differences')
                            ->where('packing_list_id', $item->packing_list_id)
                            ->select(['id', 'quantity', 'status'])
                            ->get()
                            ->map(function($diff) {
                                return [
                                    'id' => $diff->id,
                                    'quantity' => $diff->quantity,
                                    'status' => $diff->status
                                ];
                            })
                            ->toArray();
                    }

                    return [
                        'id' => $item->packing_list_id, // Use packing list ID if exists
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_cost,
                        'total_cost' => $total,
                        'searchQuery' => $item->product_name,
                        'barcode' => $item->barcode,
                        'warehouse_id' => $item->warehouse_id,
                        'expire_date' => $item->expire_date,
                        'batch_number' => $item->batch_number,
                        'location' => $item->location,
                        'fullfillment_rate' => $item->received_quantity ? round(($item->received_quantity / $item->quantity) * 100, 2) . '%' : '0%',
                        'received_quantity' => $item->received_quantity ?? 0,
                        'differences' => $differences,
                        'product' => [
                            'id' => $item->product_id,
                            'name' => $item->product_name,
                            'barcode' => $item->barcode
                        ]
                    ];
                })->toArray();

        // Get the purchase order
        $po = PurchaseOrder::findOrFail($id);
        
        // Get PO number before converting to array
        $poNumber = $po->po_number;
        $supplier = $po->supplier;
            
        // Convert to array and add items
        $po = $po->toArray();
        $po['items'] = $items;
        
        // Check for existing packing list number for this PO
        $existingPackingList = PackingList::where('purchase_order_id', $id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existingPackingList) {
            // Use the existing packing list number
            $po['packing_list_number'] = $existingPackingList->packing_list_number;
        } else {
            // Generate new packing list number only if none exists
            $po['packing_list_number'] = sprintf("PKL-%s-001", substr($po['po_number'], 3));
        }
        
        return response()->json($po, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }        
    }

    public function storePK(Request $request)
    {
        try {
            return DB::transaction(function() use ($request){

                logger()->info($request->all());


                $request->validate([
                    'purchase_order_id' => 'required',
                    'packing_list_number' => 'required',
                    'pk_date' => 'required',
                    'items' => 'required|array',
                    'items.*.product_id' => 'required',
                    'items.*.warehouse_id' => 'required',
                    'items.*.received_quantity' => 'required|numeric|min:0',
                    'items.*.quantity' => 'required|numeric',
                    'items.*.batch_number' => 'required',
                    'items.*.expire_date' => 'required',
                    'items.*.location' => 'required',
                    'items.*.unit_cost' => 'required|numeric',
                    'items.*.total_cost' => 'required|numeric',
                ]);

                if(PurchaseOrder::find($request->purchase_order_id)
                ->where('status', 'completed')
                ->exists()) return response()->json("This P.O is completed and closed", 500);

                // Create packing list for each item
                foreach($request->items as $item){
                    // if($item['received_quantity'] == 0){
                    //     continue;
                    // }

                   if(floatval($item['received_quantity']) > floatval($item['quantity'])){
                        PurchaseOrderItem::where('purchase_order_id', $request->purchase_order_id)
                        ->where('product_id', $item['product_id'])
                        ->where('product_id', $item['product_id'])
                        ->update([
                            'quantity' => $item['received_quantity']
                        ]);
                   }
                    $packingList = PackingList::updateOrCreate([
                        'id' => $item['id'] ?? null
                    ],[
                        'packing_list_number' => $request->packing_list_number,
                        'purchase_order_id' => $request->purchase_order_id,
                        'pk_date' => $request->pk_date,
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'quantity' => $item['received_quantity'],
                        'batch_number' => $item['batch_number'],
                        'expire_date' => $item['expire_date'],
                        'location' => $item['location'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost']
                    ]);


                    foreach ($item['differences'] as $difference) {
                        logger()->info("difference['id']");
                        logger()->info($difference['id']);
                        PackingListDifference::updateOrCreate([
                            'packing_list_id' => $item['id'] ?? $packingList->id,
                            'id' => $difference['id'] ?? null,
                        ],[
                            'product_id' => $item['product_id'],
                            'packing_list_id' => $item['id'] ?? $packingList->id,
                            'quantity' => $difference['quantity'],
                            'status' => $difference['status']
                        ]);
                    }

                    if(!$item['id']){
                        // update the inventory
                        $inventory = DB::table('inventories')
                            ->where('product_id', $item['product_id'])
                            ->where('warehouse_id', $item['warehouse_id'])
                            ->first();
    
                        if ($inventory) {
                            DB::table('inventories')
                                ->where('id', $inventory->id)
                                ->update([
                                    'quantity' => DB::raw('quantity + ' . $item['received_quantity']),
                                    'batch_number' => $item['batch_number'],
                                    'expiry_date' => $item['expire_date'],
                                    'unit_cost' => $item['unit_cost'],
                                    'updated_at' => now()
                                ]);
                        } else {
                            DB::table('inventories')->insert([
                                'product_id' => $item['product_id'],
                                'warehouse_id' => $item['warehouse_id'],
                                'quantity' => $item['received_quantity'],
                                'batch_number' => $item['batch_number'],
                                'expiry_date' => $item['expire_date'],
                                'unit_cost' => $item['unit_cost'],
                                'location' => $item['location'],
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }
                        IssuedQuantity::create([
                            'product_id' => $item['product_id'],
                            'quantity' => $item['received_quantity'],
                            'unit_cost' => $item['unit_cost'],
                            'total_cost' => $item['total_cost'],
                            'warehouse_id' => $item['warehouse_id'],
                            'issued_date' => Carbon::now()->toDateString(),
                            'issued_by' => auth()->user()->id,
                        ]);
                    }

                }

                return response()->json('Packing list created successfully', 200);
            });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
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
    public function show(Request $request)
    {
        $suppliers = Supplier::get();
        
        return Inertia::render('Supplies/Show', [
            'suppliers' => $suppliers,
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


    public function storeLocation(Request $request){
        try {
            Location::create([
                'location' => $request->location
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function BackOrderstatusChange(Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'id' => 'required|exists:packing_list_differences,id',
                'packing_list_id' => 'required|exists:packing_lists,id',
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'status' => 'required',
                'action' => 'required|in:update,dispose'
            ]);

            DB::beginTransaction();

            // Find the difference record
            $difference = PackingListDifference::find($request->id);
            if (!$difference) {
                return response()->json('Back order record not found', 500);
            }

            // Find the packing list
            $packingList = PackingList::where('id', $request->packing_list_id)
                ->first();

            if (!$packingList) {
                return response()->json('Packing list not found', 500);
            }

            // Find the specific packing list item for this product
            $packingListItem = PackingList::where('id', $request->packing_list_id)
                ->where('product_id', $request->product_id)
                ->first();

            if (!$packingListItem) {
                return response()->json('Packing list item not found', 500);
            }

            if ($request->action === 'update' && $request->status === 'Missing') {
                // Update received quantity for missing items that are found
                $packingListItem->increment('quantity', $request->quantity);
                $packingListItem->save();
                 // update the inventory
                 $inventory = DB::table('inventories')
                 ->where('product_id', $packingListItem->product_id)
                 ->where('warehouse_id', $packingListItem->warehouse_id)
                 ->first();

                if ($inventory) {
                    DB::table('inventories')
                        ->where('id', $inventory->id)
                        ->update([
                            'quantity' => DB::raw('quantity + ' . $request->quantity),
                            'batch_number' => $packingListItem->batch_number,
                            'expiry_date' => $packingListItem->expire_date,
                            'unit_cost' => $packingListItem->unit_cost,
                            'updated_at' => now()
                        ]);
                } else {
                    DB::table('inventories')->insert([
                        'product_id' => $packingListItem->product_id,
                        'warehouse_id' => $packingListItem->warehouse_id,
                        'quantity' => $request->quantity,
                        'batch_number' => $packingListItem->batch_number,
                        'expiry_date' => $packingListItem->expire_date,
                        'unit_cost' => $packingListItem->unit_cost,
                        'location' => $packingListItem->location,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                IssuedQuantity::create([
                    'product_id' => $packingListItem['product_id'],
                    'quantity' => $request->quantity,
                    'unit_cost' => $packingListItem['unit_cost'],
                    'warehouse_id' => $packingListItem['warehouse_id'],
                    'total_cost' => (double) $packingListItem['unit_cost'] * (double) $request->quantity,
                    'issued_date' => Carbon::now()->toDateString(),
                    'issued_by' => auth()->user()->id,
                ]);
            } else if ($request->action === 'dispose') {
                // Create disposal record
                Disposal::create([
                    'product_id' => $request->product_id,
                    'packing_list_id' => $request->packing_list_id,
                    'purchase_order_id' => $packingList->purchase_order_id,
                    'quantity' => $request->quantity,
                    'disposed_by' => auth()->id(),
                    'disposed_at' => now(),
                    'status' => $request->status,
                    'note' => $request->note ?? null
                ]);
                // return response()->json("Disposed successfully", 200);
            }

            // Decrement the difference quantity
            $difference->decrement('quantity', $request->quantity);
            $difference->refresh();

            // Delete the difference record if quantity is 0
            if ($difference->quantity == 0) {
                $difference->delete();
            }

            DB::commit();
            return response()->json('Back order ' . ($request->action === 'update' ? 'updated' : 'disposed') . ' successfully', 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }
}
