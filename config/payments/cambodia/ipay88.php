<?php

return [
    /**
     * Fixed ipay88 values
     */
    'supported_currency_codes' => array(
        'USD'
    ),

    //required field for this payment gateway
    // key_used_by_us => key_passed_to_payment_gateway
    'required_fields' => array(
        'amount' => 'Amount',
        'currency_code' => 'Currency',
        'reference_no' => 'RefNo', //string, 30 maximum
        'product_desc' => 'ProdDesc',
        'user_contact'=> 'UserContact',
        'user_email' => 'UserEmail',
        'user_name' => 'UserName',
        'response_url' => 'ResponseURL',
        'backend_url' => 'BackendURL',
        'payment_id' => 'PaymentId'
    ),

    'optional_fields' => array(
        'remark' => 'Remark',
        'transaction_id' => 'TransId',
        'auth_code' => 'AuthCode',
        'error_desc' => 'ErrorDesc',
        'creditcard_name' => 'CCName',
        'creditcard_num' => 'CCNo',
        'creditcard_issue_bank' => 'S_bankname',
        'creditcard_issue_country' => 'S_country',
    ),

    // This is a payment_id for specific payment gateway
    'payment_id_lists' => array(
        'credit_card' => 1
    ),

    'payment_url' => ' https://payment.ipay88.com.kh/epayment/entry.asp',
    //'sandbox_url' => 'https://sandbox.ipay88.co.id/epayment/entry.asp', // doesnt have sandbox
    'payment_requery_url' => 'https://payment.ipay88.com.kh/epayment/enquiry.asp',
    'merchant_key' => env('ID_IPAY88_MKEY'),
    'merchant_code' => env('ID_IPAY88_MCODE'),
    'sandbox_mode' => env('ID_IPAY88_SANDBOX', 0),
    'default_payment_id' => 1
];