<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MohInventory;
use App\Models\MohInventoryItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use App\Models\Warehouse;
use App\Imports\MohInventoryImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class MohInventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Get non-approved MOH inventories for the select dropdown
            $nonApprovedInventories = MohInventory::whereNull('approved_at')
                ->with([
                    'mohInventoryItems.product.category:id,name',
                    'mohInventoryItems.product.dosage:id,name',
                    'mohInventoryItems.warehouse:id,name',
                    'reviewer:id,name'
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Get selected MOH inventory details if ID is provided
            $selectedInventory = null;
            if ($request->filled('inventory_id')) {
                $selectedInventory = MohInventory::with([
                    'mohInventoryItems.product.category:id,name',
                    'mohInventoryItems.product.dosage:id,name',
                    'mohInventoryItems.warehouse:id,name',
                    'reviewer:id,name',
                    'approver:id,name'
                ])->find($request->inventory_id);
            }

            // Get filter options
            $categories = Category::select('id', 'name')->orderBy('name')->get();
            $dosages = Dosage::select('id', 'name')->orderBy('name')->get();

            return Inertia::render('MohInventory/Index', [
                'nonApprovedInventories' => $nonApprovedInventories,
                'selectedInventory' => $selectedInventory,
                'categories' => $categories,
                'dosages' => $dosages,
                'filters' => $request->only(['inventory_id', 'search', 'category_id', 'dosage_id']),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error loading MOH inventory: ' . $e->getMessage());
        }
    }

    /**
     * Import MOH inventory items from Excel file
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
                    'message' => 'Invalid file type. Please upload an Excel file (.xlsx, .xls) or CSV file'
                ], 422);
            }

            // Validate file size (max 50MB)
            if ($file->getSize() > 50 * 1024 * 1024) {
                return response()->json([
                    'success' => false,
                    'message' => 'File size too large. Maximum allowed size is 50MB'
                ], 422);
            }

            // Get or create MOH inventory
            $mohInventory = $this->getOrCreateMohInventory($request);

            $importId = (string) Str::uuid();

            Log::info('Queueing MOH inventory import with Maatwebsite Excel', [
                'import_id' => $importId,
                'moh_inventory_id' => $mohInventory->id,
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'extension' => $extension
            ]);

            // Initialize cache progress to 0
            Cache::put($importId, 0);

            // Queue the import job
            Excel::queueImport(new MohInventoryImport($importId, $mohInventory->id), $file)->onQueue('imports');

            return response()->json([
                'success' => true,
                'message' => 'MOH inventory import has been queued successfully',
                'import_id' => $importId,
                'moh_inventory_id' => $mohInventory->id
            ]);

        } catch (\Exception $e) {
            Log::error('MOH inventory import failed', [
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
     * Get or create MOH inventory for import
     */
    protected function getOrCreateMohInventory(Request $request)
    {
        // If a specific MOH inventory ID is provided, use it
        if ($request->filled('moh_inventory_id')) {
            $mohInventory = MohInventory::find($request->moh_inventory_id);
            if ($mohInventory) {
                return $mohInventory;
            }
        }

        // Otherwise, create a new MOH inventory
        $mohInventory = MohInventory::create([
            'uuid' => 'MOH-' . strtoupper(uniqid()),
            'date' => now()->toDateString(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        Log::info('New MOH inventory created for import', [
            'moh_inventory_id' => $mohInventory->id,
            'uuid' => $mohInventory->uuid
        ]);

        return $mohInventory;
    }

    /**
     * Get import progress
     */
    public function getImportProgress(Request $request)
    {
        $importId = $request->input('import_id');
        
        if (!$importId) {
            return response()->json([
                'success' => false,
                'message' => 'Import ID is required'
            ], 422);
        }

        $progress = Cache::get($importId, 0);

        return response()->json([
            'success' => true,
            'progress' => $progress,
            'completed' => $progress >= 100
        ]);
    }

}
