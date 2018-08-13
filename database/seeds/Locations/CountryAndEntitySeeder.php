<?php

use Illuminate\Database\Seeder;
use App\Models\Locations\{
    Country, Entity, Location, LocationTypes, StockLocation
};
use App\Models\Currency\Currency;

class CountryAndEntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."country.txt"));

        $entities = [];

        foreach ($data as $item)
        {
            switch ($item->f_company_code){
                case 'MY':
                    $active = 1;
                    $item->currency = 'MYR';
                    $entities['MY'] = 'MYEG';
                    break;
                case 'TH':
                    $active = 1;
                    $item->currency = 'THB';
                    $entities['TH'] = 'THET';
                    break;
                case 'BN':
                case 'IBN':
                    $active = 1;
                    $item->currency = 'BND';
                    $entities['BN'] = 'BNEK';
                    break;
                case 'SG':
                case 'ISG':
                    $active = 1;
                    $item->currency = 'SGD';
                    $entities['SG'] = 'SGEK';
                    break;
                case 'TW':
                    $active = 1;
                    $item->currency = 'TWD';
                    $entities['TW'] = 'CNET';
                    break;
                case 'HK':
                    $active = 1;
                    $item->currency = 'HKD';
                    $entities['HK'] = 'CNEH';
                    break;
                case 'KH':
                    $active = 1;
                    $item->currency = 'USD';
                    $entities['KH'] = 'KHEI';
                    break;
                case 'PH':
                    $active = 1;
                    $item->currency = 'PHP';
                    $entities['PH'] = 'PHEI';
                    break;
                case 'ID':
                    $active = 1;
                    $item->currency = 'IDR';
                    $entities['ID'] = 'INEG';
                    break;
                case 'CN':
                    $active = 1;
                    $item->currency = 'CNY';
                    $entities['CN'] = 'CNEC';
                    break;
                default:
                    $active = 0;
                    $item->currency = NULL;
            }

            $currency = Currency::where('code',$item->currency)->first();

            $currency = ($currency == null) ? new Currency(['id'=>NULL]) : $currency;

            Country::updateOrCreate(
                [
                    'id' => $item->f_company_id,
                    'name' => $item->f_company_name,
                    'code_iso_2' => $item->f_company_code,
                    'code' => $item->code_iso_3,
                    'call_code' => $item->call_code,
                    'tax_desc' => 'GST',
                    'default_currency_id' => $currency->id,
                    'active' => $active
                ]
            );
        }

        $countries = Country::all()->take(10);

        foreach ($countries as $country)
        {
            Entity::updateOrCreate(
                [
                    'country_id' => $country->id,
                    'name' => $entities[strtoupper($country->code_iso_2)],
                    'active' => 1
                ]
            );

            Location::updateOrCreate(
                [
                    'name' => strtoupper($country->code_iso_2). ' ONLINE'
                ],
                [
                    'zone_id' => NULL,
                    'entity_id' => Entity::where('name',$entities[strtoupper($country->code_iso_2)])
                        ->first()
                        ->id,
                    'name' => strtoupper($country->name). ' ONLINE',
                    'code' => strtoupper($country->code_iso_2). '_ONLINE',
                    'location_types_id' => LocationTypes::where('code','online')
                        ->first()
                        ->id,
                    'active' => 1
                ]
            );
        }


        //-----------------------------------------------------------------------------------------------------------


        $data = json_decode(file_get_contents('database/seeding/'."locations.txt"));

        foreach ($data as $item){

            $entity = Entity::where('name', $item->entity)->first();

            $locationType = LocationTypes::where('code', $item->location_types_code)->first();

            Location::updateOrCreate(
                [
                    'name' => $item->name
                ],
                [
                    'zone_id' => NULL,
                    'entity_id' => $entity->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'location_types_id' => $locationType->id,
                    'active' => 1
                ]
            );
        }


        //-----------------------------------------------------------------------------------------------------------


        $data = json_decode(file_get_contents('database/seeding/'."stock_locations.txt"));

        foreach ($data as $item){

            $country = Country::where('code_iso_2', $item->country_code)->first();

            StockLocation::updateOrCreate(
                [
                    'code' => $item->code
                ],
                [
                    'country_id' => $country->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'auto_release' => $item->auto_release,
                    'active' => 1
                ]
            );
        }
    }
}
