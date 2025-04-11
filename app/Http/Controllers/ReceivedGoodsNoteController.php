<?php

namespace App\Http\Controllers;

use App\Models\ReceivedGoodsNote;
use App\Models\PackingList;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReceivedGoodsNoteController extends Controller
{
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'packing_list_id' => 'required|exists:packing_lists,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'receiving_notes' => 'nullable|string',
                'supplier_vehicle_number' => 'nullable|string|max:20',
                'supplier_driver_name' => 'nullable|string|max:255',
                'supplier_driver_phone' => 'nullable|string|max:20',
            ]);

            // Generate unique RGN number
            $latestRgn = ReceivedGoodsNote::latest()->first();
            $nextNumber = $latestRgn ? intval(substr($latestRgn->rgn_number, 3)) + 1 : 1;
            $validated['rgn_number'] = 'RGN' . str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
            
            // Add user info
            $validated['receiver_id'] = Auth::id();
            $validated['created_by'] = Auth::id();
            
            $rgn = ReceivedGoodsNote::create($validated);

            DB::commit();
            
            return response()->json('Received goods note created successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(Request $request, ReceivedGoodsNote $rgn)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'receiving_notes' => 'nullable|string',
                'supplier_vehicle_number' => 'nullable|string|max:20',
                'supplier_driver_name' => 'nullable|string|max:255',
                'supplier_driver_phone' => 'nullable|string|max:20',
                'status' => 'required|in:pending,received'
            ]);

            $validated['updated_by'] = Auth::id();
            if ($request->status === 'received') {
                $validated['received_at'] = now();
            }

            $rgn->update($validated);

            DB::commit();
            
            return response()->json('Received goods note updated successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(ReceivedGoodsNote $rgn)
    {
        return Inertia::render('ReceivedGoodsNote/Show', [
            'rgn' => $rgn->load(['packingList.purchaseOrder', 'receiver', 'creator', 'updater', 'warehouse'])
        ]);
    }
}
