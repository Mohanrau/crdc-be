<?php

use App\Models\Locations\Country;
use App\Models\Sales\DeliveryOrderService;
use Illuminate\Database\Seeder;

class DeliveryOrderServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'country_code' => 'MY',
                'name' => 'CityLink'
            ],
            [
                'country_code' => 'MY',
                'name' => 'GDEX'
            ],
            [
                'country_code' => 'TH',
                'name' => 'Nim Express'
            ],

            [
                'country_code' => 'KH',
                'name' => 'Air Express Worldwide'
            ],

            [
                'country_code' => 'VN',
                'name' => 'Kerry'
            ],

            [
                'country_code' => 'HK',
                'name' => 'TA-Q-BIN'
            ],

            [
                'country_code' => 'TW',
                'name' => '宅配通'
            ],

            [
                'country_code' => 'PH',
                'name' => '2GO Express'
            ],

            [
                'country_code' => 'ID',
                'name' => 'Globalindo'
            ],

            [
                'country_code' => 'ID',
                'name' => 'JNE'
            ],

        ];

        foreach ($data as $item)
        {
            $country = Country::where('code_iso_2', $item['country_code'])->first();

            DeliveryOrderService::updateOrCreate([
                'country_id' => $country->id,
                'name' => $item['name']
            ]);
        }
    }
}
