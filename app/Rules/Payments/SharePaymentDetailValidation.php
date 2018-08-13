<?php
namespace App\Rules\Payments;

use App\{
    Interfaces\Masters\MasterInterface,
    Models\Payments\PaymentModeProvider,
    Models\Payments\Payment
};
use Illuminate\Contracts\Validation\Rule;

class SharePaymentDetailValidation implements Rule
{
    private
        $masterRepositoryObj,
        $paymentModeProviderObj,
        $paymentObj,
        $paymentModeConfigCodes;

    /**
     * SharePaymentDetailValidation constructor.
     *
     * @param MasterInterface $masterInterface
     * @param PaymentModeProvider $paymentModeProvider
     * @param Payment $payment
     */
    public function __construct(
        MasterInterface $masterInterface,
        PaymentModeProvider $paymentModeProvider,
        Payment $payment
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->paymentModeProviderObj = $paymentModeProvider;

        $this->paymentObj = $payment;

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

        //Get Online Gateway Payment Mode ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(array('payment_mode'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $onlineGatewayPaymentId = $paymentMode[$this->paymentModeConfigCodes['online payment gateway']];

        //Share Payment Only Allow Online Payment Gateway
        $paymentProviderIds = $this->paymentModeProviderObj
            ->where('master_data_id', $onlineGatewayPaymentId)
            ->pluck('id')
            ->toArray();

        $paymentDetail = $this->paymentObj->find($value);

        if($paymentDetail){
            $result = ($paymentDetail->status == 2 &&
                $paymentDetail->mapping_model == "sales" &&
                    $paymentDetail->is_share == true &&
                        in_array($paymentDetail->payment_mode_provider_id, $paymentProviderIds)
                            ) ? true : false;
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
        return trans('message.make-payment.invalid-payment-record');
    }
}
