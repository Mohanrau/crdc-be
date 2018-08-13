<?php

use App\{
    Models\Locations\Country,
    Models\Locations\State,
    Models\Settings\SelfCollectionInfo,
    Models\Stockists\Stockist
};
use Illuminate\Database\Seeder;

class SelfCollectionInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."self_collection_info.txt"));

        foreach ($data as $item)
        {
            $country = Country::where('code_iso_2', $item->country_code)->first();

            $state = State::where('name', ucwords( strtolower($item->state) ))->first();

            $stockist = Stockist::where('name', $item->name)->first();

            SelfCollectionInfo::updateOrCreate(
                [
                    'country_id' => $country->id,
                    'state_id' => isset($state) ? $state->id : null,
                    'area' => $item->area,
                    'stockist_id' => isset($stockist) ? $stockist->id : null,
                    'name' => $item->name,
                    'address' => $item->address,
                    'contact_no' => $item->contact_no
                ]
            );
        }
    }
}
