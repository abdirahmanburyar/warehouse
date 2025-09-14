<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MohInventory;
use App\Models\MohInventoryItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Location;
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
                    'reviewer:id,name',
                    'approver:id,name',
                    'rejectedBy:id,name',
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
                    'approver:id,name',
                    'rejectedBy:id,name'
                ])->find($request->inventory_id);
            }

            // Get filter options
            $categories = Category::select('id', 'name')->orderBy('name')->get();
            $dosages = Dosage::select('id', 'name')->orderBy('name')->get();
            
            // Get products and locations for edit modal
            $products = Product::with(['category:id,name', 'dosage:id,name'])
                ->select('id', 'name', 'category_id', 'dosage_id')
                ->orderBy('name')
                ->get();
            $warehouses = Warehouse::select('id', 'name')->orderBy('name')->get();
            $locations = Location::select('id', 'location', 'warehouse')->orderBy('location')->get();

            return Inertia::render('MohInventory/Index', [
                'nonApprovedInventories' => $nonApprovedInventories,
                'selectedInventory' => $selectedInventory,
                'categories' => $categories,
                'dosages' => $dosages,
                'products' => $products,
                'warehouses' => $warehouses,
                'locations' => $locations,
                'filters' => $request->only(['inventory_id', 'search', 'category_id', 'dosage_id']),
            ]);
        } catch (\Throwable $e) {
            logger()->error('Error loading MOH inventory: ' . $e->getMessage());
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

    /**
     * Test import functionality
     */
    public function testImport(Request $request)
    {
        try {
            // Create a test MOH inventory
            $mohInventory = MohInventory::create([
                'uuid' => 'MOH-TEST-' . strtoupper(uniqid()),
                'date' => now()->toDateString(),
                'reviewed_at' => null,
                'reviewed_by' => null,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            // Create a test product
            $product = Product::first();
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'No products found in database'
                ], 422);
            }

            // Create a test warehouse
            $warehouse = Warehouse::first();
            if (!$warehouse) {
                return response()->json([
                    'success' => false,
                    'message' => 'No warehouses found in database'
                ], 422);
            }

            // Create a test MOH inventory item
            $item = MohInventoryItem::create([
                'moh_inventory_id' => $mohInventory->id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => 10,
                'expiry_date' => now()->addYear()->toDateString(),
                'batch_number' => 'TEST-BATCH-001',
                'barcode' => 'TEST-BARCODE-001',
                'location' => 'Test Location',
                'notes' => 'Test item created via API',
                'uom' => 'pcs',
                'source' => 'Test Import',
                'unit_cost' => 10.50,
                'total_cost' => 105.00,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test import successful',
                'moh_inventory_id' => $mohInventory->id,
                'item_id' => $item->id,
                'product_name' => $product->name,
                'warehouse_name' => $warehouse->name
            ]);

        } catch (\Exception $e) {
            Log::error('Test import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change MOH inventory status
     */
    public function changeStatus(Request $request, MohInventory $mohInventory)
    {
        $request->validate([
            'status' => 'required|in:reviewed,approved,rejected'
        ]);

        $status = $request->input('status');
        $user = auth()->user();

        try {
            switch ($status) {
                case 'reviewed':
                    $mohInventory->update([
                        'reviewed_at' => now(),
                        'reviewed_by' => $user->id,
                    ]);
                    $message = 'MOH inventory has been reviewed successfully';
                    break;

                case 'approved':
                    if (!$mohInventory->reviewed_at) {
                        return response()->json([
                            'success' => false,
                            'message' => 'MOH inventory must be reviewed before approval'
                        ], 400);
                    }
                    
                    // Update MOH inventory status
                    $mohInventory->update([
                        'approved_at' => now(),
                        'approved_by' => $user->id,
                    ]);
                    
                    // Release items to main inventory tables
                    $this->releaseItemsToInventory($mohInventory);
                    
                    $message = 'MOH inventory has been approved and items released to main inventory successfully';
                    break;

                case 'rejected':
                    $mohInventory->update([
                        'rejected_at' => now(),
                        'rejected_by' => $user->id,
                    ]);
                    $message = 'MOH inventory has been rejected';
                    break;
            }
            return response()->json([
                'success' => true,
                'message' => $message,
                'moh_inventory' => $mohInventory->fresh(['reviewer', 'approver', 'rejectedBy'])
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the status'
            ], 500);
        }
    }

    /**
     * Update a MOH inventory item
     */
    public function updateItem(Request $request, MohInventoryItem $mohInventoryItem)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'warehouse_id' => 'required|exists:warehouses,id',
                'quantity' => 'required|numeric|min:0',
                'uom' => 'nullable|string|max:255',
                'batch_number' => 'nullable|string|max:255',
                'expiry_date' => 'nullable|date',
                'location_id' => 'nullable|exists:locations,id',
                'unit_cost' => 'nullable|numeric|min:0',
                'total_cost' => 'nullable|numeric|min:0',
                'barcode' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
            ]);

            // Get location name from location_id
            $locationName = null;
            if ($request->location_id) {
                $location = Location::find($request->location_id);
                $locationName = $location ? $location->location : null;
            }

            // Update the MOH inventory item
            $mohInventoryItem->update([
                'product_id' => $request->product_id,
                'warehouse_id' => $request->warehouse_id,
                'quantity' => $request->quantity,
                'uom' => $request->uom,
                'batch_number' => $request->batch_number,
                'expiry_date' => $request->expiry_date,
                'location' => $locationName,
                'unit_cost' => $request->unit_cost,
                'total_cost' => $request->total_cost,
                'barcode' => $request->barcode,
                'notes' => $request->notes,
            ]);

            Log::info('MOH inventory item updated', [
                'moh_inventory_item_id' => $mohInventoryItem->id,
                'moh_inventory_id' => $mohInventoryItem->moh_inventory_id,
                'product_id' => $mohInventoryItem->product_id,
                'quantity' => $mohInventoryItem->quantity,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'MOH inventory item updated successfully',
                'data' => $mohInventoryItem->fresh(['product', 'warehouse'])
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update MOH inventory item', [
                'moh_inventory_item_id' => $mohInventoryItem->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update MOH inventory item: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Release approved MOH inventory items to main inventory tables
     */
    private function releaseItemsToInventory(MohInventory $mohInventory)
    {
        try {
            DB::beginTransaction();

            // Get all MOH inventory items
            $mohItems = $mohInventory->mohInventoryItems()->with(['product', 'warehouse'])->get();

            $releasedCount = 0;
            $errors = [];

            foreach ($mohItems as $mohItem) {
                try {
                    // Debug: Log MOH item details before processing
                    Log::info('Processing MOH item for release', [
                        'moh_inventory_item_id' => $mohItem->id,
                        'product_id' => $mohItem->product_id,
                        'warehouse_id' => $mohItem->warehouse_id,
                        'batch_number' => $mohItem->batch_number,
                        'expiry_date' => $mohItem->expiry_date,
                        'location' => $mohItem->location,
                        'uom' => $mohItem->uom,
                        'unit_cost' => $mohItem->unit_cost,
                        'total_cost' => $mohItem->total_cost,
                        'quantity' => $mohItem->quantity
                    ]);

                    // Check if inventory item already exists with same criteria
                    $existingInventoryItem = InventoryItem::where('product_id', $mohItem->product_id)
                        ->where('warehouse_id', $mohItem->warehouse_id)
                        ->where('batch_number', $mohItem->batch_number)
                        ->where('expiry_date', $mohItem->expiry_date)
                        ->where('location', $mohItem->location)
                        ->first();

                    if ($existingInventoryItem) {
                        // Update existing inventory item quantity
                        $existingInventoryItem->increment('quantity', $mohItem->quantity);
                        
                        // Update the main inventory quantity
                        $inventory = $existingInventoryItem->inventory;
                        $inventory->increment('quantity', $mohItem->quantity);
                        
                    } else {
                        // Check if inventory record exists for this product
                        $inventory = Inventory::where('product_id', $mohItem->product_id)->first();

                        if ($inventory) {
                            // Update existing inventory quantity
                            $inventory->increment('quantity', $mohItem->quantity);
                            
                        } else {
                            // Create new inventory record
                            $inventory = Inventory::create([
                                'product_id' => $mohItem->product_id,
                                'quantity' => $mohItem->quantity,
                            ]);
                            
                           
                        }

                        // Create new inventory item record
                        $inventoryItem = InventoryItem::create([
                            'inventory_id' => $inventory->id,
                            'product_id' => $mohItem->product_id,
                            'warehouse_id' => $mohItem->warehouse_id,
                            'quantity' => (float) $mohItem->quantity,
                            'expiry_date' => $mohItem->expiry_date,
                            'batch_number' => $mohItem->batch_number,
                            'barcode' => $mohItem->barcode,
                            'location' => $mohItem->location,
                            'notes' => $mohItem->notes,
                            'uom' => $mohItem->uom,
                            'source' => $mohItem->source,
                            'unit_cost' => $mohItem->unit_cost ? (float) $mohItem->unit_cost : null,
                            'total_cost' => $mohItem->total_cost ? (float) $mohItem->total_cost : null,
                        ]);

                        Log::info('Created new inventory item', [
                            'inventory_item_id' => $inventoryItem->id,
                            'inventory_id' => $inventory->id,
                            'moh_inventory_item_id' => $mohItem->id,
                            'product_name' => $mohItem->product->name,
                            'warehouse_name' => $mohItem->warehouse->name,
                            'batch_number' => $mohItem->batch_number,
                            'expiry_date' => $mohItem->expiry_date,
                            'location' => $mohItem->location,
                            'quantity' => $mohItem->quantity,
                            'uom' => $mohItem->uom,
                            'unit_cost' => $mohItem->unit_cost,
                            'total_cost' => $mohItem->total_cost
                        ]);
                    }

                    $releasedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to release item {$mohItem->product->name}: " . $e->getMessage();
                    Log::error('Failed to release MOH inventory item', [
                        'moh_inventory_item_id' => $mohItem->id,
                        'product_id' => $mohItem->product_id,
                        'warehouse_id' => $mohItem->warehouse_id,
                        'batch_number' => $mohItem->batch_number,
                        'expiry_date' => $mohItem->expiry_date,
                        'location' => $mohItem->location,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (!empty($errors)) {
                Log::warning('Some items failed to release', [
                    'moh_inventory_id' => $mohInventory->id,
                    'errors' => $errors,
                    'released_count' => $releasedCount,
                    'total_items' => $mohItems->count()
                ]);
            }

            DB::commit();

            Log::info('MOH inventory items released to main inventory', [
                'moh_inventory_id' => $mohInventory->id,
                'released_count' => $releasedCount,
                'total_items' => $mohItems->count(),
                'errors_count' => count($errors)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to release MOH inventory items', [
                'moh_inventory_id' => $mohInventory->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Store a newly created MOH inventory.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.dosage_id' => 'nullable|exists:dosages,id',
                'items.*.quantity' => 'required|numeric|min:0',
                'items.*.uom' => 'nullable|string|max:255',
                'items.*.source' => 'nullable|string|max:255',
                'items.*.batch_number' => 'nullable|string|max:255',
                'items.*.expiry_date' => 'nullable|date',
                'items.*.location_id' => 'nullable|exists:locations,id',
                'items.*.warehouse_id' => 'required|exists:warehouses,id',
                'items.*.unit_cost' => 'nullable|numeric|min:0',
                'items.*.total_cost' => 'nullable|numeric|min:0',
                'items.*.barcode' => 'nullable|string|max:255',
                'items.*.notes' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();

            // Create MOH inventory
            $mohInventory = MohInventory::create([
                'uuid' => Str::uuid(),
                'date' => $request->date,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Create MOH inventory items
            foreach ($request->items as $itemData) {
                // Calculate total cost if not provided
                $totalCost = $itemData['total_cost'] ?? ($itemData['quantity'] * ($itemData['unit_cost'] ?? 0));
                
                // Get location name from location_id
                $locationName = null;
                if ($itemData['location_id']) {
                    $location = Location::find($itemData['location_id']);
                    $locationName = $location ? $location->location : null;
                }

                MohInventoryItem::create([
                    'moh_inventory_id' => $mohInventory->id,
                    'product_id' => $itemData['product_id'],
                    'dosage_id' => $itemData['dosage_id'],
                    'quantity' => $itemData['quantity'],
                    'uom' => $itemData['uom'],
                    'source' => $itemData['source'],
                    'batch_number' => $itemData['batch_number'],
                    'expiry_date' => $itemData['expiry_date'],
                    'location' => $locationName,
                    'warehouse_id' => $itemData['warehouse_id'],
                    'unit_cost' => $itemData['unit_cost'],
                    'total_cost' => $totalCost,
                    'barcode' => $itemData['barcode'],
                    'notes' => $itemData['notes'],
                ]);
            }

            DB::commit();

            Log::info('MOH inventory created successfully', [
                'moh_inventory_id' => $mohInventory->id,
                'items_count' => count($request->items),
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'MOH inventory created successfully',
                'data' => $mohInventory->load('mohInventoryItems')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to create MOH inventory', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create MOH inventory: ' . $e->getMessage()
            ], 500);
        }
    }

}

