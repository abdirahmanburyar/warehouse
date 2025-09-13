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
            // Check if required fields are present
            if (empty($row['item']) || empty($row['quantity'])) {
                Log::warning('Skipping row due to missing required fields', ['row' => $row]);
                return null;
            }

            // Get or create product
            $product = $this->getOrCreateProduct($row);
            if (!$product) {
                Log::error('Failed to get or create product', ['row' => $row]);
                return null;
            }

            // Get warehouse
            $warehouse = $this->getWarehouse($row['warehouse'] ?? 'Main Warehouse');

            // Parse expiry date
            $expiryDate = $this->parseExpiryDate($row['expiry_date'] ?? null);

            // Create MOH inventory item
            $item = MohInventoryItem::create([
                'moh_inventory_id' => $this->mohInventoryId,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => (int) $row['quantity'],
                'expiry_date' => $expiryDate,
                'batch_number' => $row['batch_no'] ?? null,
                'barcode' => $row['barcode'] ?? null,
                'location' => $row['location'] ?? null,
                'notes' => $row['notes'] ?? null,
                'uom' => $row['uom'] ?? null,
                'source' => $row['source'] ?? 'Excel Import',
                'unit_cost' => (float) ($row['unit_cost'] ?? 0),
                'total_cost' => (float) ($row['quantity']) * (float) ($row['unit_cost'] ?? 0),
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

    protected function getOrCreateProduct($row)
    {
        // First try to find existing product by name
        $product = Product::where('name', $row['item'])->first();
        
        if ($product) {
            return $product;
        }

        // If not found, create new product
        $category = $this->getOrCreateCategory($row['category'] ?? 'General');
        $dosage = $this->getOrCreateDosage($row['dosage'] ?? 'N/A');

        $product = Product::create([
            'name' => $row['item'],
            'product_code' => $this->generateProductCode($row['item']),
            'category_id' => $category->id,
            'dosage_id' => $dosage->id,
            'description' => $row['description'] ?? null,
            'is_active' => true,
        ]);

        Log::info('New product created for MOH import', [
            'product_id' => $product->id,
            'name' => $product->name,
            'category' => $category->name
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
                    'moh_inventory_id' => $this->mohInventoryId
                ]);
                
                // Update cache to indicate completion
                Cache::put($this->importId, 100);
            },
        ];
    }
}
