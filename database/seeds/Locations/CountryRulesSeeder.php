<?php

use App\Models\Locations\Country;
use App\Models\Masters\Master;
use App\Models\Masters\MasterData;
use Illuminate\Database\Seeder;

class CountryRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $masterDataObj = new MasterData();

        $data = [
            'MY' => [
                [
                    'master_key' => 'ethnic_group',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('Chinese', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Malay', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Indian', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Others', 'ethnic_group'),
                    ]
                ],
                [
                    'master_key' => 'age_for_enrollment',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('18', 'age_for_enrollment')
                    ]
                ],
                [
                    'master_key' => 'translated_name',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'translated_name')
                    ]
                ],
                [
                    'master_key' => 'tin_number',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'tin_number')
                    ]
                ]
            ],
            'TH' => [
                [
                    'master_key' => 'ethnic_group',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('Thai', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Chinese', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Malay', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Indian', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Others', 'ethnic_group'),
                    ]
                ],
                [
                    'master_key' => 'age_for_enrollment',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('20', 'age_for_enrollment')
                    ]
                ],
                [
                    'master_key' => 'translated_name',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("true", 'translated_name')
                    ]
                ],
                [
                    'master_key' => 'tin_number',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'tin_number')
                    ]
                ]
            ],
            'BN' => [
                [
                    'master_key' => 'ethnic_group',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('Chinese', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Malay', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Indian', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Others', 'ethnic_group'),
                    ]
                ],
                [
                    'master_key' => 'age_for_enrollment',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('18', 'age_for_enrollment')
                    ]
                ],
                [
                    'master_key' => 'translated_name',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'translated_name')
                    ]
                ],
                [
                    'master_key' => 'tin_number',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'tin_number')
                    ]
                ]
            ],
            'SG' => [
                [
                    'master_key' => 'ethnic_group',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('Chinese', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Malay', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Indian', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Others', 'ethnic_group'),
                    ]
                ],
                [
                    'master_key' => 'age_for_enrollment',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('18', 'age_for_enrollment')
                    ]
                ],
                [
                    'master_key' => 'translated_name',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'translated_name')
                    ]
                ],
                [
                    'master_key' => 'tin_number',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'tin_number')
                    ]
                ]
            ],
            'TW' => [
                [
                    'master_key' => 'ethnic_group',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('Chinese', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Aboriginal', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Malay', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Indian', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Others', 'ethnic_group'),
                    ]
                ],
                [
                    'master_key' => 'age_for_enrollment',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('20', 'age_for_enrollment')
                    ]
                ],
                [
                    'master_key' => 'translated_name',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("true", 'translated_name')
                    ]
                ],
                [
                    'master_key' => 'tin_number',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("true", 'tin_number')
                    ]
                ]
            ],
            'HK' => [
                [
                    'master_key' => 'ethnic_group',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('Chinese', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Malay', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Indian', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Others', 'ethnic_group'),
                    ]
                ],
                [
                    'master_key' => 'age_for_enrollment',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('18', 'age_for_enrollment')
                    ]
                ],
                [
                    'master_key' => 'translated_name',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("true", 'translated_name')
                    ]
                ],
                [
                    'master_key' => 'tin_number',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'tin_number')
                    ]
                ]
            ],
            'KH' => [
                [
                    'master_key' => 'ethnic_group',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('Khmer', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Cham', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Vietnamese', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Chinese', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Malay', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Others', 'ethnic_group'),
                    ]
                ],
                [
                    'master_key' => 'age_for_enrollment',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('18', 'age_for_enrollment')
                    ]
                ],
                [
                    'master_key' => 'translated_name',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("true", 'translated_name')
                    ]
                ],
                [
                    'master_key' => 'tin_number',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'tin_number')
                    ]
                ]
            ],
            'PH' => [
                [
                    'master_key' => 'ethnic_group',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('Filipino', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Others', 'ethnic_group'),
                    ]
                ],
                [
                    'master_key' => 'age_for_enrollment',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('18', 'age_for_enrollment')
                    ]
                ],
                [
                    'master_key' => 'translated_name',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'translated_name')
                    ]
                ],
                [
                    'master_key' => 'tin_number',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("true", 'tin_number')
                    ]
                ]
            ],
            'ID' => [
                [
                    'master_key' => 'ethnic_group',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('Chinese', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Aboriginal', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Malay', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Indian', 'ethnic_group'),
                        $masterDataObj->getIdByTitle('Others', 'ethnic_group'),
                    ]
                ],
                [
                    'master_key' => 'age_for_enrollment',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle('18', 'age_for_enrollment')
                    ]
                ],
                [
                    'master_key' => 'translated_name',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'translated_name')
                    ]
                ],
                [
                    'master_key' => 'tin_number',
                    'master_data_id' => [
                        $masterDataObj->getIdByTitle("false", 'tin_number')
                    ]
                ]
            ]
        ];

        foreach ($data as $key => $item)
        {
            $country = Country::where('code_iso_2', '=', $key)->first();

            collect($item)->each(function($record) use ($country){

                $masterId = Master::where('key', $record['master_key'])->first()->id;

                $country->countryRules()->detach([$masterId]);

                collect($record['master_data_id'])->each(function($masterDataId) use ($country, $masterId){
                    $country->countryRules()->attach(
                        $masterId,
                        [
                            'master_data_id' => $masterDataId
                        ]);
                });

            });
        }
    }
}
