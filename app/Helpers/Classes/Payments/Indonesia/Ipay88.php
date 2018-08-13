<?php
namespace App\Helpers\Classes\Payments\Indonesia;

use App\Interfaces\Masters\MasterInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Helpers\Classes\Payments\Common\Ipay88Common;
use App\Models\Payments\Payment;

class Ipay88 extends Ipay88Common
{
    protected $isEPPOnline;

    /**
     * Ipay88 constructor.
     * @param bool $isEPPOnline
     */
    public function __construct(MasterInterface $masterInterface, $isEPPOnline = false)
    {
        parent::__construct($masterInterface);
        $this->merchantKey = Config::get('payments.indonesia.ipay88.merchant_key');
        $this->merchantCode = Config::get('payments.indonesia.ipay88.merchant_code');
        $this->supportedCurrencyCodes = Config::get('payments.indonesia.ipay88.supported_currency_codes');
        $this->requiredFields = Config::get('payments.indonesia.ipay88.required_fields');
        $this->optionalFields = Config::get('payments.indonesia.ipay88.optional_fields');
        $this->paymentUrl = Config::get('payments.indonesia.ipay88.payment_url');
        $this->paymentIdLists = Config::get('payments.indonesia.ipay88.payment_id_lists');
        $this->requeryUrl = Config::get('payments.indonesia.ipay88.payment_requery_url');
        $this->sandboxUrl = Config::get('payments.indonesia.ipay88.sandbox_url');
        $this->isSandboxEnvironment = Config::get('payments.indonesia.ipay88.sandbox_mode');
        $this->paymentId = Config::get('payments.indonesia.ipay88.default_payment_id');
        $this->requiredInputs = Config::get('payments.indonesia.ipay88.required_inputs');

        $this->isEPPOnline = $isEPPOnline;

        if($isEPPOnline){
            //if this is an EPP online, there are 2 additional fields that is required
            $this->requiredFields = array_merge($this->requiredFields,
                Config::get('payments.indonesia.ipay88.epp_required_fields')
            );

            $this->requiredInputs = Config::get('payments.indonesia.ipay88.epp_online_required_inputs');
            $this->merchantKey = Config::get('payments.indonesia.ipay88.merchant_key');
            $this->merchantCode = Config::get('payments.indonesia.ipay88.merchant_code');
        }
    }

    public function getFormData(array $info)
    {
        //fixed value
        $info['response_url'] = (isset($info['response_url'])) ?
            $info['response_url'] : $this->callbackUrl.'/'.$info['sale_payment_id'];

        $info['backend_url'] = (isset($info['backend_url'])) ?
            $info['backend_url'] : $this->callbackUrl.'/'.$info['sale_payment_id'].'/1';

        $info['payment_id'] = ($this->params->get('payment_id')) ?
            $this->params->get('payment_id') : NULL;

        if($this->isEPPOnline){
            if($this->params->get('plan')){
                //plan is used by EPP. Accepting 3,6,12
                $info['plan'] = ':||IPP:'.$this->params->get('plan').'||:';
            }
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
        $eppField = '';
        if($this->isEPPOnline){
            $eppField = (isset($this->info['plan'])) ? $this->info['plan'] : NULL;
        }
        //append merchantkey + merchantcode+ reference Number + amount in cent + currency_code
        $keyCombination = $this->merchantKey.$this->merchantCode.
            $this->info['reference_no'].$this->amountWithoutDecimal.$this->currencyCode.$eppField;
        $this->generatedSignature = base64_encode($this->hex2bin(sha1($keyCombination)));
    }

    private function hex2bin($hexSource)
    {
        $bin = '';
        for ($i=0;$i<strlen($hexSource);$i=$i+2)
        {
            $bin .= chr(hexdec(substr($hexSource,$i,2)));
        }
        return $bin;
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