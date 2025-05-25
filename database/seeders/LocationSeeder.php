<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\State;
use App\Models\District;
use App\Models\City;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create states
        $states = [
            ['name' => 'Mogadishu', 'code' => 'MOG'],
            ['name' => 'Puntland', 'code' => 'PUN'],
            ['name' => 'Somaliland', 'code' => 'SOM'],
            ['name' => 'Jubaland', 'code' => 'JUB'],
            ['name' => 'South West', 'code' => 'SW'],
            ['name' => 'Galmudug', 'code' => 'GAL'],
            ['name' => 'Hirshabelle', 'code' => 'HIR'],
        ];

        foreach ($states as $stateData) {
            State::create($stateData);
        }

        // Create districts with state relationships
        $districts = [
            ['district_name' => 'Hodan', 'region_name' => 'Benadir', 'state_id' => 1],
            ['district_name' => 'Howlwadag', 'region_name' => 'Benadir', 'state_id' => 1],
            ['district_name' => 'Waberi', 'region_name' => 'Benadir', 'state_id' => 1],
            ['district_name' => 'Garowe', 'region_name' => 'Nugaal', 'state_id' => 2],
            ['district_name' => 'Bosaso', 'region_name' => 'Bari', 'state_id' => 2],
            ['district_name' => 'Hargeisa', 'region_name' => 'Woqooyi Galbeed', 'state_id' => 3],
            ['district_name' => 'Burco', 'region_name' => 'Togdheer', 'state_id' => 3],
            ['district_name' => 'Kismayo', 'region_name' => 'Lower Juba', 'state_id' => 4],
            ['district_name' => 'Baidoa', 'region_name' => 'Bay', 'state_id' => 5],
            ['district_name' => 'Dhusamareb', 'region_name' => 'Galguduud', 'state_id' => 6],
            ['district_name' => 'Jowhar', 'region_name' => 'Middle Shabelle', 'state_id' => 7],
        ];

        foreach ($districts as $districtData) {
            District::create($districtData);
        }

        // Create cities with district relationships
        $cities = [
            ['name' => 'Hodan Area', 'district_id' => 1],
            ['name' => 'Howlwadag Area', 'district_id' => 2],
            ['name' => 'Waberi Area', 'district_id' => 3],
            ['name' => 'Garowe City', 'district_id' => 4],
            ['name' => 'Bosaso City', 'district_id' => 5],
            ['name' => 'Hargeisa City', 'district_id' => 6],
            ['name' => 'Burco City', 'district_id' => 7],
            ['name' => 'Kismayo City', 'district_id' => 8],
            ['name' => 'Baidoa City', 'district_id' => 9],
            ['name' => 'Dhusamareb City', 'district_id' => 10],
            ['name' => 'Jowhar City', 'district_id' => 11],
        ];

        foreach ($cities as $cityData) {
            City::create($cityData);
        }
    }
}
