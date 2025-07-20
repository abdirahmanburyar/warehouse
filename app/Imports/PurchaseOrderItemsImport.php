<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\PurchaseOrderItem;
use App\Models\Category;
use App\Models\Dosage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class PurchaseOrderItemsImport implements 
    ToModel, 
    WithHeadingRow, 
    WithChunkReading, 
    WithBatchInserts, 
    WithValidation,
    SkipsEmptyRows, 
    WithEvents,
    ShouldQueue
{
    use Queueable, InteractsWithQueue;

    protected $purchaseOrderId;
    protected $importId;
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $productCache = [];
    protected $categoryCache = [];
    protected $dosageCache = [];

    public function __construct($purchaseOrderId, string $importId)
    {
        $this->purchaseOrderId = $purchaseOrderId;
        $this->importId = $importId;
    }

    public function model(array $row)
    {
        try {
            $this->importedCount++;
        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            $this->skippedCount++;
            throw $e;
        }
    }



    public function rules(): array
    {
        return [
            'item_description' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'uom' => 'required|string|max:50',
            'category' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
        ];
    }



    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                try {
                    // Update purchase order total amount
                    $totalAmount = PurchaseOrderItem::where('purchase_order_id', $this->purchaseOrderId)
                        ->sum('total_cost');
                    
                    DB::table('purchase_orders')
                        ->where('id', $this->purchaseOrderId)
                        ->update(['total_amount' => $totalAmount]);

                    Log::info('PurchaseOrderItems import completed', [
                        'importId' => $this->importId,
                        'purchaseOrderId' => $this->purchaseOrderId,
                        'totalAmount' => $totalAmount
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update purchase order total amount', [
                        'error' => $e->getMessage(),
                        'purchaseOrderId' => $this->purchaseOrderId
                    ]);
                }
                
                Cache::forget($this->importId);
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
