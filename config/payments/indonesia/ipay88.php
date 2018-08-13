<?php

return [
    /**
     * Fixed ipay88 values
     */
    'supported_currency_codes' => array(
        'MYR', 'USD', 'AUD', 'CAD', 'EUR', 'GBP', 'SGD', 'HKD', 'IDR', 'INR', 'PHP', 'THB', 'TWD'
    ),

    //required field for this payment gateway
    // key_used_by_us => key_passed_to_payment_gateway
    'required_fields' => array(
        'amount' => 'Amount',
        'currency_code' => 'Currency',
        'reference_no' => 'RefNo', //string, 30 maximum
        'product_desc' => 'ProdDesc',
        'user_email' => 'UserEmail',
        'user_name' => 'UserName',
        'response_url' => 'ResponseURL',
        'backend_url' => 'BackendURL'
    ),

    'epp_required_fields' => [

    ],

    'optional_fields' => array(
        'user_contact'=> 'UserContact',
        'payment_id' => 'PaymentId',
        'remark' => 'Remark',
        'transaction_id' => 'TransId',
        'auth_code' => 'AuthCode',
        'error_desc' => 'ErrorDesc',
        'creditcard_name' => 'CCName',
        'creditcard_num' => 'CCNo',
        'creditcard_issue_bank' => 'S_bankname',
        'creditcard_issue_country' => 'S_country'
    ),

    // This is a payment_id for specific payment gateway
    'payment_id_lists' => array(
        'credit_card' => 1,
        'mandiri_clickpay' => 4,
        'xl_tunai' => 7,
        'bii_va' => 9,
        'kartuku' => 10,
        'cimbclicks' => 11,
        'mandiri_ecash' => 13,
        'ib_mualamat' => 14,
        't_cash' => 15,
        'indosat_dompetku' => 16,
        'mandiri_atm_automatic' => 17,
        'pay4me' => 22,
        'paypal' => 6 // usd only
    ),
    'required_inputs' => array(
        'amount' => 'amount'
    ),
    'epp_online_required_inputs' => array(
        'plan' => 'plan',
        'payment_id' => 'bank',
        'amount' => 'amount'
    ),
    'payment_url' => 'https://payment.ipay88.co.id/epayment/entry.asp',
    'sandbox_url' => 'https://sandbox.ipay88.co.id/epayment/entry.asp',
    'payment_requery_url' => 'https://payment.ipay88.co.id/epayment/enquiry.asp',
    'merchant_key' => env('ID_IPAY88_MKEY'),
    'merchant_code' => env('ID_IPAY88_MCODE'),
    'epp_merchant_key' => env('ID_IPAY88_EPP_MKEY'),
    'epp_merchant_code' => env('ID_IPAY88_EPP_MCODE'),
    'sandbox_mode' => env('ID_IPAY88_SANDBOX', 0),
    'default_payment_id' => 1
];