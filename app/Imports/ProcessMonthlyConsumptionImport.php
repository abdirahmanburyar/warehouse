<?php

namespace App\Imports;

use App\Models\MonthlyConsumptionReport;
use App\Models\MonthlyConsumptionItem;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class ProcessMonthlyConsumptionImport
{
    protected $filePath;
    protected $facilityId;
    protected $monthYear;
    protected $importId;
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $reader;

    // Define possible column header variations
    protected $itemDescriptionHeaders = [
        'item description',
        'item_description',
        'itemdescription',
        'description',
        'item',
        'product',
        'product name',
        'product_name'
    ];

    protected $quantityHeaders = [
        'quantity',
        'qty',
        'amount',
        'count'
    ];

    /**
     * Create a new import instance.
     */
    public function __construct($filePath, $facilityId, $monthYear, $importId)
    {
        $this->filePath = $filePath;
        $this->facilityId = $facilityId;
        $this->monthYear = $monthYear;
        $this->importId = $importId;
    }

    /**
     * Import the data.
     */
    public function import()
    {
        try {
            if (!file_exists($this->filePath)) {
                throw new \Exception("Import file not found: {$this->filePath}");
            }

            // Create the reader based on file extension
            $this->reader = ReaderEntityFactory::createReaderFromFile($this->filePath);
            $this->reader->open($this->filePath);

            // Create or find the monthly report
            $report = MonthlyConsumptionReport::updateOrCreate(
                [
                    'facility_id' => $this->facilityId,
                    'month_year' => $this->monthYear
                ],
                ['generated_by' => auth()->user()->name ?? "System"]
            );

            $validRows = [];
            $headers = null;
            $itemDescriptionIndex = null;
            $quantityIndex = null;

            // Iterate through all sheets
            foreach ($this->reader->getSheetIterator() as $sheet) {
                // Iterate through all rows
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $values = $row->toArray();

                    // Handle headers
                    if (!$headers) {
                        $headers = array_map('strtolower', array_map('trim', $values));

                        // Find item description column
                        foreach ($headers as $index => $header) {
                            if (in_array($header, $this->itemDescriptionHeaders)) {
                                $itemDescriptionIndex = $index;
                                break;
                            }
                        }

                        // Find quantity column
                        foreach ($headers as $index => $header) {
                            if (in_array($header, $this->quantityHeaders)) {
                                $quantityIndex = $index;
                                break;
                            }
                        }

                        if ($itemDescriptionIndex === null || $quantityIndex === null) {
                            throw new \Exception('Required columns not found in Excel file. Please ensure the file has "Item Description" and "Quantity" columns.');
                        }
                        continue;
                    }

                    // Skip empty rows
                    if (empty(array_filter($values))) {
                        continue;
                    }

                    // Process data row
                    $description = trim($values[$itemDescriptionIndex] ?? '');
                    $quantity = (int)($values[$quantityIndex] ?? 0);

                    if (empty($description)) {
                        continue;
                    }

                    // Find product by description
                    $product = Product::where('name', $description)
                        ->first();

                    if (!$product) {
                        $this->errors[] = "Product not found: {$description}";
                        $this->skippedCount++;
                        continue;
                    }

                    $validRows[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity
                    ];
                    $this->importedCount++;
                }
            }

            // Close the reader before processing the data
            $this->reader->close();
            $this->reader = null;

            // Delete existing items for this report
            MonthlyConsumptionItem::where('parent_id', $report->id)->delete();

            // Create new items
            $items = array_map(function($item) use ($report) {
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

            // Clean up the file
            $this->cleanupFile();

            return [
                'imported' => $this->importedCount,
                'skipped' => $this->skippedCount,
                'errors' => $this->errors
            ];

        } catch (\Exception $e) {
            // Make sure to close the reader if it's open
            if ($this->reader !== null) {
                try {
                    $this->reader->close();
                } catch (\Exception $closeException) {
                   
                }
                $this->reader = null;
            }

            // Clean up the file
            $this->cleanupFile();

            throw $e;
        }
    }

    /**
     * Clean up the imported file with retries
     */
    protected function cleanupFile()
    {
        if (!file_exists($this->filePath)) {
            return;
        }

        $maxRetries = 3;
        $retryDelay = 1; // seconds

        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                if (@unlink($this->filePath)) {
                    return;
                }
                sleep($retryDelay);
            } catch (\Exception $e) {
               
                if ($i < $maxRetries - 1) {
                    sleep($retryDelay);
                }
            }
        }

        Log::error("Failed to delete file after {$maxRetries} attempts", [
            'file' => $this->filePath
        ]);
    }
} 