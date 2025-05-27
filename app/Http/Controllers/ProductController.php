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
use Throwable;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $lastFourMonths = now()->subMonths(4)->format('Y-m');
        $leadTime = 5; // days

        // Method 1: Calculate AMC and reorder level using SQL (efficient way)
        // Update reorder levels with a default of 0 for products without AMC data
        DB::statement("
            UPDATE products p
            SET reorder_level = COALESCE(
                (
                    SELECT CEIL(avg_quantity * ? + avg_quantity)
                    FROM (
                        SELECT 
                            product_id, 
                            CEIL(AVG(quantity)) as avg_quantity
                        FROM warehouse_amcs
                        WHERE month_year >= ?
                        GROUP BY product_id
                    ) amc
                    WHERE amc.product_id = p.id
                ),
                0  -- Default value if no AMC data exists
            )
        ", [$leadTime, $lastFourMonths]);

        $query = Product::with(['category', 'dosage'])->latest();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
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

        $products = $query->paginate($request->input('per_page', 2), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $products->setPath(url()->current()); // Force Laravel to use full URLs


        return Inertia::render('Product/Index', [
            'products' => ProductResource::collection($products),
            'categories' => Category::pluck('name')->toArray(),
            'dosages' => Dosage::pluck('name')->toArray(),
            'filters' => $request->only(['search', 'category', 'dosage', 'per_page', 'page'])
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
            $validated = $request->validate([
                'id' => 'nullable|exists:products,id',
                'name' => $request->id ? 'required|string|max:255' : 'required|string|max:255|unique:products,name',
                'category_id' => 'nullable|exists:categories,id',
                'dosage_id' => 'nullable|exists:dosages,id',
                'movement' => 'required',
                'reorder_level' => 'nullable|numeric',
                'facility_types' => 'nullable|array'
            ]);

            DB::beginTransaction();
            
            // Create or update the product
            $product = Product::updateOrCreate(['id' => $validated['id']], [
                'name' => $validated['name'],
                'category_id' => $validated['category_id'] ?? null,
                'dosage_id' => $validated['dosage_id'] ?? null,
                'movement' => $validated['movement'],
                'reorder_level' => $request->reorder_level ?? null
            ]);
            
            // For a new product, we need to make sure we have the correct ID
            if ($request->id === null) {
                // Get the latest product with this name
                $freshProduct = Product::where('name', $validated['name'])->latest()->first();
                
                if ($freshProduct) {
                    $product = $freshProduct;
                    logger()->info('Using product with ID: ' . $product->id);
                } else {
                    logger()->error('Could not find the newly created product');
                }
            }
            
            // Handle facility types if provided - only for new products
            if (!empty($request->facility_types) && $request->id === null && $product) {
                // Create eligible items for each facility type
                foreach ($request->facility_types as $facilityType) {
                    EligibleItem::create([
                        'product_id' => $product->id,
                        'facility_type' => $facilityType
                    ]);
                }
            }
            
            DB::commit();
            
            return response()->json($request->id ? 'Product updated successfully.' : 'Product created successfully.', 200);
        } catch (Throwable $e) {
            DB::rollBack();
            logger()->info($e->getMessage());
            return response()->json($e->getMessage(), 500);
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
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        try {
            // Start database transaction
            DB::beginTransaction();
            
            // Create a new instance of ProductsImport
            $import = new ProductsImport();
            
            // Get the file extension to determine how to process it
            $extension = $request->file('file')->getClientOriginalExtension();
            
            // Try to use ZipArchive if available, but don't require it
            if (!class_exists('ZipArchive')) {
                logger()->warning('ZipArchive extension not available. Using alternative import method.');
            }
            
            // Import the Excel file
            Excel::import($import, $request->file('file'));
            
            // Commit the transaction if everything is successful
            DB::commit();

            // Get import statistics
            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getErrors();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} products. Skipped {$skippedCount} products.",
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'errors' => $errors
            ]);
        } catch (\Throwable $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            
            logger()->info('Excel import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error importing products: ' . $e->getMessage()
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
}
