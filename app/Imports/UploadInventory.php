<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryItem;
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

class UploadInventory implements 
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

            // Validate that we have all required data
            if (!$inventory || !$product) {
                Log::error('Missing required data for InventoryItem creation', [
                    'inventory_id' => $inventory ? $inventory->id : 'null',
                    'product_id' => $product ? $product->id : 'null',
                    'expiry_date' => $expiryDate ? $expiryDate : 'null'
                ]);
                return null;
            }

            // Check if InventoryItem with same batch_number already exists
            $existingInventoryItem = InventoryItem::where('batch_number', $row['batch_no'])
                ->where('product_id', $product->id)
                ->where('warehouse_id', 1)
                ->first();

            if ($existingInventoryItem) {
                // Update existing inventory item by incrementing quantity
                $oldQuantity = $existingInventoryItem->quantity;
                $newQuantity = $oldQuantity + (float) $row['quantity'];
                
                $existingInventoryItem->update([
                    'quantity' => $newQuantity,
                    'expiry_date' => $expiryDate, // Update expiry date if provided
                    'location' => $row['location'] ?? $existingInventoryItem->location,
                    'uom' => $row['uom'] ?? $existingInventoryItem->uom,
                ]);

                Log::info('Existing InventoryItem updated', [
                    'inventory_item_id' => $existingInventoryItem->id,
                    'batch_number' => $existingInventoryItem->batch_number,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'added_quantity' => (float) $row['quantity']
                ]);
            } else {
                // Create new InventoryItem
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

                Log::info('Attempting to create new InventoryItem', $inventoryItemData);

                try {
                    // Create InventoryItem
                    $inventoryItem = InventoryItem::create($inventoryItemData);

                    Log::info('New InventoryItem created successfully', [
                        'inventory_item_id' => $inventoryItem->id,
                        'inventory_id' => $inventory->id,
                        'batch_number' => $inventoryItem->batch_number,
                        'quantity' => $inventoryItem->quantity
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create InventoryItem', [
                        'data' => $inventoryItemData,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e; // Re-throw to be caught by the outer try-catch
                }
            }

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
                // If M-y format fails, try normal date formats
                Log::info('M-y format failed, trying normal date formats', [
                    'original' => $expiryDateValue,
                    'error' => $e->getMessage()
                ]);
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

            // If all formats fail, log error and return null
            Log::error('Failed to parse expiry date - all formats failed', [
                'original' => $expiryDateValue,
                'tried_formats' => $dateFormats
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Failed to parse expiry date', [
                'original' => $expiryDateValue,
                'error' => $e->getMessage()
            ]);
            return null; // Return null since the field is nullable
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
