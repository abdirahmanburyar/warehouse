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
            'unit_cost' => (float) ($row['unit_cost'] ?? $row['Unit Cost'] ?? $row['UNIT_COST'] ?? $row['unit_cost'] ?? $row['UnitCost'] ?? 0),
            'total_cost' => (float) ($row['unit_cost'] ?? $row['Unit Cost'] ?? $row['UNIT_COST'] ?? $row['unit_cost'] ?? $row['UnitCost'] ?? 0) * (float) $quantity,
        ]);

        Log::info('MOH inventory item created', [
            'item_id' => $item->id,
            'product' => $product->name,
            'quantity' => $item->quantity,
            'unit_cost' => $item->unit_cost,
            'total_cost' => $item->total_cost
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
