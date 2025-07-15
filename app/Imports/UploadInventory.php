<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Carbon\Carbon;
// use App\Events\UpdateProductUpload;

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

    protected $importId;
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];

    public function __construct(string $importId)
    {
        $this->importId = $importId;
    }

    public function model(array $row)
    {
        try {
            logger()->info('Processing inventory row', $row);

            // Validate required fields
            if (empty($row['item']) || empty($row['quantity']) || empty($row['batch_no'])) {
                $this->errors[] = "Row skipped: Missing required fields (item, quantity, or batch_no)";
                $this->skippedCount++;
                return null;
            }

            // Check if product exists
            $product = Product::where('name', $row['item'])->first();
            
            if (!$product) {
                $this->errors[] = "Row skipped: Product '{$row['item']}' not found";
                $this->skippedCount++;
                return null;
            }

            logger()->info('Product found', [
                'productId' => $product->id,
                'productName' => $product->name
            ]);

            // Check if Inventory exists for this product
            $inventory = Inventory::where('product_id', $product->id)->first();
            
            if (!$inventory) {
                // Create new Inventory record
                $inventory = Inventory::create([
                    'product_id' => $product->id,
                    'quantity' => 0, // Will be updated by InventoryItems
                ]);
                
                logger()->info('New Inventory created', [
                    'inventoryId' => $inventory->id,
                    'productId' => $product->id
                ]);
            } else {
                logger()->info('Existing Inventory found', [
                    'inventoryId' => $inventory->id,
                    'productId' => $product->id
                ]);
            }

            // Parse expiry date from "Feb-25" format to "01-02-2025"
            $expiryDate = null;
            if (!empty($row['expiry_date'])) {
                try {
                    // Convert "Feb-25" to "01-02-2025" format
                    $expiryDate = Carbon::createFromFormat('M-y', $row['expiry_date'])->startOfMonth();
                    logger()->info('Expiry date parsed', [
                        'original' => $row['expiry_date'],
                        'parsed' => $expiryDate->format('Y-m-d')
                    ]);
                } catch (\Exception $e) {
                    logger()->error('Failed to parse expiry date', [
                        'original' => $row['expiry_date'],
                        'error' => $e->getMessage()
                    ]);
                    $this->errors[] = "Invalid expiry date format: {$row['expiry_date']}";
                    $this->skippedCount++;
                    return null;
                }
            }

            // Create InventoryItem
            $inventoryItem = InventoryItem::create([
                'inventory_id' => $inventory->id,
                'product_id' => $product->id,
                'warehouse_id' => 1, // Using warehouse_id = 1 as specified
                'quantity' => (float) $row['quantity'],
                'batch_number' => $row['batch_no'],
                'expiry_date' => $expiryDate,
                'location' => $row['location'] ?? null,
                'uom' => $row['uom'] ?? null,
                'is_active' => true,
            ]);

            logger()->info('InventoryItem created', [
                'inventoryItemId' => $inventoryItem->id,
                'batchNumber' => $inventoryItem->batch_number,
                'quantity' => $inventoryItem->quantity,
                'expiryDate' => $inventoryItem->expiry_date
            ]);

            $this->importedCount++;

            // Update progress
            Cache::put($this->importId, $this->importedCount, 3600);

            return null; // We're not using ToModel for actual model creation, just processing

        } catch (\Throwable $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->skippedCount++;
            logger()->error('UploadInventory error', [
                'row' => $row,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 50;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                logger()->info('UploadInventory: AfterImport event triggered', [
                    'importId' => $this->importId,
                    'imported' => $this->importedCount,
                    'skipped' => $this->skippedCount,
                    'errors' => count($this->errors),
                    'errorDetails' => $this->errors
                ]);
                
                Cache::forget($this->importId);
                Log::info('Inventory import completed', [
                    'importId' => $this->importId,
                    'imported' => $this->importedCount,
                    'skipped' => $this->skippedCount,
                    'errors' => count($this->errors)
                ]);
                // event(new UpdateProductUpload($this->importId, 'completed'));
            },
        ];
    }

    public function getResults()
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'errors' => $this->errors,
        ];
    }
}
