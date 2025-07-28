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
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\AfterSheet;
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
    public $facilityId;
    public $timeout = 300; // 5 minutes timeout
    public $tries = 3; // Retry 3 times on failure

    public function __construct(string $importId)
    {
        $this->importId = $importId;
        $this->facilityId = auth()->user()->facility_id;
    }

    public function model(array $row)
    {
        try {
            DB::beginTransaction();
            
            // Skip empty rows
            if (empty($row['item']) || empty($row['quantity']) || empty($row['batch_no']) || empty($row['expiry_date'])) {
                DB::rollBack();
                return null;
            }

            $product = $this->getProduct($row['item']);
            if (!$product) {
                Log::warning('Product not found during import', ['item' => $row['item']]);
                DB::rollBack();
                return null;
            }

            $inventory = $this->getInventory($product->id);
            $expiryDate = $this->parseExpiryDate($row['expiry_date']);
            $batchNumber = trim($row['batch_no']);
            
            // Check if item already exists
            $item = FacilityInventoryItem::where('batch_number', $batchNumber)
                ->where('product_id', $product->id)
                ->where('facility_inventory_id', $inventory->id)
                ->first();

            if ($item) {
                // Update existing item
                $item->increment('quantity', (float) $row['quantity']);
                Log::info('Updated existing inventory item', [
                    'batch_number' => $batchNumber,
                    'product' => $product->name,
                    'quantity_added' => $row['quantity']
                ]);
            } else {
                // Create new item
                $item = FacilityInventoryItem::create([
                    'facility_inventory_id' => $inventory->id,
                    'product_id' => $product->id,
                    'quantity' => (float) $row['quantity'],
                    'expiry_date' => $expiryDate,
                    'warehouse_id' => 1,
                    'uom' => $row['uom'] ?? null,
                    'batch_number' => $batchNumber,
                    'unit_cost' => 0.00,
                    'total_cost' => 0.00,
                ]);
                Log::info('Created new inventory item', [
                    'batch_number' => $batchNumber,
                    'product' => $product->name,
                    'quantity' => $row['quantity']
                ]);
            }

            DB::commit();
            return null;

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Inventory import error', [
                'error' => $e->getMessage(),
                'row' => $row,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }


    protected function getProduct($itemName)
    {
        return Product::where('name', $itemName)->first();
    }

    protected function getInventory($productId)
    {
        $inventory = FacilityInventory::where('product_id', $productId)
            ->where('facility_id', $this->facilityId)
            ->first();
            
        if (!$inventory) {
            $inventory = FacilityInventory::create([
                'facility_id' => $this->facilityId,
                'product_id' => $productId,
                'quantity' => 0,
                'reorder_level' => 0,
            ]);
            
            Log::info('Created new facility inventory', [
                'product_id' => $productId,
                'facility_id' => $this->facilityId
            ]);
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
            BeforeImport::class => function (BeforeImport $event) {
                Log::info('Starting inventory import process', [
                    'import_id' => $this->importId,
                    'facility_id' => $this->facilityId
                ]);
                Cache::put($this->importId, 0);
            },
            AfterSheet::class => function (AfterSheet $event) {
                $progress = 50; // Approximate progress after sheet processing
                Cache::put($this->importId, $progress);
                Log::info('Sheet processing completed', [
                    'import_id' => $this->importId,
                    'progress' => $progress
                ]);
            },
            AfterImport::class => function (AfterImport $event) {
                Cache::put($this->importId, 100);
                Log::info('Inventory import completed successfully', [
                    'import_id' => $this->importId,
                    'facility_id' => $this->facilityId
                ]);
                Cache::forget($this->importId);
            },
        ];
    }
}
