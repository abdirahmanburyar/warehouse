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

    protected function parseExpiryDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Clean the date string (remove extra spaces, tabs, etc.)
            $cleanDate = trim($dateString);
            
            // Remove any non-printable characters and normalize
            $cleanDate = preg_replace('/[^\x20-\x7E]/', '', $cleanDate);
            $cleanDate = trim($cleanDate);
            
            Log::info('Parsing expiry date', ['original' => $dateString, 'cleaned' => $cleanDate]);
            
            // Try different date formats in order of likelihood
            $formats = [
                'd/m/Y',      // 20/02/2028
                'd-m-Y',      // 20-02-2028
                'm/d/Y',      // 02/20/2028
                'm-d-Y',      // 02-20-2028
                'Y-m-d',      // 2028-02-20
                'd/m/y',      // 20/02/28
                'm/d/y',      // 02/20/28
                'd-m-y',      // 20-02-28
                'm-d-y',      // 02-20-28
                'Y/m/d',      // 2028/02/20
                'Y-m-d H:i:s', // 2028-02-20 00:00:00
                'd/m/Y H:i:s', // 20/02/2028 00:00:00
            ];
            
            // Special handling for common Excel date formats
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $cleanDate)) {
                // Format: DD/MM/YYYY or MM/DD/YYYY
                $parts = explode('/', $cleanDate);
                if (count($parts) === 3) {
                    $day = (int)$parts[0];
                    $month = (int)$parts[1];
                    $year = (int)$parts[2];
                    
                    // Try DD/MM/YYYY first (more common internationally)
                    if ($day <= 31 && $month <= 12) {
                        try {
                            $date = Carbon::createFromDate($year, $month, $day);
                            if ($date && $date->isValid()) {
                                Log::info('Successfully parsed date with manual parsing (DD/MM/YYYY)', [
                                    'original' => $dateString,
                                    'parsed' => $date->format('Y-m-d')
                                ]);
                                return $date->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            // Try MM/DD/YYYY
                            try {
                                $date = Carbon::createFromDate($year, $day, $month);
                                if ($date && $date->isValid()) {
                                    Log::info('Successfully parsed date with manual parsing (MM/DD/YYYY)', [
                                        'original' => $dateString,
                                        'parsed' => $date->format('Y-m-d')
                                    ]);
                                    return $date->format('Y-m-d');
                                }
                            } catch (\Exception $e2) {
                                // Continue to normal parsing
                            }
                        }
                    }
                }
            }
            
            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $cleanDate);
                    if ($date && $date->isValid()) {
                        Log::info('Successfully parsed date', [
                            'format' => $format,
                            'original' => $dateString,
                            'parsed' => $date->format('Y-m-d')
                        ]);
                        return $date->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    // Continue to next format
                    continue;
                }
            }

            // If none work, try Carbon's parse as last resort
            try {
                $parsedDate = Carbon::parse($cleanDate);
                if ($parsedDate && $parsedDate->isValid()) {
                    Log::info('Successfully parsed date with Carbon::parse', [
                        'original' => $dateString,
                        'parsed' => $parsedDate->format('Y-m-d')
                    ]);
                    return $parsedDate->format('Y-m-d');
                }
            } catch (\Exception $e) {
                Log::warning('Carbon::parse also failed', ['error' => $e->getMessage()]);
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
