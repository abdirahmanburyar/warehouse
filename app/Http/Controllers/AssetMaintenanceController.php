<?php

namespace App\Http\Controllers;

use App\Models\AssetItem;
use App\Models\AssetMaintenance;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssetMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $maintenance = AssetMaintenance::with(['assetItem.asset', 'performedBy'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('description', 'like', '%' . $request->search . '%')
                      ->orWhereHas('assetItem', function ($q) use ($request) {
                          $q->where('asset_name', 'like', '%' . $request->search . '%');
                      });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('maintenance_type'), function ($query) use ($request) {
                $query->where('maintenance_type', $request->maintenance_type);
            })
            ->when($request->filled('asset_item_id'), function ($query) use ($request) {
                $query->where('asset_item_id', $request->asset_item_id);
            })
            ->when($request->filled('performed_by'), function ($query) use ($request) {
                $query->where('performed_by', $request->performed_by);
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->where('scheduled_date', '>=', $request->date_from);
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->where('scheduled_date', '<=', $request->date_to);
            })
            ->orderBy('scheduled_date', 'desc')
            ->paginate(15);

        return Inertia::render('AssetMaintenance/Index', [
            'maintenance' => $maintenance,
            'filters' => $request->only(['search', 'status', 'maintenance_type', 'asset_item_id', 'performed_by', 'date_from', 'date_to']),
            'assetItems' => AssetItem::select('id', 'asset_name')->get(),
            'users' => User::select('id', 'name')->get(),
            'statuses' => AssetMaintenance::getStatuses(),
            'maintenanceTypes' => AssetMaintenance::getMaintenanceTypes(),
        ]);
    }

    public function create()
    {
        return Inertia::render('AssetMaintenance/Create', [
            'assetItems' => AssetItem::select('id', 'asset_name')->get(),
            'users' => User::select('id', 'name')->get(),
            'maintenanceTypes' => AssetMaintenance::getMaintenanceTypes(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'asset_item_id' => 'required|exists:asset_items,id',
            'maintenance_type' => 'required|string|in:' . implode(',', array_keys(AssetMaintenance::getMaintenanceTypes())),
            'description' => 'required|string|max:1000',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $maintenance = AssetMaintenance::create([
            'asset_item_id' => $request->asset_item_id,
            'maintenance_type' => $request->maintenance_type,
            'description' => $request->description,
            'scheduled_date' => $request->scheduled_date,
            'cost' => $request->cost,
            'performed_by' => $request->performed_by,
            'notes' => $request->notes,
            'status' => AssetMaintenance::STATUS_SCHEDULED,
            'metadata' => [
                'created_by' => auth()->id(),
                'created_at' => now()->toISOString(),
            ],
        ]);

        // Update asset item to indicate it needs maintenance
        $assetItem = AssetItem::find($request->asset_item_id);
        $assetItem->update(['status' => 'maintenance']);

        return redirect()->route('asset.maintenance.index')
            ->with('success', 'Maintenance scheduled successfully.');
    }

    public function show(AssetMaintenance $maintenance)
    {
        $maintenance->load(['assetItem.asset', 'performedBy']);
        
        return Inertia::render('AssetMaintenance/Show', [
            'maintenance' => $maintenance,
        ]);
    }

    public function edit(AssetMaintenance $maintenance)
    {
        $maintenance->load(['assetItem.asset']);
        
        return Inertia::render('AssetMaintenance/Edit', [
            'maintenance' => $maintenance,
            'assetItems' => AssetItem::select('id', 'asset_name')->get(),
            'users' => User::select('id', 'name')->get(),
            'maintenanceTypes' => AssetMaintenance::getMaintenanceTypes(),
        ]);
    }

    public function update(Request $request, AssetMaintenance $maintenance)
    {
        $request->validate([
            'asset_item_id' => 'required|exists:asset_items,id',
            'maintenance_type' => 'required|string|in:' . implode(',', array_keys(AssetMaintenance::getMaintenanceTypes())),
            'description' => 'required|string|max:1000',
            'scheduled_date' => 'required|date',
            'cost' => 'nullable|numeric|min:0',
            'performed_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $maintenance->update([
            'asset_item_id' => $request->asset_item_id,
            'maintenance_type' => $request->maintenance_type,
            'description' => $request->description,
            'scheduled_date' => $request->scheduled_date,
            'cost' => $request->cost,
            'performed_by' => $request->performed_by,
            'notes' => $request->notes,
        ]);

        return redirect()->route('asset.maintenance.index')
            ->with('success', 'Maintenance updated successfully.');
    }

    public function destroy(AssetMaintenance $maintenance)
    {
        $maintenance->delete();

        // Check if asset item still has other maintenance records
        $assetItem = AssetItem::find($maintenance->asset_item_id);
        if ($assetItem && !$assetItem->maintenance()->exists()) {
            $assetItem->update(['status' => 'in_use']);
        }

        return redirect()->route('asset.maintenance.index')
            ->with('success', 'Maintenance record deleted successfully.');
    }

    public function markInProgress(AssetMaintenance $maintenance)
    {
        $maintenance->markInProgress();

        return redirect()->route('asset.maintenance.index')
            ->with('success', 'Maintenance marked as in progress.');
    }

    public function markCompleted(Request $request, AssetMaintenance $maintenance)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $maintenance->markCompleted($request->notes);

        return redirect()->route('asset.maintenance.index')
            ->with('success', 'Maintenance marked as completed.');
    }

    public function cancel(Request $request, AssetMaintenance $maintenance)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $maintenance->cancel($request->notes);

        return redirect()->route('asset.maintenance.index')
            ->with('success', 'Maintenance cancelled.');
    }

    public function reschedule(Request $request, AssetMaintenance $maintenance)
    {
        $request->validate([
            'scheduled_date' => 'required|date|after_or_equal:today',
        ]);

        $maintenance->reschedule($request->scheduled_date);

        return redirect()->route('asset.maintenance.index')
            ->with('success', 'Maintenance rescheduled successfully.');
    }
}
