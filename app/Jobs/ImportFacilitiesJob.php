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

            DB::beginTransaction();

            foreach ($data as $row) {
                // Skip empty rows
                if (empty($row[0])) continue;

                $email = trim($row[3] ?? '');
                $phone = trim($row[4] ?? '');

                Facility::create([
                    'name' => trim($row[0]), // facility_name becomes name
                    'facility_type' => strtolower(trim($row[1] ?? '')),
                    'district' => trim($row[2] ?? ''),
                    'email' => !empty($email) ? $email : null,
                    'phone' => !empty($phone) ? $phone : null,
                    'address' => null,
                    'is_active' => true
                ]);
            }

            DB::commit();

            // Delete the temporary file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete the temporary file
            if (file_exists($this->filePath)) {
                unlink($this->filePath);
            }
            
            throw $e;
        }
    }
}
