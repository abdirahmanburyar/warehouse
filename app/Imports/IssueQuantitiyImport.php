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
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
        
    }

    public function collection(Collection $rows): void
    {
        try {
            Log::info("Import started", [
                'user_id' => $this->user_id,
            'month_year' => $this->month_year,
            'row_count' => $rows->count(),
        ]);

        DB::beginTransaction();

        // Check if report exists for this month_year
        $report = IssueQuantityReport::where('month_year', $this->month_year)->first();

        if ($report) {
            // Delete existing items for this report
            IssueQuantityItem::where('parent_id', $report->id)->delete();
            // Update the existing report
            $report->update([
                'total_quantity' => 0,
                'status' => 'processing',
                'generated_by' => $this->user_id,
                'updated_at' => now()
            ]);
        } else {
            // Create new report if doesn't exist
            $report = IssueQuantityReport::create([
                'month_year' => $this->month_year,
                'total_quantity' => 0,
                'created_by' => $this->user_id,
                'status' => 'processing',
                'generated_by' => $this->user_id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $totalQuantity = 0;

        foreach ($rows as $index => $row) {
            Log::debug("Processing row", ['index' => $index, 'data' => $row->toArray()]);

            // Normalize header names (case-insensitive and remove spaces)
            $rowData = [];
            foreach ($row as $key => $value) {
                $normalizedKey = strtolower(str_replace(' ', '_', trim($key)));
                $rowData[$normalizedKey] = $value;
            }

            if (!isset($rowData['item_description']) || !isset($rowData['quantity'])) {
                Log::warning("Missing data", ['row' => $index + 2, 'row_data' => $rowData]);
                throw new \Exception("Missing required data at row " . ($index + 2) . ". Required columns: 'Item Description' and 'quantity'");
            }

            $description = trim($rowData['item_description']);
            $quantity = (int) $rowData['quantity'];

            $product = Product::where('description', $description)->first();

            if (!$product) {
                Log::error("Product not found", ['description' => $description, 'row' => $index + 2]);
                throw new \Exception("Product with description '{$description}' not found (Row " . ($index + 2) . ").");
            }

            IssueQuantityItem::create([
                'parent_id' => $report->id,
                'month_year' => $this->month_year,
                'quantity' => $quantity,
                'product_id' => $product->id,
            ]);

            $totalQuantity += $quantity;
        }

        $report->update([
            'total_quantity' => $totalQuantity,
            'status' => 'completed'
        ]);

        DB::commit();

        Log::info("Import completed", ['report_id' => $report->id, 'total_quantity' => $totalQuantity]);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error("Import failed", ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        throw $e;
    }
}



    public function chunkSize(): int
    {
        return 50;
    }
}
