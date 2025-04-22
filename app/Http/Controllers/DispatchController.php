<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Facility;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Facades\Kafka;

class DispatchController extends Controller
{
    public function index()
    {
        $query = Order::whereHas('items', function($query) {
                $query->whereIn('status', ['in process']);
            })
            ->with(['facility', 'items' => function($query) {
                $query->whereIn('status', ['in process'])
                    ->with(['warehouse', 'product']);
            }]);

        // Get orders with pagination - increased to 10 per page
        $orders = $query->paginate(10)
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'facility' => [
                        'id' => $order->facility->id,
                        'name' => $order->facility->name
                    ],
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product' => [
                                'id' => $item->product->id,
                                'name' => $item->product->name
                            ],
                            'quantity' => $item->quantity,
                            'batch_number' => $item->batch_number,
                            'status' => $item->status,
                            'warehouse' => $item->warehouse ? [
                                'id' => $item->warehouse->id,
                                'name' => $item->warehouse->name
                            ] : null
                        ];
                    }),
                    'status' => $order->status,
                    'created_at' => $order->created_at
                ];
            });

        // Get all active facilities
        $facilities = Facility::select('id', 'name')
            // ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get all warehouses
        $warehouses = Warehouse::select('id', 'name')
            // ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Get statistics
        $stats = [
            'pending' => Order::whereHas('items', function($query) {
                $query->where('status', 'approved');
            })->count(),
            
            'in_process' => Order::whereHas('items', function($query) {
                $query->where('status', 'in process');
            })->count(),
            
            'dispatched_today' => Order::whereHas('items', function($query) {
                $query->where('status', 'dispatched')
                    ->whereDate('updated_at', Carbon::today());
            })->count(),
            
            'total_items' => DB::table('order_items')
                ->whereIn('status', ['approved', 'in process'])
                ->count()
        ];

        return Inertia::render('Dispatch/Index', [
            'orders' => $orders,
            'facilities' => $facilities,
            'warehouses' => $warehouses,
            'stats' => $stats
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'item_ids' => 'required|array',
            'item_ids.*' => 'exists:order_items,id'
        ]);

        try {
            DB::beginTransaction();

            $order = Order::with(['items', 'facility'])->findOrFail($request->order_id);
            $itemsToUpdate = $order->items()->whereIn('id', $request->item_ids)->get();
            
            // Validate that all items belong to the same order
            if ($itemsToUpdate->isEmpty() || $itemsToUpdate->contains('order_id', '!=', $order->id)) {
                return response()->json('Invalid items selected', 422);
            }

            // Validate that items are in a valid state for dispatch
            $invalidItems = $itemsToUpdate->filter(function ($item) {
                return !in_array($item->status, ['approved', 'in process']);
            });

            if ($invalidItems->isNotEmpty()) {
                return response()->json('Some items cannot be dispatched due to their current status', 422);
            }

            // Update the selected items
            foreach ($itemsToUpdate as $item) {
                $item->status = 'dispatched';
                $item->dispatched_at = Carbon::now();
                $item->warehouse_id = $request->warehouse_id;
                $item->save();

                // Publish to Kafka
                try {
                    Kafka::publishOrderPlaced('Refreshed');
                } catch (\Exception $e) {
                    Log::error('Failed to publish order item status change to Kafka', [
                        'order_id' => $order->id,
                        'item_id' => $item->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Check if all items in the order are now dispatched
            $allDispatched = !$order->items()->where('status', '!=', 'dispatched')->exists();
            if ($allDispatched) {
                $order->status = 'dispatched';
                $order->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Items dispatched successfully',
                'dispatched_count' => $itemsToUpdate->count(),
                'order_completed' => $allDispatched
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order dispatch failed', [
                'order_id' => $request->order_id,
                'item_ids' => $request->item_ids,
                'error' => $e->getMessage()
            ]);
            return response()->json('Failed to dispatch items: ' . $e->getMessage(), 500);
        }
    }
}
