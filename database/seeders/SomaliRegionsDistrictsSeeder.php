<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SomaliRegionsDistrictsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // First, create all regions
        $regions = [
            'Awdal',
            'Woqooyi Galbeed',
            'Togdheer',
            'Sanaag',
            'Sool',
            'Bari',
            'Nugaal',
            'Mudug',
            'Galguduud',
            'Hiran',
            'Middle Shabelle',
            'Banaadir',
            'Lower Shabelle',
            'Bakool',
            'Bay',
            'Gedo',
            'Middle Jubba',
            'Lower Jubba'
        ];

        foreach ($regions as $regionName) {
            DB::table('regions')->insertOrIgnore([
                'name' => $regionName,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Then create all districts with their corresponding regions
        $districts = [
            // Awdal Region
            ['name' => 'Borama', 'region' => 'Awdal'],
            ['name' => 'Baki', 'region' => 'Awdal'],
            ['name' => 'Lughaya', 'region' => 'Awdal'],
            ['name' => 'Zeila', 'region' => 'Awdal'],

            // Woqooyi Galbeed Region
            ['name' => 'Hargeisa', 'region' => 'Woqooyi Galbeed'],
            ['name' => 'Berbera', 'region' => 'Woqooyi Galbeed'],
            ['name' => 'Gabiley', 'region' => 'Woqooyi Galbeed'],
            ['name' => 'Sheikh', 'region' => 'Woqooyi Galbeed'],

            // Togdheer Region
            ['name' => 'Burao', 'region' => 'Togdheer'],
            ['name' => 'Oodweyne', 'region' => 'Togdheer'],
            ['name' => 'Buhoodle', 'region' => 'Togdheer'],

            // Sanaag Region
            ['name' => 'Erigavo', 'region' => 'Sanaag'],
            ['name' => 'Las Qorey', 'region' => 'Sanaag'],
            ['name' => 'Badhan', 'region' => 'Sanaag'],
            ['name' => 'Dhahar', 'region' => 'Sanaag'],

            // Sool Region
            ['name' => 'Las Anod', 'region' => 'Sool'],
            ['name' => 'Taleex', 'region' => 'Sool'],
            ['name' => 'Hudun', 'region' => 'Sool'],
            ['name' => 'Yagori', 'region' => 'Sool'],

            // Bari Region
            ['name' => 'Bosaso', 'region' => 'Bari'],
            ['name' => 'Qardho', 'region' => 'Bari'],
            ['name' => 'Iskushuban', 'region' => 'Bari'],
            ['name' => 'Alula', 'region' => 'Bari'],

            // Nugaal Region
            ['name' => 'Garowe', 'region' => 'Nugaal'],
            ['name' => 'Eyl', 'region' => 'Nugaal'],
            ['name' => 'Dangorayo', 'region' => 'Nugaal'],

            // Mudug Region
            ['name' => 'Galkayo', 'region' => 'Mudug'],
            ['name' => 'Hobyo', 'region' => 'Mudug'],
            ['name' => 'Harardhere', 'region' => 'Mudug'],
            ['name' => 'Wisil', 'region' => 'Mudug'],

            // Galguduud Region
            ['name' => 'Dhusamareb', 'region' => 'Galguduud'],
            ['name' => 'Abudwaq', 'region' => 'Galguduud'],
            ['name' => 'Adado', 'region' => 'Galguduud'],
            ['name' => 'Balanbale', 'region' => 'Galguduud'],

            // Hiran Region
            ['name' => 'Beledweyne', 'region' => 'Hiran'],
            ['name' => 'Buulobarde', 'region' => 'Hiran'],
            ['name' => 'Jalalaqsi', 'region' => 'Hiran'],

            // Middle Shabelle Region
            ['name' => 'Jowhar', 'region' => 'Middle Shabelle'],
            ['name' => 'Balad', 'region' => 'Middle Shabelle'],
            ['name' => 'Warsheikh', 'region' => 'Middle Shabelle'],
            ['name' => 'Adale', 'region' => 'Middle Shabelle'],

            // Banaadir Region (Mogadishu)
            ['name' => 'Mogadishu', 'region' => 'Banaadir'],
            ['name' => 'Afgooye', 'region' => 'Banaadir'],
            ['name' => 'Marka', 'region' => 'Banaadir'],
            ['name' => 'Wanlaweyn', 'region' => 'Banaadir'],

            // Lower Shabelle Region
            ['name' => 'Qoryooley', 'region' => 'Lower Shabelle'],
            ['name' => 'Kurtunwaarey', 'region' => 'Lower Shabelle'],
            ['name' => 'Sablale', 'region' => 'Lower Shabelle'],

            // Bakool Region
            ['name' => 'Xuddur', 'region' => 'Bakool'],
            ['name' => 'Tiyeglow', 'region' => 'Bakool'],
            ['name' => 'Wajid', 'region' => 'Bakool'],
            ['name' => 'El Barde', 'region' => 'Bakool'],

            // Bay Region
            ['name' => 'Baidoa', 'region' => 'Bay'],
            ['name' => 'Burhakaba', 'region' => 'Bay'],
            ['name' => 'Dinsoor', 'region' => 'Bay'],
            ['name' => 'Qansax Dheere', 'region' => 'Bay'],

            // Gedo Region
            ['name' => 'Garbahaarey', 'region' => 'Gedo'],
            ['name' => 'Luuq', 'region' => 'Gedo'],
            ['name' => 'Bardhere', 'region' => 'Gedo'],
            ['name' => 'El Wak', 'region' => 'Gedo'],

            // Middle Jubba Region
            ['name' => 'Bu\'aale', 'region' => 'Middle Jubba'],
            ['name' => 'Jilib', 'region' => 'Middle Jubba'],
            ['name' => 'Sakow', 'region' => 'Middle Jubba'],

            // Lower Jubba Region
            ['name' => 'Kismayo', 'region' => 'Lower Jubba'],
            ['name' => 'Jamame', 'region' => 'Lower Jubba'],
            ['name' => 'Badhaadhe', 'region' => 'Lower Jubba'],
            ['name' => 'Afmadow', 'region' => 'Lower Jubba'],
        ];

        foreach ($districts as $district) {
            DB::table('districts')->insertOrIgnore([
                'name' => $district['name'],
                'region' => $district['region'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->command->info('Somali regions and districts seeded successfully!');
        $this->command->info('Created ' . count($regions) . ' regions and ' . count($districts) . ' districts.');
    }
}
