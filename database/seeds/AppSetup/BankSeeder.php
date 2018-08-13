<?php

use Illuminate\Database\Seeder;
use App\Models\Locations\
{
    Country,
    CountryBank
};

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."banks.txt"));

        foreach ($data as $item)
        {
            $item->f_company_code = $item->f_company_code == 'ISG' ? 'SG' : $item->f_company_code;

            $item->f_company_code = $item->f_company_code == 'IBN' ? 'BN' : $item->f_company_code;

            $country = Country::where('code_iso_2', '=', $item->f_company_code)->first();

            CountryBank::updateOrCreate(
                [
                    "country_id"=>$country->id,
                    "name"=>$item->bankName,
                    "swift_code" => $item->swiftCode
                ]
            );
        }
    }
}
