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
use App\Models\MonthlyConsumptionReport;
use App\Models\MonthlyConsumptionItem;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MonthlyFacilityConsumptionImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    protected $facilityId;
    protected $productCache = [];
    protected $monthYear;
    protected $report;
    
    public function __construct($facilityId)
    {
        $this->facilityId = $facilityId;
        $this->monthYear = Carbon::now()->format('Y-m');
    }
    
    public function collection(Collection $collection)
    {
        try {
            // Create parent report if we have data
            if ($collection->count() > 0) {
                $this->report = MonthlyConsumptionReport::create([
                    'facility_id' => $this->facilityId,
                    'month_year' => $this->monthYear,
                    'generated_by' => 'Excel Import',
                ]);
            }
            
            foreach($collection as $row) {
                // Skip if no item description or quantity
                if (empty($row['item_description']) || !isset($row['quantity']) || $row['quantity'] <= 0) {
                    continue;
                }
                
                // Find product by name
                $productId = $this->getProductIdByName($row['item_description']);
                if (!$productId) {
                    Log::error("Product not found: {$row['item_description']}");
                    throw new \Exception("Product not found: {$row['item_description']}");
                }
                
                // Format dates
                $dispenseDate = $this->formatDate($row['dispense_date'] ?? null);
                $expiryDate = $this->formatDate($row['expiry_date'] ?? null);
                
                // Create consumption item
                MonthlyConsumptionItem::create([
                    'parent_id' => $this->report->id,
                    'product_id' => $productId,
                    'batch_number' => $row['batch_number'] ?? null,
                    'uom' => $row['uom'] ?? null,
                    'expiry_date' => $expiryDate ?? now()->addYear()->toDateString(),
                    'dispense_date' => $dispenseDate ?? now()->toDateString(),
                    'quantity' => (int)$row['quantity'],
                ]);
            }
        } catch (\Throwable $th) {
            Log::error($th);
            throw $th;
        }
    }
    
    /**
     * Get product ID by name, with caching for performance
     */
    protected function getProductIdByName($name)
    {
        if (isset($this->productCache[$name])) {
            return $this->productCache[$name];
        }
        
        $product = Product::where('name', $name)->first();
        
        if ($product) {
            $this->productCache[$name] = $product->id;
            return $product->id;
        }
        
        return null;
    }
    
    /**
     * Format date from DD/MM/YYYY to YYYY-MM-DD
     */
    protected function formatDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }
        
        try {
            // Handle different date formats
            if (strpos($dateString, '/') !== false) {
                // Format: DD/MM/YYYY
                $parts = explode('/', $dateString);
                if (count($parts) === 3) {
                    return Carbon::createFromFormat('d/m/Y', $dateString)->toDateString();
                }
            } else if (strpos($dateString, '-') !== false) {
                // Format: DD-MM-YYYY
                return Carbon::parse($dateString)->toDateString();
            }
            
            // Default parse attempt
            return Carbon::parse($dateString)->toDateString();
        } catch (\Exception $e) {
            Log::warning("Invalid date format: {$dateString}");
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 50;
    }
}
