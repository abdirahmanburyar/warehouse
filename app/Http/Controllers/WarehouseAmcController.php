<?php

namespace App\Http\Controllers;

use App\Models\WarehouseAmc;
use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use App\Imports\WarehouseAmcImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WarehouseAmcExport;

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

        // Add additional years for template selection (current year + 2 years back and 2 years forward)
        $currentYear = now()->year;
        $additionalYears = collect();
        for ($i = -2; $i <= 2; $i++) {
            $additionalYears->push($currentYear + $i);
        }
        
        // Merge and deduplicate years
        $years = $years->merge($additionalYears)->unique()->sort()->values();

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

        return Inertia::render('Report/WarehouseAmc', [
            'products' => $products,
            'pivotData' => $pivotData,
            'monthYears' => $monthYears,
            'filters' => $request->only(['search', 'category', 'dosage', 'year', 'sort', 'direction', 'per_page']),
            'categories' => $categories,
            'dosages' => $dosages,
            'years' => $years,
            'months' => $months,
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

        $filename = 'warehouse_amc_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new WarehouseAmcExport($pivotData, $monthYears), $filename);
    }

    /**
     * Import warehouse AMC data from Excel
     */
    public function import(Request $request)
    {
        try {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file was uploaded'
                ], 422);
            }

            $file = $request->file('file');

            // Validate file type
            $extension = $file->getClientOriginalExtension();
            if (!$file->isValid() || !in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file type. Please upload an Excel file (.xlsx, .xls) or CSV file (.csv)'
                ], 422);
            }

            $importId = (string) Str::uuid();

            Log::info('Starting warehouse AMC import with Maatwebsite Excel', [
                'import_id' => $importId,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'extension' => $extension
            ]);

            // Initialize cache progress for tracking
            Cache::put("warehouse_amc_import_{$importId}", [
                'status' => 'processing',
                'progress' => 0,
                'message' => 'Import started'
            ], 3600); // 1 hour expiry

            // Determine import method based on file size
            $fileSize = $file->getSize();
            $largeFileThreshold = 5 * 1024 * 1024; // 5MB

            if ($fileSize > $largeFileThreshold) {
                // Use queued import for large files
                try {
                    Excel::queueImport(new WarehouseAmcImport($importId), $file)->onQueue('imports');
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Large file detected. Import has been queued and will process in the background.',
                        'import_id' => $importId,
                        'queued' => true
                    ]);
                    
                } catch (\Exception $queueError) {
                    Log::warning('Queue import failed, falling back to synchronous: ' . $queueError->getMessage());
                    // Fall through to synchronous import
                }
            }

            // Use synchronous import for smaller files or if queue failed
            $import = new WarehouseAmcImport($importId);
            Excel::import($import, $file);
            
            // Get import results
            $results = $import->getResults();
            
            $message = "Import completed successfully. ";
            if ($results['imported'] > 0) {
                $message .= "Created: {$results['imported']} new AMC records. ";
            }
            if ($results['updated'] > 0) {
                $message .= "Updated: {$results['updated']} existing AMC records. ";
            }
            if ($results['skipped'] > 0) {
                $message .= "Skipped: {$results['skipped']} rows. ";
            }

            // Update cache with completion status
            Cache::put("warehouse_amc_import_{$importId}", [
                'status' => 'completed',
                'progress' => 100,
                'message' => trim($message),
                'results' => $results
            ], 3600);

            return response()->json([
                'success' => true,
                'message' => trim($message),
                'import_id' => $importId,
                'results' => $results,
                'queued' => false
            ]);

        } catch (\Throwable $e) {
            Log::error('Warehouse AMC import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update cache with error status
            if (isset($importId)) {
                Cache::put("warehouse_amc_import_{$importId}", [
                    'status' => 'failed',
                    'progress' => 0,
                    'message' => 'Import failed: ' . $e->getMessage()
                ], 3600);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check import status
     */
    public function checkImportStatus($importId)
    {
        try {
            $cacheKey = "warehouse_amc_import_{$importId}";
            $status = Cache::get($cacheKey);
            
            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Import not found or expired',
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $status,
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Import status check failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to check import status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download template for warehouse AMC import
     */
    public function downloadTemplate(Request $request)
    {
        try {
            // Get the selected year from request, default to current year
            $selectedYear = $request->get('year', now()->year);
            
            // Get months for the selected year only
            $monthYears = WarehouseAmc::select('month_year')
                ->where('month_year', 'like', $selectedYear . '-%')
                ->distinct()
                ->orderBy('month_year', 'asc')
                ->pluck('month_year');

            // If no months found for selected year, create default months
            if ($monthYears->isEmpty()) {
                $monthYears = collect();
                for ($month = 1; $month <= 12; $month++) {
                    $monthYears->push($selectedYear . '-' . str_pad($month, 2, '0', STR_PAD_LEFT));
                }
            }

            // Get ALL products for template
            $allProducts = Product::with(['category:id,name', 'dosage:id,name'])
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->leftJoin('dosages', 'products.dosage_id', '=', 'dosages.id')
                ->select([
                    'products.id',
                    'products.name',
                    'categories.name as category_name',
                    'dosages.name as dosage_name'
                ])
                ->orderBy('products.name')
                ->get();

            // Create template data with ALL products
            $templateData = [];
            foreach ($allProducts as $product) {
                $row = [
                    'name' => $product->name,
                    'category' => $product->category_name,
                    'dosage' => $product->dosage_name,
                ];

                // Add sample quantities for each month (empty for template - won't overwrite existing data)
                foreach ($monthYears as $monthYear) {
                    $row[$monthYear] = ''; // Empty cell - won't overwrite existing data
                }

                $templateData[] = $row;
            }

            $filename = 'warehouse_amc_import_template_' . $selectedYear . '_' . now()->format('Y-m-d') . '.xlsx';
            
            return Excel::download(new WarehouseAmcExport($templateData, $monthYears), $filename);

        } catch (\Exception $e) {
            Log::error('Template download failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Template download failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
