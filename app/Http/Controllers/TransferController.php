<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia;
use App\Models\Inventory;
use App\Models\FacilityInventory;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransferController extends Controller
{
    public function approve($id)
    {
        try {
            $transfer = Transfer::findOrFail($id);
            
            if ($transfer->status !== 'pending') {
                return response()->json(['message' => 'Transfer must be pending to be approved'], 400);
            }

            $transfer->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            return response()->json(['message' => 'Transfer approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to approve transfer: ' . $e->getMessage()], 500);
        }
    }

    public function reject($id)
    {
        try {
            $transfer = Transfer::findOrFail($id);
            
            if ($transfer->status !== 'pending') {
                return response()->json(['message' => 'Transfer must be pending to be rejected'], 400);
            }

            $transfer->update([
                'status' => 'rejected',
                'rejected_by' => Auth::id(),
                'rejected_at' => now()
            ]);

            // Return inventory quantity
            $inventory = Inventory::findOrFail($transfer->inventory_id);
            $inventory->increment('quantity', $transfer->quantity);

            return response()->json(['message' => 'Transfer rejected successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to reject transfer: ' . $e->getMessage()], 500);
        }
    }

    public function inProcess($id)
    {
        try {
            $transfer = Transfer::findOrFail($id);
            
            if ($transfer->status !== 'approved') {
                return response()->json(['message' => 'Transfer must be approved to be set in process'], 400);
            }

            $transfer->update([
                'status' => 'in_process',
                'process_started_at' => now()
            ]);

            return response()->json(['message' => 'Transfer marked as in process']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update transfer status: ' . $e->getMessage()], 500);
        }
    }

    public function completeTransfer($id)
    {
        DB::beginTransaction();
        try {
            $transfer = Transfer::with(['toWarehouse', 'toFacility'])->findOrFail($id);
            
            if ($transfer->status !== 'in_process') {
                return response()->json(['message' => 'Transfer must be in process to be completed'], 500);
            }

            $inventoryData = [
                'product_id' => $transfer->product_id,
                'batch_number' => $transfer->batch_no,
                'quantity' => $transfer->quantity,
                'expiry_date' => $transfer->expire_date,
                'uom' => 'pcs'
            ];

            // Check if the transfer is to a warehouse or facility
            if ($transfer->toWarehouse) {
                // Add to warehouse inventory
                $inventoryData['warehouse_id'] = $transfer->to_warehouse_id;
                Inventory::create($inventoryData);
            } else if ($transfer->toFacility) {
                // Add to facility inventory
                $inventoryData['facility_id'] = $transfer->to_facility_id;
                FacilityInventory::create($inventoryData);
            } else {
                throw new \Exception('Invalid transfer destination');
            }

            $transfer->update([
                'status' => 'transferred'
            ]);

            DB::commit();
            return response()->json(['message' => 'Transfer completed successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error completing transfer: ' . $e->getMessage()], 500);
        }
    }

    public function bulkApprove(Request $request)
    {
        try {
            $transfers = Transfer::whereIn('id', $request->transferIds)
                                ->where('status', 'pending')
                                ->get();

            foreach ($transfers as $transfer) {
                $transfer->update([
                    'status' => 'approved',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
            }

            return response()->json(['message' => count($transfers) . ' transfers approved successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to approve transfers: ' . $e->getMessage()], 500);
        }
    }

    public function bulkReject(Request $request)
    {
        DB::beginTransaction();
        try {
            $transfers = Transfer::whereIn('id', $request->transferIds)
                                ->where('status', 'pending')
                                ->get();

            foreach ($transfers as $transfer) {
                $transfer->update([
                    'status' => 'rejected',
                    'rejected_by' => Auth::id(),
                    'rejected_at' => now()
                ]);

                // Return inventory quantity
                $inventory = Inventory::findOrFail($transfer->inventory_id);
                $inventory->increment('quantity', $transfer->quantity);
            }

            DB::commit();
            return response()->json(['message' => count($transfers) . ' transfers rejected successfully']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Failed to reject transfers: ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        $transfers = Transfer::with('fromWarehouse','toWarehouse','fromFacility','toFacility')->get();
        
        // Calculate statistics
        $total = $transfers->count();
        $approvedCount = $transfers->whereIn('status', ['approved', 'in_process', 'dispatched', 'transferred'])->count();
        $inProcessCount = $transfers->whereIn('status', ['in_process', 'dispatched'])->count();
        $transferredCount = $transfers->where('status', 'transferred')->count();
        $rejectedCount = $transfers->where('status', 'rejected')->count();
        $pendingCount = $transfers->where('status', 'pending')->count();
        
        $statistics = [
            'approved' => [
                'count' => $approvedCount,
                'percentage' => $total > 0 ? round(($approvedCount / $total) * 100) : 0,
                'stages' => ['approved', 'in_process', 'dispatched', 'transferred']
            ],
            'pending' => [
                'count' => $pendingCount,
                'percentage' => $total > 0 ? round(($pendingCount / $total) * 100) : 0,
                'stages' => ['pending']
            ],
            'in_process' => [
                'count' => $inProcessCount,
                'percentage' => $total > 0 ? round(($inProcessCount / $total) * 100) : 0,
                'stages' => ['in_process', 'dispatched']
            ],
            'transferred' => [
                'count' => $transferredCount,
                'percentage' => $total > 0 ? round(($transferredCount / $total) * 100) : 0,
                'stages' => ['transferred']
            ],
            'rejected' => [
                'count' => $rejectedCount,
                'percentage' => $total > 0 ? round(($rejectedCount / $total) * 100) : 0,
                'stages' => ['rejected']
            ]
        ];

        return inertia('Transfer/Index', [
            'transfers' => $transfers,
            'statistics' => $statistics
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'destination_type' => 'required|in:warehouse,facility',
                'destination_id' => 'required|integer',
                'quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:500'
            ]);
    
            $inventory = Inventory::findOrFail($request->inventory_id);
    
            if ($request->quantity > $inventory->quantity) {
                return response()->json('Transfer quantity cannot exceed available quantity', 500);
            }

            $transferData = [
                'transferID' => str_pad(Transfer::max('id') + 1, 4, '0', STR_PAD_LEFT),
                'from_warehouse_id' => $inventory->warehouse_id,
                'product_id' => $inventory->product_id,
                'inventory_id' => $inventory->id,
                'batch_no' => $inventory->batch_number,
                'expire_date' => $inventory->expiry_date,
                'uom' => $inventory->product->uom ?? null,
                'created_by' => auth()->id(),
                'quantity' => $request->quantity,
                'transfer_date' => now(),
                'status' => 'pending',
                'note' => $request->notes,
                // Initialize all destination IDs as null
                'to_warehouse_id' => null,
                'from_facility_id' => null,
                'to_facility_id' => null
            ];

            // Update destination based on type
            if ($request->destination_type === 'warehouse') {
                $transferData['to_warehouse_id'] = $request->destination_id;
            } else {
                $transferData['to_facility_id'] = $request->destination_id;
            }

            $transfer = Transfer::create($transferData);

            // Update inventory quantity
            $inventory->decrement('quantity', $request->quantity);

            DB::commit();

            return response()->json([
                'message' => 'Transfer created successfully',
                'transfer' => $transfer
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json('Failed to create transfer: ' . $e->getMessage(), 500);
        }
    }
}
