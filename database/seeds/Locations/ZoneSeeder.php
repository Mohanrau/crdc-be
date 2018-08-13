<?php

use Illuminate\Database\Seeder;
use App\Models\Locations\
{
    Zone,
    Country
};

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // TODO: revise for actual settings
        // $zones = [
        //     [
        //         'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
        //         'name' => 'WEST MALAYSIA',
        //         'code' => 'MY-WM',
        //         'active' => 1
        //     ],
        //     [
        //         'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
        //         'name' => 'EAST MALAYSIA',
        //         'code' => 'MY-EM',
        //         'active' => 1
        //     ],
        //     [
        //         'country_id' => Country::select('id')->where('code_iso_2','TW')->first()->id,
        //         'name' => 'STANDARD AREA',
        //         'code' => 'TW-STDA',
        //         'active' => 1
        //     ],
        //     [
        //         'country_id' => Country::select('id')->where('code_iso_2','TW')->first()->id,
        //         'name' => 'OTHER DELIVERY AREA',
        //         'code' => 'TW-ODA',
        //         'active' => 1
        //     ],
        //     [
        //         'country_id' => Country::select('id')->where('code_iso_2','HK')->first()->id,
        //         'name' => 'STANDARD AREA',
        //         'code' => 'HK-STDA',
        //         'active' => 1
        //     ],
        //     [
        //         'country_id' => Country::select('id')->where('code_iso_2','HK')->first()->id,
        //         'name' => 'OTHER DELIVERY AREA 1',
        //         'code' => 'HK-ODA1',
        //         'active' => 1
        //     ],
        //     [
        //         'country_id' => Country::select('id')->where('code_iso_2','HK')->first()->id,
        //         'name' => 'OTHER DELIVERY AREA 2',
        //         'code' => 'HK-ODA2',
        //         'active' => 1
        //     ]
        // ];

        // foreach ($zones as $zone)
        // {
        //     Zone::updateOrCreate($zone);
        // }
    }
}
