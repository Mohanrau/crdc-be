<?php
namespace App\Helpers\Classes\Payments\Malaysia;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Payments\Payment;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Helpers\Classes\Payments\Common\Ipay88Common;

class Ipay88 extends Ipay88Common
{
    protected $isEPPOnline;

    /**
     * Ipay88 Malaysia constructor.
     * @param bool $isEPPOnline - i-pay88 has epp-online services
     */
    public function __construct(MasterInterface $masterInterface, $isEPPOnline = false)
    {
        parent::__construct($masterInterface);
        $this->merchantKey = Config::get('payments.malaysia.ipay88.merchant_key');
        $this->merchantCode = Config::get('payments.malaysia.ipay88.merchant_code');
        $this->supportedCurrencyCodes = Config::get('payments.malaysia.ipay88.supported_currency_codes');
        $this->requiredFields = Config::get('payments.malaysia.ipay88.required_fields');
        $this->optionalFields = Config::get('payments.malaysia.ipay88.optional_fields');
        $this->paymentUrl = Config::get('payments.malaysia.ipay88.payment_url');
        $this->paymentIdLists = Config::get('payments.malaysia.ipay88.payment_id_lists');
        $this->sandboxUrl = Config::get('payments.malaysia.ipay88.sandbox_url');
        $this->requeryUrl = Config::get('payments.malaysia.ipay88.payment_requery_url');
        $this->isSandboxEnvironment = Config::get('payments.malaysia.ipay88.sandbox_mode');
        $this->paymentId = Config::get('payments.malaysia.ipay88.default_payment_id');
        $this->requiredInputs = Config::get('payments.malaysia.ipay88.required_inputs');
        $this->isEPPOnline = $isEPPOnline;

        /**
         * For epp online, an option of 3,6,12 is available.
         * @todo should we get the information before getting the form, we must get the param from 'info'
         */
        if($isEPPOnline){
            //if this is an EPP online, there are 2 additional fields that is required
            $this->requiredFields = array_merge($this->requiredFields,
                Config::get('payments.malaysia.ipay88.epp_required_fields')
            );

            $this->requiredInputs = Config::get('payments.malaysia.ipay88.epp_online_required_inputs');
            $this->merchantKey = Config::get('payments.malaysia.ipay88.epp_merchant_key');
            $this->merchantCode = Config::get('payments.malaysia.ipay88.epp_merchant_code');
        }
    }

    /**
     * This will return the information of the form that is needed to make a call to payment gateway
     *
     * @param array $info
     * @return string
     * @throws \Exception
     */
    public function getFormData(array $info)
    {
        //fixed value
        $info['response_url'] = (isset($info['response_url'])) ?
            $info['response_url'] : $this->callbackUrl.'/'.$info['sale_payment_id'];

        $info['backend_url'] = (isset($info['backend_url'])) ?
            $info['backend_url'] : $this->callbackUrl.'/'.$info['sale_payment_id'].'/1';

        $info['payment_id'] = ($this->params->get('payment_id')) ?
            $this->params->get('payment_id') : NULL;

        if($this->params->get('plan')){
            //plan is used by EPP. Accepting 3,6,12,24,36
            $info['plan'] = $this->params->get('plan');
        }

        $this->info = $info;

        try{
            $this->validateInfo();
        }catch (Exception $e){
            throw $e;
        }

        $this->amount = $this->info['amount'] = number_format(
            $this->info['amount'],
            2,
            '.',
            ''
        );

        // ensure correctness
        $this->amountWithoutDecimal = str_replace('.', '', $this->amount);
        $this->currencyCode = $info['currency_code'];

        //now we can generate a signature
        $this->generateSignature();

        //When validated, get the form data to the frontend
        $this->prepareForm();

        //Insert Payment Info
        $this->insertPaymentTransactionInfo();

        return json_encode($this->formData);
    }

    /**
     * Generate signature for the payment
     */
    private function generateSignature()
    {
        //append merchantkey + merchantcode  + reference Number + amount in cent + currency_code
        $keyCombination = $this->merchantKey.$this->merchantCode.
            $this->info['reference_no'].$this->amountWithoutDecimal.$this->currencyCode;

        //Merge the signature and see if there is any documentation that teaches us how to generate the SHA
        $this->generatedSignature = hash('sha256', $keyCombination);
    }

    /**
     * @param Request $request
     * @param $isBackendCall
     * @return array
     */
    public function processCallback(Request $request, $isBackendCall)
    {
        return parent::processCallback($request, $isBackendCall);
    }

    /**
     * Query to verify transaction status
     *
     * @param Payment $payment
     */
    public function requeryPayment(Payment $payment)
    {
        parent::requeryPayment($payment);
    }
}