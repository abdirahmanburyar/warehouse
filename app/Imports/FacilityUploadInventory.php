<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\FacilityInventory;
use App\Models\FacilityInventoryItem;
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

class FacilityUploadInventory implements 
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
    protected $facilityId;

    public function __construct(string $importId)
    {
        $this->importId = $importId;
        $this->facilityId = auth()->user()->facility_id;
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

            // Check if FacilityInventory exists for this product and facility (with caching)
            $inventory = $this->getInventory($product->id);
            if (!$inventory) {
                // Create new FacilityInventory record
                $inventory = FacilityInventory::create([
                    'product_id' => $product->id,
                    'facility_id' => $this->facilityId,
                    'quantity' => 0,
                ]);
                $this->inventoryCache[$product->id] = $inventory;
                Log::info('New FacilityInventory created', ['inventory_id' => $inventory->id]);
            }

            // Parse expiry date - handle Excel serial numbers
            $expiryDate = $this->parseExpiryDate($row['expiry_date']);

            // Validate that we have all required data
            if (!$inventory || !$product) {
                Log::error('Missing required data for FacilityInventoryItem creation', [
                    'inventory_id' => $inventory ? $inventory->id : 'null',
                    'product_id' => $product ? $product->id : 'null',
                    'expiry_date' => $expiryDate ? $expiryDate : 'null'
                ]);
                return null;
            }

            // Prepare FacilityInventoryItem data
            $inventoryItemData = [
                'facility_inventory_id' => $inventory->id,
                'product_id' => $product->id,
                'quantity' => (float) $row['quantity'],
                'batch_number' => $row['batch_no'],
                'expiry_date' => $expiryDate,
                'uom' => $row['uom'] ?? null,
                'unit_cost' => 0.00, // Default value since it's required
                'total_cost' => 0.00, // Default value since it's required
            ];

            Log::info('Attempting to create FacilityInventoryItem', $inventoryItemData);

            try {
                // Create FacilityInventoryItem
                $inventoryItem = FacilityInventoryItem::create($inventoryItemData);

                Log::info('FacilityInventoryItem created successfully', [
                    'inventory_item_id' => $inventoryItem->id,
                    'inventory_id' => $inventory->id,
                    'batch_number' => $inventoryItem->batch_number,
                    'quantity' => $inventoryItem->quantity
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create FacilityInventoryItem', [
                    'data' => $inventoryItemData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Re-throw to be caught by the outer try-catch
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

        $inventory = FacilityInventory::where('product_id', $productId)
            ->where('facility_id', $this->facilityId)
            ->first();
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
                // Set to first day of the month
                return $date->startOfMonth()->format('Y-m-d');
            } else {
                // Try to parse as "M-y" format (Feb-25, Jun-27)
                $date = Carbon::createFromFormat('M-y', $expiryDateValue);
                // Set to first day of the month
                return $date->startOfMonth()->format('Y-m-d');
            }
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
                Log::info('Facility inventory import completed', [
                    'importId' => $this->importId
                ]);
                
                Cache::forget($this->importId);
            },
        ];
    }
}
