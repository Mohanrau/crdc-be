<?php

use App\{Models\Locations\City,
    Models\Locations\Country,
    Models\Locations\Location,
    Models\Locations\LocationAddresses,
    Models\Locations\State,
    Models\Settings\CountryDynamicContent,
    Models\Stockists\Stockist,
    Models\Stockists\StockistBusinessAddress};
use Illuminate\Database\Seeder;

class LocationAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."locations_addresses_data.txt"));

        foreach ($data as $item)
        {
            $country = Country::where('code_iso_2', $item->country_code)->first();

            $state = State::where('name', ucwords( strtolower($item->state) ))
                ->where('country_id', $country->id)
                ->first();

            $city = City::where('name', ucwords( strtolower($item->city) ))
                ->where('country_id', $country->id)
                ->first();

            $stockist = Stockist::where('stockist_number', $item->location_code)->first();

            if($stockist)
            {
                $location = Location::where('code', $item->location_code)->first();

                $stockistAddress = StockistBusinessAddress::where('stockist_id', $stockist->id)->first();

                $address = collect(json_decode($stockistAddress->addresses));

                $modifiedAddress = collect($address[0]->fields)->each(function($value, $key) use($item, $city, $state, $country){
                    if($value->label == "city")
                    {
                        $value->value = ($city) ? $city->id : null;
                    }

                    if($value->label == "country")
                    {
                        $value->value = $country->id;
                    }

                    if($value->label == "state")
                    {
                        $value->value = ($state) ? $state->id : null;
                    }
                });

                StockistBusinessAddress::where('stockist_id', $stockist->id)->update([
                    'addresses' => json_encode([['fields' => $modifiedAddress]])
                ]);

                $businessAddress = [
                    'title' => 'Business Address',
                    'fields' => $modifiedAddress
                ];
            }
            else
            {
                $location = Location::where('code', $item->location_code)
                    ->whereIn('location_types_id', [ 1, 2 ])
                    ->first();

                $addressFormat = json_decode(CountryDynamicContent::where('type', 'address')
                    ->where('country_id', $country->id)
                    ->first()->content);

                $modifiedAddress = collect($addressFormat[0]->fields)->each(function($value, $key) use($item, $city, $state, $country){
                    if($value->label == "Address 1")
                    {
                        $value->value = $item->addr1;
                    }

                    if($value->label == "Address 2")
                    {
                        $value->value = $item->addr2;
                    }

                    if($value->label == "Address 3")
                    {
                        $value->value = $item->addr3;
                    }

                    if($value->label == "Address 4")
                    {
                        $value->value = $item->addr4;
                    }
                    if($value->label == "postcode")
                    {
                        $value->value = $item->postal_code;
                    }

                    if($value->label == "city")
                    {
                        $value->value = ($city) ? $city->id : null;
                    }

                    if($value->label == "country")
                    {
                        $value->value = $country->id;
                    }

                    if($value->label == "state")
                    {
                        $value->value = ($state) ? $state->id : null;
                    }
                });

                $businessAddress = [
                    'title' => 'Business Address',
                    'fields' => $modifiedAddress
                ];
            }

            if($location)
            {
                LocationAddresses::updateOrCreate([
                    'location_id' => $location->id,
                    'area' => $item->area
                ],
                [
                    'telephone_code_id' => $country->id,
                    'telephone_num' => $item->telephone,
                    'mobile_phone_code_id' => $country->id,
                    'mobile_phone_num' => $item->mobile_1,
                    'country_id' => $country->id,
                    'state_id' => ($state) ? $state->id : null,
                    'display_name' => $item->display_name,
                    'address_data' => json_encode([$businessAddress])
                ]);
            }

        }
    }
}
