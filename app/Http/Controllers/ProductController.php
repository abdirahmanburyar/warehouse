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
use App\Imports\ProductsImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessProductImport;

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
        $tempFile = null;
        try {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file was uploaded'
                ], 422);
            }

            $file = $request->file('file');
            $tempFile = $file->getPathname(); // Get temporary file path
            
            // Validate file
            if (!$file->isValid() || !in_array($file->getClientOriginalExtension(), ['xlsx', 'xls', 'csv'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file type. Please upload an Excel file (.xlsx, .xls) or CSV file'
                ], 422);
            }

            // Create directory if it doesn't exist
            $uploadPath = public_path('excel-imports');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Generate unique filename and import ID
            $importId = uniqid();
            $filename = $importId . '.' . $file->getClientOriginalExtension();
            
            // Move file to public directory
            $file->move($uploadPath, $filename);
            $filePath = $uploadPath . '/' . $filename;

            Log::info('File uploaded successfully', [
                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $filename,
                'path' => $filePath
            ]);

            // Initialize cache values
            Cache::put("import_{$importId}_imported", 0, now()->addHours(1));
            Cache::put("import_{$importId}_skipped", 0, now()->addHours(1));
            Cache::put("import_{$importId}_errors", [], now()->addHours(1));
            Cache::put("import_{$importId}_status", 'queued', now()->addHours(1));

            // Dispatch the import job with proper queue configuration
            ProcessProductImport::dispatch($filePath, $importId)->onQueue('default');

            // Delete the temporary uploaded file
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }

            return response()->json([
                'success' => true,
                'message' => 'Import started successfully',
                'import_id' => $importId
            ]);

        } catch (\Exception $e) {
            Log::error('Import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Clean up temporary file in case of error
            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
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
            
            return response()->json([
                'success' => true,
                'data' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors,
                    'status' => $status,
                    'completed' => in_array($status, ['completed', 'failed']) || $imported > 0 || $skipped > 0
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking import status: ' . $e->getMessage()
            ], 500);
        }
    }
}
