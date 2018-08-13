<?php
namespace App\Repositories\Settings;

use App\{
    Interfaces\Settings\SettingsInterface,
    Models\Settings\Setting,
    Models\Settings\SettingKey,
    Models\Settings\RunningNumberSetting,
    Models\Settings\RunningNumberSpecialFormatSettings,
    Models\Settings\RunningNumberTransaction,
    Models\Masters\MasterData,
    Models\Locations\Location,
    Models\Locations\Country,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SettingsRepository extends BaseRepository implements SettingsInterface
{
    protected
        $settingsObj,
        $masterDataObj,
        $locationObj,
        $countryObj,
        $runningNumberSettingObj,
        $runningNumberSpecialFormatSettingsObj,
        $runningNumberTransactionObj,
        $iboMemberIdCountryPrefixConfigCodes;

    /**
     * SettingsRepository constructor.
     *
     * @param SettingKey $model
     * @param Setting $setting
     * @param MasterData $masterData
     * @param Location $location
     * @param Country $country
     * @param RunningNumberSetting $runningNumberSetting
     * @param RunningNumberSpecialFormatSettings $runningNumberSpecialFormatSettings
     * @param RunningNumberTransaction $runningNumberTransaction
     */
    public function __construct(
        SettingKey $model,
        Setting $setting,
        MasterData $masterData,
        Location $location,
        Country $country,
        RunningNumberSetting $runningNumberSetting,
        RunningNumberSpecialFormatSettings $runningNumberSpecialFormatSettings,
        RunningNumberTransaction $runningNumberTransaction
    )
    {
        parent::__construct($model);

        $this->settingsObj = $setting;

        $this->masterDataObj = $masterData;

        $this->locationObj = $location;

        $this->countryObj = $country;

        $this->runningNumberSettingObj = $runningNumberSetting;

        $this->runningNumberSpecialFormatSettingsObj = $runningNumberSpecialFormatSettings;

        $this->runningNumberTransactionObj = $runningNumberTransaction;

        $this->with = ['settingData'];

        $this->iboMemberIdCountryPrefixConfigCodes = config('mappings.ibo_member_id_country_prefix');
    }

    /**
     * get specified setting with all settingData related
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find(int $id)
    {
        $data =  $this->modelObj->findOrFail($id);

        $data->settingData = $data->settingData()->get();

        return $data;
    }

    /**
     * get setting data by setting key
     *
     * @param array $key
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSettingDataByKey(array $key)
    {
        $settingData =  $this->modelObj
            ->with('settingData')
            ->whereIn('key', $key)
            ->get();

        return collect($settingData)->mapWithKeys(function ($item) {
           $data[$item->key] =  $item->settingData;

           return $data;
        });
    }

    /**
     * get rounding adjustment setting for a given countryId
     *
     * @param int $countryId
     * @return array
     */
    public function getRoundingAdjustment(int $countryId)
    {
        //Get rounding adjustment setting
        $roundingAdjustmentSettings = $this->getSettingDataByKey(array('rounding_adjustment'));

        $roundingData = collect(json_decode($roundingAdjustmentSettings['rounding_adjustment'][0]['value']))
            ->where('country_id', $countryId)
            ->map(function ($roundingData) {
                $roundingData->master_data = $this->masterDataObj
                    ->where("master_id", $roundingData->master_id)
                    ->where("id", $roundingData->master_data_id)
                    ->first();

                return $roundingData;
            });

        return array_values($roundingData->toArray());
    }

    /**
     * get sales cancellation cooling off period, buy back policy and buy back percentage for a given countryId
     *
     * @param int $countryId
     * @return array
     */
    public function getSalesCancellationPolicy(int $countryId)
    {
        //Get cooling off, buy back anc cancellation workflow setting
        $saleCancellaitonSettings = $this->getSettingDataByKey(
            array(
                'sales_cancellations_cooling_off_period_and_buy_back_policy',
                'sales_cancellations_buy_back_percentage',
                'sales_cancellations_workflow'
            ));

        $coolingOffAndBuyBackData = collect(json_decode(
            $saleCancellaitonSettings['sales_cancellations_cooling_off_period_and_buy_back_policy'][0]['value']))
                ->where('country_id', $countryId)->first();

        return collect([
            'cooling_off_day' => $coolingOffAndBuyBackData->cooling_off_day,
            'buy_back_day' => $coolingOffAndBuyBackData->buy_back_day,
            'buy_back_percentage' => $saleCancellaitonSettings['sales_cancellations_buy_back_percentage'][0]['value'],
            'cancellation_workflow' =>
                collect(json_decode($saleCancellaitonSettings['sales_cancellations_workflow'][0]['value']))
        ]);
    }

    /**
     * get running number for a given running code, countryId and locationId
     *
     * @param string $code
     * @param int $countryId
     * @param int $locationId
     * @return mixed|null|string
     */
    public function getRunningNumber(string $code, int $countryId, int $locationId = 0)
    {
        //get country code iso 2
        $countryDetails = $this->countryObj->find($countryId);

        $countryIso2 = (isset($countryDetails->code_iso_2)) ?
            $countryDetails->code_iso_2 : NULL;

        //get location type
        $locationDetails = $this->locationObj
            ->with('locationType')
            ->find($locationId);

        $locationTypeCode = (isset($locationDetails['locationType'])) ?
            $locationDetails['locationType']->code : 'branch';

        //location type mapping
        $locationTypeMappings = array(
            'online' => 1,
            'main_branch' => 2,
            'branch' => 2,
            'stockist' => 3
        );

        //prefix and suffix indicator mapping
        $prefixSuffixMappings = array(
            '#country_code_iso_2#' => $countryIso2,
            '#sale_channel#' => $locationTypeMappings[$locationTypeCode],
            '#YY#' => date("y"),
            '#YYYY#' => date("Y"),
            '#M#' => date("n"),
            '#MM#' => date("m"),
        );

        //Retrieve Running Setup
        $runningSetting = $this->runningNumberSettingObj
            ->where('code', $code)
            ->active()
            ->first();

        $runningNumber = NULL;

        if($runningSetting){

            $runningSettingId = $runningSetting->id;

            $runningSettingBeginNumber = $runningSetting->begin_number;

            $runningSettingWidth = $runningSetting->running_width;

            $prefixText = $runningSetting->prefix;

            $suffixText = $runningSetting->suffix;

            if(!$runningSetting->is_general_mode) {

                $custumSetting = $this->runningNumberSpecialFormatSettingsObj
                    ->where('running_number_setting_id', $runningSettingId)
                    ->where('country_id', $countryId)
                    ->where('date_from', '<=', Carbon::now()->format('Y-m-d'))
                    ->where('date_to',  '>=', Carbon::now()->format('Y-m-d'))
                    ->active()
                    ->first();

                if($custumSetting){

                    $runningSettingBeginNumber = $custumSetting->begin_number;

                    $runningSettingWidth = $custumSetting->running_width;

                    $prefixText = $custumSetting->prefix;

                    $suffixText = $custumSetting->suffix;
                }
            }

            //Replace Prefix and Suffix
            foreach ($prefixSuffixMappings as $prefixSuffixMappingKey => $prefixSuffixMappingValue){
                if(!empty($prefixText))
                    $prefixText = str_replace($prefixSuffixMappingKey, $prefixSuffixMappingValue, $prefixText);

                if(!empty($suffixText))
                    $suffixText = str_replace($prefixSuffixMappingKey, $prefixSuffixMappingValue, $suffixText);
            }

            //Get Running Transaction
            $runningTransaction = $this->runningNumberTransactionObj
                ->where('running_number_setting_id', '=', $runningSettingId)
                ->where('prefix', '=', $prefixText)
                ->where('suffix', '=', $suffixText)
                ->first();

            $runningWidth = $runningFormat ='';

            if(!empty($runningSettingWidth)){
                for($j = 0; $j < $runningSettingWidth; $j++){
                    $runningWidth .= '0';
                }
            }

            if($runningTransaction) {
                if(!empty($runningTransaction->running_no))
                    $nextNumber = $runningTransaction->running_no + 1;
                else
                    $nextNumber = 1;
            } else {
                $nextNumber = $runningSettingBeginNumber;
            }

            $num = 0;
            for ($i = strlen($runningWidth); $i > 0; $i--) {
                if($nextNumber < pow(10, ($i - 1))) {
                    $runningFormat .= substr($runningWidth,$num,1);
                    $num++;
                } else {
                    $runningFormat .= $nextNumber;
                    $i = 0;
                }
            }

            //Insert OR Update Running Transaction
            $this->runningNumberTransactionObj->updateOrCreate(
                [
                    'running_number_setting_id' => $runningSettingId,
                    'prefix' => $prefixText,
                    'suffix' => $suffixText
                ],
                [
                    'running_no' => $nextNumber
                ]
            );

            $runningNumber = $prefixText . $runningFormat . $suffixText;

            //Special prefix for old member id running number
            if($code == "ibo_member_id"){
                $iboMemberIdCountryPrefix = $this->iboMemberIdCountryPrefixConfigCodes[$countryIso2];

                $runningNumber = $iboMemberIdCountryPrefix . $runningNumber;
            }
        }

        return $runningNumber;
    }
}