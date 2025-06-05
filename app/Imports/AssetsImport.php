<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\FundSource;
use App\Models\Region;
use App\Models\AssetLocation;
use App\Models\SubLocation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssetsImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            if (empty($row['asset_tag'])) {
                continue;
            }

            try {
                // Create or get category and region
                $category = AssetCategory::firstOrCreate(['name' => $row['category']]);
                $region   = Region::firstOrCreate(['name' => $row['region']]);

                // Create or get asset location
                $assetLocation = AssetLocation::firstOrCreate(['name' => $row['location']]);

                // Create or get sub location under that asset location
                $subLocation = SubLocation::firstOrCreate([
                    'name' => $row['sub_location'],
                    'asset_location_id' => $assetLocation->id,
                ]);

                // Create or get fund source
                $fundSource = FundSource::firstOrCreate(['name' => $row['source_agence']]);
                // Parse Excel date
                $acquisitionDate = null;
                if (!empty($row['acquasition_date'])) {
                    $acquisitionDate = is_numeric($row['acquasition_date'])
                        ? Date::excelToDateTimeObject($row['acquasition_date'])
                        : now();
                }
                Asset::create([
                    'asset_tag' => $row['asset_tag'],
                    'asset_category_id' => $category->id,
                    'serial_number' => $row['serial_number'] ?? '',
                    'item_description' => $row['description'] ?? '',
                    'person_assigned' => $row['assigned_to'] ?? '',
                    'asset_location_id' => $assetLocation->id,
                    'sub_location_id' => $subLocation->id,
                    'fund_source_id' => $fundSource->id,
                    'region_id' => $region->id,
                    'acquisition_date' => $acquisitionDate,
                    'original_value' => is_numeric($row['original_value']) ? $row['original_value'] : 0,
                ]);
            } catch (\Throwable $e) {
                Log::error("❌ Error importing row {$e->getMessage()}");
            }
        }
    }
    public function chunkSize(): int
    {
        return 50;
    }
}
