<?php
namespace App\Rules\Payments;

use App\Models\Payments\Payment;
use App\Models\Stockists\ConsignmentDepositRefund;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleExchange;
use Illuminate\Contracts\Validation\Rule;

class MakePaymentValidation implements Rule
{
    private
        $paymentObj,
        $consignmentDepositRefundObj,
        $saleObj,
        $saleExchangeObj,
        $fields,
        $payType;

    /**
     * MakePaymentValidation constructor.
     *
     * @param ConsignmentDepositRefund $consignmentDepositRefund
     * @param Sale $sale
     * @param SaleExchange $saleExchange
     * @param Payment $payment
     * @param array $fields
     * @param string $payType
     */
    public function __construct(
        ConsignmentDepositRefund $consignmentDepositRefund,
        Sale $sale,
        SaleExchange $saleExchange,
        Payment $payment,
        array $fields,
        string $payType
    )
    {
        $this->consignmentDepositRefundObj = $consignmentDepositRefund;

        $this->saleObj = $sale;

        $this->saleExchangeObj = $saleExchange;

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
        if(!empty($value)){

            switch($this->payType){
                case "consignment_deposit":
                    $mappingModel = "consignments_deposits_refunds";
                    break;

                case "sales":
                    $mappingModel = "sales";
                    break;

                default:
                    $mappingModel = "";
                    break;
            }

            if(empty($mappingModel)){
                return true;
            }

            $paymentAmount = $this->paymentObj
                ->where('mapping_id',$value)
                ->where('mapping_model', $mappingModel)
                ->whereIn('status', [1,2])
                ->sum('amount');

            $fields = collect($this->fields);

            $payAmount = $fields->where('name','amount')->first()['value'];

            $totalAmount = 0;

            if($this->payType == 'sales'){

                $saleDetail = $this->saleObj->find($value);

                if($saleDetail->is_product_exchange){
                    $exchangeDetail = $this->saleExchangeObj
                        ->where('sale_id', $saleDetail->id)
                        ->first();

                    $totalAmount = $exchangeDetail->balance;

                } else {
                    $totalAmount = $saleDetail->total_gmp;
                }

            } elseif ($this->payType == 'consignment_deposit') {

                $consignmentDepositDetail = $this->consignmentDepositRefundObj->find($value);

                $totalAmount = $consignmentDepositDetail->amount;
            }

            $totalPaidAmount = floatval($payAmount) + floatval($paymentAmount);

            return (floatval($totalPaidAmount) > floatval($totalAmount)) ?
                false : true;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.make-payment.amount-excess');
    }
}
