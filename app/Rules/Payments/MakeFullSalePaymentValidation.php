<?php
namespace App\Rules\Payments;

use App\Models\Payments\Payment;
use App\Models\Sales\Sale;
use Illuminate\Contracts\Validation\Rule;

class MakeFullSalePaymentValidation implements Rule
{
    private
        $paymentObj,
        $saleObj,
        $fields,
        $payType;

    /**
     * MakeFullSalePaymentValidation constructor.
     *
     * @param Sale $sale
     * @param Payment $payment
     * @param array $fields
     * @param string $payType
     */
    public function __construct(
        Sale $sale,
        Payment $payment,
        array $fields,
        string $payType
    )
    {
        $this->saleObj = $sale;

        $this->paymentObj = $payment;

        $this->fields = $fields;

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

        if(!empty($value)){

            if($this->payType == 'sales'){

                $saleDetail = $this->saleObj->find($value);

                $totalGmpValue = $saleDetail->total_gmp;

                $paymentAmount = $this->paymentObj
                    ->where('mapping_id',$value)
                    ->where('mapping_model', 'sales')
                    ->whereIn('status', [1,2])
                    ->sum('amount');

                $fields = collect($this->fields);

                $amount = $fields->where('name','amount')->first()['value'];

                if($saleDetail->skip_downline && ($saleDetail->sponsor_id == $saleDetail->user_id)){

                    $totalPaidAmount = floatval($amount) + floatval($paymentAmount);

                    $result = (floatval($totalGmpValue) > floatval($totalPaidAmount)) ?
                        true : false;
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
        return trans('message.make-payment.skip-downline-make-full-payment');
    }
}
