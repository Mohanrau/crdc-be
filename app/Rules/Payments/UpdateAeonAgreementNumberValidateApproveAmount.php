<?php
namespace App\Rules\Payments;

use App\Models\Payments\Payment;
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Config
};

class UpdateAeonAgreementNumberValidateApproveAmount implements Rule
{
    private $paymentObj, $paymentId;

    /**
     * UpdateAeonAgreementNumberValidateApproveAmount constructor.
     *
     * @param Payment $payment
     * @param $paymentId
     */
    public function __construct(
        Payment $payment,
        $paymentId
    )
    {
        $this->paymentObj = $payment;

        $this->paymentId = $paymentId;
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

        if(!empty($this->paymentId)){

            $aeonPaymentData = $this->paymentObj->find($this->paymentId);

            if($aeonPaymentData){
                $result = ($aeonPaymentData->amount >= $value) ? true : false;
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
        return trans('message.aeon-payment-update-agreement-number.invalid-approved-amount');
    }
}
