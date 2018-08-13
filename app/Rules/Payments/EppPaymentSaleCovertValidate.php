<?php
namespace App\Rules\Payments;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Payments\Payment;
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Config
};

class EppPaymentSaleCovertValidate implements Rule
{
    private $paymentObj, $masterRepositoryObj, $errorType,
        $paymentModeConfigCodes, $saleOrderStatusConfigCodes, $approvalStatusConfigCodes;

    /**
     * EppPaymentSaleCovertValidate constructor.
     *
     * @param MasterInterface $masterInterface
     * @param Payment $payment
     * @param int $userId
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

        $this->approvalStatusConfigCodes = Config::get('mappings.epp_payment_approval_status');
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
        //Get Epp Moto Payment Mode ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode', 'sale_order_status', 'epp_payment_approval_status'));

        //Get pre order status ID
        $saleOrderStatus = array_change_key_case($settingsData['sale_order_status']->pluck('id','title')->toArray());

        $preOrderId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['pre-order']];

        //Get EPP Moto Payment Mode ID
        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $eppMotoPaymentId = $paymentMode[$this->paymentModeConfigCodes['epp (moto)']];

        //Get approved status ID
        $approvalStatus = array_change_key_case($settingsData['epp_payment_approval_status']
            ->pluck('id','title')->toArray());

        $approvedId = $approvalStatus[$this->approvalStatusConfigCodes['approved']];

        //Verify Payment is Epp Moto Payment
        $eppMotoPaymentData = $this->paymentObj
            ->where('payments.id', $value)
            ->where('payments.status', 1)
            ->join('payments_modes_providers', function ($join)
                use ($eppMotoPaymentId){
                    $join->on('payments.payment_mode_provider_id', '=', 'payments_modes_providers.id')
                        ->where(function ($paymentProvidersQuery) use ($eppMotoPaymentId) {
                            $paymentProvidersQuery->where(
                                'payments_modes_providers.master_data_id', $eppMotoPaymentId);
                        });
                })
            ->first();

        if(!empty($eppMotoPaymentData)){

            $eppMotoPaymentDetail = json_decode($eppMotoPaymentData->payment_detail, true);

            //Verify Payment approval status must is approved
            if($eppMotoPaymentDetail['payment_response']['approval_status'] != $approvedId){

                $this->errorType = 'invalidEppMotoPaymentStatus';

                return false;
            }

            //Verify converted by and converted date must empty
            if(!empty($eppMotoPaymentDetail['converted_by']) && !empty($eppMotoPaymentDetail['converted_date'])){

                $this->errorType = 'eppMotoPaymentConverted';

                return false;
            }

            if($eppMotoPaymentData->sale->order_status_id != $preOrderId){
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
        } else if ($this->errorType == 'eppMotoPaymentConverted'){
            return trans('message.epp-moto-payment-update-approve-code.epp-moto-payment-converted');
        } else if ($this->errorType == 'invalidEppMotoPaymentStatus'){
            return trans('message.epp-moto-payment-update-approve-code.epp-moto-payment-not-approved');
        } else if ($this->errorType == 'invalidSaleOrderStatus'){
            return trans('message.epp-moto-payment-update-approve-code.invalid-sale-order-status');
        }
    }
}
