<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\CustodyHistory;
use App\Http\Resources\AssetResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::query();

        if($request->filled('search')){
            $assets->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('serial_number', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%')
                  ->orWhere('custody', 'like', '%' . $request->search . '%');
                  
        }

        $assets = $assets->with(['custodyHistories' => function($query) {
            $query->with('assignedBy')
                  ->orderBy('assigned_at', 'desc');
        }]);
        
        $assets = $assets->latest()->paginate(10);

        $categories = Asset::distinct()->pluck('category')->filter()->values();
        
        // Get the latest serial number
        $lastSerialNumber = Asset::orderBy('serial_number', 'desc')->value('serial_number') ?? '000000';
        
        return Inertia::render('Assets/Index', [
            'assets' => AssetResource::collection($assets),
            'filters' => $request->only('search'),
            'categories' => $categories,
            'lastSerialNumber' => $lastSerialNumber
        ]);
    }

    public function store(Request $request)
    {   
        try {
            $validated = $request->validate([
                "id" => "nullable|exists:assets,id",
                'name' => 'required|string|max:255',
                'serial_number' => 'required|string|max:255|unique:assets,serial_number,' . $request->id,
                'category' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'custody' => 'required|string|max:255',
                'quantity' => 'required|integer|min:1',
                'purchase_date' => 'required|date',
                'transfer_date' => 'required|date',
                'purchase_cost' => 'nullable|numeric',
                'notes' => 'nullable|string',
            ]);
    
            $asset = Asset::updateOrCreate(['id' => $request->id], $validated);
            return response()->json('Asset ' . ($request->id ? 'updated' : 'created') . ' successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function history(Asset $asset)
    {
        $history = $asset->custodyHistories()
            ->with('assignedBy')
            ->latest()
            ->get();

        return Inertia::render('Assets/History', [
            'asset' => $asset,
            'history' => $history
        ]);
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->back()->with('success', 'Asset deleted successfully.');
    }

    public function updateStatus(Request $request, Asset $asset)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:active,in_use,maintenance,retired,damaged',
                'notes' => 'nullable|string',
            ]);

            $asset->update([
                'status' => $validated['status']
            ]);

            return response()->json('Status updated successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }
}


