<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Dosage;
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
        try {
            // Log the row data for debugging
            Log::info('Processing MOH inventory row', ['row' => $row]);
            
            // Check if required fields are present - try different column name variations
            $itemName = $row['item'] ?? $row['Item'] ?? $row['ITEM'] ?? null;
            $quantity = $row['quantity'] ?? $row['Quantity'] ?? $row['QUANTITY'] ?? null;
            
            if (empty($itemName) || empty($quantity)) {
                Log::warning('Skipping row due to missing required fields', [
                    'row' => $row,
                    'item_name' => $itemName,
                    'quantity' => $quantity
                ]);
                return null;
            }

            // Get or create product
            $product = $this->getOrCreateProduct($row, $itemName);
            if (!$product) {
                Log::error('Failed to get or create product', ['row' => $row, 'item_name' => $itemName]);
                return null;
            }

            // Get warehouse - try different column name variations
            $warehouseName = $row['warehouse'] ?? $row['Warehouse'] ?? $row['WAREHOUSE'] ?? 'Main Warehouse';
            $warehouse = $this->getWarehouse($warehouseName);

            // Parse expiry date - try different column name variations
            $expiryDateValue = $row['expiry_date'] ?? $row['Expiry Date'] ?? $row['EXPIRY_DATE'] ?? $row['expiry'] ?? null;
            $expiryDate = $this->parseExpiryDate($expiryDateValue);

            // Create MOH inventory item with flexible column mapping
            $item = MohInventoryItem::create([
                'moh_inventory_id' => $this->mohInventoryId,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => (int) $quantity,
                'expiry_date' => $expiryDate,
                'batch_number' => $row['batch_no'] ?? $row['Batch No'] ?? $row['BATCH_NO'] ?? $row['batch_number'] ?? null,
                'barcode' => $row['barcode'] ?? $row['Barcode'] ?? $row['BARCODE'] ?? null,
                'location' => $row['location'] ?? $row['Location'] ?? $row['LOCATION'] ?? null,
                'notes' => $row['notes'] ?? $row['Notes'] ?? $row['NOTES'] ?? null,
                'uom' => $row['uom'] ?? $row['UoM'] ?? $row['UOM'] ?? $row['unit'] ?? null,
                'source' => $row['source'] ?? $row['Source'] ?? $row['SOURCE'] ?? 'Excel Import',
                'unit_cost' => (float) ($row['unit_cost'] ?? $row['Unit Cost'] ?? $row['UNIT_COST'] ?? 0),
                'total_cost' => (float) $quantity * (float) ($row['unit_cost'] ?? $row['Unit Cost'] ?? $row['UNIT_COST'] ?? 0),
            ]);

            Log::info('MOH inventory item created', [
                'item_id' => $item->id,
                'product' => $product->name,
                'quantity' => $item->quantity
            ]);

            return null;

        } catch (\Throwable $e) {
            Log::error('MOH inventory import error', [
                'error' => $e->getMessage(),
                'row' => $row,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function getOrCreateProduct($row, $itemName)
    {
        // First try to find existing product by name
        $product = Product::where('name', $itemName)->first();
        
        if ($product) {
            Log::info('Found existing product', ['product_id' => $product->id, 'name' => $product->name]);
            return $product;
        }

        // If not found, create new product
        $categoryName = $row['category'] ?? $row['Category'] ?? $row['CATEGORY'] ?? 'General';
        $dosageName = $row['dosage'] ?? $row['Dosage'] ?? $row['DOSAGE'] ?? 'N/A';
        
        $category = $this->getOrCreateCategory($categoryName);
        $dosage = $this->getOrCreateDosage($dosageName);

        $product = Product::create([
            'name' => $itemName,
            'product_code' => $this->generateProductCode($itemName),
            'category_id' => $category->id,
            'dosage_id' => $dosage->id,
            'description' => $row['description'] ?? $row['Description'] ?? $row['DESCRIPTION'] ?? null,
            'is_active' => true,
        ]);

        Log::info('New product created for MOH import', [
            'product_id' => $product->id,
            'name' => $product->name,
            'category' => $category->name,
            'dosage' => $dosage->name
        ]);

        return $product;
    }

    protected function getOrCreateCategory($categoryName)
    {
        $category = Category::where('name', $categoryName)->first();
        
        if (!$category) {
            $category = Category::create([
                'name' => $categoryName,
                'description' => 'Auto-created from MOH import'
            ]);
        }

        return $category;
    }

    protected function getOrCreateDosage($dosageName)
    {
        $dosage = Dosage::where('name', $dosageName)->first();
        
        if (!$dosage) {
            $dosage = Dosage::create([
                'name' => $dosageName,
                'description' => 'Auto-created from MOH import'
            ]);
        }

        return $dosage;
    }

    protected function getWarehouse($warehouseName)
    {
        $warehouse = Warehouse::where('name', $warehouseName)->first();
        
        if (!$warehouse) {
            // Create default warehouse if not found
            $warehouse = Warehouse::create([
                'name' => $warehouseName,
                'location' => 'Main Location',
                'is_active' => true
            ]);
        }

        return $warehouse;
    }

    protected function parseExpiryDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        try {
            // Try different date formats
            $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y'];
            
            foreach ($formats as $format) {
                $date = Carbon::createFromFormat($format, $dateString);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            }

            // If none work, try Carbon's parse
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning('Failed to parse expiry date', [
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function generateProductCode($productName)
    {
        // Generate a simple product code from the name
        $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $productName), 0, 8));
        $code .= '-' . strtoupper(uniqid());
        return $code;
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
                    'moh_inventory_id' => $this->mohInventoryId,
                    'total_rows_processed' => $event->getConcernable()->getRowCount()
                ]);
                
                // Update cache to indicate completion
                Cache::put($this->importId, 100);
            },
        ];
    }
}
