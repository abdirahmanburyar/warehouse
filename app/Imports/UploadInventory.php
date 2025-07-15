<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Carbon\Carbon;

class UploadInventory implements 
    ToModel, 
    WithHeadingRow, 
    WithChunkReading, 
    WithBatchInserts, 
    SkipsEmptyRows, 
    WithEvents
{
    public $importId;
    protected $productCache = [];
    protected $inventoryCache = [];

    public function __construct(string $importId)
    {
        $this->importId = $importId;
    }

    public function model(array $row)
    {
        try {
            // Validate required fields
            if (empty($row['item']) || empty($row['quantity']) || empty($row['batch_no'])) {
                Log::warning('Row skipped: Missing required fields', $row);
                return null;
            }

            // Check if product exists (with caching)
            $product = $this->getProduct($row['item']);
            if (!$product) {
                Log::warning("Row skipped: Product '{$row['item']}' not found", $row);
                return null;
            }

            Log::info('Processing row', [
                'item' => $row['item'],
                'product_id' => $product->id,
                'quantity' => $row['quantity'],
                'batch_no' => $row['batch_no']
            ]);

            // Check if Inventory exists for this product (with caching)
            $inventory = $this->getInventory($product->id);
            if (!$inventory) {
                // Create new Inventory record
                $inventory = Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => 0,
                ]);
                $this->inventoryCache[$product->id] = $inventory;
                Log::info('New Inventory created', ['inventory_id' => $inventory->id]);
            }

            // Parse expiry date - handle Excel serial numbers
            $expiryDate = $this->parseExpiryDate($row['expiry_date']);

            // Prepare InventoryItem data
            $inventoryItemData = [
                'inventory_id' => $inventory->id,
                'product_id' => $product->id,
                'warehouse_id' => 1, // Using warehouse_id = 1 as specified
                'quantity' => (float) $row['quantity'],
                'batch_number' => $row['batch_no'],
                'expiry_date' => $expiryDate,
                'location' => $row['location'] ?? null,
                'uom' => $row['uom'] ?? null,
                'unit_cost' => 0.00, // Default value since it's required
                'total_cost' => 0.00, // Default value since it's required
            ];

            Log::info('Attempting to create InventoryItem', $inventoryItemData);

            // Create InventoryItem
            $inventoryItem = InventoryItem::create($inventoryItemData);

            Log::info('InventoryItem created successfully', [
                'inventory_item_id' => $inventoryItem->id,
                'inventory_id' => $inventory->id,
                'batch_number' => $inventoryItem->batch_number,
                'quantity' => $inventoryItem->quantity
            ]);

            // Update progress
            $currentProgress = Cache::get($this->importId, 0);
            Cache::put($this->importId, $currentProgress + 1, 3600);

            return null;

        } catch (\Throwable $e) {
            Log::error('UploadInventory error', [
                'row' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    protected function getProduct($itemName)
    {
        if (isset($this->productCache[$itemName])) {
            return $this->productCache[$itemName];
        }

        $product = Product::where('name', $itemName)->first();
        if ($product) {
            $this->productCache[$itemName] = $product;
        }

        return $product;
    }

    protected function getInventory($productId)
    {
        if (isset($this->inventoryCache[$productId])) {
            return $this->inventoryCache[$productId];
        }

        $inventory = Inventory::where('product_id', $productId)->first();
        if ($inventory) {
            $this->inventoryCache[$productId] = $inventory;
        }

        return $inventory;
    }

    protected function parseExpiryDate($expiryDateValue)
    {
        if (empty($expiryDateValue)) {
            return null;
        }

        try {
            // Check if it's an Excel serial number (numeric)
            if (is_numeric($expiryDateValue)) {
                // Convert Excel serial number to date
                // Excel dates are days since 1900-01-01
                $excelDate = (int) $expiryDateValue;
                $unixTimestamp = ($excelDate - 25569) * 86400; // Convert to Unix timestamp
                return Carbon::createFromTimestamp($unixTimestamp);
            } else {
                // Try to parse as "M-y" format (Feb-25)
                return Carbon::createFromFormat('M-y', $expiryDateValue)->startOfMonth();
            }
        } catch (\Exception $e) {
            Log::error('Failed to parse expiry date', [
                'original' => $expiryDateValue,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 100; // Increased chunk size for better performance
    }

    public function batchSize(): int
    {
        return 50; // Reduced batch size to avoid memory issues
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                Log::info('Inventory import completed', [
                    'importId' => $this->importId
                ]);
                
                Cache::forget($this->importId);
            },
        ];
    }
}
