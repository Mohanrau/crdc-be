<?php
namespace App\Rules\Payments;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Payments\Payment;
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Config
};

class AeonPaymentReleaseCheck implements Rule
{
    private $paymentObj, $masterRepositoryObj, $errorType,
        $paymentModeConfigCodes, $saleOrderStatusConfigCodes, $approvalStatusConfigCodes;

    /**
     * AeonPaymentReleaseCheck constructor.
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

        $this->approvalStatusConfigCodes = Config::get('mappings.aeon_payment_approval_status');
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
        //Get Aeon Payment Mode ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode', 'sale_order_status', 'aeon_payment_approval_status'));

        //Get pre order status ID
        $saleOrderStatus = array_change_key_case($settingsData['sale_order_status']->pluck('id','title')->toArray());

        $completedOrderId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];

        //Get Aeon Payment Mode ID
        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $aeonPaymentCode = $this->paymentModeConfigCodes['aeon'];

        $aeonPaymentId = $paymentMode[$aeonPaymentCode];

        //Get approved status ID
        $approvalStatus = array_change_key_case($settingsData['aeon_payment_approval_status']
            ->pluck('id','title')->toArray());

        $approvedCode = $this->approvalStatusConfigCodes['approved'];

        $approvedId = $approvalStatus[$approvedCode];

        //Verify Payment is Aeon Payment
        $aeonPaymentData = $this->paymentObj
            ->where('payments.id', $value)
            ->join('payments_modes_providers', function ($join)
                use ($aeonPaymentId){
                    $join->on('payments.payment_mode_provider_id', '=', 'payments_modes_providers.id')
                        ->where(function ($paymentProvidersQuery) use ($aeonPaymentId) {
                            $paymentProvidersQuery->where(
                                'payments_modes_providers.master_data_id', $aeonPaymentId);
                        });
                })
            ->first();

        if(!empty($aeonPaymentData)){

            $aeonPaymentDetail = json_decode($aeonPaymentData->payment_detail, true);

            //Verify Payment approval status must is approved
            if($aeonPaymentDetail['payment_response']['approval_status'] != $approvedId){

                $this->errorType = 'invalidAeonPaymentStatus';

                return false;
            }

            //Verify converted by and converted date must empty
            if(!empty($aeonPaymentDetail['converted_by']) && !empty($aeonPaymentDetail['converted_date'])){

                $this->errorType = 'aeonPaymentReleased';

                return false;
            }

            if($aeonPaymentData->sale->order_status_id != $completedOrderId){
                $this->errorType = 'invalidSaleOrderStatus';

                return false;
            }

            return true;

        } else {
            $this->errorType = 'invalidAeonPaymentId';

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
        if($this->errorType == 'invalidAeonPaymentId'){
            return trans('message.aeon-payment-cooling-off-release.invalid-aeon-payment-id');
        } else if ($this->errorType == 'aeonPaymentReleased'){
            return trans('message.aeon-payment-cooling-off-release.aeon-payment-released');
        } else if ($this->errorType == 'invalidAeonPaymentStatus'){
            return trans('message.aeon-payment-cooling-off-release.invalid-aeon-payment-status');
        } else if ($this->errorType == 'invalidSaleOrderStatus'){
            return trans('message.aeon-payment-cooling-off-release.invalid-sale-order-status');
        }
    }
}
