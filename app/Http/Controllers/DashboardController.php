<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Facility;
use App\Models\User;
use App\Models\Order;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get total facilities count
        $totalFacilities = Facility::count();
        
        // Get total users count
        $totalUsers = User::count();
        
        // Get total purchase orders count
        $totalPurchaseOrders = PurchaseOrder::count();
        
        // Get total issued quantity
        $totalIssuedQuantity = DB::table('order_items')
            ->select(DB::raw('COALESCE(SUM(quantity), 0) as total_issued'))
            ->value('total_issued');
            
        // Get total received quantity
        $totalReceivedQuantity = DB::table('received_quantities')
            ->select(DB::raw('COALESCE(SUM(quantity), 0) as total_received'))
            ->value('total_received');
        
        // Get order statistics with a single query
        $orderStats = DB::table('orders')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            })
            ->toArray();

        // Define default values for all statuses
        $defaultStats = [
            'pending' => 0,
            'reviewed' => 0,
            'approved' => 0,
            'rejected' => 0,
            'in_process' => 0,
            'dispatched' => 0,
            'received' => 0,
            'delivered' => 0,
            'total' => array_sum($orderStats) // Calculate total from actual data
        ];

        // Merge with defaults to ensure all statuses exist
        $orderStats = array_merge($defaultStats, $orderStats);
        
        // Add additional calculated fields
        $orderStats['pending_review'] = $orderStats['pending'];
        $orderStats['pending_approval'] = $orderStats['reviewed'];
        
        // Add pending tasks
        $tasks = [
            'pending_review' => [
                'count' => $orderStats['pending'],
                'label' => 'orders waiting for review',
                'action' => 'Review',
                'route' => 'orders.index',
                'params' => ['status' => 'pending'],
                'icon' => 'document-text',
                'color' => 'blue'
            ],
            'pending_approval' => [
                'count' => $orderStats['pending'],
                'label' => 'orders waiting for approval',
                'action' => 'Approve',
                'route' => 'orders.index',
                'params' => ['status' => 'pending'],
                'icon' => 'check-circle',
                'color' => 'green'
            ],
            'supplies' => [
                'count' => $totalPurchaseOrders,
                'label' => 'purchase orders pending',
                'action' => 'View',
                'route' => 'supplies.index',
                'params' => ['status' => 'pending'],
                'icon' => 'shopping-cart',
                'color' => 'purple'
            ]
        ];
        
        // Filter out tasks with 0 count
        $pendingTasks = array_filter($tasks, function($task) {
            return $task['count'] > 0;
        });
        
        // Get product counts by category
        $productCategories = \App\Models\Product::select(
                'categories.name as category_name',
                DB::raw('count(*) as count')
            )
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->get()
            ->pluck('count', 'category_name')
            ->toArray();
            
        // Map category names to match the chart's expectations
        $dashboardData = [
            'product_categories' => [
                'Drugs' => $productCategories['Drugs'] ?? 0,
                'Consumables' => $productCategories['Consumables'] ?? 0,
                'Lab' => $productCategories['Lab'] ?? 0,
            ],
            'summary' => [
                [
                    'label' => 'Total Facilities',
                    'value' => $totalFacilities,
                    'unit' => 'facilities',
                    'date' => now()->format('m/d/Y'),
                    'color' => 'blue',
                ],
                [
                    'label' => 'Total Users',
                    'value' => $totalUsers,
                    'unit' => 'users',
                    'date' => now()->format('m/d/Y'),
                    'color' => 'green',
                ],
                [
                    'label' => 'Total Orders',
                    'value' => $orderStats['total'],
                    'unit' => 'orders',
                    'date' => now()->format('m/d/Y'),
                    'color' => 'red',
                ],
                [
                    'label' => 'Purchase Orders',
                    'value' => $totalPurchaseOrders,
                    'unit' => 'orders',
                    'date' => now()->format('m/d/Y'),
                    'color' => 'purple',
                ],
                [
                    'label' => 'Items Issued',
                    'value' => $totalIssuedQuantity,
                    'unit' => 'items',
                    'date' => now()->format('m/d/Y'),
                    'color' => 'indigo',
                ],
                [
                    'label' => 'Items Received',
                    'value' => $totalReceivedQuantity,
                    'unit' => 'items',
                    'date' => now()->format('m/d/Y'),
                    'color' => 'teal',
                ],
            ],
            'order_stats' => $orderStats,
            'tasks' => array_values($pendingTasks),
            'recommended_actions' => [
                'Stock levels for Paracetamol 500mg Tab 30% are below the recommended threshold. Consider placing a reorder for 1,200 units to avoid stock-out.',
                'Gil-Nugal Hospital has excess stock of Albendazole 400mg Tab. Transfer 500 units to Facility B, where demand is higher.'
            ],
            'product_status' => [
                [ 'label' => 'Paracetamol 500mg Tab', 'percent' => 30, 'approved' => $orderStats['approved'], 'rejected' => $orderStats['rejected'], 'pending' => $orderStats['pending'], 'in_process' => $orderStats['in_process'], 'dispatched' => $orderStats['dispatched'], 'delivered' => $orderStats['delivered'] ],
                [ 'label' => 'Ceftriaxone 1g Injection', 'percent' => 60 ],
                [ 'label' => 'ORS', 'percent' => 80 ],
                [ 'label' => 'Penicillin V 5MIU Injection', 'percent' => 20 ],
                [ 'label' => 'Amoxicillin 500mg Cap', 'percent' => 50 ],
                [ 'label' => 'Gloves', 'percent' => 44 ],
                [ 'label' => 'Folic acid 5mg Tab', 'percent' => 80 ],
                [ 'label' => 'Albendazole 400mg Tab', 'percent' => 100 ],
                [ 'label' => 'Tetracycline Ointment', 'percent' => 85 ],
            ],
        ];
        
        return inertia('Dashboard', [
            'dashboardData' => $dashboardData
        ]);
    }
}
