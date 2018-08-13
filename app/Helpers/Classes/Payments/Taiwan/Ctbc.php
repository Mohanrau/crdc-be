<?php
namespace App\Helpers\Classes\Payments\Taiwan;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Helpers\Classes\Payments\Payment as PaymentClass;
use App\Helpers\Classes\Payments\CtbcMacHelper;
use App\Models\Payments\Payment;

class Ctbc extends PaymentClass
{
    protected
        $merchantCode,
        $merchantId,
        $merchantName,
        $terminalId,
        $merchantKey,
        $requiredFields,
        $paymentUrl,
        $sandboxUrl,
        $isSandboxEnvironment,
        $ctbcMacHelper;

    public function __construct(CtbcMacHelper $ctbcMacHelper)
    {
        parent::__construct();

        $this->ctbcMacHelper = $ctbcMacHelper;

        $configPathPrefix = 'payments.taiwan.ctbc.';

        //CTBC Varaible Define
        $this->merchantCode = config($configPathPrefix.'merchant_code');
        $this->merchantId = config($configPathPrefix.'merchant_id');
        $this->merchantName = config($configPathPrefix.'merchant_name');
        $this->terminalId = config($configPathPrefix.'terminal_id');
        $this->merchantKey = config($configPathPrefix.'merchant_key');
        $this->requiredFields = config($configPathPrefix.'required_fields');
        $this->paymentUrl = config($configPathPrefix.'payment_url');
        $this->sandboxUrl = config($configPathPrefix.'sandbox_url');
        $this->isSandboxEnvironment = config($configPathPrefix.'sandbox_mode');

        //Parent Variable Define
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

        $info['merchant_name'] = $this->merchantName;

        $info['merchant_id'] = $this->merchantId;

        $info['terminal_id'] = $this->terminalId;

        $info['merchant_key'] = $this->merchantKey;

        $info['tx_type'] = "0";

        $info['option'] = "1";

        $info['auto_cap'] = "1";

        $info['customize'] = "1";

        $info['debug'] = "0";

        //Replace Special Character to Unique Character
        $info['reference_no'] = str_replace("-","", $info['reference_no']);

        // ensure correctness
        $this->amount = $this->info['amount'] = number_format($info['amount'], 0, '.', '');

        $this->info = $info;

        try {
            $this->validateInfo();
        } catch (Exception $e){
            throw $e;
        }

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
        $macString = $this->ctbcMacHelper
            ->auth_in_mac(
                $this->info['merchant_id'],
                $this->info['terminal_id'],
                $this->info['reference_no'],
                $this->info['amount'],
                $this->info['tx_type'],
                $this->info['option'],
                $this->info['merchant_key'],
                $this->info['merchant_name'],
                $this->info['response_url'],
                $this->info['product_desc'],
                $this->info['auto_cap'],
                $this->info['customize'],
                $this->info['debug']
            );

        $urlEnc = $this->ctbcMacHelper
            ->get_auth_urlenc(
                $this->info['merchant_id'],
                $this->info['terminal_id'],
                $this->info['reference_no'],
                $this->info['amount'],
                $this->info['tx_type'],
                $this->info['option'],
                $this->info['merchant_key'],
                $this->info['merchant_name'],
                $this->info['response_url'],
                $this->info['product_desc'],
                $this->info['auto_cap'],
                $this->info['customize'],
                $macString,
                $this->info['debug']
            );

        $this->formData = array(
            'form_attributes' => array(
                'method' => 'post',
                'action' => ($this->isSandboxEnvironment) ?
                            $this->sandboxUrl: $this->paymentUrl
            ),
            'form_inputs' => [
                'merID' => $this->merchantCode,
                'URLEnc' => $urlEnc
            ]
        );
    }

    /**
     * Insert payment transaction information
     *
     */
    public function insertPaymentTransactionInfo()
    {
        $paymentId = explode('salespayment',$this->info['reference_no'])[1];

        $payment = Payment::findOrFail($paymentId);

        if($payment){

            $paymentInfo = [
                'merchant_name' => $this->info['merchant_name'],
                'merchant_id' => $this->info['merchant_id'],
                'terminal_id' => $this->info['terminal_id'],
                'merchant_key' => $this->info['merchant_key'],
                'response_url' => $this->info['response_url'],
                'reference_no' => $this->info['reference_no'],
                'amount' => $this->info['amount'],
                'product_desc' => $this->info['product_desc'],
                'tx_type' => $this->info['tx_type'],
                'option' => $this->info['option'],
                'auto_cap' => $this->info['auto_cap'],
                'customize' => $this->info['customize'],
                'debug' => $this->info['debug']
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

        $encRes = $requestInputs['URLResEnc'];

        $merchantKey = $this->merchantKey;

        $debug = "0";

        $encArray = $this->ctbcMacHelper->gendecrypt($encRes, $merchantKey, $debug);

        $status = isset($encArray['status']) ? $encArray['status'] : "";

        $errCode = isset($encArray['errcode']) ? $encArray['errcode'] : "";

        $authCode = isset($encArray['authcode']) ? $encArray['authcode'] : "";

        $authAmt = isset($encArray['authamt']) ? $encArray['authamt'] : "";

        $lidm = isset($encArray['lidm']) ? $encArray['lidm'] : "";

        $offsetAmt = isset($encArray['offsetamt']) ? $encArray['offsetamt'] : "";

        $originalAmt = isset($encArray['originalamt']) ? $encArray['originalamt'] : "";

        $utilizedPoint = isset($encArray['utilizedpoint']) ? $encArray['utilizedpoint'] : "";

        $option = isset($encArray['numberofpay']) ? $encArray['numberofpay'] : "";

        $last4digitPan = isset($encArray['last4digitpan']) ? $encArray['last4digitpan'] : "";

        $pidResult= isset($encArray['pidResult']) ? $encArray['pidResult'] : "";

        $cardNumber = isset($encArray['CardNumber']) ? $encArray['CardNumber'] : "";

        $macString = $this->ctbcMacHelper
            ->auth_out_mac(
                $status,
                $errCode,
                $authCode,
                $authAmt,
                $lidm,
                $offsetAmt,
                $originalAmt,
                $utilizedPoint,
                $option,
                $last4digitPan,
                $merchantKey,
                $debug
            );

        $response = [
            'success' => false,
            'data' => $encArray
        ];

        if ($macString == $encArray['outmac']){

            if($status == 1 && $errCode == 0){

                $paymentId = explode('salespayment', $lidm)[1];

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
     * @return array
     */
    public function requeryPayment(Payment $payment)
    {
        //CTBC Not Support Requery Payment Function
        $results = [];

        $results['success'] = 0;

        return $results;
    }
}