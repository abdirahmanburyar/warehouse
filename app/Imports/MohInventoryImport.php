<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\MohInventory;
use App\Models\MohInventoryItem;
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
        // Log the row data for debugging
        Log::info('Processing MOH inventory row', ['row' => $row]);
        
        // Check if required fields are present - try different column name variations
        $itemName = trim($row['item'] ?? $row['Item'] ?? $row['ITEM'] ?? '');
        $quantity = trim($row['quantity'] ?? $row['Quantity'] ?? $row['QUANTITY'] ?? '');
        
        if (empty($itemName) || empty($quantity)) {
            Log::warning('Skipping row due to missing required fields', [
                'row' => $row,
                'item_name' => $itemName,
                'quantity' => $quantity
            ]);
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
        $expiryDate = $this->parseExpiryDate($expiryDateValue);

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
            'unit_cost' => (float) ($row['unit_cost'] ?? $row['Unit Cost'] ?? $row['UNIT_COST'] ?? 0),
            'total_cost' => (float) $quantity * (float) ($row['unit_cost'] ?? $row['Unit Cost'] ?? $row['UNIT_COST'] ?? 0),
        ]);

        Log::info('MOH inventory item created', [
            'item_id' => $item->id,
            'product' => $product->name,
            'quantity' => $item->quantity
        ]);

        return null;
    }

    protected function getOrCreateProduct($row, $itemName)
    {
        // Try to find existing product by name
        $product = Product::where('name', $itemName)->first();
        
        if ($product) {
            Log::info('Found existing product', ['product_id' => $product->id, 'name' => $product->name]);
            return $product;
        }

        // If not found, throw exception to stop import
        $errorMessage = "Product '{$itemName}' not found in database. Please add this product first before importing.";
        Log::error('Product not found during MOH import', [
            'product_name' => $itemName,
            'row' => $row
        ]);
        
        throw new \Exception($errorMessage);
    }


    protected function getWarehouse($warehouseName)
    {
        // Clean the warehouse name (remove tabs, extra spaces, etc.)
        $cleanName = trim($warehouseName);
        
        Log::info('Looking for warehouse', ['original_name' => $warehouseName, 'clean_name' => $cleanName]);
        
        $warehouse = Warehouse::where('name', $cleanName)->first();
        
        if (!$warehouse) {
            // If not found, throw exception to stop import
            $errorMessage = "Warehouse '{$cleanName}' not found in database. Please add this warehouse first before importing.";
            Log::error('Warehouse not found during MOH import', [
                'warehouse_name' => $cleanName,
                'original_name' => $warehouseName
            ]);
            
            throw new \Exception($errorMessage);
        } else {
            Log::info('Found existing warehouse', ['warehouse_id' => $warehouse->id, 'name' => $warehouse->name]);
        }

        return $warehouse;
    }

    protected function parseExpiryDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Clean the date string (remove extra spaces, tabs, etc.)
            $cleanDate = trim($dateString);
            
            // Try different date formats
            $formats = ['d/m/Y', 'm/d/Y', 'Y-m-d', 'd-m-Y', 'm-d-Y', 'd/m/y', 'm/d/y'];
            
            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $cleanDate);
                    if ($date && $date->isValid()) {
                        return $date->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    // Continue to next format
                    continue;
                }
            }

            // If none work, try Carbon's parse as last resort
            $parsedDate = Carbon::parse($cleanDate);
            if ($parsedDate && $parsedDate->isValid()) {
                return $parsedDate->format('Y-m-d');
            }
            
            Log::warning('Failed to parse expiry date with all methods', [
                'date_string' => $dateString,
                'clean_date' => $cleanDate
            ]);
            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to parse expiry date', [
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
            return null;
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
            AfterImport::class => function(AfterImport $event) {
                Log::info('MOH inventory import completed', [
                    'import_id' => $this->importId,
                    'moh_inventory_id' => $this->mohInventoryId
                ]);
                
                // Update cache to indicate completion
                Cache::put($this->importId, 100);
            },
        ];
    }
}
