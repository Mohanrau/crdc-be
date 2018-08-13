<?php
namespace App\Helpers\Classes\Payments\Singapore;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Helpers\Classes\Payments\Payment as PaymentClass;
use App\Models\Payments\Payment;

class Enets extends PaymentClass
{
    protected
        $merchantCode,
        $requiredFields,
        $paymentUrl,
        $requeryUrl,
        $sandboxUrl,
        $sandboxRequeryUrl,
        $isSandboxEnvironment,
        $apiKey,
        $secretKey;

    public function __construct()
    {
        parent::__construct();

        $configPathPrefix = 'payments.singapore.enets.';
        $this->merchantCode = config($configPathPrefix.'merchant_code');
        $this->apiKey = config($configPathPrefix.'api_key');
        $this->secretKey = config($configPathPrefix.'secret_key');
        $this->requiredFields = config($configPathPrefix.'required_fields');
        $this->paymentUrl = config($configPathPrefix.'payment_url');
        $this->requeryUrl = config($configPathPrefix.'payment_requery_url');
        $this->sandboxUrl = config($configPathPrefix.'sandbox_url');
        $this->sandboxRequeryUrl = config($configPathPrefix.'sandbox_requery_url');
        $this->isSandboxEnvironment = config($configPathPrefix.'sandbox_mode');
        $this->requiredInputs = config($configPathPrefix.'required_inputs');
        $this->isManual = false;
        $this->isThirdPartyRefund = true;
        $this->isFormGenerateRequired = true; // we will need a form to submit
    }

    /**
     * To retrieve the form data for the frontend to generate for submission
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

        $info['merchant_code'] = $this->merchantCode;
        $info['client_type'] = 'H';
        $info['submission_mode'] = 'B';
        $info['merchant_transaction_time'] = substr(Carbon::now()->format('Ymd H:i:s.u'), 0, -3);
        $info['merchant_timezone'] = '+8:00'; // +08:00, not sure if we need to remove the 0
        $info['payment_type'] = 'SALE';
        $info['nets_mid_indicator'] = 'U';
        $info['payment_mode'] = '';

        // ensure correctness
        $this->amount = $this->info['amount'] = number_format(
            $info['amount'],
            2,
            '.',
            ''
        );

        $info['amount'] = str_replace('.', '', (string)$this->amount); // amount without decimal
        $this->currencyCode = $info['currency_code'];
        $this->info = $info;

        try {
            $this->validateInfo();
        } catch (Exception $e){
            throw $e;
        }

        $this->amount = $this->info['amount'];

        //When validated, get the form data to the frontend
        $this->prepareForm();

        //Insert Payment Info
        $this->insertPaymentTransactionInfo();

        return collect($this->formData)->toJson(true);
    }

    /**
     * We will need to validate certain info that is needed within the payment method
     */
    protected function validateInfo()
    {
        foreach($this->requiredFields as $fieldName => $field){
            if(!isset($this->info[$fieldName])){
                throw new \Exception("Field : '".$fieldName."' is needed");
            }
        }
    }

    public function prepareForm()
    {
        //populating required fields
        foreach($this->requiredFields as $field => $requestField){
            $keyPairValue[$requestField] = $this->info[$field];
        }

        $data = collect([
            'ss' => '1',
            'msg' => $keyPairValue
        ])->toJson(JSON_UNESCAPED_SLASHES);

        $this->formData = array(
            'form_attributes' => array(
                'method' => 'post',
                'action' => ($this->isSandboxEnvironment) ?
                            $this->sandboxUrl: $this->paymentUrl
            ),
            'form_inputs' => [
                'payload' => $data,
                'apiKey' => $this->apiKey,
                'hmac' => $this->generateHMAC($data)
            ]
        );
    }

    private function generateHMAC($data)
    {
        $hashstring = hash('sha256', $data.$this->secretKey);

        return  base64_encode(hex2bin($hashstring));
    }

    /**
     * Insert payment transaction information
     *
     */
    public function insertPaymentTransactionInfo()
    {
        $paymentId = explode('-',$this->info['reference_no'])[1];

        $payment = Payment::findOrFail($paymentId);

        if($payment){

            $paymentInfo = [
                'netsMid' => $this->merchantCode,
                'merchantTxnRef' => $this->info['reference_no']
            ];

            $paymentDetail = json_decode($payment->payment_detail, true);

            $paymentDetail['payment_info'] = $paymentInfo;

            $payment->update(
                array(
                    'payment_detail' => json_encode($paymentDetail)
                )
            );
        }
    }

    /**
     * @param Request $request
     * @param $isBackendCall
     * @return array
     */
    public function processCallback(Request $request, $isBackendCall)
    {
        $requestInputs = $request->all();

        if($isBackendCall){

            $payload = $requestInputs;

            $hashValidation = true;

        } else {

            $message = urldecode($requestInputs['message']);

            $hashValue = $this->generateHMAC($requestInputs['message']);

            $hmac= $requestInputs['hmac'];

            $payload = json_decode($message, true);

            $hashValidation = (strcasecmp($hashValue, $hmac) <> 0) ? true : false;
        }

        $response = [
            'success' => false,
            'data' => []
        ];

        if($hashValidation){

            if (!empty($payload["msg"])){

                $enetsResponse = $payload["msg"];

                $enetsReference = $enetsResponse["merchantTxnRef"];

                $paymentId = explode('-', $enetsReference)[1];

                $payment = Payment::findOrFail($paymentId);

                if ($payment){

                    $enetsActionCode = $enetsResponse["actionCode"];

                    if ($enetsActionCode == 0){

                        $response['success'] = true;

                    } else if ($enetsActionCode == 2){ //This respond action code need to call transaction query API

                        $response['success'] = false;

                        $enetsResponse['payStatus'] = 2; //Set back to pending and need call transaction query API to verify it

                    } else {

                        $response['success'] = false;

                        $enetsResponse['payStatus'] = 0; // 0 indicates fail
                    }

                    $response['data'] = $enetsResponse;
                }
            }
        }

        return $response;
    }

    /**
     * Query to verify transaction status
     *
     * @param Payment $payment
     */
    public function requeryPayment(Payment $payment)
    {
        $data = json_decode($payment->payment_detail, true);

        $netsMid = $data['payment_info']['netsMid'];

        $merchantTxnRef = $data['payment_info']['merchantTxnRef'];

        $queryData = collect([
            'ss' => '1',
            'msg' => [
                'netsMid' => $netsMid,
                'merchantTxnRef' => $merchantTxnRef,
                'netsMidIndicator' => 'U',
                
            ]
        ])->toJson(JSON_UNESCAPED_SLASHES);

        $hmacValue = $this->generateHMAC($queryData);

        $http = new \GuzzleHttp\Client;

        $response = $http->post(($this->isSandboxEnvironment) ? $this->sandboxRequeryUrl: $this->requeryUrl, [
            'headers' => [
                'Content-Type'=>'application/json',
                'keyID' => $this->apiKey,
                'hmac' => $hmacValue
            ],
            'body' => $queryData
        ]);

        $queryResult = json_decode($response->getBody(), true);

        $results = [
            'data' => $queryResult
        ];

        $results['success'] = ($queryResult['msg']['netsTxnStatus'] == '0') ? true : false;

        return $results;
    }
}