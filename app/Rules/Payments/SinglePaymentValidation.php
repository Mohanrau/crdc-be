<?php
namespace App\Rules\Payments;

use App\Models\Payments\Payment;
use Illuminate\Contracts\Validation\Rule;

class SinglePaymentValidation implements Rule
{
    private
        $paymentObj,
        $payType;

    /**
     * SinglePaymentValidation constructor.
     *
     * @param Payment $payment
     * @param string $payType
     */
    public function __construct(
        Payment $payment,
        string $payType
    )
    {
        $this->paymentObj = $payment;

        $this->payType = $payType;
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

        switch($this->payType){
            case "consignment_deposit":
                $mappingModel = "consignments_deposits_refunds";
                break;

            case "sales":
                $mappingModel = "sales";
                break;

            case "user_ewallets":
                $mappingModel = "user_ewallets";
                break;

            default:
                $mappingModel = "";
                break;
        }

        if(!empty($mappingModel)){

            $paymentTransactionCount = $this->paymentObj
                ->where('mapping_id',$value)
                ->where('mapping_model', $mappingModel)
                ->whereIn('status', [1,2])
                ->count();

            if ($value) {
                $result = ($paymentTransactionCount > 0) ? false : true;
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
        return trans('message.make-payment.single-payment-trasnaction');
    }
}
