<?php

use Illuminate\Database\Seeder;
use App\Models\Locations\{
    Country,
    State,
    City
};

class StateAndCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."states_cities.txt"));

        foreach ($data as $item)
        {
            $item->country = $item->country == 'ISG' ? 'SG' : $item->country;

            $item->country = $item->country == 'IBN' ? 'BN' : $item->country;

            $country = Country::where('code_iso_2', '=', $item->country)->first();

            $state = State::where('name', '=', $item->state)->first();

            if (empty($state)) {
                State::updateOrCreate(
                    [
                        "country_id" => $country->id,
                        "name" => $item->state
                    ]
                );

                $state = State::where('name', '=', $item->state)->first();
            }

            City::updateOrCreate(
                [
                    "country_id"=>$country->id,
                    "state_id"=>$state->id,
                    "name"=>$item->city
                ]
            );
        }
    }
}
