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
        $query = Product::with(['category', 'dosage'])->latest();

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
        return Inertia::render('Product/Edit', [
            'product' => $product,
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
                'id' => 'nullable|exists:products,id',
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    $request->id ? Rule::unique('products', 'name')->ignore($request->id) : Rule::unique('products', 'name')
                ],
                'category_id' => 'nullable|exists:categories,id',
                'dosage_id' => 'nullable|exists:dosages,id',
                'movement' => 'required|string',
                'facility_types' => 'nullable|array'
            ]);
    
            DB::beginTransaction();
    
            // Create or update the product
            $product = Product::find($request->id);
            if($product){
                $product->update([
                    'name' => $request->name,
                    'category_id' => $request->category_id,
                    'dosage_id' => $request->dosage_id,
                    'movement' => $request->movement,
                ]);
            }else{
                $product = Product::create([
                    'name' => $request->name,
                    'category_id' => $request->category_id,
                    'dosage_id' => $request->dosage_id,
                    'movement' => $request->movement,
                ]);
            }
    
            // Only assign facility types for new products
            if (!$request->id && !empty($request->facility_types)) {
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
    
            return response()->json($request->id ? 'Product updated successfully.' : 'Product created successfully.', 200);
    
        } catch (Throwable $th) {
            DB::rollBack();
            logger()->error('Product store error', ['error' => $th->getMessage()]);
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
        $originalFile = null;
        try {
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file was uploaded'
                ], 422);
            }

            $file = $request->file('file');
            $originalFile = $file->getRealPath(); // Store original file path
            
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

            // Dispatch the import job
            ProcessProductImport::dispatch($filePath, $importId);

            // Delete the original uploaded file
            if ($originalFile && file_exists($originalFile)) {
                unlink($originalFile);
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

            // Clean up original file in case of error
            if ($originalFile && file_exists($originalFile)) {
                unlink($originalFile);
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
            
            return response()->json([
                'success' => true,
                'data' => [
                    'imported' => $imported,
                    'skipped' => $skipped,
                    'errors' => $errors,
                    'completed' => $imported > 0 || $skipped > 0 // Simple way to check if processing has started
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
