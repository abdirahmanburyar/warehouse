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
                        
                        // Handle column name variations (including typos)
                        $itemDescriptionKey = $this->findColumnKey($rowArray, ['item_description', 'item descriptoin', 'item_descriptoin', 'item description', 'description', 'item']);
                        $quantityKey = $this->findColumnKey($rowArray, ['quantity', 'qty', 'amount']);
                        
                        Log::info("Column mapping - Item description key: {$itemDescriptionKey}, Quantity key: {$quantityKey}");
                        
                        // Check for required fields with more detailed logging
                        $hasItemDescription = $itemDescriptionKey && isset($rowArray[$itemDescriptionKey]) && !empty($rowArray[$itemDescriptionKey]);
                        $hasQuantity = $quantityKey && isset($rowArray[$quantityKey]) && is_numeric($rowArray[$quantityKey]) && (int)$rowArray[$quantityKey] > 0;
                        
                        Log::info("Row {$index} validation - Has item description: " . ($hasItemDescription ? 'Yes' : 'No') . 
                                 ", Has valid quantity: " . ($hasQuantity ? 'Yes' : 'No'));
                        
                        // Skip if missing required fields
                        if (!$hasItemDescription || !$hasQuantity) {
                            $reason = !$hasItemDescription ? 'Missing item description' : 'Invalid quantity';
                            Log::warning("Skipping row {$index} - {$reason}");
                            $skippedRows[] = ["row" => $index + 2, "reason" => $reason, "data" => $rowArray]; // +2 for Excel row number (1-based + header)
                            continue;
                        }
                        
                        // Normalize the row data with consistent keys
                        $rowArray['item_description'] = $rowArray[$itemDescriptionKey];
                        $rowArray['quantity'] = $rowArray[$quantityKey];
                        
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
                        // Find column keys for other fields with flexible matching
                        $batchNumberKey = $this->findColumnKey($row, ['batch_number', 'batch number', 'batch', 'batch no', 'lot number', 'lot']);
                        $expiryDateKey = $this->findColumnKey($row, ['expiry_date', 'expiry', 'exp date', 'exp', 'expiration date', 'expiration']);
                        $dispenseDateKey = $this->findColumnKey($row, ['dispense_date', 'dispense date', 'dispense', 'date dispensed', 'date']);
                        $uomKey = $this->findColumnKey($row, ['uom', 'unit', 'unit of measure', 'unit of measurement']);
                        $barcodeKey = $this->findColumnKey($row, ['barcode', 'bar code', 'code']);
                        
                        Log::info("Additional column mappings - Batch: {$batchNumberKey}, Expiry: {$expiryDateKey}, Dispense: {$dispenseDateKey}, UOM: {$uomKey}, Barcode: {$barcodeKey}");
                        
                        // Get values with fallbacks
                        $batchNumber = $batchNumberKey ? ($row[$batchNumberKey] ?? null) : null;
                        $uom = $uomKey ? ($row[$uomKey] ?? null) : null;
                        $barcode = $barcodeKey ? ($row[$barcodeKey] ?? null) : null;
                        
                        // Format dates with better handling
                        $dispenseDate = $dispenseDateKey ? $this->formatDate($row[$dispenseDateKey] ?? null) : null;
                        $expiryDate = $expiryDateKey ? $this->formatDate($row[$expiryDateKey] ?? null) : null;
                        
                        // Use default dates if needed
                        if (empty($dispenseDate)) {
                            $dispenseDate = now()->toDateString();
                            Log::info("Using default dispense date: {$dispenseDate}");
                        }
                        
                        // Create item data with careful handling of nulls
                        $itemData = [
                            'parent_id' => $this->report->id,
                            'product_id' => $row['product_id'],
                            'batch_number' => $batchNumber,
                            'uom' => $uom,
                            'expiry_date' => $expiryDate, // Can be null
                            'dispense_date' => $dispenseDate,
                            'quantity' => (int)$row['quantity'],
                        ];
                        
                        // Add barcode if the field exists in the model
                        if ($barcode) {
                            $itemData['barcode'] = $barcode;
                        }
                        
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
    
    /**
     * Define chunk size for batch processing
     */
    public function chunkSize(): int
    {
        return 50;
    }
    
    /**
     * Define which queue to use
     */
    public function queue()
    {
        return 'default';
    }
    
    /**
     * Helper method to find a column key from possible variations
     * This helps handle typos and different naming conventions in Excel files
     * 
     * @param array $row The row data
     * @param array $possibleKeys Array of possible column names
     * @return string|null The found key or null if not found
     */
    protected function findColumnKey(array $row, array $possibleKeys)
    {
        // First try exact matches
        foreach ($possibleKeys as $key) {
            if (isset($row[$key])) {
                return $key;
            }
        }
        
        // Then try case-insensitive matches
        $lowercaseKeys = array_map('strtolower', array_keys($row));
        foreach ($possibleKeys as $key) {
            $lowercaseKey = strtolower($key);
            $index = array_search($lowercaseKey, $lowercaseKeys);
            if ($index !== false) {
                $actualKey = array_keys($row)[$index];
                return $actualKey;
            }
        }
        
        // Finally try partial matches
        foreach ($possibleKeys as $key) {
            $lowercaseKey = strtolower($key);
            foreach ($lowercaseKeys as $index => $rowKey) {
                if (strpos($rowKey, $lowercaseKey) !== false || strpos($lowercaseKey, $rowKey) !== false) {
                    $actualKey = array_keys($row)[$index];
                    return $actualKey;
                }
            }
        }
        
        return null;
    }
}
