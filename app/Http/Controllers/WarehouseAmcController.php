<?php

namespace App\Http\Controllers;

use App\Models\WarehouseAmc;
use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class WarehouseAmcController extends Controller
{
    /**
     * Display the warehouse AMC report
     */
    public function index(Request $request)
    {
        // Get unique month-years for filtering and display
        $monthYears = WarehouseAmc::select('month_year')
            ->distinct()
            ->orderBy('month_year', 'desc')
            ->pluck('month_year');

        // Get years from month-years
        $years = $monthYears->map(function($monthYear) {
            return explode('-', $monthYear)[0];
        })->unique()->sort()->values();

        // Get months from month-years
        $months = $monthYears->map(function($monthYear) {
            return explode('-', $monthYear)[1];
        })->unique()->sort()->values();

        // Get categories for filter
        $categories = Category::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get dosages for filter
        $dosages = Dosage::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Build the pivot table query
        $query = Product::query()
            ->with([
                'category:id,name',
                'dosage:id,name'
            ])
            ->select([
                'products.id',
                'products.name',
                'categories.name as category_name',
                'dosages.name as dosage_name'
            ])
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('dosages', 'products.dosage_id', '=', 'dosages.id')
            ->whereExists(function($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('warehouse_amcs')
                    ->whereColumn('warehouse_amcs.product_id', 'products.id');
            });

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('categories.name', 'like', "%{$search}%")
                  ->orWhere('dosages.name', 'like', "%{$search}%");
            });
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('categories.id', $request->category);
        }

        // Apply dosage filter
        if ($request->filled('dosage')) {
            $query->where('dosages.id', $request->dosage);
        }

        // Apply year filter
        if ($request->filled('year')) {
            $query->whereExists(function($subQuery) use ($request) {
                $subQuery->select(DB::raw(1))
                    ->from('warehouse_amcs')
                    ->whereColumn('warehouse_amcs.product_id', 'products.id')
                    ->where('warehouse_amcs.month_year', 'like', "{$request->year}-%");
            });
        }

        // Apply sorting
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        if ($sortField === 'category_name') {
            $query->orderBy('categories.name', $sortDirection);
        } elseif ($sortField === 'dosage_name') {
            $query->orderBy('dosages.name', $sortDirection);
        } else {
            $query->orderBy('products.name', $sortDirection);
        }

        // Paginate results
        $perPage = $request->get('per_page', 25);
        $products = $query->paginate($perPage);

        // Transform data to pivot table format
        $pivotData = [];
        foreach ($products->items() as $product) {
            $row = [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category_name,
                'dosage' => $product->dosage_name,
                'months' => []
            ];

            // Get consumption data for each month
            foreach ($monthYears as $monthYear) {
                $consumption = WarehouseAmc::where('product_id', $product->id)
                    ->where('month_year', $monthYear)
                    ->value('quantity') ?? 0;
                
                $row['months'][$monthYear] = $consumption;
            }

            $pivotData[] = $row;
        }

        // Calculate summary statistics
        $summary = [
            'total_products' => Product::whereExists(function($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('warehouse_amcs')
                    ->whereColumn('warehouse_amcs.product_id', 'products.id');
            })->count(),
            'total_consumption' => WarehouseAmc::sum('quantity'),
            'average_consumption' => WarehouseAmc::avg('quantity'),
            'highest_consumption' => WarehouseAmc::max('quantity'),
            'lowest_consumption' => WarehouseAmc::min('quantity'),
        ];

        // Get top consuming products
        $topProducts = WarehouseAmc::with('product:id,name')
            ->select('product_id', DB::raw('SUM(quantity) as total_consumption'))
            ->groupBy('product_id')
            ->orderBy('total_consumption', 'desc')
            ->limit(10)
            ->get();

        // Get consumption trend for current year
        $currentYear = now()->year;
        $consumptionTrend = WarehouseAmc::select('month_year', DB::raw('SUM(quantity) as total_consumption'))
            ->where('month_year', 'like', "{$currentYear}-%")
            ->groupBy('month_year')
            ->orderBy('month_year')
            ->get();

        return Inertia::render('Report/WarehouseAmc', [
            'products' => $products,
            'pivotData' => $pivotData,
            'monthYears' => $monthYears,
            'filters' => $request->only(['search', 'category', 'dosage', 'year', 'sort', 'direction', 'per_page']),
            'categories' => $categories,
            'dosages' => $dosages,
            'years' => $years,
            'months' => $months,
            'summary' => $summary,
            'topProducts' => $topProducts,
            'consumptionTrend' => $consumptionTrend,
        ]);
    }

    /**
     * Get warehouse AMC data for API
     */
    public function getData(Request $request)
    {
        $query = WarehouseAmc::query()
            ->with([
                'product:id,name,category_id,dosage_id',
                'product.category:id,name',
                'product.dosage:id,name'
            ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('product.category', function($q) use ($request) {
                $q->where('id', $request->category);
            });
        }

        if ($request->filled('dosage')) {
            $query->whereHas('product.dosage', function($q) use ($request) {
                $q->where('id', $request->dosage);
            });
        }

        if ($request->filled('month_year')) {
            $query->where('month_year', $request->month_year);
        }

        // Apply sorting
        $sortField = $request->get('sort', 'month_year');
        $sortDirection = $request->get('direction', 'desc');
        
        if ($sortField === 'product_name') {
            $query->whereHas('product', function($q) use ($sortDirection) {
                $q->orderBy('name', $sortDirection);
            });
        } elseif ($sortField === 'quantity') {
            $query->orderBy('quantity', $sortDirection);
        } elseif ($sortField === 'month_year') {
            $query->orderBy('month_year', $sortDirection);
        } else {
            $query->orderBy('month_year', 'desc');
        }

        $perPage = $request->get('per_page', 25);
        return $query->paginate($perPage);
    }

    /**
     * Export warehouse AMC data
     */
    public function export(Request $request)
    {
        // Get unique month-years for display
        $monthYears = WarehouseAmc::select('month_year')
            ->distinct()
            ->orderBy('month_year', 'desc')
            ->pluck('month_year');

        // Build the pivot table query
        $query = Product::query()
            ->with([
                'category:id,name',
                'dosage:id,name'
            ])
            ->select([
                'products.id',
                'products.name',
                'categories.name as category_name',
                'dosages.name as dosage_name'
            ])
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('dosages', 'products.dosage_id', '=', 'dosages.id')
            ->whereExists(function($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('warehouse_amcs')
                    ->whereColumn('warehouse_amcs.product_id', 'products.id');
            });

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('products.name', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $query->where('categories.id', $request->category);
        }

        if ($request->filled('dosage')) {
            $query->where('dosages.id', $request->dosage);
        }

        if ($request->filled('year')) {
            $query->whereExists(function($subQuery) use ($request) {
                $subQuery->select(DB::raw(1))
                    ->from('warehouse_amcs')
                    ->whereColumn('warehouse_amcs.product_id', 'products.id')
                    ->where('warehouse_amcs.month_year', 'like', "{$request->year}-%");
            });
        }

        $products = $query->orderBy('products.name')->get();

        // Transform data to pivot table format
        $pivotData = [];
        foreach ($products as $product) {
            $row = [
                'name' => $product->name,
                'category' => $product->category_name,
                'dosage' => $product->dosage_name,
            ];

            // Add consumption data for each month
            foreach ($monthYears as $monthYear) {
                $consumption = WarehouseAmc::where('product_id', $product->id)
                    ->where('month_year', $monthYear)
                    ->value('quantity') ?? 0;
                
                $row[$monthYear] = $consumption;
            }

            $pivotData[] = $row;
        }

        $filename = 'warehouse_amc_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($pivotData, $monthYears) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            $csvHeaders = ['Item', 'Category', 'Dosage Form'];
            foreach ($monthYears as $monthYear) {
                $csvHeaders[] = $monthYear;
            }
            fputcsv($file, $csvHeaders);

            // Add data
            foreach ($pivotData as $row) {
                $csvRow = [
                    $row['name'],
                    $row['category'],
                    $row['dosage']
                ];
                
                foreach ($monthYears as $monthYear) {
                    $csvRow[] = $row[$monthYear];
                }
                
                fputcsv($file, $csvRow);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
