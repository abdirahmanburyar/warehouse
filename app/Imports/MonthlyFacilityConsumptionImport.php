<?php

namespace App\Imports;

use App\Models\MonthlyConsumptionReport;
use App\Models\MonthlyConsumptionItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\User;

class MonthlyFacilityConsumptionImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    protected $facilityId;
    protected $productCache = [];
    protected $monthYear;
    protected $report;
    
    public function __construct($facilityId, $monthYear)
    {
        $this->facilityId = $facilityId;
        $this->monthYear = $monthYear;
    }
    
    public function collection(Collection $collection)
    {
        try {
            // Use transaction to ensure all operations succeed or fail together
            DB::transaction(function() use ($collection) {
                // Check if a report already exists for this facility and month
                $existingReport = MonthlyConsumptionReport::where('facility_id', $this->facilityId)
                    ->where('month_year', $this->monthYear)
                    ->first();
                    
                if ($existingReport) {
                    Log::info("Deleting existing report ID: {$existingReport->id} for facility: {$this->facilityId}, month: {$this->monthYear}");
                    // Delete existing items first
                    MonthlyConsumptionItem::where('parent_id', $existingReport->id)->delete();
                    // Then delete the report
                    $existingReport->delete();
                }
                
                // Create parent report if we have data
                if ($collection->count() > 0) {
                    $this->report = MonthlyConsumptionReport::create([
                        'facility_id' => $this->facilityId,
                        'month_year' => $this->monthYear,
                        'generated_by' => 'Excel Import',
                    ]);
                    
                    Log::info("Created parent report ID: {$this->report->id} for facility: {$this->facilityId}, month: {$this->monthYear}");
                    
                    $itemCount = 0;
                    $validRows = [];
                    
                    // First pass - validate all rows and collect valid ones
                    foreach($collection as $row) {
                        Log::info("Processing row: " . json_encode($row));
                        
                        // Skip if no item description or quantity
                        if (empty($row['item_description'])) {
                            Log::warning("Skipping row - missing item_description");
                            continue;
                        }
                        
                        if (!isset($row['quantity']) || (int)$row['quantity'] <= 0) {
                            Log::warning("Skipping row - invalid quantity for item: {$row['item_description']}");
                            continue;
                        }
                        
                        // Find product by name
                        $productId = $this->getProductIdByName($row['item_description']);
                        if (!$productId) {
                            Log::error("Product not found: {$row['item_description']}");
                            throw new \Exception("Product not found: {$row['item_description']}");
                        }
                        
                        // Add to valid rows
                        $row['product_id'] = $productId;
                        $validRows[] = $row;
                    }
                    
                    // Check if we have any valid rows
                    if (count($validRows) === 0) {
                        Log::error("No valid items found in import, rolling back transaction");
                        throw new \Exception("No valid items found in the import file");
                    }
                    
                    // Second pass - create items for valid rows
                    foreach($validRows as $row) {
                        // Format dates
                        $dispenseDate = $this->formatDate($row['dispense_date'] ?? null);
                        $expiryDate = $this->formatDate($row['expiry_date'] ?? null);
                        
                        // Debug the data we're about to insert
                        $itemData = [
                            'parent_id' => $this->report->id,
                            'product_id' => $row['product_id'],
                            'batch_number' => $row['batch_number'] ?? null,
                            'uom' => $row['uom'] ?? null,
                            'expiry_date' => $expiryDate,
                            'dispense_date' => $dispenseDate ?? now()->toDateString(),
                            'quantity' => (int)$row['quantity'],
                        ];
                        
                        Log::info("Attempting to create item with data: " . json_encode($itemData));
                        
                        try {
                            // Create consumption item
                            $item = MonthlyConsumptionItem::create($itemData);
                            Log::info("Successfully created item ID: {$item->id}");
                            $itemCount++;
                        } catch (\Exception $e) {
                            Log::error("Failed to create item: " . $e->getMessage());
                            throw $e;
                        }
                    }
                    
                    Log::info("Created {$itemCount} consumption items for report ID: {$this->report->id}");
                    
                    // Final check - if no items were created, roll back
                    if ($itemCount === 0) {
                        Log::error("No items were created, rolling back transaction");
                        throw new \Exception("Failed to create any consumption items");
                    }
                }
            }, 3); // 3 retries on deadlock
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
     * Format date from various formats to YYYY-MM-DD
     */
    protected function formatDate($dateString)
    {
        if (empty($dateString)) {
            Log::info("Empty date string, returning null");
            return null;
        }
        
        Log::info("Formatting date: {$dateString}");
        
        try {
            // Handle different date formats
            if (strpos($dateString, '/') !== false) {
                // Format: DD/MM/YYYY
                $parts = explode('/', $dateString);
                if (count($parts) === 3) {
                    $formattedDate = Carbon::createFromFormat('d/m/Y', $dateString)->toDateString();
                    Log::info("Formatted date from DD/MM/YYYY: {$dateString} to {$formattedDate}");
                    return $formattedDate;
                }
            } else if (strpos($dateString, '-') !== false) {
                // Check if it's already in YYYY-MM-DD format
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
                    Log::info("Date already in YYYY-MM-DD format: {$dateString}");
                    return $dateString;
                }
                
                // Format: DD-MM-YYYY
                $formattedDate = Carbon::parse($dateString)->toDateString();
                Log::info("Formatted date from DD-MM-YYYY: {$dateString} to {$formattedDate}");
                return $formattedDate;
            }
            
            // Default parse attempt
            $formattedDate = Carbon::parse($dateString)->toDateString();
            Log::info("Formatted date using default parser: {$dateString} to {$formattedDate}");
            return $formattedDate;
        } catch (\Exception $e) {
            Log::warning("Invalid date format: {$dateString} - Error: {$e->getMessage()}");
            return null;
        }
    }

    public function chunkSize(): int
    {
        return 50;
    }
}
