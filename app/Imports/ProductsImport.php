<?php

namespace App\Imports;

use App\Models\Product;
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
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class ProductsImport implements 
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

    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $errors = [];
    protected $categoryCache = [];
    protected $dosageCache = [];
    protected $importId;

    public function __construct(string $importId)
    {
        $this->importId = $importId;
    }

    public function model(array $row)
    {
        try {
            if (empty($row['item_description'])) {
                $this->skippedCount++;
                return null;
            }

            $itemName = trim($row['item_description']);

            if (strlen($itemName) > 255) {
                $this->errors[] = "Item description too long: " . substr($itemName, 0, 50) . "...";
                $this->skippedCount++;
                return null;
            }

            // Category
            $categoryId = null;
            if (!empty($row['category'])) {
                $category = trim($row['category']);
                if (strlen($category) > 255) {
                    $this->errors[] = "Category name too long: " . substr($category, 0, 50) . "...";
                    $this->skippedCount++;
                    return null;
                }

                if (!isset($this->categoryCache[$category])) {
                    $categoryModel = Category::firstOrCreate(
                        ['name' => $category],
                        ['is_active' => true]
                    );
                    $this->categoryCache[$category] = $categoryModel->id;
                }
                $categoryId = $this->categoryCache[$category];
            }

            // Dosage
            $dosageId = null;
            if (!empty($row['dosage_form'])) {
                $dosageForm = trim($row['dosage_form']);
                if (strlen($dosageForm) > 255) {
                    $this->errors[] = "Dosage form name too long: " . substr($dosageForm, 0, 50) . "...";
                    $this->skippedCount++;
                    return null;
                }

                if (!isset($this->dosageCache[$dosageForm])) {
                    $dosageModel = Dosage::firstOrCreate(['name' => $dosageForm]);
                    $this->dosageCache[$dosageForm] = $dosageModel->id;
                }
                $dosageId = $this->dosageCache[$dosageForm];
            }

            $this->importedCount++;

            // Update progress in cache
            Cache::increment($this->importId);

            //  // Find the highest productID in the database
            //  $maxProductId = Product::max('productID');
            
            //  // If there are existing products, increment the highest productID
            //  if ($maxProductId) {
            //      $nextId = (int)$maxProductId + 1;
            //  } else {
            //      // Start from 1 if no products exist
            //      $nextId = 1;
            //  }
             
            //  // Format as 6-digit number with leading zeros
            //  $productID = str_pad($nextId, 6, '0', STR_PAD_LEFT);

            Product::create([
                'name' => $itemName,
                'category_id' => $categoryId,
                'dosage_id' => $dosageId,
                'is_active' => true,
            ]);

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
                Cache::forget($this->importId);
                Log::info('Product import completed', ['importId' => $this->importId]);
                broadcast(new ImportProgress($this->importId, 'completed'));
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
