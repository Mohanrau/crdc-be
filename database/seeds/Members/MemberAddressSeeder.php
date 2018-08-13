<?php

use Illuminate\Database\Seeder;
use App\Models\
{
    Users\User,
    Members\MemberAddress,
    Locations\Country,
    Locations\State,
    Locations\City
};
class MemberAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $limit = 1000;
//
//        for ($i = 0; $i < $limit; $i++) {
//            factory(App\Models\Members\MemberAddress::class)->create();
//        }


        $data = json_decode(file_get_contents('database/seeding/'."addressess.txt"));

        $results = [];

        $addressType = 0;

        foreach ($data as $item) {

            if(empty($results[$item->f_code]) and $item->f_country_code != 'TW') {

                $results[$item->f_code] = [
                    'addresses' => [
                        [
                            'title'=> 'Permanent',
                            'fields'=>[
                                [
                                    'index'=>1,
                                    'label'=>'Address 1',
                                    'type'=>'input',
                                    'order'=>1,
                                    'value'=>'',
                                    'min'=>6,
                                    'helper'=>'Address must be greater then 6 character',
                                    'required'=>true
                                ],
                                [
                                    'index'=>2,
                                    'label'=>'Address 2',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>3,
                                    'label'=>'Address 3',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>4,
                                    'label'=>'Address 4',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>5,
                                    'label'=>'postcode',
                                    'type'=>'input',
                                    'order'=>3,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>6,
                                    'label'=>'city',
                                    'type'=>'select',
                                    'key'=>'cities',
                                    'identifier'=>'cities',
                                    'order'=>4,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>7,
                                    'label'=>'country',
                                    'type'=>'select',
                                    'key'=>'countries',
                                    'order'=>6,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>8,
                                    'label'=>'state',
                                    'type'=>'select',
                                    'key'=>'states',
                                    'identifier'=>'states',
                                    'trigger'=>'cities',
                                    'order'=>5,
                                    'value'=>'',
                                    'required'=>true
                                ]
                            ]
                        ],
                        [
                            'title'=> 'Correspondence',
                            'fields'=>[
                                [
                                    'index'=>1,
                                    'label'=>'Address 1',
                                    'type'=>'input',
                                    'order'=>1,
                                    'value'=>'',
                                    'min'=>6,
                                    'helper'=>'Address must be greater then 6 character',
                                    'required'=>true
                                ],
                                [
                                    'index'=>2,
                                    'label'=>'Address 2',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>3,
                                    'label'=>'Address 3',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>4,
                                    'label'=>'Address 4',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>5,
                                    'label'=>'postcode',
                                    'type'=>'input',
                                    'order'=>3,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>6,
                                    'label'=>'city',
                                    'type'=>'select',
                                    'key'=>'cities',
                                    'identifier'=>'cities',
                                    'order'=>4,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>7,
                                    'label'=>'country',
                                    'type'=>'select',
                                    'key'=>'countries',
                                    'order'=>6,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>8,
                                    'label'=>'state',
                                    'type'=>'select',
                                    'key'=>'states',
                                    'identifier'=>'states',
                                    'trigger'=>'cities',
                                    'order'=>5,
                                    'value'=>'',
                                    'required'=>true
                                ]
                            ]
                        ],
                        [
                            'title'=> 'Shipping 1',
                            'fields'=>[
                                [
                                    'index'=>1,
                                    'label'=>'Address 1',
                                    'type'=>'input',
                                    'order'=>1,
                                    'value'=>'',
                                    'min'=>6,
                                    'helper'=>'Address must be greater then 6 character',
                                    'required'=>true
                                ],
                                [
                                    'index'=>2,
                                    'label'=>'Address 2',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>3,
                                    'label'=>'Address 3',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>4,
                                    'label'=>'Address 4',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>5,
                                    'label'=>'postcode',
                                    'type'=>'input',
                                    'order'=>3,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>6,
                                    'label'=>'city',
                                    'type'=>'select',
                                    'key'=>'cities',
                                    'identifier'=>'cities',
                                    'order'=>4,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>7,
                                    'label'=>'country',
                                    'type'=>'select',
                                    'key'=>'countries',
                                    'order'=>6,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>8,
                                    'label'=>'state',
                                    'type'=>'select',
                                    'key'=>'states',
                                    'identifier'=>'states',
                                    'trigger'=>'cities',
                                    'order'=>5,
                                    'value'=>'',
                                    'required'=>true
                                ]
                            ]
                        ],
                        [
                            'title'=> 'Shipping 2',
                            'fields'=>[
                                [
                                    'index'=>1,
                                    'label'=>'Address 1',
                                    'type'=>'input',
                                    'order'=>1,
                                    'value'=>'',
                                    'min'=>6,
                                    'helper'=>'Address must be greater then 6 character',
                                    'required'=>true
                                ],
                                [
                                    'index'=>2,
                                    'label'=>'Address 2',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>3,
                                    'label'=>'Address 3',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>4,
                                    'label'=>'Address 4',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>5,
                                    'label'=>'postcode',
                                    'type'=>'input',
                                    'order'=>3,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>6,
                                    'label'=>'city',
                                    'type'=>'select',
                                    'key'=>'cities',
                                    'identifier'=>'cities',
                                    'order'=>4,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>7,
                                    'label'=>'country',
                                    'type'=>'select',
                                    'key'=>'countries',
                                    'order'=>6,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>8,
                                    'label'=>'state',
                                    'type'=>'select',
                                    'key'=>'states',
                                    'identifier'=>'states',
                                    'trigger'=>'cities',
                                    'order'=>5,
                                    'value'=>'',
                                    'required'=>true
                                ]
                            ]
                        ]
                    ]
                ];
            }

            if(empty($results[$item->f_code]) and $item->f_country_code == 'TW') {

                $results[$item->f_code] = [
                    'addresses' => [
                        [
                            'title'=>'Permanent',
                            'fields'=>[
                                [
                                    'index'=>1,
                                    'label'=>'市/縣',
                                    'type'=>'input',
                                    'order'=>1,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>2,
                                    'label'=>'鄉鎮/市區',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>3,
                                    'label'=>'里/村',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>4,
                                    'label'=>'鄰',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>5,
                                    'label'=>'路/街',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>6,
                                    'label'=>'段',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>7,
                                    'label'=>'巷',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>8,
                                    'label'=>'弄',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>9,
                                    'label'=>'號',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>10,
                                    'label'=>'樓之',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>11,
                                    'label'=>'室',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>12,
                                    'label'=>'postcode',
                                    'type'=>'input',
                                    'order'=>3,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>13,
                                    'label'=>'city',
                                    'type'=>'select',
                                    'key'=>'cities',
                                    'identifier'=>'cities',
                                    'order'=>4,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>14,
                                    'label'=>'country',
                                    'type'=>'select',
                                    'key'=>'countries',
                                    'order'=>6,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>15,
                                    'label'=>'state',
                                    'type'=>'select',
                                    'key'=>'states',
                                    'identifier'=>'states',
                                    'trigger'=>'cities',
                                    'order'=>5,
                                    'value'=>'',
                                    'required'=>true
                                ]
                            ]
                        ],
                        [
                            'title'=>'Correspondence',
                            'fields'=>[
                                [
                                    'index'=>1,
                                    'label'=>'市/縣',
                                    'type'=>'input',
                                    'order'=>1,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>2,
                                    'label'=>'鄉鎮/市區',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>3,
                                    'label'=>'里/村',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>4,
                                    'label'=>'鄰',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>5,
                                    'label'=>'路/街',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>6,
                                    'label'=>'段',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>7,
                                    'label'=>'巷',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>8,
                                    'label'=>'弄',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>9,
                                    'label'=>'號',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>10,
                                    'label'=>'樓之',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>11,
                                    'label'=>'室',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>12,
                                    'label'=>'postcode',
                                    'type'=>'input',
                                    'order'=>3,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>13,
                                    'label'=>'city',
                                    'type'=>'select',
                                    'key'=>'cities',
                                    'identifier'=>'cities',
                                    'order'=>4,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>14,
                                    'label'=>'country',
                                    'type'=>'select',
                                    'key'=>'countries',
                                    'order'=>6,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>15,
                                    'label'=>'state',
                                    'type'=>'select',
                                    'key'=>'states',
                                    'identifier'=>'states',
                                    'trigger'=>'cities',
                                    'order'=>5,
                                    'value'=>'',
                                    'required'=>true
                                ]
                            ]
                        ],
                        [
                            'title'=>'Shipping 1',
                            'fields'=>[
                                [
                                    'index'=>1,
                                    'label'=>'市/縣',
                                    'type'=>'input',
                                    'order'=>1,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>2,
                                    'label'=>'鄉鎮/市區',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>3,
                                    'label'=>'里/村',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>4,
                                    'label'=>'鄰',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>5,
                                    'label'=>'路/街',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>6,
                                    'label'=>'段',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>7,
                                    'label'=>'巷',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>8,
                                    'label'=>'弄',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>9,
                                    'label'=>'號',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>10,
                                    'label'=>'樓之',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>11,
                                    'label'=>'室',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>12,
                                    'label'=>'postcode',
                                    'type'=>'input',
                                    'order'=>3,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>13,
                                    'label'=>'city',
                                    'type'=>'select',
                                    'key'=>'cities',
                                    'identifier'=>'cities',
                                    'order'=>4,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>14,
                                    'label'=>'country',
                                    'type'=>'select',
                                    'key'=>'countries',
                                    'order'=>6,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>15,
                                    'label'=>'state',
                                    'type'=>'select',
                                    'key'=>'states',
                                    'identifier'=>'states',
                                    'trigger'=>'cities',
                                    'order'=>5,
                                    'value'=>'',
                                    'required'=>true
                                ]
                            ]
                        ],
                        [
                            'title'=>'Shipping 2',
                            'fields'=>[
                                [
                                    'index'=>1,
                                    'label'=>'市/縣',
                                    'type'=>'input',
                                    'order'=>1,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>2,
                                    'label'=>'鄉鎮/市區',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>3,
                                    'label'=>'里/村',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>4,
                                    'label'=>'鄰',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>5,
                                    'label'=>'路/街',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>6,
                                    'label'=>'段',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>7,
                                    'label'=>'巷',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>8,
                                    'label'=>'弄',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>9,
                                    'label'=>'號',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>10,
                                    'label'=>'樓之',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>11,
                                    'label'=>'室',
                                    'type'=>'input',
                                    'order'=>2,
                                    'value'=>'',
                                    'required'=>false
                                ],
                                [
                                    'index'=>12,
                                    'label'=>'postcode',
                                    'type'=>'input',
                                    'order'=>3,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>13,
                                    'label'=>'city',
                                    'type'=>'select',
                                    'key'=>'cities',
                                    'identifier'=>'cities',
                                    'order'=>4,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>14,
                                    'label'=>'country',
                                    'type'=>'select',
                                    'key'=>'countries',
                                    'order'=>6,
                                    'value'=>'',
                                    'required'=>true
                                ],
                                [
                                    'index'=>15,
                                    'label'=>'state',
                                    'type'=>'select',
                                    'key'=>'states',
                                    'identifier'=>'states',
                                    'trigger'=>'cities',
                                    'order'=>5,
                                    'value'=>'',
                                    'required'=>true
                                ]
                            ]
                        ]
                    ]
                ];
            }

            if(!is_null($item->f_country_code))
            {
                $country = Country::where('code_iso_2', $item->f_country_code)->first();
            }
            else
            {
                $country = new Country(['id'=>NULL]);
            }

            if(!is_null($item->f_state_desp))
            {
                $state = State::where('name', 'LIKE', $item->f_state_desp)
                    ->where('country_id', '=', $country->id)
                    ->first();

                if(!is_null($item->f_city_desp) and !empty($state))
                {
                    $city = City::where('name', 'LIKE', $item->f_city_desp)
                        ->where('state_id', '=', $state->id)
                        ->first();
                }
                else
                {
                    $city = new City(['id'=>NULL]);
                }

                if(empty($state))
                {
                    $state = new State(['id'=>NULL]);
                }

                if(empty($city))
                {
                    $city = new City(['id'=>NULL]);
                }
            }
            else
            {
                $state = new State(['id'=>NULL]);

                $city = new City(['id'=>NULL]);
            }

            switch($item->f_address_type){
                case 'per':
                    $addressType = 0;
                    break;
                case 'corr':
                    $addressType = 1;
                    break;
                case 'ship':
                    $addressType = 2;
                    break;
                case 'ship2':
                    $addressType = 3;
                    break;
            }

            $results[$item->f_code]['addresses'][$addressType]['fields'][0]['value'] = $item->address1;
            $results[$item->f_code]['addresses'][$addressType]['fields'][1]['value'] = $item->address2;
            $results[$item->f_code]['addresses'][$addressType]['fields'][2]['value'] = $item->address3;
            $results[$item->f_code]['addresses'][$addressType]['fields'][3]['value'] = $item->address4;
            $results[$item->f_code]['addresses'][$addressType]['fields'][4]['value'] = $item->f_postcode;
            $results[$item->f_code]['addresses'][$addressType]['fields'][5]['value'] = $city->id;
            $results[$item->f_code]['addresses'][$addressType]['fields'][6]['value'] = $country->id;
            $results[$item->f_code]['addresses'][$addressType]['fields'][7]['value'] = $state->id;

        }

        foreach ($results as $key => $result)
        {
            $user = User::where('old_member_id', '=', $key)->first();

            if(empty($user)){
                continue;
            }

            MemberAddress::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'address_data' => json_encode($result['addresses'], JSON_UNESCAPED_SLASHES)
                ]
            );
        }
    }
}
