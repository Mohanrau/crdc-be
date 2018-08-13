<?php

use Illuminate\Database\Seeder;
use App\Models\{
    Settings\SettingKey,
    Settings\Setting,
    Locations\Country
};
use App\Interfaces\Masters\MasterInterface;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param MasterInterface $masterInterface
     * @return void
     */
    public function run(MasterInterface $masterInterface)
    {
        //Retrieve Related Reference ID
        $countryList = Country::whereIn('code_iso_2',array('MY','SG','BN','TH','HK','TW','PH','KH','ID'))->pluck('id', 'code_iso_2');

        $settingMasterData = $masterInterface->getMasterDataByKey(array('rounding_adjustment'));

        //Start Prepare Rounding Adjustment Json Value
        $roundingMasterId = $settingMasterData['rounding_adjustment'][0]['master_id'];

        $roundingList = array_change_key_case($settingMasterData['rounding_adjustment']->pluck('id','title')->toArray());

        $roundingAdjustmentDatas = collect(json_decode(file_get_contents('database/seeding/'."rounding_adjustment.txt")));

        $roundingAdjustmentSettings = $roundingAdjustmentDatas
            ->map(function ($roundingAdjustmentData) use ($countryList, $roundingMasterId, $roundingList) {
                return [
                    'country_id' => $countryList[$roundingAdjustmentData->country_code],
                    'master_id' => $roundingMasterId,
                    'master_data_id' => $roundingList[$roundingAdjustmentData->master_data_code],
                    'status' => $roundingAdjustmentData->status
                ];
            });
        //End Prepare Rounding Adjustment Json Value

        //Start Prepare Cooling Off Period and Buy Back Policy Json Value
        $coolingOffPeriodAndBuyBackPolicyDatas = collect(json_decode(file_get_contents(
            'database/seeding/'."cooling_off_periods_and_buy_back_policies.txt")));

        $coolingOffPeriodAndBuyBackPolicySettings = $coolingOffPeriodAndBuyBackPolicyDatas
            ->map(function ($policyData) use($countryList){
                return [
                    'country_id' => $countryList[$policyData->country_code],
                    'cooling_off_day' => $policyData->cooling_off_day,
                    'buy_back_day' => $policyData->buy_back_day,
                    'status' => $policyData->status
                ];
            });
        //End Prepare Cooling Off Period and Buy Back Policy Json Value

        $settingDatas = [
            [
                'name' => 'Rounding Adjustment',
                'key' => 'rounding_adjustment',
                'setting_data' => [
                    [
                        'value' => json_encode($roundingAdjustmentSettings),
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Sales Cancellations Cooling Off Period and Buy Back Policy',
                'key' => 'sales_cancellations_cooling_off_period_and_buy_back_policy',
                'setting_data' => [
                    [
                        'value' => json_encode($coolingOffPeriodAndBuyBackPolicySettings),
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Sales Cancellations Buy Back Percentage',
                'key' => 'sales_cancellations_buy_back_percentage',
                'setting_data' => [
                    [
                        'value' => 90,
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Base Currency',
                'key' => 'base_currency',
                'setting_data' => [
                    [
                        'value' => 'USD',
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Sales Cancellations Workflow',
                'key' => 'sales_cancellations_workflow',
                'setting_data' => [
                    [
                        'value' => file_get_contents('database/seeding/'."sales_cancellation_workflow.txt"),
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Stockist Consignment Transaction Workflow',
                'key' => 'stockist_consignment_transaction_workflow',
                'setting_data' => [
                    [
                        'value' => file_get_contents('database/seeding/'."stockist_consignment_transaction_workflow.txt"),
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Current Cw Id',
                'key' => 'current_cw_id',
                'setting_data' => [
                    [
                        'value' => '',
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'GIRO Type',
                'key' => 'giro_type',
                'setting_data' => [
                    [
                        'value' => file_get_contents('database/seeding/'."ewallet_giro_types.txt"),
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Amp CV to Upgrade Each Rank of BA Enrollment',
                'key' => 'amp_cv_to_upgrade_each_rank_of_ba_enrollment',
                'setting_data' => [
                    [
                        'value' => '120',
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Sales AMP Cv per Allcation',
                'key' => 'sales_amp_cv_per_allcation',
                'setting_data' => [
                    [
                        'value' => 60,
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Minimum AMP Cv per Sales',
                'key' => 'minimum_amp_cv_per_sales',
                'setting_data' => [
                    [
                        'value' => 120,
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Minimum BA Upgrade Cv per Sales',
                'key' => 'minimum_ba_upgrade_cv_per_sales',
                'setting_data' => [
                    [
                        'value' => 60,
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Payment Transaction Verify Buffer Time',
                'key' => 'payment_transaction_verify_buffer_time',
                'setting_data' => [
                    [
                        'value' => 15,
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Rental Sale Order Workflow',
                'key' => 'rental_sale_order_workflow',
                'setting_data' => [
                    [
                        'value' => '{"rental_sale_order":"rental_sale_order"}',
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ],
            [
                'name' => 'Yonyou UAP Token',
                'key' => 'yonyou_uap_token',
                'setting_data' => [
                    [
                        'value' => '00000164583b5fbc487d0c4b149578a209ae5ec13be86ff83eafca966393c2cb1cc6b757327acbd0ed8f1e0e572f5367f560fbe83e68dd335089af0eee3f403089d86692a3280771fa676b3862bf2eef4a86576dd58bc49efb63deebe346e93949316fdc00000164583b5fbc',
                        'mapping_id' => NULL,
                        'mapping_model' => NULL,
                        'active' => 1,
                    ]
                ]
            ]
        ];

        foreach ($settingDatas as $data)
        {
            $settingData = $data['setting_data'];

            unset($data['setting_data']);

            $settingKey = SettingKey::updateOrCreate($data);

            collect($settingData)->each(function($data) use ($settingKey){
                Setting::updateOrCreate(
                    [
                        'setting_key_id' => $settingKey->id,
                        'mapping_id' => $data['mapping_id'],
                        'mapping_model' => $data['mapping_model']
                    ],
                    [
                        'value' => $data['value'],
                        'active' => $data['active']
                    ]
                );
            });
        }

        Artisan::call("general:update-current-cw");
    }
}
