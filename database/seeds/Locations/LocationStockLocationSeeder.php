<?php

use App\Models\Locations\Location;
use Illuminate\Database\Seeder;

class LocationStockLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $locations = Location::with('entity.country.stockLocation')->get();

        foreach ($locations as $location)
        {
            $location->stockLocation()->syncWithoutDetaching(
                $location->entity->country->stockLocation()->pluck('id')->toArray()
            );
        }
    }
}
