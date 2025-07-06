<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Disposal;
use App\Models\Liquidate;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Facility;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\DisposalResource;
use App\Http\Resources\LiquidateResource;

class LiquidateDisposalController extends Controller
{
    public function disposals(Request $request){
        $disposals = Disposal::query()->with([
            'product',
            'disposedBy',
            'approvedBy',
            'reviewedBy',
            'rejectedBy',
            'backOrder'
        ])
        ->whereIn('status', ['pending', 'reviewed'])
        ->latest('disposal_id');

        if ($request->has('search')) {
            $search = $request->search;
             $disposals->where('disposal_id', 'like', "%{$search}%")
                ->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%")
                      ->orWhere('batch_number', 'like', "%{$search}%");
                });
        }

        // Filter by warehouse (applied to reported_by field)
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $disposals->whereHas('disposedBy.warehouse', function ($q) use ($request) {
                $q->where('name', $request->warehouse_id);
            });
        }

        // Filter by facility (applied to reported_by field)
        if ($request->has('facility_id') && $request->facility_id) {
            $disposals->whereHas('disposedBy.facility', function ($q) use ($request) {
                $q->where('name', $request->facility_id);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'pending') {
                $disposals->whereNull('reviewed_at')->whereNull('approved_at')->whereNull('rejected_at');
            } elseif ($request->status === 'reviewed') {
                $disposals->whereNotNull('reviewed_at')->whereNull('approved_at')->whereNull('rejected_at');
            } elseif ($request->status === 'approved') {
                $disposals->whereNotNull('approved_at');
            } elseif ($request->status === 'rejected') {
                $disposals->whereNotNull('rejected_at');
            }
        }

        $disposals = $disposals->paginate($request->input('per_page', 10), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $disposals->setPath(url()->current()); // Force Laravel to use full URLs
        
        // Get warehouses and facilities for filters
        $warehouses = Warehouse::pluck('name')->toArray();
        $facilities = Facility::pluck('name')->toArray();
        
        return inertia('LiquidateDisposal/Disposal', [
            'disposals' => DisposalResource::collection($disposals),
            'filters' => $request->only('search', 'per_page', 'page', 'warehouse_id', 'facility_id', 'status'),
            'warehouses' => $warehouses,
            'facilities' => $facilities,
        ]);
    }

    public function liquidates(Request $request){   
        $liquidates = Liquidate::query()->with([
            'product',
            'liquidatedBy',
            'approvedBy',
            'reviewedBy',
            'rejectedBy',
            'backOrder'
        ])
        ->whereIn('status', ['pending', 'reviewed'])
        ->latest('liquidate_id');

        if ($request->has('search')) {
            $search = $request->search;
            $liquidates->where('liquidate_id', 'like', "%{$search}%")
                ->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%")
                      ->orWhere('batch_number', 'like', "%{$search}%");
                });
        }

        // Filter by warehouse (applied to reported_by field)
        if ($request->has('warehouse') && $request->warehouse) {
            $liquidates->whereHas('liquidatedBy.warehouse', function ($q) use ($request) {
                $q->where('name', $request->warehouse);
            });
        }

        // Filter by facility (applied to reported_by field)
        if ($request->has('facility') && $request->facility) {
            $liquidates->whereHas('liquidatedBy.facility', function ($q) use ($request) {
                $q->where('name', $request->facility);
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'pending') {
                $liquidates->whereNull('reviewed_at')->whereNull('approved_at')->whereNull('rejected_at');
            } elseif ($request->status === 'reviewed') {
                $liquidates->whereNotNull('reviewed_at')->whereNull('approved_at')->whereNull('rejected_at');
            } elseif ($request->status === 'approved') {
                $liquidates->whereNotNull('approved_at');
            } elseif ($request->status === 'rejected') {
                $liquidates->whereNotNull('rejected_at');
            }
        }

        $liquidates = $liquidates->paginate($request->input('per_page', 2), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $liquidates->setPath(url()->current()); // Force Laravel to use full URLs

        // Get warehouses and facilities for filters
        $warehouses = Warehouse::pluck('name')->toArray();
        $facilities = Facility::pluck('name')->toArray();
        
        return inertia('LiquidateDisposal/Liquidate', [
            'liquidates' => LiquidateResource::collection($liquidates),
            'filters' => $request->only('search', 'per_page', 'page', 'warehouse', 'facility', 'status'),
            'warehouses' => $warehouses,
            'facilities' => $facilities,
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
