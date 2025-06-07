<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\IssueQuantityReport;
use App\Models\IssueQuantityItem;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class IssueQuantitiyImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    protected $month_year;
    protected $user_id;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes

    public function __construct($month_year, $user_id)
    {
        $this->month_year = $month_year;
        $this->user_id = $user_id;
        
        Log::info('IssueQuantitiyImport constructed', [
            'month_year' => $this->month_year,
            'user_id' => $this->user_id
        ]);
    }
    
    public function collection(Collection $rows): void
    {
        Log::info('Processing Excel import chunk', [
            'rows_count' => $rows->count(),
            'month_year' => $this->month_year,
            'user_id' => $this->user_id
        ]);
        
        try {
            DB::beginTransaction();
            
            // Create or find the report for this month/year
            $report = IssueQuantityReport::firstOrCreate(
                ['month_year' => $this->month_year],
                [
                    'month_year' => $this->month_year,
                    'total_quantity' => 0, // Initialize total quantity
                    'created_by' => $this->user_id,
                    'status' => 'processing', // Set status to processing
                    'generated_by' => $this->user_id
                ]
            );
            
            $chunkTotalQty = 0;
            
            foreach ($rows as $rowIndex => $row) {
                Log::info('Processing row', ['row_index' => $row]);
                // Extract data from the row, ensuring keys exist
                $itemDescription = $row['item_description'] ?? null;
                $warehouseName = $row['warehouse'] ?? null;
                $locationName = $row['location'] ?? null;
                $quantity = isset($row['quantity']) ? (float)$row['quantity'] : 0;
                $batchNumber = $row['batch_number'] ?? null;
                $uom = $row['uom'] ?? null;
                $expiryDateExcel = $row['expiry_date'] ?? null;
                $unitCost = isset($row['unit_cost']) ? (float)$row['unit_cost'] : 0;
                $totalCost = isset($row['total_cost']) ? (float)$row['total_cost'] : 0;
                $issuedDateExcel = $row['issued_date'] ?? null;
                $barcode = $row['barcode'] ?? null;
                
                // Skip rows with missing required data
                if (empty($itemDescription) || empty($warehouseName)) {
                    Log::warning('Skipping row with missing item_description or warehouse', [
                        'row_index' => $rowIndex,
                        'item_description' => $itemDescription,
                        'warehouse' => $warehouseName
                    ]);
                    continue;
                }
                
                // Format dates if they exist
                $expiryDate = null;
                if ($expiryDateExcel) {
                    try {
                        if (is_numeric($expiryDateExcel)) {
                            $expiryDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($expiryDateExcel));
                        } else {
                            $expiryDate = Carbon::parse($expiryDateExcel);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Invalid expiry_date format', ['date' => $expiryDateExcel, 'row_index' => $rowIndex]);
                    }
                }
                
                $issuedDate = null;
                if ($issuedDateExcel) {
                    try {
                        if (is_numeric($issuedDateExcel)) {
                            $issuedDate = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($issuedDateExcel));
                        } else {
                            $issuedDate = Carbon::parse($issuedDateExcel);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Invalid issued_date format', ['date' => $issuedDateExcel, 'row_index' => $rowIndex]);
                    }
                }
                
                // Find or create product
                $product = Product::where('name', $itemDescription)->first();
                if (!$product) {
                    Log::error('Product not found during import', ['item_description' => $itemDescription, 'row_index' => $rowIndex]);
                    // Optionally, you could create the product here if desired
                    // For now, we'll skip this item or throw an exception
                    // throw new \Exception("Product not found: {$itemDescription}");
                    continue; // Skip this item if product not found
                }
                
                // Find or create warehouse
                $warehouse = Warehouse::firstOrCreate(['name' => $warehouseName], ['user_id' => $this->user_id]);
                
                // Find or create location
                $location = null;
                if ($locationName) {
                    $location = Location::firstOrCreate(
                        ['warehouse_id' => $warehouse->id, 'location' => $locationName]
                    );
                } else {
                    // Create a default location if none provided
                    $location = Location::firstOrCreate(
                        ['warehouse_id' => $warehouse->id, 'location' => 'Default']
                    );
                }
                
                // Create issue quantity item
                IssueQuantityItem::create([
                    'product_id' => $product->id,
                    'parent_id' => $report->id,
                    'warehouse_id' => $warehouse->id,
                    'location_id' => $location->id,
                    'quantity' => $quantity,
                    'batch_number' => $batchNumber,
                    'uom' => $uom,
                    'expiry_date' => $expiryDate,
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                    'issued_date' => $issuedDate,
                    'barcode' => $barcode,
                    'issued_by' => $this->user_id,
                ]);
                
                $chunkTotalQty += $quantity;
                
            }
            
            // Atomically update the total quantity for the report
            if ($chunkTotalQty > 0) {
                $report->increment('total_quantity', $chunkTotalQty);
            }
            
            DB::commit();
            
            Log::info('Import chunk processed successfully', [
                'report_id' => $report->id,
                'chunk_total_quantity' => $chunkTotalQty,
                'rows_in_chunk' => $rows->count()
            ]);
            
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Import chunk failed', [
                'month_year' => $this->month_year,
                'user_id' => $this->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Re-throw the exception to let Laravel's queue worker handle retries/failures
            throw $e;
        }
    }


    public function chunkSize(): int
    {
        return 50;
    }
}
