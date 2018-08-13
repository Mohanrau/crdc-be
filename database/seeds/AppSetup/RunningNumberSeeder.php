<?php

use Illuminate\Database\Seeder;
use App\Models\Settings\RunningNumberSetting;
use App\Models\Settings\RunningNumberSpecialFormatSettings;
use App\Models\Locations\Country;

class RunningNumberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $runningNumberSettings = [
            [
                'code' => 'tax_invoice',
                'name' => 'Tax Invoice',
                'is_general_mode' => 0,
                'prefix' => '#country_code_iso_2##YY##MM#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 5,
                'active' => 1
            ],
            [
                'code' => 'pre_order',
                'name' => 'Pre-Order',
                'is_general_mode' => 1,
                'prefix' => '#country_code_iso_2#P#YY##MM#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 5,
                'active' => 1
            ],
            [
                'code' => 'exchange_bill',
                'name' => 'Exchange Bill',
                'is_general_mode' => 1,
                'prefix' => '#country_code_iso_2#EX#YY##MM#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 5,
                'active' => 1
            ],
            [
                'code' => 'ha_lingerie_activate',
                'name' => 'HA/Lingerie Activate',
                'is_general_mode' => 1,
                'prefix' => '#country_code_iso_2#A#YY##MM#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 5,
                'active' => 1
            ],
            [
                'code' => 'ha_lingerie_redemption',
                'name' => 'HA/Lingerie Redemption',
                'is_general_mode' => 1,
                'prefix' => '#country_code_iso_2#R#YY##MM#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 5,
                'active' => 1
            ],
            [
                'code' => 'ha_lingerie_e_voucher',
                'name' => 'HA/Lingerie eVoucher',
                'is_general_mode' => 1,
                'prefix' => '#country_code_iso_2#E#YY##MM#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 5,
                'active' => 1
            ],
            [
                'code' => 'self_pick_up',
                'name' => 'Self Pick Up',
                'is_general_mode' => 1,
                'prefix' => '#country_code_iso_2#S#YY##MM#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 5,
                'active' => 1
            ],
            [
                'code' => 'credit_note',
                'name' => 'Credit Note',
                'is_general_mode' => 1,
                'prefix' => '#country_code_iso_2#C#YY##MM#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 5,
                'active' => 1
            ],
            [
                'code' => 'consignment_deposit',
                'name' => 'Consignment Deposit',
                'is_general_mode' => 1,
                'prefix' => 'CD#YY##country_code_iso_2#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 7,
                'active' => 1
            ],
            [
                'code' => 'consignment_refund',
                'name' => 'Consignment Refund',
                'is_general_mode' => 1,
                'prefix' => 'CR#YY##country_code_iso_2#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 7,
                'active' => 1
            ],
            [
                'code' => 'consignment_order',
                'name' => 'Consignment Order',
                'is_general_mode' => 1,
                'prefix' => 'CN#YY##country_code_iso_2#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 7,
                'active' => 1
            ],
            [
                'code' => 'consignment_return',
                'name' => 'Consignment Return',
                'is_general_mode' => 1,
                'prefix' => 'CRN#YY##country_code_iso_2#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 7,
                'active' => 1
            ],
            [
                'code' => 'aeon_payment_id',
                'name' => 'Aeon Payment Transaction ID',
                'is_general_mode' => 1,
                'prefix' => '#country_code_iso_2#',
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 8,
                'active' => 1
            ],
            [
                'code' => 'ibo_member_id',
                'name' => 'IBO Member ID',
                'is_general_mode' => 1,
                'prefix' => NULL,
                'suffix' => NULL,
                'begin_number' => 84798,
                'running_width' => 9,
                'active' => 1
            ],
            [
                'code' => 'ewallet_transaction_number',
                'name' => 'EWallet Transaction Number',
                'is_general_mode' => 1,
                'prefix' => NULL,
                'suffix' => NULL,
                'begin_number' => 1,
                'running_width' => 11,
                'active' => 1
            ]
        ];

        foreach ($runningNumberSettings as $runningNumberSetting)
        {
            RunningNumberSetting::updateOrCreate(
                [
                    'code' => $runningNumberSetting['code']
                ],
                [
                    'name' => $runningNumberSetting['name'],
                    'is_general_mode' => $runningNumberSetting['is_general_mode'],
                    'prefix' => $runningNumberSetting['prefix'],
                    'suffix' => $runningNumberSetting['suffix'],
                    'begin_number' => $runningNumberSetting['begin_number'],
                    'running_width' => $runningNumberSetting['running_width'],
                    'active' => $runningNumberSetting['active']
                ]
            );
        }

        $countryList = Country::whereIn('code_iso_2',array('MY','SG','BN','TH','HK','TW','PH','KH','ID'))->pluck('id', 'code_iso_2');

        $runningNumberSettingList = RunningNumberSetting::pluck('id', 'code');

        $specialFormatSettings = json_decode(file_get_contents('database/seeding/'."running_number_special_format_setting.txt"));

        foreach($specialFormatSettings as $specialFormatSetting)
        {
            RunningNumberSpecialFormatSettings::updateOrCreate(
                [
                    "running_number_setting_id" => $runningNumberSettingList[$specialFormatSetting->running_number_setting_code],
                    "country_id" => $countryList[$specialFormatSetting->country_code],
                    "date_from" => $specialFormatSetting->date_from,
                    "date_to" => $specialFormatSetting->date_to
                ],
                [
                    'prefix' => $specialFormatSetting->prefix,
                    'suffix' => $specialFormatSetting->suffix,
                    'begin_number' => $specialFormatSetting->begin_number,
                    'end_number' => $specialFormatSetting->end_number,
                    'running_width' => $specialFormatSetting->running_width,
                    'active' => $specialFormatSetting->active
                ]
            );
        }
    }
}
