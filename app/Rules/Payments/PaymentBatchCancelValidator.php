<?php
namespace App\Rules\Payments;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Payments\Payment;
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Config
};

class PaymentBatchCancelValidator implements Rule
{
    private
        $masterRepositoryObj,
        $paymentObj,
        $paymentMode,
        $paymentModeConfigCodes,
        $saleOrderStatusConfigCodes,
        $aeonApprovalStatusConfigCodes,
        $aeonDocumentStatusConfigCodes,
        $eppApprovalStatusConfigCodes,
        $eppDocumentStatusConfigCodes,
        $errorType;

    /**
     * PaymentBatchCancelValidator constructor.
     *
     * @param MasterInterface $masterInterface
     * @param Payment $payment
     * @param string $paymentMode
     */
    public function __construct(
        MasterInterface $masterInterface,
        Payment $payment,
        string $paymentMode
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->paymentObj = $payment;

        $this->paymentMode = $paymentMode;

        $this->paymentModeConfigCodes = Config::get('mappings.payment_mode');

        $this->saleOrderStatusConfigCodes = Config::get('mappings.sale_order_status');

        $this->aeonApprovalStatusConfigCodes = Config::get('mappings.aeon_payment_approval_status');

        $this->aeonDocumentStatusConfigCodes = Config::get('mappings.aeon_payment_document_status');

        $this->eppApprovalStatusConfigCodes = Config::get('mappings.epp_payment_approval_status');

        $this->eppDocumentStatusConfigCodes = Config::get('mappings.epp_payment_document_status');
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

        //Get Master Data ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'payment_mode',
                'sale_order_status',
                'aeon_payment_approval_status',
                'aeon_payment_document_status',
                'epp_payment_approval_status',
                'epp_payment_document_status'
            ));

        //Get pre order status ID
        $saleOrderStatus = array_change_key_case($settingsData['sale_order_status']->pluck('id','title')->toArray());

        $preOrderId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['pre-order']];

        //Get EPP Moto Payment Mode ID
        $paymentMode = array_change_key_case($settingsData['payment_mode']
            ->pluck('id','title')->toArray());

        $paymentDetail = $this->paymentObj->find($value);

        if($paymentDetail->sale->order_status_id != $preOrderId){
            $this->errorType = 'invalidSaleOrderStatus';

            return false;
        }

        if($paymentDetail->status == 2){

            if(!empty($this->paymentMode)){

                $paymentModeId = $paymentDetail->paymentModeProvider->master_data_id;

                $paymentJsonDetail = json_decode($paymentDetail->payment_detail, true);

                if($this->paymentMode == 'epp_moto'){

                    $eppApprovalStatus = array_change_key_case($settingsData['epp_payment_approval_status']
                        ->pluck('id','title')->toArray());

                    $eppDocumentStatus = array_change_key_case($settingsData['epp_payment_document_status']
                        ->pluck('id','title')->toArray());

                    $eppMotoPaymentId = $paymentMode[$this->paymentModeConfigCodes['epp (moto)']];

                    $eppMotoPendingId = $eppApprovalStatus[$this->eppApprovalStatusConfigCodes['pending']];

                    $eppMotoNewDocumentId = $eppDocumentStatus[$this->eppDocumentStatusConfigCodes['n']];

                    if($paymentModeId == $eppMotoPaymentId){

                        //Approval and document status must is pending
                        $this->errorType = 'invalidEppPaymentApprovalStatus';

                        $result = ($paymentJsonDetail['payment_response']['approval_status'] == $eppMotoPendingId &&
                            $paymentJsonDetail['payment_response']['doc_status'] == $eppMotoNewDocumentId &&
                                $paymentJsonDetail['payment_response']['approval_code'] == '') ? true : false;

                    } else {

                        $this->errorType = 'invalidEppMotoPayment';

                        $result = false;
                    }

                } else if ($this->paymentMode == 'aeon'){

                    $aeonApprovalStatus = array_change_key_case($settingsData['aeon_payment_approval_status']
                        ->pluck('id','title')->toArray());

                    $aeonDocumentStatus = array_change_key_case($settingsData['aeon_payment_document_status']
                        ->pluck('id','title')->toArray());

                    $aeonPaymentId = $paymentMode[$this->paymentModeConfigCodes['aeon']];

                    $aeonPendingId = $aeonApprovalStatus[$this->aeonApprovalStatusConfigCodes['pending']];

                    $aeonNewDocumentId = $aeonDocumentStatus[$this->aeonDocumentStatusConfigCodes['n']];

                    if($paymentModeId == $aeonPaymentId){

                        //Approval and document status must is pending
                        $this->errorType = 'invalidAeonPaymentApprovalStatus';

                        $result = ($paymentJsonDetail['payment_response']['approval_status'] == $aeonPendingId &&
                            $paymentJsonDetail['payment_response']['doc_status'] == $aeonNewDocumentId &&
                                $paymentJsonDetail['payment_response']['agreement_no'] == '') ? true : false;

                    } else {

                        $this->errorType = 'invalidAeonPayment';

                        $result = false;
                    }
                }
            }

        } else if($paymentDetail->status == 1){

            $this->errorType = 'paymentCompleted';

            $result = false;

        } else {

            $this->errorType = 'paymentCancelled';

            $result = false;
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
            case "paymentCompleted":
               $message = trans('message.payment-cancellation.payment-completed');
                break;

            case "paymentCancelled":
               $message = trans('message.payment-cancellation.payment-cancelled');
                break;

            case "invalidAeonPaymentApprovalStatus":
               $message = trans('message.payment-cancellation.aeon-payment-approved');
                break;

            case "invalidAeonPayment":
               $message = trans('message.payment-cancellation.invalid-aeon-payment');
                break;

            case "invalidEppPaymentApprovalStatus":
               $message = trans('message.payment-cancellation.epp-moto-payment-approved');
                break;

            case "invalidEppMotoPayment":
               $message = trans('message.payment-cancellation.invalid-epp-moto-payment');
                break;

            case "invalidSaleOrderStatus":
               $message = trans('message.payment-cancellation.invalid-sale-order-status');
                break;

            default:
                $message = '';
        }

        return $message;
    }
}
