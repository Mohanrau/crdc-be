<?php
namespace App\Rules\Payments;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Payments\Payment;
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Config
};

class EppMotoUpdateApproveCodeValidation implements Rule
{
    private $paymentObj, $masterRepositoryObj, $errorType,
        $paymentModeConfigCodes, $saleOrderStatusConfigCodes, $approvalStatusConfigCodes;

    /**
     * EppMotoUpdateApproveCodeValidation constructor.
     *
     * @param MasterInterface $masterInterface
     * @param Payment $payment
     */
    public function __construct(
        MasterInterface $masterInterface,
        Payment $payment
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->paymentObj = $payment;

        $this->paymentModeConfigCodes = Config::get('mappings.payment_mode');

        $this->saleOrderStatusConfigCodes = Config::get('mappings.sale_order_status');

        $this->approvalStatusConfigCodes = config('mappings.epp_payment_approval_status');
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
        //Get Mater Data Detail
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode', 'sale_order_status', 'epp_payment_approval_status'));

        //Get pre order status ID
        $saleOrderStatus = array_change_key_case($settingsData['sale_order_status']->pluck('id','title')->toArray());

        $preOrderId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['pre-order']];

        //Get EPP Moto Payment Mode ID
        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $eppMotoPaymentId = $paymentMode[$this->paymentModeConfigCodes['epp (moto)']];

        //Get pending status ID
        $approvalStatus = array_change_key_case($settingsData['epp_payment_approval_status']
            ->pluck('id','title')->toArray());

        $pendingId = $approvalStatus[$this->approvalStatusConfigCodes['pending']];

        //Verify Payment is EPP Moto Payment
        $eppPaymentData = $this->paymentObj
            ->where('payments.id', $value)
            ->where('payments.status', 2)
            ->join('payments_modes_providers', function ($join)
                use ($eppMotoPaymentId){
                    $join->on('payments.payment_mode_provider_id', '=', 'payments_modes_providers.id')
                        ->where(function ($paymentProvidersQuery) use ($eppMotoPaymentId) {
                            $paymentProvidersQuery->where(
                                'payments_modes_providers.master_data_id', $eppMotoPaymentId);
                        });
                })
            ->first();

        if(!empty($eppPaymentData)){

            $eppPaymentDetail = json_decode($eppPaymentData->payment_detail, true);

            //Verify Payment approval status must is approved
            if($eppPaymentDetail['payment_response']['approval_status'] != $pendingId){
                $this->errorType = 'invalidEppMotoPaymentStatus';

                return false;
            }

            if($eppPaymentData->sale->order_status_id != $preOrderId){
                $this->errorType = 'invalidSaleOrderStatus';

                return false;
            }

            return true;

        } else {
            $this->errorType = 'invalidEppMotoPaymentId';

            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if($this->errorType == 'invalidEppMotoPaymentId'){
            return trans('message.epp-moto-payment-update-approve-code.invalid-epp-moto-payment-id');
        } else if ($this->errorType == 'invalidEppMotoPaymentStatus'){
            return trans('message.epp-moto-payment-update-approve-code.invalid-epp-moto-payment-status');
        } else if ($this->errorType == 'invalidSaleOrderStatus'){
            return trans('message.epp-moto-payment-update-approve-code.invalid-sale-order-status');
        }
    }
}
