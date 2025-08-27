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

class AssetsImport implements ToCollection, WithHeadingRow, WithChunkReading, SkipsOnError, ShouldQueue
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
            
            // Log data types for debugging
            foreach ($firstRow as $key => $value) {
                $type = is_object($value) ? get_class($value) : gettype($value);
                Log::info("🔍 Column '{$key}' type: {$type}, value: " . json_encode($value));
            }
        }
        
        foreach ($rows as $index => $row) {
            Log::info("📝 Processing row " . ($index + 1) . ": " . json_encode($row));
            
            // Custom validation - check required fields
            if (empty($row['asset_tag']) || empty($row['asset_name'])) {
                Log::warning("⚠️ Skipping row " . ($index + 1) . " - missing asset_tag or asset_name");
                continue;
            }
            
            // Validate other required fields
            $requiredFields = ['category', 'type', 'fund_source', 'region', 'asset_location', 'sub_location'];
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (empty($row[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                Log::warning("⚠️ Skipping row " . ($index + 1) . " - missing required fields: " . implode(', ', $missingFields));
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

                    // Create or get assignee
                    $assignee = null;
                    if (!empty($row['assignee'])) {
                        $assignee = Assignee::firstOrCreate(
                            ['name' => trim($row['assignee'])],
                            [
                                'email' => null,
                                'phone' => null,
                                'department' => null
                            ]
                        );
                        Log::info("👤 Assignee: " . $assignee->name . " (ID: " . $assignee->id . ") - " . ($assignee->wasRecentlyCreated ? "Created" : "Found existing"));
                    } else {
                        Log::info("👤 No assignee specified");
                    }

                    // Parse acquisition date
                    $acquisitionDate = null;
                    if (!empty($row['acquisition_date'])) {
                        try {
                            $dateValue = $row['acquisition_date'];
                            
                            // Handle different data types that Excel might send
                            if (is_numeric($dateValue)) {
                                // Handle Excel date serial numbers
                                $acquisitionDate = Date::excelToDateTimeObject($dateValue);
                                Log::info("📅 Excel serial number '{$dateValue}' converted to: " . $acquisitionDate->format('Y-m-d'));
                            } elseif (is_object($dateValue) && method_exists($dateValue, 'format')) {
                                // Already a DateTime object
                                $acquisitionDate = \Carbon\Carbon::instance($dateValue);
                                Log::info("📅 DateTime object converted to: " . $acquisitionDate->format('Y-m-d'));
                            } else {
                                // Convert to string and parse
                                $dateString = (string) $dateValue;
                                $dateString = trim($dateString);
                                
                                Log::info("📅 Processing date string: '{$dateString}'");
                                
                                // Handle various date formats
                                if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $dateString)) {
                                    // Format: M/D/YYYY or MM/DD/YYYY
                                    $acquisitionDate = \Carbon\Carbon::createFromFormat('n/j/Y', $dateString);
                                } elseif (preg_match('/^\d{4}-\d{1,2}-\d{1,2}$/', $dateString)) {
                                    // Format: YYYY-MM-DD
                                    $acquisitionDate = \Carbon\Carbon::parse($dateString);
                                } elseif (preg_match('/^\d{1,2}-\d{1,2}\/\d{4}$/', $dateString)) {
                                    // Format: M-D/YYYY or MM-DD/YYYY
                                    $acquisitionDate = \Carbon\Carbon::createFromFormat('n-j/Y', $dateString);
                                } else {
                                    // Try Carbon's automatic parsing for other formats
                                    $acquisitionDate = \Carbon\Carbon::parse($dateString);
                                }
                                
                                Log::info("📅 Parsed date '{$dateString}' to: " . $acquisitionDate->format('Y-m-d'));
                            }
                        } catch (\Exception $e) {
                            Log::warning("⚠️ Could not parse date '{$row['acquisition_date']}', using current date. Error: " . $e->getMessage());
                            $acquisitionDate = now();
                        }
                    } else {
                        $acquisitionDate = now();
                        Log::info("📅 No date provided, using current date");
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
            'in use' => 'in_use',
            'inactive' => 'pending_approval',
            'maintenance' => 'maintenance',
            'retired' => 'retired',
            'disposed' => 'disposed',
        ];

        return $statusMap[strtolower(trim($status))] ?? 'pending_approval';
    }
}
