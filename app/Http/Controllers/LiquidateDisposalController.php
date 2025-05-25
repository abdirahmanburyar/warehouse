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
            'packingList',
            'inventory',
            'disposedBy',
            'approvedBy'
        ])->latest('disposed_at')->get();
        
        return inertia('LiquidateDisposal/Disposal', [
            'disposals' => $disposals
        ]);
    }

    public function liquidates(Request $request){   
        $liquidates = Liquidate::with([
            'product',
            'purchaseOrder',
            'packingList',
            'inventory',
            'liquidatedBy',
            'approvedBy'
        ])->latest('liquidated_at')->get();
        
        return inertia('LiquidateDisposal/Liquidate', [
            'liquidates' => $liquidates
        ]);
    }
    
    /**
     * Approve a disposal record
     */
    public function approveDisposal(Request $request, $id)
    {
        $disposal = Disposal::findOrFail($id);
        
        // Check if already approved
        if ($disposal->approved_by !== null) {
            return response()->json([
                'message' => 'This disposal has already been approved.'
            ], 422);
        }
        
        // Update the disposal record
        $disposal->approved_by = Auth::id();
        $disposal->approved_at = now();
        $disposal->save();
        
        return response()->json([
            'message' => 'Disposal approved successfully',
            'disposal' => $disposal
        ]);
    }
    
    /**
     * Reject a disposal record
     */
    public function rejectDisposal(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);
        
        $disposal = Disposal::findOrFail($id);
        
        // Check if already approved
        if ($disposal->approved_by !== null) {
            return response()->json([
                'message' => 'Cannot reject an already approved disposal.'
            ], 422);
        }
        
        // Update the disposal record with rejection note
        $disposal->status = 'rejected';
        $disposal->note = $request->reason;
        $disposal->save();
        
        return response()->json([
            'message' => 'Disposal rejected successfully',
            'disposal' => $disposal
        ]);
    }
    
    /**
     * Rollback an approved disposal to pending status
     */
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
     * Approve a liquidation record
     */
    public function approveLiquidate(Request $request, $id)
    {
        $liquidate = Liquidate::findOrFail($id);
        
        // Check if already approved
        if ($liquidate->approved_by !== null) {
            return response()->json([
                'message' => 'This liquidation has already been approved.'
            ], 422);
        }
        
        // Update the liquidation record
        $liquidate->approved_by = Auth::id();
        $liquidate->approved_at = now();
        $liquidate->save();
        
        return response()->json([
            'message' => 'Liquidation approved successfully',
            'liquidate' => $liquidate
        ]);
    }
    
    /**
     * Reject a liquidation record
     */
    public function rejectLiquidate(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);
        
        $liquidate = Liquidate::findOrFail($id);
        
        // Check if already approved
        if ($liquidate->approved_by !== null) {
            return response()->json([
                'message' => 'Cannot reject an already approved liquidation.'
            ], 422);
        }
        
        // Update the liquidation record with rejection note
        $liquidate->status = 'rejected';
        $liquidate->note = $request->reason;
        $liquidate->save();
        
        return response()->json([
            'message' => 'Liquidation rejected successfully',
            'liquidate' => $liquidate
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
}
