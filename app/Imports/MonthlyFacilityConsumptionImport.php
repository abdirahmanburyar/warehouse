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
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\User;

class MonthlyFacilityConsumptionImport implements ToCollection, WithHeadingRow
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
            // Log the Excel data structure to understand what we're working with
            Log::info("Excel import started for facility: {$this->facilityId}, month: {$this->monthYear}");
            Log::info("Total rows in collection: " . $collection->count());
            
            if ($collection->count() > 0) {
                $firstRow = $collection->first();
                Log::info("Sample row structure: " . json_encode(array_keys($firstRow->toArray())));
                Log::info("Sample row data: " . json_encode($firstRow->toArray()));
            }
            
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
                    $skippedRows = [];
                    
                    // First pass - validate all rows and collect valid ones
                    foreach($collection as $index => $row) {
                        // Convert row to array to ensure we can access all fields
                        $rowArray = $row->toArray();
                        Log::info("Processing row {$index}: " . json_encode($rowArray));
                        
                        // Check for required fields with more detailed logging
                        $hasItemDescription = isset($rowArray['item_description']) && !empty($rowArray['item_description']);
                        $hasQuantity = isset($rowArray['quantity']) && is_numeric($rowArray['quantity']) && (int)$rowArray['quantity'] > 0;
                        
                        Log::info("Row {$index} validation - Has item_description: " . ($hasItemDescription ? 'Yes' : 'No') . 
                                 ", Has valid quantity: " . ($hasQuantity ? 'Yes' : 'No'));
                        
                        // Skip if missing required fields
                        if (!$hasItemDescription || !$hasQuantity) {
                            $reason = !$hasItemDescription ? 'Missing item_description' : 'Invalid quantity';
                            Log::warning("Skipping row {$index} - {$reason}");
                            $skippedRows[] = ["row" => $index + 2, "reason" => $reason, "data" => $rowArray]; // +2 for Excel row number (1-based + header)
                            continue;
                        }
                        
                        // Find product by name with better error handling
                        try {
                            $productId = $this->getProductIdByName($rowArray['item_description']);
                            if (!$productId) {
                                Log::error("Product not found: {$rowArray['item_description']}");
                                $skippedRows[] = ["row" => $index + 2, "reason" => "Product not found", "data" => $rowArray];
                                continue; // Skip this row instead of failing the entire import
                            }
                            
                            // Add to valid rows
                            $rowArray['product_id'] = $productId;
                            $validRows[] = $rowArray;
                        } catch (\Exception $e) {
                            Log::error("Error finding product: " . $e->getMessage());
                            $skippedRows[] = ["row" => $index + 2, "reason" => "Error: " . $e->getMessage(), "data" => $rowArray];
                            continue;
                        }
                    }
                    
                    // Log skipped rows summary
                    if (count($skippedRows) > 0) {
                        Log::warning("Skipped " . count($skippedRows) . " rows during import");
                        Log::warning("Skipped rows details: " . json_encode($skippedRows));
                    }
                    
                    // Check if we have any valid rows
                    if (count($validRows) === 0) {
                        Log::error("No valid items found in import, rolling back transaction");
                        throw new \Exception("No valid items found in the import file. Please check the Excel format and data.");
                    }
                    
                    Log::info("Found " . count($validRows) . " valid rows for import");
                    
                    // Second pass - create items for valid rows
                    foreach($validRows as $index => $row) {
                        // Format dates with better handling
                        $dispenseDate = $this->formatDate($row['dispense_date'] ?? null);
                        $expiryDate = $this->formatDate($row['expiry_date'] ?? null);
                        
                        // Use default dates if needed
                        if (empty($dispenseDate)) {
                            $dispenseDate = now()->toDateString();
                            Log::info("Using default dispense date: {$dispenseDate}");
                        }
                        
                        // Create item data with careful handling of nulls
                        $itemData = [
                            'parent_id' => $this->report->id,
                            'product_id' => $row['product_id'],
                            'batch_number' => $row['batch_number'] ?? null,
                            'uom' => $row['uom'] ?? null,
                            'expiry_date' => $expiryDate, // Can be null
                            'dispense_date' => $dispenseDate,
                            'quantity' => (int)$row['quantity'],
                        ];
                        
                        Log::info("Creating item {$index} with data: " . json_encode($itemData));
                        
                        try {
                            // Create consumption item
                            $item = MonthlyConsumptionItem::create($itemData);
                            Log::info("Successfully created item ID: {$item->id}");
                            $itemCount++;
                        } catch (\Exception $e) {
                            Log::error("Failed to create item {$index}: " . $e->getMessage());
                            throw $e;
                        }
                    }
                    
                    Log::info("Created {$itemCount} consumption items for report ID: {$this->report->id}");
                    
                    // Final check - if no items were created, roll back
                    if ($itemCount === 0) {
                        Log::error("No items were created, rolling back transaction");
                        throw new \Exception("Failed to create any consumption items. Please check the Excel format.");
                    }
                }
            }, 3); // 3 retries on deadlock
        } catch (\Throwable $th) {
            Log::error("Import failed with error: " . $th->getMessage());
            Log::error($th);
            throw $th;
        }
    }
    
    /**
     * Get product ID by name with improved matching
     */
    protected function getProductIdByName($name)
    {
        if (empty($name)) {
            Log::warning("Empty product name provided");
            return null;
        }
        
        // Normalize the name
        $normalizedName = trim($name);
        
        // Check cache first
        if (isset($this->productCache[$normalizedName])) {
            Log::info("Product found in cache: {$normalizedName} => {$this->productCache[$normalizedName]}");
            return $this->productCache[$normalizedName];
        }
        
        Log::info("Looking up product: {$normalizedName}");
        
        // Try exact match first
        $product = Product::where('name', $normalizedName)->first();
        
        if (!$product) {
            // Try case-insensitive match
            $product = Product::whereRaw('LOWER(name) = ?', [strtolower($normalizedName)])->first();
        }
        
        if (!$product) {
            // Try partial match (contains)
            $product = Product::where('name', 'like', "%{$normalizedName}%")->first();
        }
        
        if ($product) {
            Log::info("Product found: {$normalizedName} => {$product->id} ({$product->name})");
            $this->productCache[$normalizedName] = $product->id;
            return $product->id;
        }
        
        Log::warning("Product not found: {$normalizedName}");
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
}
