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
            DB::beginTransaction();
            // if (empty($row['item']) || empty($row['quantity']) || empty($row['batch_no'])) {
            //     return null;
            // }

            // // Get or cache the product
            // $product = $this->getProduct($row['item']);
            // if (!$product) {
            //     return null;
            // }

            // // Get or cache the inventory
            // $inventory = $this->getInventory($product->id);

            logger()->info('Inventory', $row);  

            // $batchNumber = trim($row['batch_no']);
            // $expiryDate = $this->parseExpiryDate($row['expiry_date']);
            // $warehouseId = 1; // static for now

            // $item = $inventory->items()->where([
            //     'product_id' => $product->id,
            //     'batch_number' => $batchNumber,
            // ])->first();
            
            // if ($item) {
            //     $item->increment('quantity', (float) $row['quantity']);
            // } else {
            //     $inventory->items()->create([
            //         'product_id' => $product->id,
            //         'warehouse_id' => 1,
            //         'quantity' => (float) $row['quantity'],
            //         'batch_number' => $batchNumber,
            //         'expiry_date' => $expiryDate,
            //         'location' => $row['location'] ?? null,
            //         'uom' => $row['uom'] ?? null,
            //         'unit_cost' => 0.00,
            //         'total_cost' => 0.00,
            //     ]);
            // }
                  

            // // Update import progress
            // $currentProgress = Cache::get($this->importId, 0);
            // Cache::put($this->importId, $currentProgress + 1, 3600);
            DB::commit();

            return null;

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Inventory import error', [
                'error' => $e->getMessage(),
                'row' => $row
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
        if (!$inventory) {
            $inventory = Inventory::create([
                'product_id' => $productId,
                'quantity' => 0,
            ]);
        }
        $this->inventoryCache[$productId] = $inventory;            

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
                Cache::forget($this->importId);
            },
        ];
    }
}
