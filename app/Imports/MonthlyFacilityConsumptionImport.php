<?php

namespace App\Imports;

use App\Models\MonthlyConsumptionItem;
use App\Models\MonthlyConsumptionReport;
use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class MonthlyFacilityConsumptionImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    protected $facilityId;
    protected $monthYear;
    
    public function __construct($facilityId, $monthYear)
    {
        $this->facilityId = $facilityId;
        $this->monthYear = $monthYear;
    }

    public function collection(Collection $rows)
    {
        try {            
            // Create or find the monthly report
            $report = MonthlyConsumptionReport::updateOrCreate(
                [
                    'facility_id' => $this->facilityId,
                    'month_year' => $this->monthYear
                ],
                ['generated_by' => auth()->user()->name ?? "System"]
            );

            $validRows = [];
            
            // First pass: validate rows and prepare data
            foreach ($rows as $row) {
                $description = $this->getRowValue($row, ['item_description']);
                $quantity = (int)$this->getRowValue($row, ['quantity']);
                
                if (empty($description)) {
                    continue;
                }
                
                // Find product by description
                $product = Product::where('description', $description)->first();
                
                if (!$product) {
                    Log::warning("Product not found: {$description}");
                    continue;
                }
                
                $validRows[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity
                ];
            }
            
            // Delete existing items for this report
            MonthlyConsumptionItem::where('parent_id', $report->id)->delete();
            
            // Create new items
            $items = array_map(function($item) {
                return [
                    'parent_id' => $report->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }, $validRows);
            
            // Batch insert for better performance
            if (!empty($items)) {
                MonthlyConsumptionItem::insert($items);
            }
            
        } catch (\Exception $e) {
            Log::error("Import failed: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }
    
    protected function getRowValue($row, array $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            $key = strtolower($key);
            if (isset($row[$key])) {
                return $row[$key];
            }
        }
        return null;
    }
    
    public function chunkSize(): int
    {
        return 50;
    }
    
    public function batchSize(): int
    {
        return 50;
    }
}