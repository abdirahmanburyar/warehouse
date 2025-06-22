<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\BackOrderHistory;
use App\Models\Disposal;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\IssuedQuantity;
use App\Models\Location;
use App\Models\PackingList;
use App\Models\PackingListDifference;
use App\Models\PoDocument;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\Supply;
use App\Models\SupplyItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\PackingListItem;
use App\Models\BackOrder;
use App\Models\ReceivedQuantity;
use App\Models\Liquidate;
use App\Http\Resources\SupplierResource;
use Inertia\Inertia;

class SupplyController extends Controller
{
    /**
     * Display a listing of the supplies.
     */
    public function index(Request $request)
    {
        $purchaseOrders = PurchaseOrder::with(['supplier'])
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
                $query->whereHas('supplier', function($query) use ($request) {
                    $query->where('name', 'like', "%{$request->supplier}%");
                });
            })
            ->when($request->filled('status'), function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->orderBy('po_date', 'desc')
            ->orderBy('created_at', 'desc');

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

        $purchaseOrders = $purchaseOrders->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $purchaseOrders->setPath(url()->current()); // Force Laravel to use full URLs


        return Inertia::render('Supplies/Index', [
            'purchaseOrders' => PurchaseOrderResource::collection($purchaseOrders),
            'filters' => $request->only('search', 'page','per_page', 'supplier','status'),
            'suppliers' => Supplier::pluck('name')->toArray(),
            'stats' => $stats
        ]);
    }

    public function create(Request $request){
        return inertia('Supplies/Create');
    }

    public function showPO(Request $request, $id){
        $po = PurchaseOrder::with('items.product','supplier','documents','approvedBy','rejectedBy','reviewedBy')->find($id);
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
            $results = PackingListDifference::whereNull('finalized')->whereHas('packingListItem', function($query) use ($id) {
                $query->where('packing_list_id', $id);
            })
                ->with('product:id,name,productID','packingListItem.packingList:id,packing_list_number')
                ->get();

            return response()->json($results, 200);

        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
    
    public function deleteDocument(Request $request, $id)
    {
        try {
            $document = PoDocument::findOrFail($id);
            
            // Delete the physical file
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            
            // Delete the database record
            $document->delete();
            
            return response()->json(['message' => 'Document deleted successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting document: ' . $e->getMessage()], 500);
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

            $item = PackingListDifference::where('id', $request->id)->with('packingListItem')->first();
            
            // Create a new liquidation record
            $liquidate = Liquidate::create([
                'product_id' => $request->product_id,
                'packing_listitem_id' => $item->packing_listitem_id,
                'liquidated_by' => auth()->id(),
                'liquidated_at' => Carbon::now(),
                'quantity' => $request->quantity,
                'status' => 'pending', // Default status is pending
                'note' => $note,
                'barcode' => $item->packingListItem->barcode,
                'expire_date' => $item->packingListItem->expire_date,
                'batch_number' => $item->packingListItem->batch_number,
                'uom' => $item->packingListItem->uom,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            ]);
            
            // Find and delete the record from PackingListDifference table
            if ($item) {
                // Create a record in BackOrderHistory before deleting
                BackOrderHistory::create([
                    'packing_list_id' => $request->packing_list_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'status' => 'Liquidated',
                    'note' => $request->note ?? 'Liquidated by ' . auth()->user()->name,
                    'performed_by' => auth()->id()
                ]);
                
                // // Delete the record
                // if ($item->quantity == $request->quantity) {
                // } else {
                //     $item->decrement('quantity', $request->quantity);
                // }
                $item->update([
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
                'packing_listitem_id' => 'required|exists:packing_list_items,id',                
                'note' => 'nullable|string|max:255',
                'quantity' => "required|min:1",
                'attachments' => 'nullable|array',
                'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // Max 10MB per file
            ]);

            
            // Start a database transaction
            DB::beginTransaction();
            
            // Get the packing list to include its number in the note
            $item = PackingListItem::with('packingList')->find($request->packing_listitem_id);
            $packingListNumber = $item ? $item->packingList->packing_list_number : 'Unknown';
            
            // Generate note based on condition and source
            $note = "PL ($packingListNumber) - {$item->status}";
            if ($request->note) {
                $note .= " - {$item->note}";
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
                'product_id' => $item->product_id,
                'packing_listitem_id' => $request->packing_listitem_id,
                'disposed_by' => auth()->id(),
                'disposed_at' => Carbon::now(),
                'quantity' => $request->quantity,
                'status' => 'pending', // Default status is pending
                'note' => $note,
                'barcode' => $item->barcode,
                'expire_date' => $item->expire_date,
                'batch_number' => $item->batch_number,
                'uom' => $item->uom,
                'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            ]);
            
            // Find and delete the record from PackingListDifference table
            $packingListDiff = PackingListDifference::find($request->id);
            if ($packingListDiff) {
                // Create a record in BackOrderHistory before deleting
                BackOrderHistory::create([
                    'packing_list_id' => $packingListDiff->packing_list_id,
                    'product_id' => $packingListDiff->product_id,
                    'quantity' => $request->quantity,
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
                'packing_listitem_id' => 'required|exists:packing_list_items,id',
                'quantity' => 'required|integer|min:1',
                'original_quantity' => 'required|integer|min:1',
            ]);
            
            // Start a database transaction
            DB::beginTransaction();
            
            // Find the packing list difference record
            $packingListDiff = PackingListDifference::with('packingListItem')->find($request->id);
            
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
            $inventory = InventoryItem::where('product_id', $request->product_id)
                ->where('batch_number', $packingListDiff->packingListItem->batch_number)
                ->first();
            
            if ($inventory) {
                $inventory->increment('quantity', $receivedQuantity);
                $inventory->save();
                ReceivedQuantity::create([
                    'quantity' => $receivedQuantity,
                    'received_by' => auth()->id(),
                    'received_at' => now(),
                    'transfer_id' => null,
                    'product_id' => $inventory->product_id,
                    'packing_list_id' => $packingListDiff->packingListItem->packing_list_id,
                    'uom' => $inventory->uom,
                    'barcode' => $inventory->barcode,
                    'batch_number' => $inventory->batch_number,
                    'warehouse_id' => $inventory->warehouse_id,
                    'expiry_date' => $inventory->expire_date,
                    'unit_cost' => $inventory->unit_cost,
                    'total_cost' => $inventory->unit_cost * $receivedQuantity
                ]);
            } else {
                // Create a new inventory record if it doesn't exist
                $inventory = Inventory::firstOrCreate([
                    'product_id' => $request->product_id,
                ], [
                    'quantity' => 0,
                ]);
                $inventory->increment('quantity', $receivedQuantity);
                $inventory->save();
                
                $inventory = InventoryItem::create([
                    'inventory_id' => $inventory->id,
                    'quantity' => $receivedQuantity,
                    'batch_number' => $packingListDiff->packingListItem->batch_number,
                    'expiry_date' => $packingListDiff->packingListItem->expire_date,
                    'barcode' => $packingListDiff->packingListItem->barcode,
                    'warehouse_id' => $packingListDiff->packingListItem->warehouse_id,
                    'location' => $packingListDiff->packingListItem->location,
                    'unit_cost' => $packingListDiff->packingListItem->unit_cost,
                    'total_cost' => $packingListDiff->packingListItem->unit_cost * $receivedQuantity,
                    'uom' => $packingListDiff->packingListItem->uom,
                    'status' => 'active'
                ]);
                ReceivedQuantity::create([
                    'quantity' => $receivedQuantity,
                    'received_by' => auth()->id(),
                    'received_at' => now(),
                    'transfer_id' => null,
                    'product_id' => $inventory->product_id,
                    'packing_list_id' => $validated['packing_list_id'],
                    'uom' => $inventory->uom,
                    'warehouse_id' => $inventory->warehouse_id,
                    'barcode' => $inventory->barcode,
                    'batch_number' => $inventory->batch_number,
                    'expiry_date' => $inventory->expire_date,
                    'unit_cost' => $inventory->unit_cost,
                    'total_cost' => $inventory->unit_cost * $receivedQuantity
                ]);
            }
            
            // Update the packing list quantity
            $packingList = PackingListItem::find($request->packing_listitem_id);
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
        $packingList = PackingList::whereHas('items.differences')->select('id','packing_list_number')->with('purchaseOrder:id,po_number')->get();
        return inertia("Supplies/BackOrder", [
            'packingList' => $packingList
        ]);
    }

    public function getPO(Request $request, $id)
    {
        try {
            // Fetch the purchase order with supplier
            $po = PurchaseOrder::with('supplier')->findOrFail($id);
    
            // Get the latest packing list for this PO
            $latestPackingList = PackingList::where('purchase_order_id', $id)
                ->latest('created_at')
                ->first();
    
            // Fetch purchase order items with related products and packing list items using joins
            $items = DB::table('purchase_order_items as poi')
                ->select(
                    'poi.id',
                    'poi.purchase_order_id',
                    'poi.product_id',
                    'poi.quantity',
                    'poi.unit_cost as po_unit_cost',
                    'poi.total_cost as po_total_cost',
                    'poi.uom as po_uom',
                    'p.name as product_name',
                    'pli.barcode',
                    'pl.id as packing_list_id',
                    'pli.id as packing_list_item_id',
                    'pli.location', 
                    'pli.quantity as received_quantity',
                    'pli.batch_number',
                    'pli.expire_date',
                    'pl.status as pl_status',
                    'w.name as warehouse_name',
                    'w.id as warehouse_id',
                    'pli.unit_cost',
                    'pli.total_cost',
                    'pl.created_at as pl_created_at'
                )
                ->join('products as p', 'p.id', '=', 'poi.product_id')
                ->leftJoin('packing_lists as pl', function ($join) use ($id) {
                    $join->on('pl.purchase_order_id', '=', DB::raw($id));
                })
                ->leftJoin('packing_list_items as pli', function ($join) {
                    $join->on('pli.po_item_id', '=', 'poi.id')
                         ->on('pli.packing_list_id', '=', 'pl.id');
                })
                ->leftJoin('warehouses as w', 'w.id', '=', 'pli.warehouse_id')
                ->where('poi.purchase_order_id', $id)
                ->get();
    
            // Group by purchase order item id to aggregate packing list data
            $groupedItems = $items->groupBy('id');
    
            $transformedItems = $groupedItems->map(function ($group) {
                $first = $group->first();
    
                $totalReceivedQty = $group->sum('received_quantity') ?? 0;
                $remainingQty = $first->quantity - $totalReceivedQty;
    
                // Determine latest packing list status based on created_at timestamp
                $latestPLItem = $group->filter(fn($item) => $item->pl_status !== null)
                                      ->sortByDesc('pl_created_at')
                                      ->first();
    
                $status = $latestPLItem->pl_status ?? 'pending';
    
                // Build packing list details array
                $packingLists = $group->filter(fn($item) => $item->packing_list_id !== null)
                    ->map(function ($plItem) {
                        return [
                            'id' => $plItem->packing_list_id,
                            'quantity' => $plItem->received_quantity,
                            'batch_number' => $plItem->batch_number,
                            'barcode' => $plItem->barcode,
                            'expire_date' => $plItem->expire_date,
                            'warehouse_id' => $plItem->warehouse_id,
                            'warehouse' => $plItem->warehouse_id ? [
                                'id' => $plItem->warehouse_id,
                                'name' => $plItem->warehouse_name,
                            ] : null,
                            'location' => $plItem->location,
                            'status' => $plItem->pl_status,
                            'uom' => $plItem->po_uom,
                            'differences' => [] // to be filled later
                        ];
                    })->values();
    
                // Fetch all differences for packing lists of this PO item in a single query
                $plIds = $packingLists->pluck('id')->all();
                $differences = DB::table('packing_list_differences')
                    ->whereIn('packing_list_id', $plIds)
                    ->get()
                    ->map(function ($diff) {
                        return [
                            'id' => $diff->id,
                            'quantity' => $diff->quantity,
                            'status' => $diff->status,
                            'created_at' => $diff->created_at,
                        ];
                    })
                    ->groupBy('packing_list_id');
    
                // Attach differences to packing lists
                $packingLists = $packingLists->map(function ($pl) use ($differences) {
                    $pl['differences'] = $differences[$pl['id']] ?? [];
                    return $pl;
                });
    
                // Use latest packing list for warehouse and location info
                $latestPL = $group->filter(fn($item) => $item->packing_list_id !== null)
                                  ->sortByDesc('pl_created_at')
                                  ->first();
    
                return [
                    'id' => $latestPL->packing_list_id ?? null,
                    'product_id' => $first->product_id,
                    'po_item_id' => $first->id,
                    'quantity' => $first->quantity,
                    'unit_cost' => $first->po_unit_cost,
                    'total_cost' => $remainingQty * $first->po_unit_cost,
                    'searchQuery' => $first->product_name,
                    'barcode' => $first->barcode,
                    'warehouse_id' => $latestPL->warehouse_id ?? null,
                    'expire_date' => $latestPL->expire_date ?? null,
                    'location' => $latestPL->location ?? null,
                    'status' => $status,
                    'uom' => $first->po_uom,
                    'batch_number' => $latestPL->batch_number ?? null,
                    'fullfillment_rate' => $first->quantity > 0
                        ? round(($totalReceivedQty / $first->quantity) * 100, 2) . '%'
                        : '0%',
                    'received_quantity' => $totalReceivedQty,
                    'mismatches' => $remainingQty,
                    'product' => [
                        'id' => $first->product_id,
                        'name' => $first->product_name,
                    ],
                    'warehouse' => $latestPL ? [
                        'id' => $latestPL->warehouse_id,
                        'name' => $latestPL->warehouse_name,
                    ] : null,
                    'packing_lists' => $packingLists,
                ];
            })->values();
    
            // Prepare response
            $result = $po->toArray();
            $result['items'] = $transformedItems;
    
            if ($latestPackingList) {
                $result['packing_list_number'] = $latestPackingList->packing_list_number;
                $result['ref_no'] = $latestPackingList->ref_no;
                $result['pk_date'] = $latestPackingList->pk_date;
                $result['status'] = $latestPackingList->status;
            } else {
                $result['packing_list_number'] = sprintf("PKL-%s-001", substr($po->po_number, 3));
                $result['status'] = 'pending';
                $result['ref_no'] = "";
                $result['pk_date'] = "";
            }
    
            return response()->json($result, 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
    public function storePK(Request $request)
    {
        try {
            return DB::transaction(function() use ($request){
                $validated = $request->validate([
                    'id' => 'nullable',
                    'id' => 'required|exists:purchase_orders,id',
                    'packing_list_number' => 'required',
                    'ref_no' => 'nullable',
                    'pk_date' => 'required|date',
                    'items' => 'required|array',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.warehouse_id' => 'required|exists:warehouses,id',
                    'items.*.received_quantity' => 'required|numeric|min:0',
                    'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
                    'items.*.id' => 'nullable',
                    'items.*.quantity' => 'required|numeric',
                    'items.*.barcode' => 'nullable|string',
                    'items.*.uom' => 'required|string',
                    'items.*.batch_number' => 'required|string',
                    'items.*.expire_date' => 'required|date',
                    'items.*.location' => 'required|string',
                    'items.*.unit_cost' => 'required|numeric|min:0',
                    'items.*.total_cost' => 'required|numeric|min:0',
                    'items.*.status' => 'required|in:pending,approved,rejected',
                    'items.*.differences' => 'nullable|array',
                    'items.*.differences.*.id' => 'nullable',
                    'items.*.differences.*.quantity' => 'required_with:items.*.differences|numeric|min:1',
                    'items.*.differences.*.status' => 'required_with:items.*.differences|in:Expired,Damaged,Missing,Low quality,Lost'
                ]);

                $request['purchase_order_id'] = $request->id;
                
                // Create or update the packing list
                $packingList = PackingList::updateOrCreate(
                    ['id' => $request->id],
                    [
                        'packing_list_number' => $request->packing_list_number,
                        'purchase_order_id' => $request->purchase_order_id,
                        'ref_no' => $request->ref_no,
                        'pk_date' => $request->pk_date,
                        'status' => 'pending',
                        'confirmed_by' => auth()->id(),
                        'confirmed_at' => now(),
                    ]
                );

                // Process each item
                foreach($request->items as $item) {
                    // Create or update packing list item
                    $packingListItem = PackingListItem::updateOrCreate(
                        [
                            'id' => $item['id'] ?? null,
                            'packing_list_id' => $packingList->id,
                            'po_item_id' => $item['po_item_id']
                        ],
                        [
                            'product_id' => $item['product_id'],
                            'warehouse_id' => $item['warehouse_id'],
                            'location' => $item['location'],
                            'barcode' => $item['barcode'],
                            'batch_number' => $item['batch_number'],
                            'quantity' => $item['received_quantity'],
                            'uom' => $item['uom'],
                            'expire_date' => $item['expire_date'],
                            'unit_cost' => $item['unit_cost'],
                            'total_cost' => $item['unit_cost'] * $item['received_quantity']
                        ]
                    );

                    // Handle differences
                    if (!empty($item['differences'])) {
                        foreach ($item['differences'] as $diff) {
                            $packingListItem->differences()->updateOrCreate(
                                ['id' => $diff['id'] ?? null],
                                [
                                    'product_id' => $item['product_id'],
                                    'quantity' => $diff['quantity'],
                                    'status' => $diff['status']
                                ]
                            );
                        }
                    } else {
                        // Delete any existing differences if the array is empty
                        $packingListItem->differences()
                            ->where('product_id', $item['product_id'])
                            ->delete();
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
            $po = PurchaseOrder::with('supplier','items.product:id,name','items.edited:id,name')->find($id);
            return inertia('Supplies/EditPo', [
                'products' => $products,
                'suppliers' => $suppliers,
                'po' => $po
            ]);
        } catch (\Throwable $th) {
            return inertia('Supplies/EditPo', [
                'error' => $th->getMessage()
            ]);
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
                    'po_date' => 'required|date',
                    'original_po_no' => 'nullable',
                    'notes' => 'nullable',
                    'items' => 'required|array|min:1',
                    'items.*.id' => 'nullable|integer',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.unit_cost' => 'required|numeric|min:0',
                    'items.*.uom' => 'nullable',
                    'items.*.quantity' => 'required|integer|min:1',
                    'items.*.total_cost' => 'required|numeric|min:0'
                ]);
                
                $po = PurchaseOrder::updateOrCreate([
                    'id' => $request->id
                ], [
                    'po_number' => $validated['po_number'],
                    'notes' => $validated['notes'],
                    'original_po_no' => $validated['original_po_no'],
                    'po_date' => $validated['po_date'],
                    'supplier_id' => $validated['supplier_id'],
                    'created_by' => auth()->id()
                ]);


                // Process each item individually
                foreach ($validated['items'] as $item) {
                    // Prepare the data for update or create
                    if($item['product_id'] == null) continue;
                    $itemData = [
                        'purchase_order_id' => $po->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'uom' => $item['uom'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost']
                    ];
                    
                    // Only track original quantity when editing an existing item
                    if (isset($item['id'])) {
                        $existingItem = PurchaseOrderItem::find($item['id']);
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
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            // Adjust inventory
           $po = PurchaseOrder::find($id);
           if($po->status != 'pending') {
            return response()->json("This P.O can be not deleted, becouse its ". $po->status, 500);
           }
           $po->items()->delete();
           $po->delete();
            DB::commit();
            return response()->json('Supply deleted successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function uploadDocument(Request $request, $id)
    {
        try {
            $po = PurchaseOrder::find($id);
            if (!$po) {
                return response()->json('Purchase order not found', 404);
            }

            $request->validate([
                'document' => 'required|file|mimes:pdf'
            ],[
                'document.required' => 'Document is required',
                'document.file' => 'Document must be a file',
                'document.mimes' => 'Document must be a PDF file'
            ]);

            $index = $po->documents()->count() + 1;
            $file = $request->file('document');
            $fileName = 'purchase_order_' . time() . '_' . $index . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('attachments/purchase_orders'), $fileName);

            $po->documents()->create([
                'purchase_order_id' => $po->id,
                'document_type' => 'purchase_order',
                'file_name' => $file->getClientOriginalName(),
                'file_path' => '/attachments/purchase_orders/' . $fileName,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => filesize(public_path('attachments/purchase_orders/' . $fileName)),
                'uploaded_by' => auth()->id(),
                'uploaded_at' => now()->toDateTimeString()
            ]);

            return response()->json('Document uploaded successfully', 200);

        } catch (\Throwable $th) {
            return response()->json('Upload failed: The uploaded file could not be saved.', 500);
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
                'warehouse' => 'required|string'
            ]);
            
            $location = Location::create([
                'location' => $request->location,
                'warehouse' => $request->warehouse
            ]);
            
            return response()->json([
                'message' => 'Location created successfully',
                'location' => [
                    'id' => $location->id,
                    'location' => $location->location,
                    'warehouse' => $location->warehouse
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function showPK(Request $request){
        $pk = PackingList::with(['items.product', 'items.differences', 'purchaseOrder.supplier'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('packing_list_number')
            ->map(function ($group) {
                $firstPL = $group->first();
                
                // Get all items across all packing lists in the group
                $allItems = $group->flatMap(function($pl) {
                    return $pl->items;
                });
                
                // Count total items and unique products
                $totalItems = $allItems->count();
                $uniqueProductIds = $allItems->pluck('product_id')->unique()->count();
                
                // Calculate total received and original quantities
                $totalReceivedQty = $allItems->sum('quantity');
                $totalOriginalQty = $allItems->sum(function($item) {
                    return $item->purchaseOrderItem ? $item->purchaseOrderItem->quantity : 0;
                });
                
                // Calculate fulfillment rate
                $fulfillmentRate = $totalOriginalQty > 0 ? ($totalReceivedQty / $totalOriginalQty) * 100 : 0;
                
                // Calculate total cost
                $totalCost = $allItems->sum('total_cost');
                
                // Calculate average lead time
                $avgLeadTime = $group->avg(function($pl) {
                    if ($pl->confirmed_at) {
                        return round(Carbon::parse($pl->confirmed_at)->diffInMonths(Carbon::now()), 1);
                    }
                    return 0;
                });
                
                // Count total differences (issues)
                $totalDifferences = $allItems->flatMap(function($item) {
                    return $item->differences;
                })->count();

                return [
                    'id' => $firstPL->id,
                    'packing_list_number' => $firstPL->packing_list_number,
                    'supplier' => $firstPL->purchaseOrder && $firstPL->purchaseOrder->supplier ? [
                        'name' => $firstPL->purchaseOrder->supplier->name,
                        'id' => $firstPL->purchaseOrder->supplier->id
                    ] : null,
                    'receiving_date' => $firstPL->created_at,
                    'total_items' => $totalItems,
                    'total_product_ids' => $uniqueProductIds,
                    'total_cost' => $totalCost,
                    'avg_lead_time' => $avgLeadTime > 0 ? $avgLeadTime . ' Months' : 'N/A',
                    'fulfillment_rate' => round($fulfillmentRate, 2) . '%',
                    'needs_back_order' => $totalDifferences > 0,
                    'status' => $firstPL->status,
                    'ref_no' => $firstPL->ref_no,
                    'pk_date' => $firstPL->pk_date,
                    'total_differences' => $totalDifferences
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
        $history = BackOrderHistory::with('packingList', 'product')->get();
        return inertia('Supplies/ShowBackOrder', [
            'history' => $history
        ]);
    }

    public function editPK($id)
    {
        $packing_list = PackingList::with([
            'items.warehouse:id,name',
            'items.product:id,name',
            'items.purchaseOrderItem:id,quantity',
            'items.differences',
            'purchaseOrder.supplier'
        ])
        ->find($id);

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
                    'pk_date' => 'required|date',
                    'packing_list_number' => 'required',
                    'purchase_order_id' => 'required|exists:purchase_orders,id',
                    'ref_no' => 'nullable',
                    'items' => 'required|array',
                    'items.*.id' => 'required|exists:packing_list_items,id',
                    'items.*.product_id' => 'required|exists:products,id',
                    'items.*.warehouse_id' => 'required|exists:warehouses,id',
                    'items.*.po_item_id' => 'required|exists:purchase_order_items,id',
                    'items.*.barcode' => 'nullable',
                    'items.*.batch_number' => 'required',
                    'items.*.location' => 'required|string',
                    'items.*.quantity' => 'required|numeric|min:0',
                    'items.*.uom' => 'required',
                    'items.*.expire_date' => 'required|date',
                    'items.*.unit_cost' => 'required|numeric|min:0',
                    'items.*.total_cost' => 'required|numeric|min:0',
                    'items.*.differences' => 'nullable|array',
                    'items.*.differences.*.id' => 'nullable|exists:packing_list_differences,id',
                    'items.*.differences.*.quantity' => 'required|numeric|min:0',
                    'items.*.differences.*.status' => 'required|in:Missing,Damaged,Expired'
                ]);

                // Update main packing list
                $packingList = PackingList::findOrFail($request->id);
                $packingList->update([
                    'pk_date' => $request->pk_date,
                    'ref_no' => $request->ref_no,
                    'confirmed_by' => auth()->user()->id,
                    'confirmed_at' => now(),
                ]);

                // Update each item
                foreach($request->items as $item) {
                    $packingListItem = $packingList->items()->findOrFail($item['id']);
                    
                    $packingListItem->update([
                        'warehouse_id' => $item['warehouse_id'],
                        'location' => $item['location'],
                        'quantity' => $item['quantity'],
                        'batch_number' => $item['batch_number'],
                        'barcode' => $item['barcode'],
                        'expire_date' => $item['expire_date'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['total_cost'],
                    ]);

                    // Handle differences
                    if (empty($item['differences'])) {
                        // If no differences provided, delete existing ones
                        $packingListItem->differences()->delete();
                    } else {
                        // Get existing difference IDs
                        $existingDiffIds = $packingListItem->differences()->pluck('id')->toArray();
                        $newDiffIds = [];

                        // Update or create differences
                        foreach ($item['differences'] as $diff) {
                            $difference = $packingListItem->differences()
                                ->updateOrCreate(
                                    ['id' => $diff['id'] ?? null],
                                    [
                                        'quantity' => $diff['quantity'],
                                        'status' => $diff['status'],
                                        'product_id' => $item['product_id']
                                    ]
                                );
                            $newDiffIds[] = $difference->id;
                        }

                        // Delete differences that weren't updated or created
                        $packingListItem->differences()
                            ->whereIn('id', array_diff($existingDiffIds, $newDiffIds))
                            ->delete();
                    }
                }

                return response()->json('Packing list updated successfully', 200);
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
                'status' => 'required|in:reviewed'
            ]);

            PackingList::where('id', $request->id)
                ->update([
                    'status' => $request->status,
                    'reviewed_by' => auth()->user()->id,
                    'reviewed_at' => now()
                ]);

            return response()->json('Items have been reviewed successfully', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    // approve packing list and release to the inventory
    public function approvePK(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:packing_lists,id',
            'status' => 'required|in:approved',
            'items' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $packingList = PackingList::with('items.purchaseOrderItem')->findOrFail($request->id);

            foreach ($request->items as $itemData) {
                $pli = PackingListItem::findOrFail($itemData['id']);
                $recvQty = (int)$itemData['quantity'];
                if ($recvQty <= 0) continue;

                // 1️⃣ Upsert Inventory
                $inventory = Inventory::firstOrCreate(
                    [
                        'product_id' => $pli->product_id,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );
                $inventory->increment('quantity', $recvQty);

                // 2️⃣ Create or update InventoryItem batch record
                $inventoryItem = InventoryItem::firstOrNew([
                    'inventory_id'   => $inventory->id,
                    'batch_number'   => $pli->batch_number,
                    'warehouse_id'   => $pli->warehouse_id,
                ]);

                // fill or update fields
                $inventoryItem->fill([
                    'product_id'    => $pli->product_id,
                    'quantity'      => ($inventoryItem->exists ? $inventoryItem->quantity : 0) + $recvQty,
                    'expiry_date'   => $pli->expire_date,
                    'barcode'       => $pli->barcode,
                    'location'      => $pli->location,
                    'uom'           => $pli->uom,
                    'unit_cost'     => $pli->unit_cost,
                    'total_cost'    => $pli->unit_cost * $inventoryItem->quantity,
                ]);
                $inventoryItem->save();

                // 3️⃣ Track received quantities
                ReceivedQuantity::create([
                    'quantity'         => $recvQty,
                    'received_by'      => auth()->id(),
                    'received_at'      => now(),
                    'product_id'       => $pli->product_id,
                    'packing_list_id'  => $pli->packing_list_id,
                    'expiry_date'      => $pli->expire_date,
                    'uom'              => $pli->uom,
                    'warehouse_id'     => $pli->warehouse_id,
                    'barcode'          => $pli->barcode,
                    'batch_number'     => $pli->batch_number,
                    'unit_cost'        => $pli->unit_cost,
                    'total_cost'       => $pli->unit_cost * $recvQty,
                ]);

                // 4️⃣ Check for quantity differences
                $poQty = $pli->purchaseOrderItem->quantity ?? 0;
                $diff = $pli->quantity - $poQty;
                if ($diff > 0) {
                    PackingListDifference::firstOrCreate(
                        [
                            'packing_listitem_id' => $pli->id,
                            'status'              => 'Missing'
                        ],
                        [
                            'product_id' => $pli->product_id,
                            'quantity'   => $diff,
                        ]
                    );
                }
            }

            // 5️⃣ Update packing list status
            $packingList->update([
                'status'         => $request->status,
                'approved_by'    => auth()->id(),
                'approved_at'    => now(),
            ]);

            // 6️⃣ Optionally close PO if fully received
            $po = PurchaseOrder::with('items', 'packingLists')
                ->find($packingList->purchase_order_id);

            if ($po) {
                $poQtys = $po->items->groupBy('product_id')
                    ->map(fn($it) => $it->sum('quantity'));
                $plQtys = $po->packingLists->where('status', 'approved')
                    ->flatMap->items
                    ->groupBy('product_id')
                    ->map(fn($it) => $it->sum('quantity'));

                if ($poQtys->every(fn($qty, $pid) => $plQtys->get($pid, 0) >= $qty)) {
                    $po->update(['status' => 'completed']);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Packing list approved and inventory updated'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
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
