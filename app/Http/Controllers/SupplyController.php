<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Events\InventoryUpdated;
use App\Models\Product;
use App\Models\Supply;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Models\PackingList;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\BackOrderHistory;
use App\Models\SupplyItem;
use App\Models\BackOrder;
use App\Models\IssuedQuantity;
use App\Models\Liquidate;
use App\Models\PackingListDifference;
use App\Models\Location;
use App\Models\Disposal;
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
            ->where('status', '!=', 'completed')
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

        // Calculate lead times as difference between packing list confirm_at and PO po_date
        // Only for non-completed purchase orders
        $leadTimes = DB::table('packing_lists as pl')
            ->join('purchase_orders as po', 'po.id', '=', 'pl.purchase_order_id')
            ->select(
                DB::raw('MAX(TIMESTAMPDIFF(MONTH, po.po_date, pl.confirmed_at)) as max_lead_time'),
                DB::raw('ROUND(AVG(TIMESTAMPDIFF(MONTH, po.po_date, pl.confirmed_at)), 1) as avg_lead_time'),
                DB::raw('MIN(TIMESTAMPDIFF(MONTH, po.po_date, pl.confirmed_at)) as low_lead_time'),
                DB::raw('COUNT(*) as total_pls')
            )
            ->where('pl.status', '=', 'approved')
            ->where('po.status', '!=', 'completed')
            ->first();

        // Get statistics for the cards
        $stats = [
            'total_items' => PurchaseOrder::count(),
            'total_cost' => PurchaseOrder::join('purchase_order_items', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
                ->sum(DB::raw('quantity * unit_cost')),
            'lead_times' => [
                'max' => round($leadTimes->max_lead_time ?? 0, 1) . ' Months',
                'avg' => round($leadTimes->avg_lead_time ?? 0, 1) . ' Months',
                'low' => round($leadTimes->low_lead_time ?? 0, 1) . ' Months',
            ],
            'pending_orders' => PurchaseOrder::where('status', 'pending')->count(),
            'back_orders' => PackingListDifference::count(), // Count the number of back orders instead of summing quantities
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
                                'product_name' => "kk",
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
        return inertia("Supplies/PurchaseO/Show", [
            'po' => $po
        ]);
    }

    public function newPackingList(Request $request){
        $purchaseOrders = PurchaseOrder::where('status', 'approved')->select('id','po_number','supplier_id','po_date','po_number','status')
            ->with(['supplier'])
            ->get();
        $warehouses = Warehouse::select('id', 'name')->get();
        return inertia("Supplies/PackingList", [
            'purchaseOrders' => $purchaseOrders,
            'warehouses' => $warehouses,
            'locations' => Location::get(),
        ]);
    }

    public function getBackOrder(Request $request, $id)
    {
        try {
            // Join packing_list_differences, products, and packing_lists
            $results = PackingListDifference::whereHas('packingList', function($query) use ($id) {
                $query->where('purchase_order_id', $id);
            })
                ->with('product:id,name,productID','packingList')
                ->get();

            return response()->json($results, 200);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    
    public function liquidate(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'id' => 'required|exists:packing_list_differences,id',
                'product_id' => 'required|exists:products,id',
                'packing_list_id' => 'required|exists:packing_lists,id',
                'quantity' => 'required|integer|min:1',
                'status' => 'required|string',
                'note' => 'nullable|string|max:255',
                'barcode' => 'nullable|string',
                'expire_date' => 'nullable|date',
                'batch_number' => 'nullable|string',
                'uom' => 'nullable|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB per file
            ]);
            
            // Start a database transaction
            DB::beginTransaction();
            
            // Get the packing list to include its number in the note
            $packingList = PackingList::find($request->packing_list_id);
            $packingListNumber = $packingList ? $packingList->packing_list_number : 'Unknown';
            
            // Generate note based on condition and source
            $note = "PL ($packingListNumber) - {$request->status}";
            if ($request->note) {
                $note .= " - {$request->note}";
            }
            
            // Handle file attachments if any
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    $fileName = 'liquidate_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('attachments/liquidations'), $fileName);
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => '/attachments/liquidations/' . $fileName,
                        'type' => $file->getClientMimeType(),
                        'size' => filesize(public_path('attachments/liquidations/' . $fileName)),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }
            
            // Create a new liquidation record
            $liquidate = Liquidate::create([
                'product_id' => $request->product_id,
                'packing_list_id' => $request->packing_list_id,
                'liquidated_by' => auth()->id(),
                'liquidated_at' => Carbon::now(),
                'quantity' => $request->quantity,
                'status' => 'pending', // Default status is pending
                'note' => $note,
                'barcode' => $request->barcode,
                'expire_date' => $request->expire_date,
                'batch_number' => $request->batch_number,
                'uom' => $request->uom,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            ]);
            
            // Find and delete the record from PackingListDifference table
            $packingListDiff = PackingListDifference::find($request->id);
            if ($packingListDiff) {
                // Create a record in BackOrderHistory before deleting
                BackOrderHistory::create([
                    'packing_list_id' => $packingListDiff->packing_list_id,
                    'product_id' => $packingListDiff->product_id,
                    'quantity' => $packingListDiff->quantity,
                    'status' => 'Liquidated',
                    'note' => $request->note ?? 'Liquidated by ' . auth()->user()->name,
                    'performed_by' => auth()->id()
                ]);
                
                // Delete the record
                $packingListDiff->update([
                    'finalized' => 'Liquidated'
                ]);
            }
            
            // Commit the transaction
            DB::commit();
            
            return response()->json([
                'message' => 'Item has been liquidated successfully',
                'liquidate' => $liquidate
            ], 200);
            
        } catch (\Throwable $th) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function dispose(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'id' => 'required|exists:packing_list_differences,id',
                'product_id' => 'required|exists:products,id',
                'packing_list_id' => 'required|exists:packing_lists,id',
                'quantity' => 'required|integer|min:1',
                'status' => 'required|string',
                'note' => 'nullable|string|max:255',
                'barcode' => 'nullable|string',
                'expire_date' => 'nullable|date',
                'batch_number' => 'nullable|string',
                'uom' => 'nullable|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB per file
            ]);
            
            // Start a database transaction
            DB::beginTransaction();
            
            // Get the packing list to include its number in the note
            $packingList = PackingList::find($request->packing_list_id);
            $packingListNumber = $packingList ? $packingList->packing_list_number : 'Unknown';
            
            // Generate note based on condition and source
            $note = "PL ($packingListNumber) - {$request->status}";
            if ($request->note) {
                $note .= " - {$request->note}";
            }
            
            // Handle file attachments if any
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $index => $file) {
                    $fileName = 'liquidate_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('attachments/disposals'), $fileName);
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => '/attachments/disposals/' . $fileName,
                        'type' => $file->getClientMimeType(),
                        'size' => filesize(public_path('attachments/disposals/' . $fileName)),
                        'uploaded_at' => now()->toDateTimeString()
                    ];
                }
            }
            
            // Create a new liquidation record
            $disposal = Disposal::create([
                'product_id' => $request->product_id,
                'packing_list_id' => $request->packing_list_id,
                'disposed_by' => auth()->id(),
                'disposed_at' => Carbon::now(),
                'quantity' => $request->quantity,
                'status' => 'pending', // Default status is pending
                'note' => $note,
                'barcode' => $request->barcode,
                'expire_date' => $request->expire_date,
                'batch_number' => $request->batch_number,
                'uom' => $request->uom,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            ]);
            
            // Find and delete the record from PackingListDifference table
            $packingListDiff = PackingListDifference::find($request->id);
            if ($packingListDiff) {
                // Create a record in BackOrderHistory before deleting
                BackOrderHistory::create([
                    'packing_list_id' => $packingListDiff->packing_list_id,
                    'product_id' => $packingListDiff->product_id,
                    'quantity' => $packingListDiff->quantity,
                    'status' => 'Disposed',
                    'note' => $request->note ?? 'Disposed by ' . auth()->user()->name,
                    'performed_by' => auth()->id()
                ]);
                
                // Delete the record
                $packingListDiff->update([
                    'finalized' => 'Disposed'
                ]);
            }
            
            // Commit the transaction
            DB::commit();
            
            return response()->json([
                'message' => 'Item has been disposed successfully',
                'disposal' => $disposal
            ], 200);
            
        } catch (\Throwable $th) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }
    
    public function receive(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'id' => 'required|exists:packing_list_differences,id',
                'product_id' => 'required|exists:products,id',
                'packing_list_id' => 'required|exists:packing_lists,id',
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'quantity' => 'required|integer|min:1',
                'original_quantity' => 'required|integer|min:1',
            ]);
            
            // Start a database transaction
            DB::beginTransaction();
            
            // Find the packing list difference record
            $packingListDiff = PackingListDifference::find($request->id);
            
            if (!$packingListDiff) {
                return response()->json([
                    'message' => 'Back order item not found'
                ], 404);
            }
            
            // Calculate the remaining quantity
            $receivedQuantity = $request->quantity;
            $originalQuantity = $request->original_quantity;
            $remainingQuantity = $originalQuantity - $receivedQuantity;
            
            // Create a record in BackOrderHistory for the received items
            BackOrderHistory::create([
                'packing_list_id' => $packingListDiff->packing_list_id,
                'product_id' => $packingListDiff->product_id,
                'quantity' => $receivedQuantity,
                'status' => 'Received',
                'note' => $request->note ?? 'Received by ' . auth()->user()->name,
                'performed_by' => auth()->id()
            ]);
            
            // Update inventory with the received items
            $inventory = Inventory::where('product_id', $request->product_id)->first();
            
            if ($inventory) {
                $inventory->increment('quantity', $receivedQuantity);
                $inventory->save();
            } else {
                // Create a new inventory record if it doesn't exist
                Inventory::create([
                    'product_id' => $request->product_id,
                    'quantity' => $receivedQuantity,
                    'status' => 'active'
                ]);
            }
            
            // Update the packing list quantity
            $packingList = PackingList::find($request->packing_list_id);
            if ($packingList) {
                // Add the received quantity to the packing list quantity
                $packingList->quantity += $receivedQuantity;
                $packingList->save();
            }
            
            // Handle the packing list difference record based on remaining quantity
            if ($remainingQuantity <= 0) {
                // If nothing remains, delete the record
                $packingListDiff->delete();
            } else {
                // If some items remain, update the quantity
                $packingListDiff->quantity = $remainingQuantity;
                $packingListDiff->save();
            }
            
            // Commit the transaction
            DB::commit();
            
            return response()->json([
                'message' => "Successfully received {$receivedQuantity} items" . ($remainingQuantity > 0 ? ", {$remainingQuantity} items remaining" : ""),
                'received_quantity' => $receivedQuantity,
                'remaining_quantity' => $remainingQuantity
            ], 200);
            
        } catch (\Throwable $th) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            return response()->json([
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function backOrder(Request $request){
        $po = PurchaseOrder::select('id','po_number')->get();
        return inertia("Supplies/BackOrder", [
            'po' => $po
        ]);
    }

    public function getPO(Request $request, $id) {
        try {
            // Get purchase order items with left joins to related tables
            $items = DB::table('purchase_order_items as poi')
                ->select(
                    'poi.*',
                    'p.name as product_name',
                    'pl.barcode',
                    'poi.uom as po_uom',
                    'pl.id as packing_list_id',
                    'pl.quantity as received_quantity',
                    'pl.batch_number',
                    'pl.expire_date',
                    'pl.status as pl_status',
                    'w.name as warehouse_name',
                    'l.location as location_name',
                    'w.id as warehouse_id',
                    'l.id as location_id'
                )
                ->join('products as p', 'p.id', '=', 'poi.product_id')
                ->leftJoin('packing_lists as pl', 'pl.po_id', '=', 'poi.id')
                ->leftJoin('warehouses as w', 'w.id', '=', 'pl.warehouse_id')
                ->leftJoin('locations as l', 'l.id', '=', 'pl.location_id')
                ->where('poi.purchase_order_id', $id)
                ->get();
    
            // Group by purchase order item and transform data
            $transformedItems = collect($items)
                ->groupBy('id')
                ->map(function($groupedItem) {
                    $firstItem = $groupedItem->first();
                    $received_quantity = $groupedItem->sum('received_quantity') ?: 0;
                    $remaining_quantity = $firstItem->quantity - $received_quantity;
    
                    // if ($remaining_quantity <= 0) {
                    //     return null;
                    // }
    
                    // Get the latest packing list status
                    $latestStatus = collect($groupedItem)->where('pl_status', '!=', null)->sortByDesc('created_at')->first();
                    $status = $latestStatus ? $latestStatus->pl_status : 'pending';
    
                    // Get all packing lists for this item
                    $packingLists = collect($groupedItem)->filter(function($item) {
                        return $item->packing_list_id !== null;
                    })->map(function($pl) {
                        return [
                            'id' => $pl->packing_list_id,
                            'quantity' => $pl->received_quantity,
                            'batch_number' => $pl->batch_number,
                            'barcode' => $pl->barcode,
                            'expire_date' => $pl->expire_date,
                            'warehouse_id' => $pl->warehouse_id,
                            'warehouse' => $pl->warehouse_id ? [
                                'id' => $pl->warehouse_id,
                                'name' => $pl->warehouse_name
                            ] : null,
                            'location_id' => $pl->location_id,
                            'location' => $pl->location_id ? [
                                'id' => $pl->location_id,
                                'name' => $pl->location_name
                            ] : null,
                            'status' => $pl->pl_status,
                            'uom' => $pl->po_uom,
                            'differences' => []
                        ];
                    })->values();
    
                    // Get the latest packing list for warehouse and location info
                    $latestPackingList = collect($groupedItem)->where('packing_list_id', '!=', null)->sortByDesc('created_at')->first();

                    // Get differences for this item's packing lists
                    $differences = [];
                    foreach ($packingLists as $pl) {
                        $plDifferences = DB::table('packing_list_differences')
                            ->where('packing_list_id', $pl['id'])
                            ->get()
                            ->map(function($diff) {
                                return [
                                    'id' => $diff->id,
                                    'quantity' => $diff->quantity,
                                    'status' => $diff->status,
                                    'created_at' => $diff->created_at
                                ];
                            })
                            ->toArray();
                        $differences = array_merge($differences, $plDifferences);
                    }

                    return [
                        'id' => $latestPackingList ? $latestPackingList->packing_list_id : null,
                        'product_id' => $firstItem->product_id,
                        'po_id' => $firstItem->id,
                        'quantity' => $firstItem->quantity,
                        'unit_cost' => $firstItem->unit_cost,
                        'total_cost' => $remaining_quantity * $firstItem->unit_cost,
                        'searchQuery' => $firstItem->product_name,
                        'barcode' => $firstItem->barcode,
                        'warehouse_id' => $latestPackingList ? $latestPackingList->warehouse_id : null,
                        'expire_date' => $latestPackingList ? $latestPackingList->expire_date : null,
                        'location_id' => $latestPackingList ? $latestPackingList->location_id : null,
                        'status' => $status,
                        'uom' => $firstItem->po_uom,
                        'batch_number' => $latestPackingList ? $latestPackingList->batch_number : null,
                        'fullfillment_rate' => round(($received_quantity / $firstItem->quantity) * 100, 2) . '%',
                        'received_quantity' => $received_quantity,
                        'mismatches' => $remaining_quantity,
                        'product' => [
                            'id' => $firstItem->product_id,
                            'name' => $firstItem->product_name,
                        ],
                        'warehouse' => $latestPackingList ? [
                            'id' => $latestPackingList->warehouse_id,
                            'name' => $latestPackingList->warehouse_name
                        ] : null,
                        'location' => $latestPackingList ? [
                            'id' => $latestPackingList->location_id,
                            'location' => $latestPackingList->location_name
                        ] : null,
                        'differences' => $differences
                    ];
                })->filter()->values();
    
            // Get the purchase order with supplier
            $po = PurchaseOrder::with('supplier')->findOrFail($id);
            
            // Convert to array and add items
            $result = $po->toArray();
            $result['items'] = $transformedItems;
    
            // Check for existing packing list number for this PO
            $existingPackingList = PackingList::where('purchase_order_id', $id)
                ->orderBy('created_at', 'desc')
                ->first();
    
            if ($existingPackingList) {
                $result['packing_list_number'] = $existingPackingList->packing_list_number;
                $result['ref_no'] = $existingPackingList->ref_no;
                $result['pk_date'] = $existingPackingList->pk_date;
            } else {
                $result['packing_list_number'] = sprintf("PKL-%s-001", substr($po->po_number, 3));
            }
    
            return response()->json($result, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    } 
    
    public function storePK(Request $request)
    {
        try {
            return DB::transaction(function() use ($request){
                $request->validate([
                    'purchase_order_id' => 'required',
                    'packing_list_number' => 'required',
                    'ref_no' => 'nullable',
                    'pk_date' => 'required',
                    'items' => 'required|array',
                    'items.*.product_id' => 'required',
                    'items.*.warehouse_id' => 'required',
                    'items.*.received_quantity' => 'required|numeric|min:0',
                    'items.*.po_id' => 'required|exists:purchase_order_items,id',
                    'items.*.id' => 'nullable',
                    'items.*.quantity' => 'required|numeric',
                    'items.*.barcode' => 'nullable',
                    'items.*.uom' => 'nullable',
                    'items.*.batch_number' => 'required',
                    'items.*.expire_date' => 'required',
                    'items.*.location_id' => 'required',
                    'items.*.unit_cost' => 'required|numeric',
                    'items.*.total_cost' => 'required|numeric',
                    'items.*.status' => 'required',
                    'items.*.differences' => 'nullable|array'
                ]);

                // Create packing list for each item
                foreach($request->items as $item){
                    if ($item['status'] == 'approved') {
                        continue;
                    }

                    $packingList = PackingList::updateOrCreate([
                        'id' => $item['id'] ?? null
                    ],[
                        'packing_list_number' => $request->packing_list_number,
                        'purchase_order_id' => $request->purchase_order_id,
                        'pk_date' => $request->pk_date,
                        'product_id' => $item['product_id'],
                        'ref_no' => $request->ref_no,
                        'po_id' => $item['po_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'quantity' => $item['received_quantity'],
                        'batch_number' => $item['batch_number'],
                        'barcode' => $item['barcode'],
                        'uom' => $item['uom'],
                        'expire_date' => $item['expire_date'],
                        'location_id' => $item['location_id'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost'],
                        'status' => $item['status'] ?? 'pending',
                        'confirmed_by' => auth()->user()->id,
                        'confirmed_at' => Carbon::now(),
                    ]);

                    // Clear existing differences if array is empty
                    if (empty($item['differences'])) {
                        $packingList->differences()->delete();
                        continue;
                    }

                    logger()->info($item);

                    // Handle differences if they exist
                    if (!empty($item['differences'])) {
                        foreach($item['differences'] as $diff){
                            if (!empty($diff['quantity'])) {
                                $packingList->differences()->updateOrCreate([
                                    'id' => $diff['id'] ?? null
                                ],[
                                    'quantity' => $diff['quantity'],
                                    'status' => $diff['status'] ?? 'Missing',
                                    'product_id' => $item['product_id'],
                                ]);
                            }
                        }
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
        $products = Product::select('id','name')->get();
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
            $po = PurchaseOrder::with('supplier','items.product:id,name','items.edited:id,name')->findOrFail($id);
            
            return inertia('Supplies/EditPo', [
                'products' => $products,
                'suppliers' => $suppliers,
                'po' => $po
            ]);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('supplies.index')->with('error', 'Purchase order not found.');
        } catch (\Throwable $th) {
            return redirect()->route('supplies.index')->with('error', $th->getMessage());
        }
    }

    public function storePO(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validate([
                    'id' => 'nullable|integer',
                    'supplier_id' => 'required|exists:suppliers,id',
                    'po_number' => 'required|unique:purchase_orders,po_number,' . $request->id,
                    'po_date' => 'required',
                    'original_po_no' => 'nullable',
                    'items' => 'required|array|min:1',
                    'items.*.id' => 'nullable|integer',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.unit_cost' => 'required|numeric|min:0',
                    'items.*.uom' => 'nullable',
                    'items.*.quantity' => 'required|integer|min:1',
                    'items.*.total_cost' => 'required|numeric|min:0',
                ]);

                // Check if this is a new purchase order or an update
                $isNew = !$request->id;
                
                $po = PurchaseOrder::updateOrCreate([
                    'id' => $request->id
                ], [
                    'po_number' => $validated['po_number'],
                    'original_po_no' => $validated['original_po_no'],
                    'supplier_id' => $validated['supplier_id'],
                    'po_date' => $validated['po_date'],
                    'created_by' => auth()->id()
                ]);

                // Process each item individually
                foreach ($validated['items'] as $item) {
                    // If this is an update (not a new PO) and the item exists, get the existing item to check for quantity changes
                    $existingItem = null;
                    if (!$isNew && isset($item['id'])) {
                        $existingItem = PurchaseOrderItem::find($item['id']);
                    }
                    
                    // Prepare the data for update or create
                    $itemData = [
                        'purchase_order_id' => $po->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'uom' => $item['uom'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost']
                    ];
                    
                    // Only track original quantity when editing an existing item
                    if (!$isNew && isset($item['id'])) {
                        // If this is an existing item and the quantity has changed, keep the original quantity
                        if ($existingItem && $existingItem->quantity != $item['quantity']) {
                            // If original_quantity is not set yet, use the existing quantity as the original
                            if (!$existingItem->original_quantity) {
                                $itemData['original_quantity'] = $existingItem->quantity;
                            }
                            // Otherwise keep the existing original_quantity
                            else {
                                $itemData['original_quantity'] = $existingItem->original_quantity;
                            }
                            
                            // Same logic for original_uom
                            if (!$existingItem->original_uom) {
                                $itemData['original_uom'] = $existingItem->uom;
                            } else {
                                $itemData['original_uom'] = $existingItem->original_uom;
                            }
                            
                            // Add edited_by to track who made the change
                            $itemData['edited_by'] = auth()->id();
                        }
                    }
                    
                    // Update or create the purchase order item
                    $poItem = PurchaseOrderItem::updateOrCreate(['id' => $item['id'] ?? null], $itemData);
                }
                
                return response()->json($request->id ? 'Purchase order updated successfully' : 'Purchase order created successfully', 200);
            });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
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
     * Display the specified supply.
     */
    public function reviewPO($id)
    {
        try {
            $po = PurchaseOrder::findOrFail($id);
            $po->reviewed_by = auth()->id();
            $po->reviewed_at = now();
            $po->status = "reviewed";
            $po->save();

            return response()->json('Purchase order has been marked for review');
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function rejectPO(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:1000'
            ]);

            return DB::transaction(function () use ($id, $validated) {
                $po = PurchaseOrder::findOrFail($id);
                
                if (!$po->reviewed_by || !$po->reviewed_at) {
                    return response()->json('Purchase order must be reviewed before it can be rejected', 422);
                }

                if ($po->approved_at) {
                    return response()->json('Cannot reject an approved purchase order', 422);
                }

                // Update PO status
                $po->status = 'rejected';
                $po->rejected_by = auth()->id();
                $po->rejected_at = now();
                $po->rejection_reason = $validated['reason'];
                $po->save();

                return response()->json('Purchase order has been rejected');
            });
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function approvePO($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $po = PurchaseOrder::with('items')->findOrFail($id);
                
                if (!$po->reviewed_by || !$po->reviewed_at) {
                    return response()->json('Purchase order must be reviewed before it can be approved', 422);
                }

                if ($po->rejected_at) {
                    return response()->json('Cannot approve a rejected purchase order', 422);
                }

                // Update PO status
                $po->status = 'approved';
                $po->approved_by = auth()->id();
                $po->approved_at = now();
                $po->save();
                
                // Reset original quantity and UOM for all items
                foreach ($po->items as $item) {
                    $item->update([
                        'original_quantity' => null,
                        'original_uom' => null,
                        'edited_by' => null
                    ]);
                }

                return response()->json('Purchase order has been approved and inventory has been updated');
            });
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
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
            $request->validate([
                'location' => 'required|string',
                'warehouse_id' => 'required|exists:warehouses,id'
            ]);
            
            $location = Location::create([
                'location' => $request->location,
                'warehouse_id' => $request->warehouse_id
            ]);
            
            // Load the warehouse relationship to ensure complete data
            $location->load('warehouse');
            
            return response()->json([
                'message' => 'Location created successfully',
                'location' => [
                    'id' => $location->id,
                    'location' => $location->location,
                    'warehouse_id' => $location->warehouse_id,
                    'warehouse' => $location->warehouse ? [
                        'id' => $location->warehouse->id,
                        'name' => $location->warehouse->name
                    ] : null
                ]
            ], 200);
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
                'action' => 'required'
            ]);

           logger()->info($validated);

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

            // Check if packing list is approved
            if ($packingListItem->status === 'approved') {
                return response()->json('Cannot modify back order for approved packing list', 500);
            }

            if ($request->status == 'Missing') {
                // Update received quantity for missing items that are found
                $packingListItem->increment('quantity', $request->quantity);
                $packingListItem->refresh();
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
                        'location_id' => $packingListItem->location_id,
                        'barcode' => $packingListItem->barcode,
                        'uom' => $packingListItem->uom,
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

            }else{
                Disposal::create([
                    'product_id' => $request->product_id,
                    'packing_list_id' => $request->packing_list_id,
                    'purchase_order_id' => $packingList->purchase_order_id,
                    'quantity' => $request->quantity,
                    'disposed_by' => auth()->id(),
                    'disposed_at' => now(),
                    'status' => 'Received',
                    'note' => $request->note ?? null
                ]);
                
            }
            BackOrderHistory::create([
                'product_id' => $request->product_id,
                'packing_list_id' => $request->packing_list_id,
                'purchase_order_id' => $packingList->purchase_order_id,
                'quantity' => $request->quantity,
                'status' => $request->status == "Missing" ? 'Received' : $request->status,
                'notes' => $request->note ?? null,
                'performed_by' => auth()->id()
            ]);
            

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

    public function showPK(Request $request){
        $pk = PackingList::with(['po_item', 'purchaseOrder.supplier'])
            ->select(
                'packing_lists.id',
                'packing_lists.packing_list_number',
                'packing_lists.total_cost',
                'packing_lists.created_at',
                'packing_lists.confirmed_at',
                'packing_lists.purchase_order_id',
                'packing_lists.po_id',
                'packing_lists.quantity as received_quantity',
                'purchase_order_items.quantity as original_quantity'
            )
            ->join('purchase_order_items', 'packing_lists.po_id', '=', 'purchase_order_items.id')
            ->orderBy('packing_lists.created_at', 'desc')
            ->get()
            ->groupBy('packing_list_number')
            ->map(function ($group) {
                $firstPL = $group->first();
                $totalItems = $group->count();

                // Count unique product IDs
                $uniqueProductIds = $group->pluck('product_id')->unique()->count();
                
                // Calculate fulfillment rate based on original PO quantity
                $fulfillmentRate = $group->avg(function($pl) {
                    $receivedQty = $pl->received_quantity ?? 0;
                    $originalQty = $pl->original_quantity ?? 0;
                    return $originalQty > 0 ? ($receivedQty / $originalQty) * 100 : 0;
                });

                // Calculate average lead time
                $avgLeadTime = $group->avg(function($pl) {
                    if ($pl->confirmed_at) {
                        return round(Carbon::parse($pl->confirmed_at)->diffInMonths(Carbon::now()), 1);
                    }
                    return 0;
                });

                return [
                    'packing_list_number' => $firstPL->packing_list_number,
                    'supplier' => $firstPL->purchaseOrder && $firstPL->purchaseOrder->supplier ? [
                        'name' => $firstPL->purchaseOrder->supplier->name,
                        'id' => $firstPL->purchaseOrder->supplier->id
                    ] : null,
                    'receiving_date' => $firstPL->created_at->format('M d, Y h:i A'),
                    'total_items' => $totalItems,
                    'total_product_ids' => $uniqueProductIds,
                    'total_cost' => $group->sum('total_cost'),
                    'avg_lead_time' => $avgLeadTime > 0 ? $avgLeadTime . ' Months' : 'N/A',
                    'fulfillment_rate' => round($fulfillmentRate, 2) . '%',
                    'needs_back_order' => $fulfillmentRate < 100
                ];
            });

        return inertia('Supplies/ShowPK', [
            'packing_list' => $pk
        ]);
    }

    public function show(Request $request){
        return inertia('Supplies/Show', [
            'suppliers' => Supplier::get()
        ]);
    }

    public function showBackOrder(Request $request){
        $history = BackOrderHistory::with('packingList')->get();
        return inertia('Supplies/ShowBackOrder', [
            'history' => $history
        ]);
    }

    public function editPK($pk)
    {
        $packing_list = PurchaseOrder::whereHas('packingLists', function($query) use ($pk){
            $query->where('packing_list_number', $pk);                
        })
        ->with([
            'packingLists' => function($query) {
                $query->select('packing_lists.*')
                      ->leftJoin('purchase_order_items', 'packing_lists.po_id', '=', 'purchase_order_items.id')
                      ->with([
                          'warehouse:id,name',
                          'product:id,name',
                          'location:id,location',
                          'po_item:id,quantity',
                          'differences'
                      ]);
            },
            'supplier'
        ])
        ->first();

        $warehouses = Warehouse::select('id', 'name')->get();
        $locations = Location::select('id', 'location')->get();

        return Inertia::render('Supplies/EditPK', [
            'packing_list' => $packing_list,
            'warehouses' => $warehouses,
            'locations' => $locations
        ]);
    }

    public function storePKLocation(Request $request)
    {
        try {
            $request->validate([
                'id' => 'nullable',
                'location' => 'required|string|unique:locations,location,' . $request->id
            ]);
    
            $location = Location::updateOrCreate([
                'id' => $request->id
            ],[
                'location' => $request->location
            ]);
    
            return response()->json([
                'message' => 'Location created successfully',
                'location' => $location
            ]);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function updatePK(Request $request)
    {
        try {
            return DB::transaction(function() use ($request){
                $request->validate([
                    'id' => 'required',
                    'pk_date' => 'nullable',
                    'items' => 'required|array',
                    'items.*.product_id' => 'required',
                    'items.*.id' => 'required',
                    'items.*.warehouse_id' => 'required',
                    'items.*.received_quantity' => 'required|numeric|min:0',
                    'items.*.quantity' => 'required|numeric',
                    'items.*.batch_number' => 'required',
                    'items.*.expire_date' => 'required',
                    'items.*.location_id' => 'required',
                    'items.*.unit_cost' => 'required|numeric',
                    'items.*.total_cost' => 'required|numeric',
                    'items.*.differences' => 'nullable|array',
                    'items.*.id' => 'nullable',
                    'items.*.quantity' => 'nullable',
                    'items.*.status' => 'nullable',
                ]);

                // Create packing list for each item
                foreach($request->items as $item){
                    // Skip items that are already approved
                    // if ($item['status'] == 'approved') {
                    //     continue;
                    // }
                   
                    $packingList = PackingList::updateOrCreate([
                        'id' => $item['id'] ?? null
                    ],[
                        'packing_list_number' => $item['packing_list_number'],
                        'purchase_order_id' => $request->id,
                        'pk_date' => $request->pk_date,
                        'product_id' => $item['product_id'],
                        'warehouse_id' => $item['warehouse_id'],
                        'quantity' => $item['received_quantity'],
                        'batch_number' => $item['batch_number'],
                        'pk_date' => $item['pk_date'],
                        'ref_no' => $item['ref_no'],
                        'expire_date' => $item['expire_date'],
                        'location_id' => $item['location_id'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost'],
                        'confirmed_by' => auth()->user()->id,
                        'confirmed_at' => Carbon::now(),
                    ]);
                    // Clear existing differences if array is empty
                    if (empty($item['differences'])) {
                        $packingList->differences()->delete();
                        continue;
                    }

                    // Handle differences if they exist
                    if (!empty($item['differences'])) {
                        foreach($item['differences'] as $diff){
                            if (!empty($diff['quantity'])) {
                                $packingList->differences()->updateOrCreate([
                                    'id' => $diff['id'] ?? null
                                ],[
                                    'quantity' => $diff['quantity'],
                                    'status' => $diff['status'] ?? 'Missing',
                                    'product_id' => $item['product_id'],
                                ]);
                            }
                        }
                    }
                }

                return response()->json('Packing list created successfully', 200);
            });
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }

    }    

    public function reviewPK(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'items' => 'required|array',
                'items.*.id' => 'required',
                'items.*.status' => 'required|in:reviewed'
            ]);

            foreach($request->items as $item) {
                PackingList::where('id', $item['id'])
                    ->update([
                        'status' => $item['status'],
                        'reviewed_by' => auth()->user()->id,
                        'reviewed_at' => now()
                    ]);
            }

            return response()->json('Items have been reviewed successfully');
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function approvePK(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'items' => 'required|array',
            'items.*.id' => 'required',
            'items.*.status' => 'required|string',
            'items.*.received_quantity' => 'required',
            'items.*.product_id' => 'required',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->items as $item) {
                // Get the full packing list item data
                $packingListItem = DB::table('packing_lists')
                    ->where('id', $item['id'])
                    ->first();

                if ($packingListItem) {
                    // Update packing list status
                    DB::table('packing_lists')
                        ->where('id', $item['id'])
                        ->update([
                            'status' => $item['status'],
                            'approved_at' => now(),
                            'approved_by' => auth()->user()->id
                        ]);

                    // Check if inventory exists for this product in this warehouse
                    $inventory = DB::table('inventories')
                        ->where('product_id', $packingListItem->product_id)
                        ->where('warehouse_id', $packingListItem->warehouse_id)
                        ->where('batch_number', $packingListItem->batch_number)
                        ->first();

                    $receivedQuantity = (int) $item['received_quantity'];
                    if ($receivedQuantity > 0) {
                        if ($inventory) {
                            // Update existing inventory
                            DB::table('inventories')
                                ->where('id', $inventory->id)
                                ->update([
                                    'quantity' => DB::raw('quantity + ' . $receivedQuantity),
                                    'batch_number' => $packingListItem->batch_number,
                                    'expiry_date' => $packingListItem->expire_date,
                                    'barcode' => $packingListItem->barcode,
                                    'unit_cost' => $packingListItem->unit_cost,
                                    'updated_at' => now()
                                ]);
                        } else {
                            // Create new inventory record
                            DB::table('inventories')->insert([
                                'product_id' => $packingListItem->product_id,
                                'warehouse_id' => $packingListItem->warehouse_id,
                                'quantity' => $receivedQuantity,
                                'batch_number' => $packingListItem->batch_number,
                                'barcode' => $packingListItem->barcode,
                                'uom' => $packingListItem->uom,
                                'expiry_date' => $packingListItem->expire_date,
                                'unit_cost' => $packingListItem->unit_cost,
                                'location_id' => $packingListItem->location_id,
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        }

                        // Record the issued quantity
                        DB::table('issued_quantities')->insert([
                            'product_id' => $packingListItem->product_id,
                            // 'barcode' => $packingListItem->barcode,
                            'quantity' => $receivedQuantity,
                            'unit_cost' => $packingListItem->unit_cost,
                            'total_cost' => $packingListItem->unit_cost * $receivedQuantity,
                            'warehouse_id' => $packingListItem->warehouse_id,
                            'issued_date' => now()->toDateString(),
                            'issued_by' => auth()->user()->id,
                        ]);
                    }

                // Only create a difference record if there's actually a difference in quantity
                $difference = (int) $packingListItem->quantity - (int) $item['received_quantity'];
                if ($difference > 0) {
                    // Check if a difference record already exists
                    $existingDiff = DB::table('packing_list_differences')
                        ->where('packing_list_id', $item['id'])
                        ->where('status', 'Missing')
                        ->first();
                    
                    if (!$existingDiff) {
                        DB::table('packing_list_differences')->insert([
                            'product_id' => $packingListItem->product_id,
                            'packing_list_id' => $item['id'],
                            'quantity' => $difference,
                            'status' => 'Missing',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            // Check if PO should be marked as completed
            $purchaseOrder = PurchaseOrder::with(['items', 'packingLists' => function($query) {
                $query->where('status', 'approved');
            }])->find($request->id);        
            if ($purchaseOrder) {
                // Get total quantities from PO items
                $poQuantities = $purchaseOrder->items->groupBy('product_id')
                    ->map(fn($items) => $items->sum('quantity'));
                
                // Get total quantities from approved packing lists only
                $plQuantities = $purchaseOrder->packingLists->groupBy('product_id')
                    ->map(fn($items) => $items->sum('quantity'));
                
                // Check if quantities match for all products
                $allReceived = $poQuantities->every(function($quantity, $productId) use ($plQuantities) {
                    return $plQuantities->get($productId, 0) >= $quantity;
                });

                // Update PO status if all items received
                if ($allReceived) {
                    $purchaseOrder->update(['status' => 'completed']);
                }
            }
        }

            DB::commit();
            return response()->json('Packing list items have been approved and inventory has been updated');
        }catch (\Throwable $th) {
            DB::rollBack();
            logger()->error('Error approving packing list: ' . $th->getMessage());
            return response()->json($th->getMessage(), 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $packingList = PackingList::findOrFail($id);

            $validated = $request->validate([
                'batch_number' => 'required|string',
                'location' => 'required|string',
                'expire_date' => 'required',
                'quantity' => 'required|numeric|min:0'
            ]);

            logger()->info($validated);

            $packingList->update([
                'batch_number' => $validated['batch_number'],
                'location' => $validated['location'],
                'quantity' => $validated['quantity'],
                'expire_date' => $validated['expire_date']
            ]);

            return redirect()->route('supplies.showPK')
                ->with('success', 'Packing list updated successfully');
        } catch (\Exception $e) {
            return redirect()->route('supplies.showPK')
                ->with('error', 'Error updating packing list: ' . $e->getMessage());
        }
    }

    public function locationsShow(Request $request){
        $locations = Location::get();
        return inertia("Supplies/Location", [
            "locations" => $locations
        ]);
    }

    public function locationEdit(Request $request, $id){
        $location = Location::find($id);
        return inertia("Supplies/LocationEdit", [
            "location" => $location
        ]);
    }

    public function loadItems($id){
        try {
            $items = PurchaseOrderItem::where('purchase_order_id', $id)->get();
            return response()->json($items, 200);
        } catch (\Throwable $th) {
            return response()->json($$th->getMessage(), 500);
        }
    }

    public function deletePackingListDiff($id)
    {
        try {
            // Get the difference record first to get its packing_list_id
            $difference = DB::table('packing_list_differences')->where('id', $id)->first();
            if (!$difference) {
                return response()->json(['message' => 'Difference not found'], 404);
            }

            // Get the packing list to get the purchase order item id
            $packingList = DB::table('packing_lists')->where('id', $difference->packing_list_id)->first();
            if (!$packingList) {
                return response()->json(['message' => 'Packing list not found'], 404);
            }

            // Delete the difference
            DB::table('packing_list_differences')->where('id', $id)->delete();

            // Get all remaining differences for ALL packing lists of this purchase order item
            $differences = DB::table('packing_list_differences as pld')
                ->join('packing_lists as pl', 'pl.id', '=', 'pld.packing_list_id')
                ->where('pl.po_id', $packingList->po_id)
                ->select('pld.*')
                ->get()
                ->map(function($diff) {
                    return [
                        'id' => $diff->id,
                        'quantity' => $diff->quantity,
                        'status' => $diff->status,
                        'created_at' => $diff->created_at
                    ];
                })
                ->values()
                ->toArray();

            return response()->json([
                'message' => 'Difference deleted successfully',
                'differences' => $differences
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting difference: ' . $e->getMessage()], 500);
        }
    }
}
