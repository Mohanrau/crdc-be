<?php

use Illuminate\Database\Seeder;
use App\Models\{
    Payments\PaymentModeProvider,
    Payments\PaymentModeSetting,
    Locations\Country,
    Locations\LocationTypes
};
use App\Interfaces\Masters\MasterInterface;

class PaymentModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param MasterInterface $masterInterface
     * @return void
     */
    public function run(MasterInterface $masterInterface)
    {
        $settingMasterData = $masterInterface->getMasterDataByKey(array('payment_mode'));

        $paymentModeList = array_change_key_case($settingMasterData['payment_mode']->pluck('id','title')->toArray());

        $paymentModeProviders = json_decode(file_get_contents('database/seeding/'."payment_mode_provider.txt"));

        foreach ($paymentModeProviders as $paymentModeProvider)
        {
            PaymentModeProvider::updateOrCreate(
                [
                    'master_data_id' => $paymentModeList[$paymentModeProvider->master_data_title],
                    'code' => $paymentModeProvider->code,
                    'name' => $paymentModeProvider->name,
                    'is_stockist_payment_verification' => $paymentModeProvider->is_stockist_payment_verification
                ]
            );
        }

        $countryList = Country::whereIn('code_iso_2',array('MY','SG','BN','TH','HK','TW','PH','KH','ID'))->pluck('id', 'code_iso_2');

        $locationTypesList = LocationTypes::pluck('id', 'code');

        $paymentModeSettings = json_decode(file_get_contents('database/seeding/'."payment_mode_setting.txt"));

        foreach($paymentModeSettings as $paymentModeSetting)
        {
            $payment_mode_provider = PaymentModeProvider::where("code", $paymentModeSetting->sale_payment_mode_provider_code)->first();

            PaymentModeSetting::updateOrCreate(
                [
                    "payment_mode_provider_id" => $payment_mode_provider->id,
                    "location_type_id" => $locationTypesList[$paymentModeSetting->location_type_code],
                    "country_id" => $countryList[$paymentModeSetting->country_code]
                ],
                [
                    "payment_mode_provider_id" => $payment_mode_provider->id,
                    "location_type_id" => $locationTypesList[$paymentModeSetting->location_type_code],
                    "country_id" => $countryList[$paymentModeSetting->country_code],
                    "configuration_file_name" => $paymentModeSetting->configuration_file_name,
                    "allow_partial" => $paymentModeSetting->allow_partial,
                    "active" => $paymentModeSetting->active,
                    "setting_detail" => (isset($paymentModeSetting->setting_detail)) ?
                        json_encode($paymentModeSetting->setting_detail) : NULL
                ]
            );
        }
    }
}
