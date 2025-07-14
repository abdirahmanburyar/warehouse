<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Facility;
use Illuminate\Support\Facades\DB;
use App\Models\PackingList;
use App\Models\PurchaseOrder;
use App\Models\BackOrder;
use App\Models\Product;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Order;
use App\Models\Supplier;
use Carbon\Carbon;
use App\Models\IssueQuantityReport;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get distinct facility types and their counts (only main types)
        $facilityTypes = Facility::select('facility_type', DB::raw('count(*) as count'))
            ->whereIn('facility_type', ['Health Centre', 'Primary Health Unit', 'Regional Hospital', 'District Hospital'])
            ->groupBy('facility_type')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($type) {
                return [
                    'label' => $this->getAbbreviatedName($type->facility_type),
                    'fullName' => $type->facility_type,
                    'value' => $type->count,
                    'color' => $this->getFacilityTypeColor($type->facility_type),
                ];
            });
        
        $filter = $request->input('order_filter', 'PO'); // default to PO

        $orderCounts = [
            'PKL' => PackingList::count(),
            'PO'  => PurchaseOrder::where('status', 'approved')->count(),
            'BO'  => BackOrder::count(),
        ];

        // Product category counts
        $productCategoryCounts = [
            'Drugs' => Product::whereHas('category', function($q) { $q->where('name', 'Drug'); })->count(),
            'Consumable' => Product::whereHas('category', function($q) { $q->where('name', 'Consumable'); })->count(),
            'Lab' => Product::whereHas('category', function($q) { $q->where('name', 'Lab'); })->count(),
        ];

        // Transfer received count
        $transferReceivedCount = Transfer::where('status', 'received')->count();

        // User and Warehouse counts
        $userCount = User::count();
        $warehouseCount = Warehouse::count();

        // Order status statistics
        $orderStats = [
            'pending' => Order::where('status', 'pending')->count(),
            'reviewed' => Order::where('status', 'reviewed')->count(),
            'approved' => Order::where('status', 'approved')->count(),
            'in_process' => Order::where('status', 'in_process')->count(),
            'dispatched' => Order::where('status', 'dispatched')->count(),
            'received' => Order::where('status', 'received')->count(),
            'rejected' => Order::where('status', 'rejected')->count(),
        ];

        // Total cost of approved purchase orders
        $totalApprovedPOCost = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_orders.status', 'approved')
            ->sum('purchase_order_items.total_cost');

        // Get all suppliers first
        $suppliers = Supplier::select('id', 'name')->get();
        
        // Fulfillment percentage for packing list items by supplier
        $fulfillmentData = collect();
        
        foreach ($suppliers as $supplier) {
            $supplierFulfillment = DB::table('packing_list_items as pli')
                ->join('packing_lists as pl', 'pli.packing_list_id', '=', 'pl.id')
                ->join('purchase_order_items as poi', 'pli.po_item_id', '=', 'poi.id')
                ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
                ->where('po.supplier_id', $supplier->id)
                ->select(
                    DB::raw('SUM(poi.quantity) as total_ordered'),
                    DB::raw('SUM(pli.quantity) as total_received')
                )
                ->first();
            
            $fulfillmentPercentage = 0;
            if ($supplierFulfillment && $supplierFulfillment->total_ordered > 0) {
                $fulfillmentPercentage = round(($supplierFulfillment->total_received / $supplierFulfillment->total_ordered) * 100, 2);
            }
            
            $fulfillmentData->push((object) [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'total_ordered' => $supplierFulfillment ? $supplierFulfillment->total_ordered : 0,
                'total_received' => $supplierFulfillment ? $supplierFulfillment->total_received : 0,
                'fulfillment_percentage' => $fulfillmentPercentage
            ]);
        }

        // Calculate overall average fulfillment
        $overallFulfillment = $fulfillmentData->avg('fulfillment_percentage') ?? 0;

        // Check if we have any data in the related tables
        $packingListItemsCount = DB::table('packing_list_items')->count();
        $purchaseOrderItemsCount = DB::table('purchase_order_items')->count();
        $packingListsCount = DB::table('packing_lists')->count();
        $purchaseOrdersCount = DB::table('purchase_orders')->count();

        $ordersDelayedCount = \App\Models\Order::whereNotNull('order_date')
            ->whereNotNull('expected_date')
            ->whereRaw('order_date < expected_date')
            ->count();

        // Issued Quantity Data for Warehouse Tab
        $issuedMonths = IssueQuantityReport::orderBy('month_year', 'desc')->pluck('month_year')->unique()->toArray();
        $selectedIssuedMonth = request('issued_month', \Carbon\Carbon::now()->format('Y-m'));
        $issuedReport = IssueQuantityReport::where('month_year', $selectedIssuedMonth)
            ->with(['items.product'])
            ->first();
        $issuedData = [];
        if ($issuedReport) {
            foreach ($issuedReport->items as $item) {
                $issuedData[] = [
                    'product' => $item->product ? $item->product->name : 'Unknown',
                    'quantity' => $item->quantity,
                ];
            }
        }

        // Warehouse Tab Data Type Selection
        $warehouseDataType = request('warehouse_data_type', 'qty_issued');
        $issuedMonths = IssueQuantityReport::orderBy('month_year', 'desc')->pluck('month_year')->unique()->toArray();
        $selectedIssuedMonth = request('issued_month', Carbon::now()->format('Y-m'));
        $warehouseChartData = [];
        if ($warehouseDataType === 'qty_issued') {
            $issuedReport = IssueQuantityReport::where('month_year', $selectedIssuedMonth)
                ->with(['items.product'])
                ->first();
            if ($issuedReport) {
                foreach ($issuedReport->items as $item) {
                    $warehouseChartData[] = [
                        'product' => $item->product ? $item->product->name : 'Unknown',
                        'quantity' => $item->quantity,
                    ];
                }
            }
        }

        return Inertia::render('Dashboard', [
            'dashboardData' => [
                'summary' => $facilityTypes,
                'order_stats' => [],
                'tasks' => [],
                'recommended_actions' => [],
                'product_status' => []
            ],
            'orderCard' => [
                'filter' => $filter,
                'counts' => $orderCounts,
            ],
            'productCategoryCard' => $productCategoryCounts,
            'transferReceivedCard' => $transferReceivedCount,
            'userCountCard' => $userCount,
            'warehouseCountCard' => $warehouseCount,
            'tracertableData' => [
                'summary' => [
                    'totalItems' => 0,
                    'warehouseItems' => 0,
                    'facilityItems' => 0,
                    'totalQuantity' => 0
                ],
                'warehouseItems' => [],
                'facilityItems' => [],
                'facilities' => [],
                'warehouseChartData' => [
                    'labels' => ['In Stock', 'Low Stock', 'Out of Stock'],
                    'data' => [0, 0, 0]
                ],
                'facilityChartData' => [
                    'labels' => [],
                    'data' => []
                ]
            ],
            'orderStats' => $orderStats,
            'totalApprovedPOCost' => $totalApprovedPOCost,
            'fulfillmentData' => $fulfillmentData,
            'overallFulfillment' => $overallFulfillment,
            'ordersDelayedCount' => $ordersDelayedCount,
            'issuedMonths' => $issuedMonths,
            'selectedIssuedMonth' => $selectedIssuedMonth,
            'warehouseDataType' => $warehouseDataType,
            'warehouseChartData' => $warehouseChartData,
            'issuedData' => $issuedData,
        ]);
    }

    private function getFacilityTypeColor($facilityType)
    {
        $colors = [
            'Regional Hospital' => 'red',
            'District Hospital' => 'orange',
            'Health Centre' => 'blue',
            'Primary Health Unit' => 'green'
        ];

        return $colors[$facilityType] ?? 'gray';
    }

    private function getAbbreviatedName($facilityType)
    {
        $abbreviations = [
            'Regional Hospital' => 'RH',
            'District Hospital' => 'DH',
            'Health Centre' => 'HC',
            'Primary Health Unit' => 'PHU'
        ];

        return $abbreviations[$facilityType] ?? $facilityType;
    }
}
