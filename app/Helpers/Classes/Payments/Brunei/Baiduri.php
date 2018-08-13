<?php
namespace App\Helpers\Classes\Payments\Brunei;

use App\Helpers\Classes\Payments\Payment as PaymentClass;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Models\Payments\Payment;

class Baiduri extends PaymentClass
{
    protected $merchantId;
    protected $merchantCode;
    protected $supportedCurrencyCodes;
    protected $requiredFields;
    protected $optionalFields;
    protected $paymentUrl;
    protected $sandboxUrl;
    protected $isSandboxEnvironment;
    protected $defaultValues;
    protected $signature;

    /**
     * Key configurations from config
     */
    public function __construct()
    {
        parent::__construct();
        $this->merchantId = Config::get('payments.brunei.baiduri.merchant_id');
        $this->merchantCode = Config::get('payments.brunei.baiduri.merchant_code');
        $this->supportedCurrencyCodes = Config::get('payments.brunei.baiduri.supported_currency_codes');
        $this->secureHashSecret = Config::get('payments.brunei.baiduri.shared_secret');
        $this->requiredFields = Config::get('payments.brunei.baiduri.required_fields');
        $this->optionalFields = Config::get('payments.brunei.baiduri.optional_fields');
        $this->defaultValues = Config::get('payments.brunei.baiduri.default_values');
        $this->paymentUrl = Config::get('payments.brunei.baiduri.payment_url');
        $this->sandboxUrl = Config::get('payments.brunei.baiduri.sandbox_url');
        $this->isSandboxEnvironment = Config::get('payments.brunei.baiduri.sandbox_mode');
        $this->requiredInputs = Config::get('payments.brunei.baiduri.required_inputs');
        $this->isManual = false;
        $this->isThirdPartyRefund = true;
        $this->isFormGenerateRequired = true;
    }

    /**
     * @param array $info
     */
    public function getFormData(array $info)
    {
        //ensure correctness of the amount
        $info['amount'] = $info['amount'] * 100; //to remove the .00

        $this->info = array_merge($info, $this->defaultValues);
        $this->info['merchant_id'] = $this->merchantId;
        $this->info['merchant_code'] = $this->merchantCode;
        $this->info['return_url'] = $this->callbackUrl.'/'.$info['sale_payment_id'];
        $this->info['order_info'] = $this->info['reference_no'];
        $this->validateInfo();

        $formData = $this->prepareForm();

        return(json_encode($formData));
    }

    private function prepareForm()
    {
        $requestParams = array();
        //sort all the keys in asc (baiduri bank requirement)
        collect($this->requiredFields)->except(['secure_hash', 'secure_hash_type'])
            ->each(function($key, $value) use (&$requestParams) {
            $requestParams[$key] = $this->info[$value];
        });

        collect($this->optionalFields)->except(['secure_hash', 'secure_hash_type'])
            ->each(function($key, $value) use (&$requestParams) {
            $requestParams[$key] = $this->info[$value];
        });

        //we will have to generate the signature
        $concatParams = [];
        $arrayParams = [];

        collect($requestParams)->each(function($value, $key) use(&$concatParams, &$arrayParams){
            $concatParams[] = $key.'='.$value;
            $arrayParams[$key] = $value;
        });

        asort($concatParams);
        ksort($arrayParams);
        $concatParams = implode('&', $concatParams);

        $this->signature = strtoupper(hash_hmac('SHA256', $concatParams, hex2bin($this->secureHashSecret)));

        //once signature generated, return the link to be called
        $concatParams .= '&'.$this->requiredFields['secure_hash'].'='.$this->signature;
        $concatParams .= '&'.$this->requiredFields['secure_hash_type'].'='.$this->defaultValues['secure_hash_type'];

        $arrayParams[$this->requiredFields['secure_hash']] = $this->signature;
        $arrayParams[$this->requiredFields['secure_hash_type']] = $this->defaultValues['secure_hash_type'];

        $submitLink = ($this->isSandboxEnvironment) ? $this->sandboxUrl : $this->paymentUrl;

        return array(
            'form_attributes' => array(
                'method' => 'get',
                'action' => $submitLink
            ),
            'form_inputs' => $arrayParams
        );

    }

    /**
     * We will need to validate certain info that is needed within the payment method
     */
    protected function validateInfo()
    {
        //validate all except secure hash and return url
        collect($this->requiredFields)->except('secure_hash', 'return_url')->map(function($field, $fieldName){
            if (!isset($this->info[$fieldName]) || $this->info[$fieldName] == '') {
                throw new \Exception("Field : '" . $fieldName . "' is needed");
            }

            if ($fieldName == 'currency_code' && !in_array($this->info['currency_code'],
                    $this->supportedCurrencyCodes)) {
                throw new \Exception("Currency Not supported");
            }
        });
    }

    /**
     * @param Request $request
     * @param $isBackendCall
     * @return array
     */
    public function processCallback(Request $request, $isBackendCall)
    {
        //@todo we need to ensure the source of the caller is from ipay88
        $paymentId = explode('-', $request->get('vpc_MerchTxnRef'))[1];
        $status = ($request->get('vpc_TxnResponseCode') == 0) ? 1 : 0; // 0 indicates success from baiduri
        $payment = Payment::findOrFail($paymentId);
        $response = [];

        if($payment){
            //find out if the payment is success or fail
            $response['success'] = ($status == '1') ? true : false;
        }

        $response['data'] = json_encode($request->all());

        return $response;
    }

    /**
     * Query to verify transaction status
     *
     * @param Payment $payment
     */
    public function requeryPayment(Payment $payment)
    {
        //TODO ALSON :: Will be implement payment transaction requery with Baiduri payment gateway
        $results = [];

        $results['success'] = 0;

        return $results;
    }
}