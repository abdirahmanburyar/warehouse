<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\MohInventory;
use App\Models\MohInventoryItem;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Carbon\Carbon;

class MohInventoryImport implements 
    ToModel, 
    WithHeadingRow, 
    WithChunkReading, 
    WithBatchInserts, 
    SkipsEmptyRows, 
    WithEvents,
    ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public $importId;
    public $mohInventoryId;
    private $totalRows = 0;
    private $processedRows = 0;

    public function __construct(string $importId, int $mohInventoryId)
    {
        $this->importId = $importId;
        $this->mohInventoryId = $mohInventoryId;
        
        Log::info('MohInventoryImport initialized', [
            'import_id' => $importId,
            'moh_inventory_id' => $this->mohInventoryId
        ]);
    }

    public function model(array $row)
    {
        // Increment processed rows counter
        $this->processedRows++;
        
        // Update progress every 10 rows or at the end
        if ($this->processedRows % 10 == 0 || $this->processedRows == $this->totalRows) {
            $progress = $this->totalRows > 0 ? min(100, round(($this->processedRows / $this->totalRows) * 100)) : 0;
            Cache::put($this->importId, $progress);
            Log::info('MOH inventory import progress updated', [
                'import_id' => $this->importId,
                'processed_rows' => $this->processedRows,
                'total_rows' => $this->totalRows,
                'progress' => $progress
            ]);
        }
        
        // Log the row data for debugging
        Log::info('Processing MOH inventory row', ['row' => $row]);
        
        // Check if required fields are present - try different column name variations
        $itemName = $row['item'] ?? $row['Item'];
        $quantity = $row['quantity'] ?? $row['Quantity'];
        
        if (empty($itemName) || empty($quantity)) {
            return null;
        }

        // Get product (will throw exception if not found)
        $product = $this->getOrCreateProduct($row, $itemName);

        // Get warehouse (will throw exception if not found)
        $warehouseName = $row['warehouse'] ?? $row['Warehouse'] ?? $row['WAREHOUSE'] ?? 'Main Warehouse';
        // Clean the warehouse name to remove tabs and extra whitespace
        $warehouseName = trim(preg_replace('/\s+/', ' ', $warehouseName));
        $warehouse = $this->getWarehouse($warehouseName);

        // Parse expiry date - try different column name variations
        $expiryDateValue = $row['expiry_date'] ?? $row['Expiry Date'] ?? $row['EXPIRY_DATE'] ?? $row['expiry'] ?? null;
        $expiryDate = $this->parseExpiryDate($row['expiry_date']);

        // Create MOH inventory item with flexible column mapping and data cleaning
        $item = MohInventoryItem::create([
            'moh_inventory_id' => $this->mohInventoryId,
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => (int) $quantity,
            'expiry_date' => $expiryDate,
            'batch_number' => trim($row['batch_no'] ?? $row['Batch No'] ?? $row['BATCH_NO'] ?? $row['batch_number'] ?? '') ?: null,
            'barcode' => trim($row['barcode'] ?? $row['Barcode'] ?? $row['BARCODE'] ?? '') ?: null,
            'location' => trim($row['location'] ?? $row['Location'] ?? $row['LOCATION'] ?? '') ?: null,
            'notes' => trim($row['notes'] ?? $row['Notes'] ?? $row['NOTES'] ?? '') ?: null,
            'uom' => trim($row['uom'] ?? $row['UoM'] ?? $row['UOM'] ?? $row['unit'] ?? '') ?: null,
            'source' => trim($row['source'] ?? $row['Source'] ?? $row['SOURCE'] ?? '') ?: 'Excel Import',
            'unit_cost' => (float) ($row['unit_cost'] ?? $row['Unit Cost'] ?? $row['UNIT_COST'] ?? $row['unit_cost'] ?? $row['UnitCost'] ?? 0),
            'total_cost' => (float) ($row['unit_cost'] ?? $row['Unit Cost'] ?? $row['UNIT_COST'] ?? $row['unit_cost'] ?? $row['UnitCost'] ?? 0) * (float) $quantity,
        ]);
        return null;
    }

    protected function getOrCreateProduct($row, $itemName)
    {
        // Try to find existing product by name
        $product = Product::where('name', $itemName)->first();
        
        if ($product) {
            Log::info('Found existing product', ['product_id' => $product->id, 'name' => $itemName]);
            return $product;
        }

        // If not found, create the product
        Log::info('Product not found, creating new product', ['name' => $itemName]);
        
        // Get or create category
        $categoryName = trim($row['category'] ?? $row['Category'] ?? $row['CATEGORY'] ?? 'General');
        $category = $this->getOrCreateCategory($categoryName);
        
        // Get or create dosage
        $dosageName = trim($row['uom'] ?? $row['UoM'] ?? $row['UOM'] ?? $row['unit'] ?? 'Pcs');
        $dosage = $this->getOrCreateDosage($dosageName);
        
        // Create the product
        $product = Product::create([
            'name' => $itemName,
            'category_id' => $category->id,
            'dosage_id' => $dosage->id,
            'is_active' => true,
            'tracert_type' => ['batch_number', 'expiry_date'] // Default tracking types
        ]);
        
        Log::info('Created new product', [
            'product_id' => $product->id,
            'name' => $itemName,
            'category_id' => $category->id,
            'dosage_id' => $dosage->id
        ]);
        
        return $product;
    }

    protected function getOrCreateCategory($categoryName)
    {
        // Try to find existing category by name
        $category = Category::where('name', $categoryName)->first();
        
        if ($category) {
            Log::info('Found existing category', ['category_id' => $category->id, 'name' => $categoryName]);
            return $category;
        }

        // If not found, create the category
        Log::info('Category not found, creating new category', ['name' => $categoryName]);
        
        $category = Category::create([
            'name' => $categoryName,
            'description' => "Auto-created category for MOH inventory import",
            'is_active' => true
        ]);
        
        Log::info('Created new category', ['category_id' => $category->id, 'name' => $categoryName]);
        
        return $category;
    }

    protected function getOrCreateDosage($dosageName)
    {
        // Try to find existing dosage by name
        $dosage = Dosage::where('name', $dosageName)->first();
        
        if ($dosage) {
            Log::info('Found existing dosage', ['dosage_id' => $dosage->id, 'name' => $dosageName]);
            return $dosage;
        }

        // If not found, create the dosage
        Log::info('Dosage not found, creating new dosage', ['name' => $dosageName]);
        
        $dosage = Dosage::create([
            'name' => $dosageName,
            'description' => "Auto-created dosage for MOH inventory import",
            'is_active' => true
        ]);
        
        Log::info('Created new dosage', ['dosage_id' => $dosage->id, 'name' => $dosageName]);
        
        return $dosage;
    }

    protected function getWarehouse($warehouseName)
    {
        // Clean the warehouse name (remove tabs, extra spaces, etc.)
        $cleanName = trim($warehouseName);
                
        $warehouse = Warehouse::where('name', $cleanName)->first();
        
        if (!$warehouse) {
            // If not found, throw exception to stop import
            $errorMessage = "Warehouse '{$cleanName}' not found in database. Please add this warehouse first before importing.";
            
            throw new \Exception($errorMessage);
        } else {
            Log::info('Found existing warehouse', ['warehouse_id' => $warehouse->id, 'name' => $warehouse->name]);
        }

        return $warehouse;
    }

    protected function parseExpiryDate($expiryDateValue)
    {
        if (empty($expiryDateValue)) {
            return null; // Return null since the field is nullable
        }

        try {
            // Check if it's an Excel serial number (numeric)
            if (is_numeric($expiryDateValue)) {
                // Convert Excel serial number to date
                // Excel dates are days since 1900-01-01
                $excelDate = (int) $expiryDateValue;
                $unixTimestamp = ($excelDate - 25569) * 86400; // Convert to Unix timestamp
                $date = Carbon::createFromTimestamp($unixTimestamp);
                return $date->format('Y-m-d');
            }

            // Try to parse as "M-y" format (Feb-25, Jun-27) - Default format
            try {
                $date = Carbon::createFromFormat('M-y', $expiryDateValue);
                // Set to first day of the month for month-year format
                return $date->startOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                // If M-y format fails, continue to try normal date formats
                // Don't throw exception, just continue
            }

            // Try various normal date formats
            $dateFormats = [
                'd-m-Y',    // 15-02-2025
                'Y-m-d',    // 2025-02-15
                'd/m/Y',    // 15/02/2025
                'Y/m/d',    // 2025/02/15
                'm-d-Y',    // 02-15-2025
                'Y-m-d H:i:s', // 2025-02-15 00:00:00
                'd-m-Y H:i:s', // 15-02-2025 00:00:00
            ];

            foreach ($dateFormats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $expiryDateValue);
                    return $date->format('Y-m-d');
                } catch (\Exception $e) {
                    // Continue to next format
                    continue;
                }
            }
            return null;

        } catch (\Exception $e) {
            return null; // Return null since the field is nullable
        }
    }


    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function(BeforeImport $event) {
                // Get total row count for progress tracking
                $this->totalRows = $event->getReader()->getTotalRows();
                Log::info('MOH inventory import started', [
                    'import_id' => $this->importId,
                    'moh_inventory_id' => $this->mohInventoryId,
                    'total_rows' => $this->totalRows
                ]);
                
                // Initialize progress to 0
                Cache::put($this->importId, 0);
            },
            AfterImport::class => function(AfterImport $event) {
                Log::info('MOH inventory import completed', [
                    'import_id' => $this->importId,
                    'moh_inventory_id' => $this->mohInventoryId,
                    'processed_rows' => $this->processedRows
                ]);
                
                // Update cache to indicate completion
                Cache::put($this->importId, 100);
            },
            ImportFailed::class => function(ImportFailed $event) {
                Log::error('MOH inventory import failed', [
                    'import_id' => $this->importId,
                    'moh_inventory_id' => $this->mohInventoryId,
                    'error' => $event->getException()->getMessage()
                ]);
                
                // Update cache to indicate failure
                Cache::put($this->importId, -1);
            },
        ];
    }
}
