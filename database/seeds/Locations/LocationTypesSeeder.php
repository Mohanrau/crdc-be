<?php

use Illuminate\Database\Seeder;
use App\Models\Locations\LocationTypes;

class LocationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $locationTypes = [
            [
                'code' => 'main_branch',
                'name' => 'Main Branch',
            ],
            [
                'code' => 'branch',
                'name' => 'Branch',
            ],
            [
                'code' => 'stockist',
                'name' => 'Stockist',
            ],
            [
                'code' => 'online',
                'name' => 'Online',
            ]
        ];

        foreach ($locationTypes as $locationType)
        {
            LocationTypes::updateOrCreate($locationType);
        }
    }
}
