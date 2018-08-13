<?php

return [
    'supported_currency_codes' => [
        'BND'
    ],

    //required field for this payment gateway
    // key_used_by_us => key_passed_to_payment_gateway
    'required_fields' => [
        'version' => 'vpc_Version',
        'command' => 'vpc_Command',
        'merchant_code' => 'vpc_AccessCode',
        'reference_no' => 'vpc_MerchTxnRef',
        'merchant_id'=> 'vpc_Merchant',
        'order_info' => 'vpc_OrderInfo',
        'amount' => 'vpc_Amount',
        'locale' => 'vpc_Locale',
        'return_url' => 'vpc_ReturnURL',
        'secure_hash' => 'vpc_SecureHash',
        'secure_hash_type' => 'vpc_SecureHashType'
    ],

    'optional_fields' => [
        'currency' => 'vpc_Currency', //default to brunei dollar
        'return_auth_response' => 'vpc_ReturnAuthResponseData' // Y or N
    ],

    'default_values' => [
        'version' => 1,
        'command' => 'pay',
        'currency' => 'BND',
        'return_auth_response' => 'Y',
        'locale' => 'en',
        'secure_hash_type' => 'SHA256'
    ],
    'required_inputs' => array(
        'amount' => 'amount'
    ),
    'payment_url' => 'https://migs.mastercard.com.au/vpcpay',
    'sandbox_url' => 'https://migs-mtf.mastercard.com.au/vpcpay',
    'shared_secret' => env('BN_BAIDURI_HASH_SECRET'),
    'payment_requery_url' => '',
    'merchant_id' => env('BN_BAIDURI_MID'),
    'merchant_code' => env('BN_BAIDURI_MCODE'),
    'sandbox_mode' => env('BN_BAIDURI_SANDBOX', 0)
];