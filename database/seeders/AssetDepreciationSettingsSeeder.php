<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AssetDepreciationSetting;

class AssetDepreciationSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Installing default asset depreciation settings...');
        
        AssetDepreciationSetting::installDefaults();
        
        $this->command->info('Default asset depreciation settings installed successfully!');
    }
}
