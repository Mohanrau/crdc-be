<?php

use Illuminate\Database\Seeder;
use App\Models\
{
    Locations\Country,
    Settings\Tax
};

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."taxes.txt"));

        foreach ($data as $item)
        {
            $country = Country::where('code_iso_2', $item->country_code)->first();

            Tax::updateOrCreate(
                [
                    'country_id' => $country->id,
                    'code' => $item->code
                ],
                [
                    'rate' => $item->rate,
                    'default' => $item->default,
                    'active' => $item->active
                ]
            );
        }
    }
}
