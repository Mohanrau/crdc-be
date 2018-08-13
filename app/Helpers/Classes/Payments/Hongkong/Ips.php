<?php
namespace App\Helpers\Classes\Payments\Hongkong;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Helpers\Classes\Payments\Payment as PaymentClass;
use App\Models\Payments\Payment;
use GuzzleHttp;
use DOMDocument;

class Ips extends PaymentClass
{
    protected
        $merchantCode,
        $merchantCodeSkrill,
        $merchantCodeUnionpay,
        $merchantCert,
        $requiredFields,
        $optionalFields,
        $paymentUrl,
        $paymentIdLists,
        $requeryUrl,
        $paymentId;

    /**
     * Ips constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $configPathPrefix = 'payments.hongkong.ips.';

        $this->merchantCode = config($configPathPrefix.'merchant_code');
        $this->merchantCodeUnionpay = config($configPathPrefix.'merchant_code_list.union_pay');
        $this->merchantCodeSkrill = config($configPathPrefix.'merchant_code_list.skrill_pay');
        $this->merchantCert = config($configPathPrefix.'merchant_cert');
        $this->supportedCurrencyCodes = config($configPathPrefix.'supported_currency_codes');
        $this->requiredFields = config($configPathPrefix.'required_fields');
        $this->optionalFields = config($configPathPrefix.'optional_fields');
        $this->paymentUrl = config($configPathPrefix.'payment_url');
        $this->paymentIdLists = config($configPathPrefix.'payment_id_lists');
        $this->requeryUrl = config($configPathPrefix.'payment_requery_url');
        $this->paymentId = config($configPathPrefix.'default_payment_id');
        $this->requiredInputs = config($configPathPrefix.'required_inputs');
        $this->isManual = false;
        $this->isThirdPartyRefund = true;
        $this->isFormGenerateRequired = true; // we will need a form to submit
    }

    private function fillDefaultInfo()
    {
        $this->info['signature_method'] = 2; //2 means using MD5 authentication

        switch($this->params->get('payment_type_id')){
            case 1:
            case '01' :
                //union pay
                $this->merchantCode = $this->info['merchant_code'] = $this->merchantCodeUnionpay;
                $this->info['gateway_type'] = '01';
                break;

            case 7:
            case '07' :
                // skrill
                $this->merchantCode = $this->info['merchant_code'] = $this->merchantCodeSkrill;
                $this->info['geteway_type'] = '07';
                break;

        }

        $this->info['order_date'] = Carbon::now()->format('Ymd');

        $this->info['merchant_certificate'] = $this->merchantCert;

        $this->info['order_encode_type'] = 2;

        $this->info['return_encode_type'] = 12;

        $this->info['return_type'] = 1; //will use server to server return(POST)

        $this->info['language'] = 'EN';
    }

    public function getFormData(array $info)
    {
        //fixed value
        $info['merchant_url'] = (isset($info['merchant_url'])) ?
            $info['merchant_url'] : $this->callbackUrl.'/'.$info['sale_payment_id'];

        $info['server_url'] = (isset($info['server_url'])) ?
            $info['server_url'] : $this->callbackUrl.'/'.$info['sale_payment_id'].'/1';

        $this->info = $info;

        $this->fillDefaultInfo();

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

        $this->currencyCode = $info['currency_code'];

        //now we can generate a signature
        $this->generateSignature();

        //When validated, get the form data to the frontend
        $this->prepareForm();

        $this->formData;

        //Insert Payment Info
        $this->insertPaymentTransactionInfo();

        return json_encode($this->formData);
    }

    public function prepareForm()
    {
        //populating required fields
        foreach($this->requiredFields as $field => $requestField){
            $keyPairValue[$requestField] = $this->info[$field];
        }

        //populating additional fields
        foreach($this->optionalFields as $field => $requestField){
            if(isset($this->info[$field])){
                $keyPairValue[$requestField] = $this->info[$field];
            }
        }

        $this->formData = array(
            'form_attributes' => array(
                'method' => 'post',
                'action' => $this->paymentUrl
            ),
            'form_inputs' => $keyPairValue
        );
    }

    /**
     * We will need to validate certain info that is needed within the payment method
     */
    protected function validateInfo()
    {
        //validate all except secure hash and return url
        collect($this->requiredFields)->except('signature')->map(function($field, $fieldName){
            if (!isset($this->info[$fieldName]) || $this->info[$fieldName] == '') {
                throw new \Exception("Field : '" . $fieldName . "' is needed");
            }
        });
    }

