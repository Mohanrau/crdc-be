<?php

use App\Models\Locations\City;
use App\Models\Locations\Country;
use App\Models\Locations\StockLocation;
use Illuminate\Database\Seeder;

class StockLocationCitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = Country::active()->get();

        foreach ($countries as $country) {
            $stockLocationIds = [];

            $cities = City::where('country_id', $country->id)->orderBy('name', 'desc')->get();

            foreach ($cities as $city) {
                $stockLocations = StockLocation::where('country_id', $country->id)->whereNotIn('id', $stockLocationIds);

                $stockLocationWithCity = $stockLocations->where('name', 'like', '%' . strtolower($city->name) . '%');

                if ($stockLocationWithCity->count()) {
                    $stockLocationIds = array_merge($stockLocationIds, $stockLocationWithCity->pluck('id')->toArray());

                    $city->stockLocation()->syncWithoutDetaching(
                        $stockLocationWithCity->pluck('id')->toArray()
                    );
                }
            }
        }
    }
}