<?php

return [
    /**
     * Fixed ipay88 values
     */
    'supported_currency_codes' => array(
        'PHP'
    ),

    //required field for this payment gateway
    // key_used_by_us => key_passed_to_payment_gateway
    'required_fields' => array(
        'amount' => 'Amount',
        'currency_code' => 'Currency',
        'reference_no' => 'RefNo', //string, 20 maximum
        'product_desc' => 'ProdDesc',
        'user_email' => 'UserEmail',
        'user_name' => 'UserName',
        'response_url' => 'ResponseURL',
        'backend_url' => 'BackendURL',
    ),

    'optional_fields' => array(
        'user_contact'=> 'UserContact',
        'remark' => 'Remark',
        'transaction_id' => 'TransId',
        'auth_code' => 'AuthCode',
        'error_desc' => 'ErrorDesc',
        'creditcard_name' => 'CCName',
        'creditcard_num' => 'CCNo',
        'creditcard_issue_bank' => 'S_bankname',
        'creditcard_issue_country' => 'S_country',
        'payment_id' => 'PaymentId'
    ),

    // This is a payment_id for specific payment gateway
    'payment_id_lists' => array(
        'credit_card' => 1
    ),
    'required_inputs' => array(
        'amount' => 'amount'
    ),
    'payment_url' => 'https://payment.ipay88.com.ph/epayment/entry.asp',
    'sandbox_url' => 'https://sandbox.ipay88.com.ph/epayment/entry.asp',
    'payment_requery_url' => 'https://payment.ipay88.com.ph/epayment/enquiry.asp',
    'merchant_key' => env('PH_IPAY88_MKEY'),
    'merchant_code' => env('PH_IPAY88_MCODE'),
    'sandbox_mode' => env('PH_IPAY88_SANDBOX', 0),
    'default_payment_id' => 1
];