    /**
     * Generate signature for the payment
     */
    private function generateSignature()
    {
        //append merchantkey + merchantcode+ reference Number + amount in cent + currency_code
        $keyCombination = 'billno'.$this->info['reference_no'].'amount'.$this->amount.'date'.$this->info['order_date']
            .'currencytype'.$this->currencyCode.'cert'.$this->merchantCert;

        $this->generatedSignature = $this->info['signature'] = md5($keyCombination);
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
     * Insert payment transaction information
     *
     */
    public function insertPaymentTransactionInfo()
    {
        $paymentId = explode('-',$this->info['reference_no'])[1];

        $payment = Payment::findOrFail($paymentId);

        if($payment){

            $paymentInfo = [
                'MerchantCode' => $this->merchantCode,
                'RefNo' => $this->info['reference_no'],
                'Amount' => $this->info['amount'],
                'OrderDate' => $this->info['order_date'],
                'CurrencyType' => $this->info['currency_code'],
                'GatewayType' => $this->info['gateway_type']
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
        $responseData = $request->all();

        $billNo = $responseData['billno'];

        $amount = $responseData['amount'];

        $orderDate = $responseData['date'];

        $succFlag = $responseData['succ'];

        $ipsBillNo = $responseData['ipsbillno'];

        $currencyType = $responseData['Currency_type'];

        $signature = $responseData['signature'];

        $response = [
            'success' => false,
            'data' => $responseData
        ];

        if($succFlag == 'Y'){

            $keyCombine = 'billno' . $billNo . 'amount' . $amount . 'date' . $orderDate . 'succ' . $succFlag . 'ipsbillno'
                . $ipsBillNo . 'currencytype' . $currencyType . 'cert' . $this->merchantCert;

            $keySignature = md5($keyCombine);

            if($keySignature == $signature){

                $paymentId = explode('-', $billNo)[1];

                $payment = Payment::findOrFail($paymentId);

                if($payment){
                    $response['success'] = true;
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

        $merchantCode = $data['payment_info']['MerchantCode'];

        $refNo = $data['payment_info']['RefNo'];

        $amount = $data['payment_info']['Amount'];

        $orderDate = $data['payment_info']['OrderDate'];

        $currencyType = $data['payment_info']['CurrencyType'];

        $gatewayType = $data['payment_info']['GatewayType'];

        $keyCombine = 'billno' . $refNo . 'amount' . $amount . 'date' . $orderDate .
            'currencytype' . $currencyType . 'cert' . $this->merchantCert;

        $keySignature = md5($keyCombine);

        // Set post content
        $xml = '<?xml version="1.0" encoding="utf-8"?>
		<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
            <soap:Body>
                <QueryTrdOrder xmlns="http://tempuri.org/">
                    <Mer_code>' . $merchantCode . '</Mer_code>
                    <Billno>' . $refNo . '</Billno>
                    <Amount>' . $amount . '</Amount>
                    <Date>' . $orderDate . '</Date>
                    <Currency_Type>' . $currencyType . '</Currency_Type>
                    <Gateway_Type>' . $gatewayType . '</Gateway_Type>
                    <OrderEncodeType>2</OrderEncodeType>
                    <RetEncodeType>12</RetEncodeType>
                    <SignMD5>' . $keySignature . '</SignMD5>
                </QueryTrdOrder>
            </soap:Body>
        </soap:Envelope>';

        $http = new \GuzzleHttp\Client;

        $response = $http->post($this->requeryUrl, [
            'headers' => [
                'Host' => 'webservice.hkipsec.com',
                'Content-Type' => 'text/xml; charset=utf-8',
                'Content-Length' => 'length',
                'SOAPAction' => 'http://tempuri.org/QueryTrdOrder'
            ],
            'body' => $xml
        ]);

        $results = $response->getBody();

        $results = str_replace('&lt;','<',$results);

        $results = str_replace('&gt;','>',$results);

        $resultString = explode('<?xml version="1.0" encoding="utf-8"?>', $results);

        $contents = '<?xml version="1.0" encoding="utf-8"?>' . $resultString[1] . $resultString[2];

        $document = new DOMDocument();

        $document->loadXML($contents);

        $queryResult = [
            'mercode' => $document->getElementsByTagName('mercode')->item(0)->nodeValue,
            'ReqDate' => $document->getElementsByTagName('ReqDate')->item(0)->nodeValue,
            'retencodetype' => $document->getElementsByTagName('retencodetype')->item(0)->nodeValue,
            'ErrCode' => $document->getElementsByTagName('ErrCode')->item(0)->nodeValue,
            'ErrMsg' => $document->getElementsByTagName('ErrMsg')->item(0)->nodeValue,
            'signature' => $document->getElementsByTagName('signature')->item(0)->nodeValue,
            'Approvalcode' => $document->getElementsByTagName('Approvalcode')->item(0)->nodeValue,
            'Currency_Type' => $document->getElementsByTagName('Currency_Type')->item(0)->nodeValue,
            'billno' => $document->getElementsByTagName('billno')->item(0)->nodeValue,
            'amount' => $document->getElementsByTagName('amount')->item(0)->nodeValue,
            'date' => $document->getElementsByTagName('date')->item(0)->nodeValue,
            'ipsbillno' => $document->getElementsByTagName('ipsbillno')->item(0)->nodeValue,
            'succ' => $document->getElementsByTagName('succ')->item(0)->nodeValue,
            'msg' => $document->getElementsByTagName('msg')->item(0)->nodeValue,
        ];

        $results = [
            'success' => false,
            'data' => $queryResult
        ];

        if($queryResult['ErrCode'] == 'AB00000F'){

            if($queryResult['succ'] == 'Y'){

                $returnKeyCombine = 'billno' . $queryResult['billno'] . 'amount' . $queryResult['amount'] . 'date' . $queryResult['date'] .
                    'succ' . $queryResult['succ'] . 'ipsbillno' . $queryResult['ipsbillno'] . 'currencytype' . $queryResult['Currency_Type'] .
                        'cert' . $this->merchantCert;

                $retureKeySignature = md5($returnKeyCombine);

                if($retureKeySignature == $queryResult['signature']){
                     $results['success'] = true;
                }
            }
        }

        return $results;
    }
}