<?php
namespace App\Rules\Payments;

use App\{
    Interfaces\Masters\MasterInterface,
    Models\Payments\PaymentModeProvider,
    Models\Payments\PaymentModeSetting
};
use Illuminate\{
    Contracts\Validation\Rule
};

class EppEligibilityAmountValidation implements Rule
{
    private 
        $masterRepositoryObj,
        $paymentModeProviderObj,
        $paymentModeSettingObj,
        $fields,
        $paymentModeConfigCodes,
        $minIssuingAmount,
        $errorType;

    /**
     * EppEligibilityAmountValidation constructor.
     *
     * @param MasterInterface $masterInterface
     * @param PaymentModeProvider $paymentModeProvider
     * @param PaymentModeSetting $paymentModeSetting
     * @param $fields
     */
    public function __construct(
        MasterInterface $masterInterface,
        PaymentModeProvider $paymentModeProvider,
        PaymentModeSetting $paymentModeSetting,
        $fields
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->paymentModeProviderObj = $paymentModeProvider;

        $this->paymentModeSettingObj = $paymentModeSetting;

        $this->fields = $fields;

        $this->paymentModeConfigCodes = config('mappings.payment_mode');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = true;

        //Get Mater Data Detail
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(array('payment_mode'));

        //Get Epp Motor and Epp Terminal Payment Mode ID
        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $eppTerminalPaymentId = $paymentMode[$this->paymentModeConfigCodes['epp (terminal)']];

        $eppMotorPaymentId = $paymentMode[$this->paymentModeConfigCodes['epp (moto)']];

        $eppProviderIds = $this->paymentModeProviderObj
            ->whereIn('master_data_id', [$eppTerminalPaymentId, $eppMotorPaymentId])
            ->pluck('id');

        $paymentSetting = $this->paymentModeSettingObj
            ->where('id', $value)
            ->whereIn('payment_mode_provider_id', $eppProviderIds)
            ->first();

        if($paymentSetting){

            $fields = collect($this->fields);

            $amount = $fields->where('name','amount')->first()['value'];

            $issuingBank = $fields->where('name','issuing_bank')->first()['value'];

            $tenure = $fields->where('name','tenure')->first()['value'];

            if(!empty($issuingBank) && !empty($tenure)){

                $paymentSettingDetail = json_decode($paymentSetting->setting_detail, true);

                $eligibilityDetail = $paymentSettingDetail[0]['epp_eligibility'];

                if(isset($eligibilityDetail[$issuingBank][$tenure])){

                    $issuingAmount = $eligibilityDetail[$issuingBank][$tenure];

                    $this->errorType = 'insufficientLoanAmount';

                    $this->minIssuingAmount = $issuingAmount;

                    $result = ($amount >= $issuingAmount) ? true : false;

                } else {

                    $this->errorType = 'invalidTenure';

                    $result = false;
                }
            }
        }

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        switch ($this->errorType) {
            case "invalidTenure":
                $msg = trans('message.make-payment.epp-eligibility-invalid-tenure');
                break;

            case "insufficientLoanAmount":
                $msg = trans('message.make-payment.epp-eligibility-insufficient-loan-amount', [
                    'amount' => $this->minIssuingAmount
                ]);
                break;

            default :
                $msg = trans('message.make-payment.epp-eligibility-invalid-tenure');
                break;
        };

        return $msg;
    }
}
