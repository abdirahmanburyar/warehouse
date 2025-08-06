<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EligibleItem;

class UpdateEligibilityFormats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eligibility:update-formats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update eligibility facility_type formats from snake_case to proper format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating eligibility item formats...');

        // Mapping of snake_case to proper format
        $formatMappings = [
            'general_hospital' => 'General Hospital',
            'district_hospital' => 'District Hospital',
            'health_center' => 'Health Center',
            'clinic' => 'Clinic',
            'regional_hospital' => 'Regional Hospital',
            'referral_hospital' => 'Referral Hospital',
            'private_hospital' => 'Private Hospital',
            'community_health_center' => 'Community Health Center',
            // Add more mappings as needed
        ];

        $updatedCount = 0;

        foreach ($formatMappings as $oldFormat => $newFormat) {
            $count = EligibleItem::where('facility_type', $oldFormat)
                ->update(['facility_type' => $newFormat]);
            
            if ($count > 0) {
                $this->info("Updated {$count} records from '{$oldFormat}' to '{$newFormat}'");
                $updatedCount += $count;
            }
        }

        $this->info("Total records updated: {$updatedCount}");
        
        if ($updatedCount === 0) {
            $this->info('No records needed updating.');
        }

        return 0;
    }
}
