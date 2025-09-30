<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\MohInventory;
use App\Models\MohInventoryItem;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Facades\Cache;
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
        
    }

    public function model(array $row)
    {
        // Increment processed rows counter
        $this->processedRows++;
        
        // Update progress every 10 rows or at the end
        if ($this->processedRows % 10 == 0 || $this->processedRows == $this->totalRows) {
            $progress = $this->totalRows > 0 ? min(100, round(($this->processedRows / $this->totalRows) * 100)) : 0;
            Cache::put($this->importId, $progress);

        }
        
        
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
            return $product;
        }

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

        
        return $product;
    }

    protected function getOrCreateCategory($categoryName)
    {
        // Try to find existing category by name
        $category = Category::where('name', $categoryName)->first();
        
        if ($category) {
            return $category;
        }

        $category = Category::create([
            'name' => $categoryName,
            'description' => "Auto-created category for MOH inventory import",
            'is_active' => true
        ]);
                
        return $category;
    }

    protected function getOrCreateDosage($dosageName)
    {
        // Try to find existing dosage by name
        $dosage = Dosage::where('name', $dosageName)->first();
        
        if ($dosage) {
            return $dosage;
        }

        $dosage = Dosage::create([
            'name' => $dosageName,
            'description' => "Auto-created dosage for MOH inventory import",
            'is_active' => true
        ]);
        
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
                
                // Initialize progress to 0
                Cache::put($this->importId, 0);
            },
            AfterImport::class => function(AfterImport $event) {
                
                // Update cache to indicate completion
                Cache::put($this->importId, 100);
            },
            ImportFailed::class => function(ImportFailed $event) {
                                
                // Update cache to indicate failure
                Cache::put($this->importId, -1);
            },
        ];
    }
}
