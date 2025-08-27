<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\FundSource;
use App\Models\Region;
use App\Models\AssetLocation;
use App\Models\SubLocation;
use App\Models\Assignee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssetsImport implements ToCollection, WithHeadingRow, WithChunkReading, WithValidation, SkipsOnError, ShouldQueue
{
    use SkipsErrors;

    protected $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId ?? auth()->id();
    }

    public function collection(Collection $rows)
    {
        Log::info("🚀 Starting import with " . $rows->count() . " rows");
        
        // Log the first row to see what columns we're actually getting
        if ($rows->count() > 0) {
            $firstRow = $rows->first();
            Log::info("📋 First row column names: " . json_encode(array_keys($firstRow->toArray())));
            Log::info("📋 First row data: " . json_encode($firstRow->toArray()));
        }
        
        foreach ($rows as $index => $row) {
            Log::info("📝 Processing row " . ($index + 1) . ": " . json_encode($row));
            
            if (empty($row['asset_tag']) || empty($row['asset_name'])) {
                Log::warning("⚠️ Skipping row " . ($index + 1) . " - missing asset_tag or asset_name");
                continue;
            }

            try {
                DB::transaction(function () use ($row, $index) {
                    // Create or get related models
                    $category = AssetCategory::firstOrCreate(['name' => trim($row['category'])]);
                    $type = AssetType::firstOrCreate([
                        'name' => trim($row['type']),
                        'asset_category_id' => $category->id
                    ]);
                    
                    $region = Region::firstOrCreate(['name' => trim($row['region'])]);
                    $fundSource = FundSource::firstOrCreate(['name' => trim($row['fund_source'])]);
                    
                    $assetLocation = AssetLocation::firstOrCreate(['name' => trim($row['asset_location'])]);
                    $subLocation = SubLocation::firstOrCreate([
                        'name' => trim($row['sub_location']),
                        'asset_location_id' => $assetLocation->id,
                    ]);

                    // Validate assignee exists (don't create new ones)
                    $assignee = null;
                    if (!empty($row['assignee'])) {
                        $assignee = Assignee::where('name', trim($row['assignee']))->first();
                        if (!$assignee) {
                            throw new \Exception("Assignee '{$row['assignee']}' not found. Please create the assignee first or use an existing one.");
                        }
                    }

                    // Parse acquisition date
                    $acquisitionDate = null;
                    if (!empty($row['acquisition_date'])) {
                        if (is_numeric($row['acquisition_date'])) {
                            $acquisitionDate = Date::excelToDateTimeObject($row['acquisition_date']);
                        } else {
                            $acquisitionDate = \Carbon\Carbon::parse($row['acquisition_date']);
                        }
                    } else {
                        $acquisitionDate = now();
                    }

                    // Map status to valid enum values
                    $status = $this->mapStatus($row['status'] ?? 'pending_approval');

                    // Create the asset
                    $asset = Asset::create([
                        'acquisition_date' => $acquisitionDate,
                        'fund_source_id' => $fundSource->id,
                        'region_id' => $region->id,
                        'asset_location_id' => $assetLocation->id,
                        'sub_location_id' => $subLocation->id,
                        'status' => 'pending_approval', // Asset status is always pending_approval initially
                        'submitted_by' => $this->userId,
                        'submitted_at' => now(),
                    ]);

                    // Create the asset item
                    AssetItem::create([
                        'asset_id' => $asset->id,
                        'asset_tag' => trim($row['asset_tag']),
                        'asset_name' => trim($row['asset_name']),
                        'serial_number' => !empty($row['serial_number']) ? trim($row['serial_number']) : 'SN-' . uniqid(),
                        'asset_category_id' => $category->id,
                        'asset_type_id' => $type->id,
                        'assignee_id' => $assignee?->id,
                        'status' => $status,
                        'original_value' => is_numeric($row['original_value']) ? (float)$row['original_value'] : 0,
                    ]);

                    Log::info("✅ Successfully imported asset: {$row['asset_tag']} - {$row['asset_name']}");
                });

            } catch (\Throwable $e) {
                Log::error("❌ Error importing row " . ($index + 2) . ": {$e->getMessage()}");
                Log::error("Row data: " . json_encode($row));
                throw $e;
            }
        }
    }

    public function rules(): array
    {
        return [
            'asset_tag' => 'required|string|max:255',
            'asset_name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'fund_source' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'asset_location' => 'required|string|max:255',
            'sub_location' => 'required|string|max:255',
            'assignee' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'original_value' => 'nullable|numeric|min:0',
            'acquisition_date' => 'nullable|string|max:255',
        ];
    }

    public function chunkSize(): int
    {
        return 50;
    }

    /**
     * Map user-friendly status to valid enum values
     */
    private function mapStatus(?string $status): string
    {
        if (empty($status)) {
            return 'pending_approval';
        }

        $statusMap = [
            'active' => 'in_use',
            'inactive' => 'pending_approval',
            'maintenance' => 'maintenance',
            'retired' => 'retired',
            'disposed' => 'disposed',
        ];

        return $statusMap[strtolower(trim($status))] ?? 'pending_approval';
    }
}
