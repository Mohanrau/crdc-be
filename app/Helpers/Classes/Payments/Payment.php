<?php
namespace App\Helpers\Classes\Payments;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Class Payment
 *
 * To be extended by payment classes to retrieve the general functions as a general payment responsibility.
 */
class Payment
{
    protected $salePaymentId,
        $isManual,
        $isThirdPartyRefund,
        $isFormGenerateRequired,
        $isCreationGeneratePaymentDetail,
        $callbackUrl,
        $salePaymentObj,
        $params,
        $requiredInputs;

    public function __construct()
    {
        //if this is a manual payment, there wont be any changes of payment status from third party API
        $this->isManual = true;

        //if this is involved third party payment gateway, this value should be true
        $this->isThirdPartyRefund = false;

        //if this is a payment require form input, then will be generate form to get user input
        $this->isFormGenerateRequired = false;

        //if this is a payment require to generate extra payment information during payment creation
        $this->isCreationGeneratePaymentDetail = false;

        // we need to bind this to the sale Payment id when there's callback involved
        $this->salePaymentId = 0;

        //the callback url - excluding the id, will need to merge it with the sales id
        $this->callbackUrl = url('api/v1/payments/callback/');

        //params is empty unless set through the setter
        $this->params = [];
    }

    /**
     * To retrieve the callback from API and process the payload, return false if is a manual payment.
     *
     * @param Request $request
     * @param $isBackendCall - to determine if this is called via backend url
     * @return array
     */
    public function processCallback(Request $request, $isBackendCall)
    {
        return array(
            'success' => false,
            'data' => array()
        );
    }

    /**
     *  get payment required inputs
     *
     * @return Boolean
     */
    public function requiredInputs()
    {
        return $this->requiredInputs;
    }

    /**
     * Getter to get is this payment is manual or not
     *
     * @return Boolean
     */
    public function isManual()
    {
        return $this->isManual;
    }
    
    /**
     * Getter to get is this payment is third party refund or not
     *
     * @return Boolean
     */
    public function isThirdPartyRefund()
    {
        return $this->isThirdPartyRefund;
    }

    /**
     * To check is this payment is form generate required
     *
     * @return Boolean
     */
    public function isFormGenerateRequired()
    {
        return $this->isFormGenerateRequired;
    }

    /**
     * To check is this payment is it need generate payment detail during creation
     *
     * @return Boolean
     */
    public function isCreationGeneratePaymentDetail()
    {
        return $this->isCreationGeneratePaymentDetail;
    }

    /**
     * To hide privacy input data during creation
     *
     * @return array
     */
    public function modifyPaymentInputData($fields)
    {
        $modifyField = [];

        collect($fields)->each(function ($field) use (&$modifyField){

            if($field['name'] == 'card_number'){

                $value = $field['value'];

                $field['value'] = substr_replace($value, str_repeat("X", 12), 0, 12);

            } else if ($field['name'] == 'cvv_code') {
                return true;
            }

            array_push($modifyField, $field);
        });

        return $modifyField;
    }

    /**
     * Additional params are set to grab more information from the payments
     * For example, mpos will need approval codes, etc
     * @param $params
     */
    public function setAdditionalParams(Collection $params)
    {
        $this->params = $params;
    }

    /**
     * To validate if the manual payment is correct
     * @return bool
     */
    public function validateManualPayment()
    {
        return true;
    }

    /**
     * To process manual payment if needed
     *
     * @param $paymentId
     * @return bool
     */
    public function processManualPayment($paymentId)
    {
        return true;
    }
}