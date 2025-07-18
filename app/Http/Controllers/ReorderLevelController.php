<?php

namespace App\Http\Controllers;

use App\Models\ReorderLevel;
use App\Models\Product;
use App\Imports\ReorderLevelImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReorderLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $reorderLevels = ReorderLevel::query();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $reorderLevels->where(function ($query) use ($search) {
                $query->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('productID', 'like', "%{$search}%");
                });
            });
        }

        $reorderLevels->with('product')
            ->latest();

        $reorderLevels = $reorderLevels->paginate($request->input('per_page', 25), ['*'], 'page', $request->input('page', 1))
            ->withQueryString();
        $reorderLevels->setPath(url()->current()); // Force Laravel to use full URLs

        return Inertia::render('ReorderLevel/Index', [
            'reorderLevels' => $reorderLevels,
            'filters' => $request->only(['search', 'per_page', 'page'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::select('id', 'name', 'productID')
            ->orderBy('name')
            ->get();

        return Inertia::render('ReorderLevel/Create', [
            'products' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Handle multiple items
        if ($request->has('items') && is_array($request->items)) {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.amc' => 'required|numeric|min:0',
                'items.*.lead_time' => 'required|integer|min:1'
            ]);

            $createdCount = 0;
            foreach ($request->items as $item) {
                ReorderLevel::create([
                    'product_id' => $item['product_id'],
                    'amc' => $item['amc'],
                    'lead_time' => $item['lead_time']
                ]);
                $createdCount++;
            }

            $message = $createdCount === 1 
                ? 'Reorder level created successfully.' 
                : "{$createdCount} reorder levels created successfully.";

            return redirect()->route('reorder-levels.index')
                ->with('success', $message);
        }

        // Handle single item (backward compatibility)
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'amc' => 'required|numeric|min:0',
            'lead_time' => 'required|integer|min:1'
        ]);

        $reorderLevel = ReorderLevel::create([
            'product_id' => $request->product_id,
            'amc' => $request->amc,
            'lead_time' => $request->lead_time
        ]);

        return redirect()->route('reorder-levels.index')
            ->with('success', 'Reorder level created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReorderLevel $reorderLevel)
    {
        $reorderLevel->load('product');
        
        $products = Product::select('id', 'name', 'productID')
            ->orderBy('name')
            ->get();

        return Inertia::render('ReorderLevel/Edit', [
            'reorderLevel' => $reorderLevel,
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReorderLevel $reorderLevel)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'amc' => 'required|numeric|min:0',
            'lead_time' => 'required|integer|min:1'
        ]);

        $reorderLevel->update([
            'product_id' => $request->product_id,
            'amc' => $request->amc,
            'lead_time' => $request->lead_time
        ]);

        return redirect()->route('reorder-levels.index')
            ->with('success', 'Reorder level updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReorderLevel $reorderLevel)
    {
        $reorderLevel->delete();

        return redirect()->route('reorder-levels.index')
            ->with('success', 'Reorder level deleted successfully.');
    }

    /**
     * Import reorder levels from Excel file
     */
    public function importExcel(Request $request)
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
            if (!$file->isValid() || $extension !== 'xlsx') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file type. Please upload an Excel file (.xlsx)'
                ], 422);
            }
    
            // Validate file size (max 50MB)
            if ($file->getSize() > 50 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'File size too large. Maximum allowed size is 50MB'
                ], 422);
            }
    
            $importId = (string) Str::uuid();
    
            Log::info('Starting reorder level import with Maatwebsite Excel', [
                'import_id' => $importId,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'extension' => $extension
            ]);
    
            // Initialize cache progress to 0
            Cache::put($importId, 0);
    
            // Import synchronously to avoid serialization issues
            Excel::import(new ReorderLevelImport($importId), $file);
    
            return response()->json([
                'success' => true,
                'message' => 'Import completed successfully',
                'import_id' => $importId
            ]);
    
        } catch (\Throwable $e) {
            Log::error('Reorder level import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
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
                        'item_description' => 'Required - Product name/description (must exist in products table)',
                        'amc' => 'Optional - Average Monthly Consumption (numeric, min 0, default 0)',
                        'lead_time' => 'Optional - Lead Time in days (integer, min 1, default 5)'
                    ],
                    'sample_data' => [
                        [
                            'item_description' => 'Paracetamol 500mg',
                            'amc' => 100.5,
                            'lead_time' => 5
                        ],
                        [
                            'item_description' => 'Amoxicillin 250mg',
                            'amc' => 75.0,
                            'lead_time' => 7
                        ],
                        [
                            'item_description' => 'Ibuprofen 400mg',
                            'amc' => 50.0,
                            'lead_time' => ''
                        ]
                    ],
                    'file_requirements' => [
                        'supported_formats' => ['xlsx'],
                        'max_file_size' => '50MB',
                        'first_row' => 'Must contain headers',
                        'encoding' => 'UTF-8 recommended'
                    ],
                    'validation_rules' => [
                        'item_description' => 'Required, must match existing product name exactly',
                        'amc' => 'Optional, numeric, minimum 0, default 0',
                        'lead_time' => 'Optional, integer, minimum 1, default 5 days'
                    ],
                    'formula' => 'Reorder Level = AMC × Lead Time'
                ]
            ]
        ]);
    }
}
