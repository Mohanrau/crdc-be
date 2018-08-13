<?php

return [
    /**
     * Fixed ENETS values
     */
    'supported_currency_codes' => array(
        'SGD'
    ),

    //required field for this payment gateway
    // key_used_by_us => key_passed_to_payment_gateway
    'required_fields' => array(
        'merchant_code' => 'netsMid',
        'amount' => 'txnAmount',
        'reference_no' => 'merchantTxnRef', //string, 20 maximum
        'backend_url' => 's2sTxnEndURL',
        'response_url' => 'b2sTxnEndURL',
        'submission_mode' => 'submissionMode',
        'payment_type' => 'paymentType',
        'payment_mode' => 'paymentMode',
        'client_type' => 'clientType',
        'currency_code' => 'currencyCode',
        'merchant_transaction_time' => 'merchantTxnDtm',
        'merchant_timezone' => 'merchantTimeZone',
        'nets_mid_indicator' => 'netsMidIndicator'
    ),
    'required_inputs' => array(
        'amount' => 'amount'
    ),
    'payment_url' => 'https://www2.enets.sg/GW2/TxnReqListenerToHost',
    'sandbox_url' => 'https://uat2.enets.sg/GW2/TxnReqListenerToHost',
    'payment_requery_url' => 'https://api.nets.com.sg/GW2/TxnQuery',
    'sandbox_requery_url' => 'https://uat-api.nets.com.sg:9065/GW2/TxnQuery',
    'api_key' => env('SG_ENETS_API_KEY', '154eb31c-0f72-45bb-9249-84a1036fd1ca'),
    'secret_key' => env('SG_ENETS_SECRET_KEY', '38a4b473-0295-439d-92e1-ad26a8c60279'),
    'merchant_code' => env('SG_ENETS_MCODE', 'UMID_877772003'),
    'sandbox_mode' => env('SG_ENETS_SANDBOX', 0)
];