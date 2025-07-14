<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use App\Models\EligibleItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\DosageResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $query = Product::query();
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // EligibleItem
        if($request->filled('eligible')){
            $type = $request->input('eligible');
            if($type == 'All'){
                $query->whereHas('eligible', function ($q) use ($request) {
                    $q->whereIn('facility_type', ['Regional Hospital', 'District Hospital', 'Health Centre', 'Primary Health Unit']);
                });
            }else{
                $query->whereHas('eligible', function ($q) use ($type) {
                    $q->where('facility_type', 'like', "%{$type}%");
                });
            }
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        // Dosage filter
        if ($request->filled('dosage')) {
            $query->whereHas('dosage', function ($q) use ($request) {
                $q->where('name', $request->dosage);
            });
        }

        $query->with(['category', 'dosage','eligible'])->latest();

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $query->with('dosage','category');

        $products = $query->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $products->setPath(url()->current()); // Force Laravel to use full URLs


        return Inertia::render('Product/Index', [
            'products' => ProductResource::collection($products),
            'categories' => Category::pluck('name')->toArray(),
            'dosages' => Dosage::pluck('name')->toArray(),
            'filters' => $request->only(['search', 'category', 'dosage', 'per_page', 'page','eligible', 'status'])
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return Inertia::render('Product/Create', [
            'categories' => CategoryResource::collection(Category::all()),
            'dosages' => DosageResource::collection(Dosage::all())
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        // Load the product with its relationships
        $product->load(['category', 'dosage', 'eligible']);
        
        // Get facility types from eligible items
        $facilityTypes = $product->eligible->pluck('facility_type')->toArray();
        
        // Add facility_types to the product data
        $productData = $product->toArray();
        $productData['facility_types'] = $facilityTypes;
        
        return Inertia::render('Product/Edit', [
            'product' => $productData,
            'categories' => CategoryResource::collection(Category::all()),
            'dosages' => DosageResource::collection(Dosage::all())
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('products', 'name')
                ],
                'category_id' => 'nullable|exists:categories,id',
                'dosage_id' => 'nullable|exists:dosages,id',
                'facility_types' => 'nullable|array',
                'tracert_type' => 'nullable|string',
            ]);
    
            DB::beginTransaction();
    
            $product = Product::create([
                'tracert_type' => $request->tracert_type,
                'name' => $request->name,
                'category_id' => $request->category_id,
                'dosage_id' => $request->dosage_id,
            ]);
    
            // Assign facility types for new products
            if (!empty($request->facility_types)) {
                $facilityTypes = $request->facility_types;
    
                if (in_array('All', $facilityTypes)) {
                    // Replace "All" with all actual types
                    $facilityTypes = ['Health Centre', 'Primary Health Unit', 'District Hospital', 'Regional Hospital'];
                }
    
                foreach ($facilityTypes as $type) {
                    EligibleItem::create([
                        'product_id' => $product->id,
                        'facility_type' => $type,
                    ]);
                }
            }
    
            DB::commit();
    
            return response()->json('Product created successfully.', 200);
    
        } catch (Throwable $th) {
            DB::rollBack();
            logger()->error('Product store error', ['error' => $th->getMessage()]);
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('products', 'name')->ignore($product->id)
                ],
                'category_id' => 'nullable|exists:categories,id',
                'dosage_id' => 'nullable|exists:dosages,id',
                'tracert_type' => 'nullable|string',
                'facility_types' => 'nullable|array',
            ]);
    
            DB::beginTransaction();
    
            $product->update([
                'name' => $request->name,
                'category_id' => $request->category_id,
                'dosage_id' => $request->dosage_id,
                'tracert_type' => $request->tracert_type,
            ]);

            // Handle facility types - remove existing and add new ones
            if (isset($request->facility_types)) {
                // Delete all existing eligible items for this product
                $product->eligible()->delete();
                
                // Add new facility types if any are selected
                if (!empty($request->facility_types)) {
                    $facilityTypes = $request->facility_types;
        
                    if (in_array('All', $facilityTypes)) {
                        // Replace "All" with all actual types
                        $facilityTypes = ['Health Centre', 'Primary Health Unit', 'District Hospital', 'Regional Hospital'];
                    }
        
                    foreach ($facilityTypes as $type) {
                        EligibleItem::create([
                            'product_id' => $product->id,
                            'facility_type' => $type,
                        ]);
                    }
                }
            }
    
            DB::commit();
    
            return response()->json('Product updated successfully.', 200);
    
        } catch (Throwable $th) {
            DB::rollBack();
            logger()->error('Product update error', ['error' => $th->getMessage()]);
            return response()->json($th->getMessage(), 500);
        }
    }
    

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Import products from Excel file.
     */
    public function importExcel(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv',
            ]);
    
            $importId = (string) Str::uuid();
    
            // Get total rows (first sheet only)
            $collection = Excel::toCollection(new ProductImport($importId), $request->file('file'));
            $totalRows = $collection->first()->count();
    
            Cache::put($importId . '_progress', 0);
            Cache::put($importId . '_total', $totalRows);
    
            Excel::queueImport(new ProductImport($importId), $request->file('file'))
                ->onQueue('imports');
    
            return response()->json([
                'message' => 'Import started successfully.',
                'import_id' => $importId
            ], 200);
    
        } catch (\Throwable $th) {
            logger()->error('Product import error', ['error' => $th->getMessage()]);
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Get sample Excel format and validation rules
     */
    public function getImportFormat()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'format' => [
                    'headers' => [
                        'item_description' => 'Required - Product name/description (max 255 characters)',
                        'category' => 'Optional - Product category (max 255 characters)',
                        'dosage_form' => 'Optional - Dosage form (max 255 characters)'
                    ],
                    'sample_data' => [
                        [
                            'item_description' => 'Paracetamol 500mg',
                            'category' => 'Pain Relief',
                            'dosage_form' => 'Tablet'
                        ],
                        [
                            'item_description' => 'Amoxicillin 250mg',
                            'category' => 'Antibiotics',
                            'dosage_form' => 'Capsule'
                        ]
                    ],
                    'file_requirements' => [
                        'supported_formats' => ['xlsx', 'xls', 'csv'],
                        'max_file_size' => '50MB',
                        'first_row' => 'Must contain headers',
                        'encoding' => 'UTF-8 recommended'
                    ],
                    'validation_rules' => [
                        'item_description' => 'Required, max 255 characters, must be unique',
                        'category' => 'Optional, max 255 characters, will be created if not exists',
                        'dosage_form' => 'Optional, max 255 characters, will be created if not exists'
                    ]
                ]
            ]
        ]);
    }

    public function toggleStatus(Product $product)
    {
        try {
            $product->is_active = !$product->is_active;
            $product->save();

            return response()->json($product->is_active ? 'Product activated successfully.' : 'Product deactivated successfully.', 200);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage(), 500);
        }
    }

    /**
     * Check the status of a queued import
     */
    public function checkImportStatus($importId)
    {
        try {
            $imported = Cache::get("import_{$importId}_imported", 0);
            $skipped = Cache::get("import_{$importId}_skipped", 0);
            $errors = Cache::get("import_{$importId}_errors", []);
            $status = Cache::get("import_{$importId}_status", 'unknown');
            $startedAt = Cache::get("import_{$importId}_started_at");
            
            // Calculate processing time if started
            $processingTime = null;
            if ($startedAt) {
                $processingTime = now()->diffInSeconds($startedAt);
            }

            // Determine if processing is complete
            $isCompleted = in_array($status, ['completed', 'failed']);
            $hasStarted = $imported > 0 || $skipped > 0 || $status !== 'queued';
            
            return response()->json([
                'success' => true,
                'data' => [
                    'import_id' => $importId,
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors,
                    'status' => $status,
                    'started_at' => $startedAt,
                    'processing_time_seconds' => $processingTime,
                    'has_started' => $hasStarted,
                    'completed' => $isCompleted,
                    'total_processed' => $imported + $skipped,
                    'error_count' => count($errors)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking import status', [
                'import_id' => $importId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error checking import status: ' . $e->getMessage()
            ], 500);
        }
    }
}
