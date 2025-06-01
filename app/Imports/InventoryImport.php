<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Location;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InventoryImport implements ToCollection
{
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $batchSize = 100;

    /**
     * Process the collection of rows from the Excel file
     */
    public function collection(Collection $rows)
    {
        // Get headers to identify columns
        $headers = $rows->first();
        if (!$headers) return;
        
        // Initialize column indexes
        $itemDescIndex = null;
        $uomIndex = null;
        $quantityIndex = null;
        $batchNumberIndex = null;
        $barcodeIndex = null;
        $expiryDateIndex = null;
        $warehouseIndex = null;
        $locationIndex = null;
        
        foreach ($headers as $key => $header) {
            if (is_string($header)) {
                $header = trim(strtolower($header));
                if ($header === 'item description') {
                    $itemDescIndex = $key;
                } else if ($header === 'uom') {
                    $uomIndex = $key;
                } else if ($header === 'quantity') {
                    $quantityIndex = $key;
                } else if ($header === 'batch number') {
                    $batchNumberIndex = $key;
                } else if ($header === 'barcode') {
                    $barcodeIndex = $key;
                } else if ($header === 'expiry date') {
                    $expiryDateIndex = $key;
                } else if ($header === 'warehouse') {
                    $warehouseIndex = $key;
                } else if ($header === 'location') {
                    $locationIndex = $key;
                }
            }
        }
        
        // Validate that we found the required columns
        if ($itemDescIndex === null) {
            $this->errors[] = "Item Description column not found in Excel file";
            return;
        }
        
        if ($quantityIndex === null) {
            $this->errors[] = "Quantity column not found in Excel file";
            return;
        }
        
        if ($warehouseIndex === null) {
            $this->errors[] = "Warehouse column not found in Excel file";
            return;
        }
        
        // Increase PHP execution time limit for large imports
        set_time_limit(300); // 5 minutes
        
        DB::beginTransaction();
        try {
            // Cache for warehouses and locations to reduce database queries
            $warehouseCache = [];
            $locationCache = [];
            $productCache = [];
            
            // Process data rows
            foreach ($rows->skip(1) as $rowIndex => $row) {
                // Skip empty rows
                if (empty($row[$itemDescIndex]) || empty($row[$warehouseIndex])) {
                    $this->errors[] = "Row " . ($rowIndex + 2) . " skipped: Missing required data";
                    $this->skippedCount++;
                    continue;
                }
                
                // Trim whitespace from values
                $itemName = trim($row[$itemDescIndex]);
                $uom = ($uomIndex !== null && !empty($row[$uomIndex])) ? trim($row[$uomIndex]) : null;
                $quantity = ($quantityIndex !== null && !empty($row[$quantityIndex])) ? (float)$row[$quantityIndex] : 0;
                $batchNumber = ($batchNumberIndex !== null && !empty($row[$batchNumberIndex])) ? trim($row[$batchNumberIndex]) : null;
                $barcode = ($barcodeIndex !== null && !empty($row[$barcodeIndex])) ? trim($row[$barcodeIndex]) : null;
                $expiryDate = ($expiryDateIndex !== null && !empty($row[$expiryDateIndex])) ? $row[$expiryDateIndex] : null;
                $warehouseName = trim($row[$warehouseIndex]);
                $locationName = ($locationIndex !== null && !empty($row[$locationIndex])) ? trim($row[$locationIndex]) : null;

                // Convert Excel date if needed
                if ($expiryDate && is_numeric($expiryDate)) {
                    $expiryDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expiryDate)->format('Y-m-d');
                }
                
                // Find product by name or create new one
                if (!isset($productCache[$itemName])) {
                    $product = Product::where('name', $itemName)->first();
                    if (!$product) {
                        // Get first available category and dosage
                        $defaultCategory = \App\Models\Category::first();
                        $defaultDosage = \App\Models\Dosage::first();
                        
                        if (!$defaultCategory || !$defaultDosage) {
                            $this->errors[] = "Row " . ($rowIndex + 2) . " skipped: No categories or dosages exist in the system. Please create them first.";
                            $this->skippedCount++;
                            continue;
                        }
                        
                        // Create new product if it doesn't exist
                        $product = Product::create([
                            'name' => $itemName,
                            'category_id' => $defaultCategory->id,
                            'dosage_id' => $defaultDosage->id,
                            'reorder_level' => 10, // Default reorder level
                            'is_active' => true,
                        ]);
                        
                        // Log that we created a new product
                        Log::info("Created new product during import: {$itemName} with ID {$product->id}");
                    }
                    $productCache[$itemName] = $product->id;
                }
                $productId = $productCache[$itemName];
                
                // Find or create warehouse
                if (!isset($warehouseCache[$warehouseName])) {
                    $warehouse = Warehouse::firstOrCreate(
                        ['name' => $warehouseName],
                        [
                            'code' => strtoupper(substr(str_replace(' ', '', $warehouseName), 0, 5)),
                            'status' => 'active',
                            'user_id' => auth()->id() // Add authenticated user ID
                        ]
                    );
                    $warehouseCache[$warehouseName] = $warehouse->id;
                }
                $warehouseId = $warehouseCache[$warehouseName];
                
                // Find or create location if provided
                $locationId = null;
                if ($locationName) {
                    $cacheKey = $warehouseId . '-' . $locationName;
                    if (!isset($locationCache[$cacheKey])) {
                        $location = Location::firstOrCreate(
                            [
                                'location' => $locationName,
                                'warehouse_id' => $warehouseId
                            ]
                        );
                        $locationCache[$cacheKey] = $location->id;
                    }
                    $locationId = $locationCache[$cacheKey];
                }
                
                // Check if inventory already exists with the same product, warehouse, and batch number
                $existingInventory = Inventory::where('product_id', $productId)
                    ->where('warehouse_id', $warehouseId)
                    ->where('batch_number', $batchNumber)
                    ->first();
                
                try {
                    if ($existingInventory) {
                        // Update existing inventory
                        $existingInventory->quantity += $quantity;
                        if ($locationId) {
                            $existingInventory->location_id = $locationId;
                        }
                        if ($expiryDate) {
                            $existingInventory->expiry_date = $expiryDate;
                        }
                        if ($uom) {
                            $existingInventory->uom = $uom;
                        }
                        if ($barcode) {
                            $existingInventory->barcode = $barcode;
                        }
                        $existingInventory->save();
                    } else {
                        // Create new inventory record
                        Inventory::create([
                            'product_id' => $productId,
                            'warehouse_id' => $warehouseId,
                            'location_id' => $locationId,
                            'quantity' => $quantity,
                            'batch_number' => $batchNumber,
                            'barcode' => $barcode,
                            'expiry_date' => $expiryDate,
                            'uom' => $uom,
                            'reorder_level' => 10, // Default value
                            'is_active' => true,
                        ]);
                    }
                    
                    // Increment imported count
                    $this->importedCount++;
                } catch (\Exception $e) {
                    $this->errors[] = "Failed to import inventory for '{$itemName}': {$e->getMessage()}";
                    $this->skippedCount++;
                    continue;
                }

                // Log progress for large imports
                if ($this->importedCount % 500 === 0) {
                    Log::info("Processed {$this->importedCount} inventory items so far");
                }
            }
            
            // Log final processing count
            Log::info("Processed final batch, total: {$this->importedCount} inventory items");
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Inventory import error: ' . $e->getMessage());
            $this->errors[] = "Import failed: " . $e->getMessage();
        }
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
