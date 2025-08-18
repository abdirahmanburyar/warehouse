<?php

namespace App\Jobs;

use App\Models\Facility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportFacilitiesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        try {
            $data = Excel::toCollection(null, $this->filePath)[0];

            // Remove header row if present
            if ($data->count() > 0 && 
                strtolower($data[0][0]) === 'facility_name' && 
                strtolower($data[0][1]) === 'facility type' && 
                strtolower($data[0][2]) === 'district' && 
                strtolower($data[0][3]) === 'email' && 
                strtolower($data[0][4]) === 'phone') {
                $data = $data->slice(1);
            }

            // Validate that we have data
            if ($data->count() === 0) {
                throw new \Exception('No data found in the uploaded file. Please ensure the file contains facility data.');
            }

            DB::beginTransaction();

            $importedCount = 0;
            $errors = [];

            foreach ($data as $index => $row) {
                // Skip empty rows
                if (empty($row[0])) continue;

                try {
                    $email = trim($row[3] ?? '');
                    $phone = trim($row[4] ?? '');

                    // Validate required fields
                    if (empty(trim($row[0]))) {
                        throw new \Exception("Row " . ($index + 2) . ": Facility name is required");
                    }
                    if (empty(trim($row[1]))) {
                        throw new \Exception("Row " . ($index + 2) . ": Facility type is required");
                    }
                    if (empty(trim($row[2]))) {
                        throw new \Exception("Row " . ($index + 2) . ": District is required");
                    }

                    Facility::create([
                        'name' => trim($row[0]), // facility_name becomes name
                        'facility_type' => strtolower(trim($row[1] ?? '')),
                        'district' => trim($row[2] ?? ''),
                        'email' => !empty($email) ? $email : null,
                        'phone' => !empty($phone) ? $phone : null,
                        'address' => null,
                        'is_active' => true
                    ]);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = $e->getMessage();
                }
            }

            // If we have errors, rollback and throw exception
            if (!empty($errors)) {
                DB::rollBack();
                throw new \Exception('Import failed with errors: ' . implode('; ', $errors));
            }

            DB::commit();

            // Log successful import
            \Log::info('Facilities import completed successfully', [
                'imported_count' => $importedCount,
                'file_path' => $this->filePath
            ]);

            // Delete the temporary file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log the error
            \Log::error('Facilities import failed', [
                'error' => $e->getMessage(),
                'file_path' => $this->filePath,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Delete the temporary file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
            
            throw $e;
        }
    }
}
