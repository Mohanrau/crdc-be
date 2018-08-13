<?php

use Illuminate\Database\Seeder;
use App\Models\Locations\Country;
use App\Models\Currency\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = json_decode(file_get_contents('database/seeding/'."currencies.txt"));

        foreach ($countries as $country)
        {
            Currency::updateOrCreate([
                'name' => $country->name,
                'code' => $country->code,
                'active' => 1
            ]);
        }
    }
}
