<?php

namespace App\Imports;

use App\Models\Facility;
use App\Models\FacilityType;
use App\Models\District;
use App\Models\Region;
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
use Illuminate\Support\Str;

class FacilitiesImport implements 
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
    protected $facilityTypeCache = [];
    protected $districtCache = [];
    protected $regionCache = [];
    protected $importId;

    public function __construct(string $importId)
    {
        $this->importId = $importId;
    }

    public function model(array $row)
    {
        try {
            // Check if required fields are present
            if (empty($row['facility_name'])) {
                $this->skippedCount++;
                return null;
            }

            $facilityName = trim($row['facility_name']);

            if (strlen($facilityName) > 255) {
                $this->errors[] = "Facility name too long: " . substr($facilityName, 0, 50) . "...";
                $this->skippedCount++;
                return null;
            }

            // Facility Type
            $facilityType = null;
            if (!empty($row['facility_type'])) {
                $typeName = trim($row['facility_type']);
                if (strlen($typeName) > 255) {
                    $this->errors[] = "Facility type name too long: " . substr($typeName, 0, 50) . "...";
                    $this->skippedCount++;
                    return null;
                }

                if (!isset($this->facilityTypeCache[$typeName])) {
                    $facilityTypeModel = FacilityType::firstOrCreate(
                        ['name' => $typeName],
                        ['is_active' => true]
                    );
                    $this->facilityTypeCache[$typeName] = $facilityTypeModel->name;
                }
                $facilityType = $this->facilityTypeCache[$typeName];
            }

            // District
            $district = null;
            if (!empty($row['district'])) {
                $districtName = trim($row['district']);
                if (strlen($districtName) > 255) {
                    $this->errors[] = "District name too long: " . substr($districtName, 0, 50) . "...";
                    $this->skippedCount++;
                    return null;
                }

                if (!isset($this->districtCache[$districtName])) {
                    $districtModel = District::firstOrCreate(
                        ['name' => $districtName]
                    );
                    $this->districtCache[$districtName] = $districtModel->name;
                }
                $district = $this->districtCache[$districtName];
            }

            // Region (required - derive from district if not provided)
            $region = null;
            if (!empty($row['region'])) {
                $regionName = trim($row['region']);
                if (strlen($regionName) > 255) {
                    $this->errors[] = "Region name too long: " . substr($regionName, 0, 50) . "...";
                    $this->skippedCount++;
                    return null;
                }

                if (!isset($this->regionCache[$regionName])) {
                    $regionModel = Region::firstOrCreate(
                        ['name' => $regionName]
                    );
                    $this->regionCache[$regionName] = $regionModel->name;
                }
                $region = $this->regionCache[$regionName];
            } else {
                // If region is not provided, derive it from district or use a default
                if (!empty($district)) {
                    // Try to find a region that matches the district
                    $regionModel = Region::where('name', 'like', '%' . $district . '%')->first();
                    if ($regionModel) {
                        $region = $regionModel->name;
                    } else {
                        // Create a default region based on district
                        $region = 'Region ' . $district;
                        $regionModel = Region::firstOrCreate(['name' => $region]);
                        $region = $regionModel->name;
                    }
                } else {
                    // If no district either, use a default region
                    $region = 'Default Region';
                    $regionModel = Region::firstOrCreate(['name' => $region]);
                    $region = $regionModel->name;
                }
            }

            // Email validation
            $email = null;
            if (!empty($row['email'])) {
                $email = trim($row['email']);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[] = "Invalid email format for facility: " . $facilityName;
                    $this->skippedCount++;
                    return null;
                }
            }

            // Phone validation
            $phone = null;
            if (!empty($row['phone'])) {
                $phone = trim($row['phone']);
                if (strlen($phone) > 20) {
                    $this->errors[] = "Phone number too long for facility: " . $facilityName;
                    $this->skippedCount++;
                    return null;
                }
            }

            $this->importedCount++;

            // Update progress in cache
            Cache::increment($this->importId);

            // Create or update facility
            $facility = Facility::updateOrCreate([
                'name' => $facilityName,
            ], [
                'name' => $facilityName,
                'facility_type' => $facilityType,
                'district' => $district,
                'region' => $region,
                'email' => $email,
                'phone' => $phone,
                'address' => !empty($row['address']) ? trim($row['address']) : null,
                'handled_by' => null,
                'is_active' => true,
                'has_cold_storage' => false, // Default value
            ]);

            return $facility;

        } catch (\Exception $e) {
            $facilityName = $row['facility_name'] ?? 'Unknown';
            $this->errors[] = "Error processing facility '{$facilityName}': " . $e->getMessage();
            $this->skippedCount++;
            throw $e;
        }
    }

    public function rules(): array
    {
        return [
            'facility_name' => 'required|string|max:255',
            'facility_type' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'region' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
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
                Log::info('Facilities import completed', [
                    'importId' => $this->importId,
                    'imported' => $this->importedCount,
                    'skipped' => $this->skippedCount,
                    'errors' => count($this->errors)
                ]);
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
