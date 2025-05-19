<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Inertia\Inertia;

class OrderItemController extends Controller
{
    public function orderItemsPending(Request $request, $order_id)
    {
        try {
            logger()->info('Fetching order', ['order_id' => $order_id]);
            
            $order = Order::with('items','items.product', 'items.inventory_allocations', 'facility', 'user', 'items.warehouse')
                ->where('status', 'pending')
                ->findOrFail($order_id);
            
            logger()->info('Order found', [
                'order_id' => $order_id,
                'order_number' => $order->order_number,
                'items_count' => $order->items->count()
            ]);
            
            return inertia("OrderItem/Pending", ['order' => $order]);
        } catch (\Exception $e) {
            logger()->error('Error in orderItemsPending', [
                'order_id' => $order_id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function orderItemsApproved(Request $request, $order_id)
    {
        try {
            $order = Order::with(['items' => function($query) {
                $query->where('status', 'approved');
            }, 'items.product', 'items.inventory_allocations', 'facility', 'user', 'items.warehouse'])
            ->findOrFail($order_id);
            
            return inertia("OrderItem/Approved", ['order' => $order]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function orderItemsInProcess(Request $request, $order_id)
    {
        try {
            $order = Order::with(['items' => function($query) {
                $query->where('status', 'in_process');
            }, 'items.product', 'items.inventory_allocations', 'facility', 'user', 'items.warehouse'])
            ->findOrFail($order_id);
            
            return inertia("OrderItem/InProccess", ['order' => $order]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function orderItemsDispatched(Request $request, $order_id)
    {
        try {
            $order = Order::with(['items' => function($query) {
                $query->where('status', 'dispatched');
            }, 'items.product', 'items.inventory_allocations', 'facility', 'user', 'items.warehouse'])
            ->findOrFail($order_id);
            
            return inertia("OrderItem/Dispatched", ['order' => $order]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function orderItemsDelivered(Request $request, $order_id)
    {
        try {
            $order = Order::with(['items' => function($query) {
                $query->where('status', 'delivered');
            }, 'items.product', 'items.inventory_allocations', 'facility', 'user', 'items.warehouse'])
            ->findOrFail($order_id);
            
            return inertia("OrderItem/Delivered", ['order' => $order]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function orderItemsReceived(Request $request, $order_id)
    {
        try {
            $order = Order::with(['items' => function($query) {
                $query->where('status', 'received');
            }, 'items.product', 'items.inventory_allocations', 'facility', 'user', 'items.warehouse'])
            ->findOrFail($order_id);
            
            return inertia("OrderItem/Received", ['order' => $order]);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
