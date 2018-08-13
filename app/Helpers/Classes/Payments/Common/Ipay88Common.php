<?php
namespace App\Helpers\Classes\Payments\Common;

use App\Helpers\Classes\Payments\Payment as PaymentClass;
use App\Interfaces\Masters\MasterInterface;
use App\Models\Payments\Payment;
use GuzzleHttp;
use Illuminate\Http\Request;

class Ipay88Common extends PaymentClass
{
    /**
     * Readme : Do keep note that there is no sandbox environment in Malaysia, so a real value must be used.
     *          For indonesia on the other hand, they have a sandbox value. So, if we want to test, we should use the
     *          sandbox url instead.
     */

    /**
     * Key configurations from config
     */
    protected $merchantKey; // will be instantiated based on the merchant key from settings
    protected $merchantCode; // will be instantiated based on the merchant code from settings
    protected $supportedCurrencyCodes;
    protected $requiredFields;
    protected $optionalFields;
    protected $paymentUrl;
    protected $paymentIdLists;
    protected $requeryUrl;

    /**
     * populated through setter
     */
    protected $paymentId;
    protected $currencyCode; // need to find out the currency code
    protected $amount; // amount in decimal point
    protected $amountWithoutDecimal;
    protected $info;
    protected $generatedSignature;
    protected $formData;
    protected $isSandboxEnvironment;

    protected $masterRepositoryObj;

    public function __construct(MasterInterface $masterInterface)
    {
        parent::__construct();

        $this->isManual = false;

        $this->isThirdPartyRefund = true;

        $this->isFormGenerateRequired = true;

        $this->callbackUrl = url('api/v1/payments/callback/');

        $this->approvalStatusConfigCodes = config('mappings.epp_payment_approval_status');

        $this->docStatusConfigCodes = config('mappings.epp_payment_document_status');

        $this->masterRepositoryObj = $masterInterface;
    }

    public function prepareForm()
    {
        $keyPairValue = array(
            'MerchantCode' => $this->merchantCode,
            'SignatureType' => "SHA256",
            'Signature' => $this->generatedSignature
        );

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
                'action' => ($this->isSandboxEnvironment) ?
                    $this->sandboxUrl: $this->paymentUrl
            ),
            'form_inputs' => $keyPairValue
        );
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
                'Amount' => $this->info['amount']
            ];

            $paymentResponse = [];

            if(isset($this->isEPPOnline) && $this->isEPPOnline){

                //Get Master Data ID
                $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
                    array('epp_payment_approval_status', 'epp_payment_document_status'));

                $approvalStatus = array_change_key_case($settingsData['epp_payment_approval_status']
                    ->pluck('id','title')->toArray());

                $pendingId = $approvalStatus[$this->approvalStatusConfigCodes['pending']];

                $documentStatus = array_change_key_case($settingsData['epp_payment_document_status']
                    ->pluck('id','title')->toArray());

                $newDocumentId = $documentStatus[$this->docStatusConfigCodes['n']];

                $paymentResponse['doc_status'] = $newDocumentId;

                $paymentResponse['tenure'] = '';

                $paymentResponse['card_type'] = '';

                $paymentResponse['card_holder_name'] = '';

                $paymentResponse['card_number'] = '';

                $paymentResponse['cvv_code'] = '';

                $paymentResponse['card_expiry_date'] = '';

                $paymentResponse['approval_code'] = '';

                $paymentResponse['approval_status'] = $pendingId;

                $paymentResponse['approved_by'] = '';

                $paymentResponse['approved_date'] = '';

                $paymentResponse['converted_by'] = '';

                $paymentResponse['converted_date'] = '';
            }

            $paymentDetail = json_decode($payment->payment_detail, true);

            $paymentDetail['payment_info'] = $paymentInfo;

            $paymentDetail['payment_response'] = $paymentResponse;

            $payment->update(
                array(
                    'payment_detail' => json_encode($paymentDetail)
                )
            );
        }
    }

    /**
     * Query to verify transaction status
     *
     * @param Payment $payment
     */
    public function requeryPayment(Payment $payment)
    {
        $data = json_decode($payment->payment_detail, true);

        $client = new GuzzleHttp\Client(['base_uri' => $this->requeryUrl]);

        $response = $client->request('GET', '', ['query' => [
            'MerchantCode' => $data['payment_info']['MerchantCode'],
            'RefNo' => $data['payment_info']['RefNo'],
            'Amount' => $data['payment_info']['Amount']
        ], ['stream' => true]]);

        $body = $response->getBody()->read(1024);

        $results = [];

        $results['success'] = ($body != '00') ? 0 : 1;

        $results['data'] = $body;

        return $results;
    }

    /**
     * @param Request $request
     * @param $isBackendCall
     * @return array
     */
    public function processCallback(Request $request, $isBackendCall)
    {
        //Get Master Data ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('epp_payment_approval_status', 'epp_payment_document_status'));

        //Get Pending status ID
        $approvalStatus = array_change_key_case($settingsData['epp_payment_approval_status']
            ->pluck('id','title')->toArray());

        $approvedId = $approvalStatus[$this->approvalStatusConfigCodes['approved']];

        $declinedId = $approvalStatus[$this->approvalStatusConfigCodes['declined']];

        //Get Document status ID
        $documentStatus = array_change_key_case($settingsData['epp_payment_document_status']
            ->pluck('id','title')->toArray());

        $processDocumentId = $documentStatus[$this->docStatusConfigCodes['p']];

        $voidDocumentId = $documentStatus[$this->docStatusConfigCodes['v']];

        //@todo we need to ensure the source of the caller is from ipay88
        $paymentId = explode('-', $request->get('RefNo'))[1];

        $status = $request->get('Status');

        $payment = Payment::findOrFail($paymentId);

        $response = [];

        if($payment){
            //find out if the payment is success or fail
            $response['success'] = ($status == '1') ? true : false;
        }

        //if this is a backend call, it should not show anything but RECEIVEOK
        if($isBackendCall){
            $response['printout'] = 'RECEIVEOK';
        }

        $responseData = $request->all();

        $tenure = NULL;

        if(isset($responseData['plan'])){
            $tenure = $responseData['plan'];
        }

        if(isset($responseData['xfield1'])){
            $tenure = $responseData['xfield1'];
        }

        if(isset($this->isEPPOnline) && $this->isEPPOnline){
            $eppParameter = [
                'doc_status' => ($status == '1') ? $processDocumentId : $voidDocumentId,
                'tenure' => $tenure,
                'card_type' => '',
                'card_holder_name' => '',
                'card_number' => '',
                'cvv_code' => '',
                'card_expiry_date' => '',
                'approval_code' => '',
                'approval_status' => ($status == '1') ? $approvedId : $declinedId,
                'approved_by' => '',
                'approved_date' => date('Y-m-d'),
                'converted_by' => '',
                'converted_date' => ''
            ];
        } else {
            $eppParameter = [];
        }

        $response['data'] = array_merge($responseData, $eppParameter);

        return $response;
    }

    /**
     * We will need to validate certain info that is needed within the payment method
     */
    protected function validateInfo()
    {
        foreach($this->requiredFields as $fieldName => $field){
            if(!isset($this->info[$fieldName]) || $this->info[$fieldName] == ''){
                throw new \Exception("Field : '".$fieldName."' is needed");
            }

            if($fieldName == 'currency_code' && !in_array($this->info['currency_code'], $this->supportedCurrencyCodes)){
                throw new \Exception("Currency Not supported");
            }
        }
    }
}