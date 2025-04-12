<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Facades\Kafka;
use App\Models\Facility;
use App\Models\Product;
use App\Models\Warehouse;
use App\Events\OrderEvent;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['facility', 'user', 'items.product', 'warehouse'])
            ->when($request->from && $request->to, function ($query) use ($request) {
                $query->whereBetween('order_date', [$request->from, $request->to]);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('facility', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest();

        $orders = $query->paginate($request->input('perPage', 7), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();

        $orders->setPath(url()->current());

        // Get order statistics
        $stats = Order::select('status', DB::raw('count(*) as count'))
            ->where('warehouse_id', auth()->user()->warehouse_id)
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            })
            ->toArray();

        // Ensure all statuses have a value
        $defaultStats = [
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'in processing' => 0,
            'dispatched' => 0,
            'delivered' => 0
        ];

        $stats = array_merge($defaultStats, $stats);

        return Inertia::render('Order/Index', [
            'stats' => $stats,
            'orders' => OrderResource::collection($orders),
            'facilities' => Facility::select('id', 'name')->get(),
            'filters' => $request->only('search', 'status', 'page', 'from', 'to'),
            'warehouses' => Warehouse::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'warehouse_id' => 'required|exists:warehouses,id',
                'facility_id' => 'required|exists:facilities,id',
                'order_date' => 'required|date',
                'expected_date' => 'nullable|date|after_or_equal:order_date',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.id' => 'nullable|exists:order_items,id',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:1',
            ]);

            DB::beginTransaction();

            // Create or update order
            $order = Order::updateOrCreate(
                ['id' => $request->id],
                [
                    'warehouse_id' => $validated['warehouse_id'],
                    'facility_id' => $validated['facility_id'],
                    'user_id' => auth()->id(),
                    'order_number' => $request->id ? Order::find($request->id)->order_number : 'ORD-' . strtoupper(uniqid()),
                    'status' => $request->id ? Order::find($request->id)->status : 'pending',
                    'number_items' => collect($validated['items'])->sum('quantity'),
                    'notes' => $validated['notes'] ?? null,
                    'order_date' => $validated['order_date'],
                    'expected_date' => $validated['expected_date'] ?? null,
                ]
            );

            // Get current item IDs
            $currentItemIds = collect($validated['items'])
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete items that are not in the request (only pending orders)
            if ($request->id) {
                $order->items()
                    ->whereNotIn('id', $currentItemIds)
                    ->where('order_id', $order->id)
                    ->delete();
            }

            // Process each item
            foreach ($validated['items'] as $itemData) {
                if (!empty($itemData['id'])) {
                    // Update existing item
                    $order->items()
                        ->where('id', $itemData['id'])
                        ->update([
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                        ]);
                } else {
                    // Create new item
                    $order->items()->create([
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity']
                    ]);
                }
            }
            
            Kafka::publishOrderPlaced('Refreshed');

            event(new OrderEvent('Refreshed'));

            DB::commit();
            return response()->json('Order ' . ($request->id ? 'updated' : 'created') . ' successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

    public function show(Order $order)
    {
        $order->load(['facility', 'user', 'items.product']);
        
        return Inertia::render('Order/Show', [
            'order' => new OrderResource($order)
        ]);
    }


    public function destroy(Order $order)
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be deleted.');
        }

        $order->items()->delete();
        $order->delete();

        return back()->with('success', 'Order deleted successfully.');
    }

    public function bulk(Request $request)
    {
        try {
            $orderIds = $request->input('orderIds');

            // Validate that at least one order is selected
            if (empty($orderIds)) {
                return response()->json('Please select at least one order', 400);
            }
            
            // Get all selected orders
            $orders = Order::whereIn('id', $orderIds)->get();
            
            // Check if any order has non-pending items and collect their IDs
            $nonPendingOrders = [];
            foreach ($orders as $order) {
                if ($order->status !== 'pending') {
                    $nonPendingOrders[] = $order->id;
                }
            }
            
            if (!empty($nonPendingOrders)) {
                return response()->json('Cannot delete orders that are not in pending status', 500);
            }
            
            // Delete orders if all are pending
            $orders->each(function ($order) {
                $order->items()->delete();
                $order->delete();
            });
            
            return response()->json('Selected orders deleted successfully', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function searchProduct(Request $request)
    {
        try {
            $search = $request->input('search');
            $products = Product::where('name', 'like', '%' . $search . '%')
                ->orWhere('barcode', 'like', '%' . $search . '%')
                ->select('id', 'name', 'barcode')
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                    ];
                });
                    
            return response()->json($products, 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            $order = Order::findOrFail($request->id);
            $allowedStatuses = [
                'pending' => ['approved', 'rejected'],
                'approved' => ['in processing'],
                'in processing' => ['dispatched'],
                'dispatched' => ['delivered']
            ];

            // Check if the current status can transition to the requested status
            if (!isset($allowedStatuses[$order->status]) || !in_array($request->status, $allowedStatuses[$order->status])) {
                return response()->json("Order cannot be changed from {$order->status} to {$request->status}", 500);
            }        

            $order->update([
                'status' => $request->status,
            ]);

            // Trigger Kafka event for order status change
            try {
                Kafka::publishOrderPlaced("Refreshed");
            } catch (\Throwable $e) {
                return response()->json($e->getMessage(), 500);
            }

            event(new OrderEvent('Refreshed'));

            DB::commit();
            return response()->json('Order status updated successfully.', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json($e->getMessage(), 500);
        }
    }

}
