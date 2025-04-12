<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpiredResource;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ExpiredController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $tab = $request->query('tab', 'all');
        $warehouseId = $request->query('warehouse_id');
        
        // Base query - with eager loading for better performance
        $inventoryQuery = Inventory::with(['product', 'warehouse'])
            ->join('products', 'inventories.product_id', '=', 'products.id')
            ->select('inventories.*', 'products.name as product_name');

        // Apply warehouse filter for non-admin users
        if (!$user->hasRole('admin') && $user->warehouse_id) {
            $inventoryQuery->where('inventories.warehouse_id', $user->warehouse_id);
        }
        // Apply warehouse filter if provided in request
        elseif ($warehouseId) {
            $inventoryQuery->where('inventories.warehouse_id', $warehouseId);
        }

        // Apply tab-specific filters
        switch ($tab) {
            case 'near':
                // Items expiring in the next 30 days
                $inventoryQuery->whereNotNull('inventories.expiry_date')
                    ->where('inventories.is_active', true)
                    ->where(function($query) {
                        $now = now();
                        $thirtyDaysFromNow = $now->copy()->addDays(30);
                        $query->whereDate('inventories.expiry_date', '>', $now)
                            ->whereDate('inventories.expiry_date', '<=', $thirtyDaysFromNow);
                    });
                break;
                
            case 'expired':
                // Items that have already expired but not marked as disposed
                $inventoryQuery->whereNotNull('inventories.expiry_date')
                    ->where('inventories.is_active', true)
                    ->whereDate('inventories.expiry_date', '<=', now());
                break;
                
            case 'disposed':
                // Items marked as inactive (assuming this indicates disposal)
                $inventoryQuery->whereNotNull('inventories.expiry_date')
                    ->where('inventories.is_active', false);
                break;
                
            default: // 'all'
                // All items with issues (near expiry, expired, or disposed)
                $inventoryQuery->whereNotNull('inventories.expiry_date')
                    ->where(function($query) {
                        $now = now();
                        $thirtyDaysFromNow = $now->copy()->addDays(30);
                        
                        $query->where(function($q) use ($now, $thirtyDaysFromNow) {
                            // Near expiry items
                            $q->where('inventories.is_active', true)
                              ->whereDate('inventories.expiry_date', '>', $now)
                              ->whereDate('inventories.expiry_date', '<=', $thirtyDaysFromNow);
                        })->orWhere(function($q) use ($now) {
                            // Expired items
                            $q->where('inventories.is_active', true)
                              ->whereDate('inventories.expiry_date', '<=', $now);
                        })->orWhere(function($q) {
                            // Disposed items
                            $q->where('inventories.is_active', false);
                        });
                    });
                break;
        }
        
        // Apply search filter if provided
        if ($request->filled('search')) {
            $search = $request->input('search');
            $inventoryQuery->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('inventories.batch_number', 'like', "%{$search}%")
                  ->orWhere('inventories.location', 'like', "%{$search}%");
            });
        }
        
        // Apply sorting if provided
        if ($request->filled('sort_field')) {
            $sortField = $request->input('sort_field', 'expiry_date');
            $sortDirection = $request->input('sort_direction', 'asc');
            
            // Properly qualify the sort field if it's a common column name
            if (in_array($sortField, ['is_active', 'expiry_date', 'manufacturing_date', 'quantity', 'batch_number', 'location'])) {
                $sortField = 'inventories.' . $sortField;
            } elseif ($sortField === 'product_name') {
                $sortField = 'products.name';
            }
            
            $inventoryQuery->orderBy($sortField, $sortDirection);
        } else {
            // Default sorting by expiry date
            $inventoryQuery->orderBy('inventories.expiry_date', 'asc');
        }
        
        // Paginate the results
        $inventories = $inventoryQuery->paginate($request->input('per_page', 10), ['*'], 'page', $request->input('page', 1))
        ->withQueryString();

        $inventories->setPath(route('expired.index'));
        
        // Extract non-empty filters
        $filters = [];
        if ($request->filled('search')) $filters['search'] = $request->input('search');
        if ($request->filled('sort_field')) $filters['sort_field'] = $request->input('sort_field');
        if ($request->filled('sort_direction')) $filters['sort_direction'] = $request->input('sort_direction');
        if ($warehouseId) $filters['warehouse_id'] = $warehouseId;

        
        // Count expired items for dashboard metrics (using more efficient queries)
        $baseQuery = Inventory::query()
            ->whereNotNull('expiry_date')
            ->when(!$user->hasRole('admin') && $user->warehouse_id, function($query) use ($user) {
                return $query->where('warehouse_id', $user->warehouse_id);
            })
            ->when($warehouseId && $user->hasRole('admin'), function($query) use ($warehouseId) {
                return $query->where('warehouse_id', $warehouseId);
            });

        // Near expiry items (next 30 days)
        $nearExpiryCount = (clone $baseQuery)
            ->where('is_active', true)
            ->where(function($query) {
                $now = now();
                $thirtyDaysFromNow = $now->copy()->addDays(30);
                $query->whereDate('expiry_date', '>', $now)
                    ->whereDate('expiry_date', '<=', $thirtyDaysFromNow);
            })
            ->count();
            
        // Already expired items
        $expiredCount = (clone $baseQuery)
            ->where('is_active', true)
            ->whereDate('expiry_date', '<=', now())
            ->count();
            
        // Disposed items
        $disposedCount = (clone $baseQuery)
            ->where('is_active', false)
            ->count();
        return Inertia::render('Expired/Index', [
            'inventories' => ExpiredResource::collection($inventories),
            'activeTab' => $tab,
            'filters' => $filters,
            'counts' => [
                'near' => $nearExpiryCount,
                'expired' => $expiredCount,
                'disposed' => $disposedCount,
                'all' => $nearExpiryCount + $expiredCount + $disposedCount
            ],
            'warehouses' => Warehouse::where('is_active', true)->get(['id', 'name']),
        ]);
    }
    
    // Mark expired items as disposed (inactive)
    public function markAsDisposed(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'inventory_ids' => 'required|array',
            'inventory_ids.*' => 'exists:inventories,id',
            'notes' => 'nullable|string',
        ]);
        
        $query = Inventory::whereIn('id', $request->inventory_ids);
        
        // Restrict to user's warehouse if not admin
        if (!$user->hasRole('admin') && $user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }
        
        $count = $query->update([
            'is_active' => false,
            'notes' => $request->notes ? $request->notes : 'Marked as disposed due to expiration'
        ]);
        
        return redirect()->back()->with('success', $count . ' items marked as disposed');
    }
}
