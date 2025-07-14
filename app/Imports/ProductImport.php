<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Category;
use App\Models\DosageForm;
use App\Models\Product;


class ProductImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    public $timeout = 0;
    public $tries = 3;

    public function collection(Collection $rows)
    {
        try {
            
            foreach ($rows as $row) {
                if($row['category']){
                    $category = Category::firstOrCreate(['name' => $row['category']]);
                }
                if($row['dosage_form']){
                    $dosageForm = DosageForm::firstOrCreate(['name' => $row['dosage_form']]);
                }
                if($row['item_description']){
                    $product = Product::updateOrCreate(
                        [
                            'name' => $row['item_description'],
                        ],
                        [
                            'name' => $row['item_description'],
                            'category_id' => $category->id ?? null,
                            'dosage_form_id' => $dosageForm->id ?? null,
                        ]);
                    logger()->info($product);
                }
            }
        } catch (\Throwable $th) {
            logger()->error($th);
        }
    }

    public function chunkSize(): int
    {
        return 50;
    }
}
