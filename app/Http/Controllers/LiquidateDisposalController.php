<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Disposal;
use App\Models\Liquidate;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class LiquidateDisposalController extends Controller
{
    public function disposals(Request $request){
        $disposals = Disposal::with([
            'product',
            'purchaseOrder',
            'packingList.warehouse',
            'packingList.location',
            'inventory.warehouse',
            'inventory.location',
            'disposedBy',
            'approvedBy',
            'reviewedBy',
            'rejectedBy'
        ])->latest('disposed_at')->get();
        
        return inertia('LiquidateDisposal/Disposal', [
            'disposals' => $disposals
        ]);
    }

    public function liquidates(Request $request){   
        $liquidates = Liquidate::with([
            'product',
            'purchaseOrder',
            'packingList.warehouse',
            'packingList.location',
            'inventory.warehouse',
            'inventory.location',
            'liquidatedBy',
            'approvedBy',
            'reviewedBy',
            'rejectedBy'
        ])->latest('liquidated_at')->get();
        
        return inertia('LiquidateDisposal/Liquidate', [
            'liquidates' => $liquidates
        ]);
    }
    
    public function rollbackDisposal(Request $request, $id)
    {
        $disposal = Disposal::findOrFail($id);
        
        // Check if not approved
        if ($disposal->approved_by === null) {
            return response()->json([
                'message' => 'This disposal has not been approved yet.'
            ], 422);
        }
        
        // Reset the approval information
        $disposal->approved_by = null;
        $disposal->approved_at = null;
        $disposal->status = 'pending';
        $disposal->save();
        
        return response()->json([
            'message' => 'Disposal rolled back successfully',
            'disposal' => $disposal
        ]);
    }
    
    /**
     * Rollback an approved liquidation to pending status
     */
    public function rollbackLiquidate(Request $request, $id)
    {
        $liquidate = Liquidate::findOrFail($id);
        
        // Check if not approved
        if ($liquidate->approved_by === null) {
            return response()->json([
                'message' => 'This liquidation has not been approved yet.'
            ], 422);
        }
        
        // Reset the approval information
        $liquidate->approved_by = null;
        $liquidate->approved_at = null;
        $liquidate->status = 'pending';
        $liquidate->save();
        
        return response()->json([
            'message' => 'Liquidation rolled back successfully',
            'liquidate' => $liquidate
        ]);
    }

    public function reviewLiquidate(Request $request, $id)
    {
        try {
            $liquidate = Liquidate::findOrFail($id);
            if(!$liquidate) return response()->json("Liquidate not found", 500);
            if($liquidate->status == 'pending') {
                $liquidate->status = 'reviewed';
                $liquidate->reviewed_at = now();
                $liquidate->reviewed_by = Auth::id();
                $liquidate->save();
            }
        
            return response()->json("Liquidate Reviewed", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function approveLiquidate(Request $request, $id)
    {
        try {
            $liquidate = Liquidate::findOrFail($id);
            if(!$liquidate) return response()->json("Liquidate not found", 500);
            if($liquidate->status == 'reviewed' || $liquidate->status == 'rejected') {
                $liquidate->status = 'approved';
                $liquidate->approved_at = now();
                $liquidate->rejected_at = null;
                $liquidate->rejection_reason = null;
                $liquidate->rejected_by = null;
                $liquidate->approved_by = Auth::id();
                $liquidate->save();
            }        
            return response()->json("Liquidate Approved", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function rejectLiquidate(Request $request, $id)
    {
        try {
            $liquidate = Liquidate::findOrFail($id);
            if(!$liquidate) return response()->json("Liquidate not found", 500);
            if($liquidate->status == 'reviewed') {
                $liquidate->status = 'rejected';
                $liquidate->rejected_at = now();
                $liquidate->rejection_reason = $request->reason;
                $liquidate->rejected_by = Auth::id();
                $liquidate->save();
            }        
            return response()->json("Liquidate Rejected", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    // disposals
    public function reviewDisposal(Request $request, $id)
    {
        try {
            $disposal = Disposal::findOrFail($id);
            if(!$disposal) return response()->json("Disposal not found", 500);
            if($disposal->status == 'pending') {
                $disposal->status = 'reviewed';
                $disposal->reviewed_at = now();
                $disposal->reviewed_by = Auth::id();
                $disposal->save();
            }
        
            return response()->json("Disposal Reviewed", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function approveDisposal(Request $request, $id)
    {
        try {
            $disposal = Disposal::findOrFail($id);
            if(!$disposal) return response()->json("Disposal not found", 500);
            if($disposal->status == 'reviewed' || $disposal->status == 'rejected') {
                $disposal->status = 'approved';
                $disposal->approved_at = now();
                $disposal->rejected_at = null;
                $disposal->rejection_reason = null;
                $disposal->rejected_by = null;
                $disposal->approved_by = Auth::id();
                $disposal->save();
            }        
            return response()->json("Disposal Approved", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function rejectDisposal(Request $request, $id)
    {
        try {
            $disposal = Disposal::findOrFail($id);
            if(!$disposal) return response()->json("Disposal not found", 500);
            if($disposal->status == 'reviewed') {
                $disposal->status = 'rejected';
                $disposal->rejected_at = now();
                $disposal->rejection_reason = $request->reason;
                $disposal->rejected_by = Auth::id();
                $disposal->save();
            }        
            return response()->json("Disposal Rejected", 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

}
