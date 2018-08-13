<?php

return [
    /**
     * Fixed ips values
     */
    'supported_currency_codes' => [
        'CNY', 'USD', 'HKD', 'SGD', 'EUR'
    ],
    //required field for this payment gateway
    // key_used_by_us => key_passed_to_payment_gateway
    'required_fields' => [
        'merchant_code' => 'Mer_code',
        'amount' => 'Amount',
        'currency_code' => 'Currency_Type',
        'reference_no' => 'Billno', //string, 30 maximum
        'order_date' => 'Date', //YYYYMMDD
        'gateway_type' => 'Gateway_Type', // this refers to our payment id lists
        'signature_method' => 'OrderEncodeType',
        'signature' => "SignMD5",
        'order_encode_type' => 'OrderEncodeType',
        'return_encode_type' => 'RetEncodeType',
        'return_type' => 'RetType',
        'server_url' => 'ServerUrl',
        'merchant_url' => 'Merchanturl',
        'language' => 'Lang'
    ],
    'merchant_code_list' => [
        'union_pay' => env('HK_IPS_UNIONPAY_MCODE'), // 01
        'skrill_pay' => env('HK_IPS_SKRILL_MCODE'), // 07
    ],
    'optional_fields' => [
        'language' => 'Lang',
        'merchant_url' => 'Merchanturl',
        'fail_url' => 'FailUrl'
    ],
    'required_inputs' => array(
        'payment_type_id' => 'payment type',
        'amount' => 'amount'
    ),
    // This is a payment_id for specific payment gateway
    'payment_id_lists' => [
        'rmb_credit_card' => "01",
        'international_credit_card' => "02",
        'wechat_pay' => "04",
        'skrill_pay' => "07"
    ],

    'payment_url' => ' https://pay.hkipsec.com/Receiver.aspx',
    'payment_requery_url' => 'https://webservice.hkipsec.com/query.asmx',
    'merchant_cert' => env('HK_IPS_CERT'),
    'merchant_code' => env('HK_IPS_MCODE'),
    'default_payment_id' => '01'
];