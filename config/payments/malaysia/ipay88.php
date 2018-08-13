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

    // we will override the required field with this
    'epp_required_fields' => array(

    ),

    // This is a payment_id for specific payment gateway
    'payment_id_lists' => array(
        'credit_card' => 2,
        'maybank2u' => 6,
        'alliance_online' => 8,
        'am_online' => 10,
        'rhb_online' => 14,
        'hongleong_online' => 15,
        'cimb' => 20,
        'web_cash' => 22,
        'paypal' => 48,
        'celcom_aircash' => 100,
        'bank_rakyat_online' => 102,
        'affin_online' => 103,
        'bank_islam' => 134,
        'uob' => 152,
        'bank_mualamat' => 166,
        'ocbc' => 167,
        'standard_chartered_bank' => 168,

        //EPP based payment
        'public_bank_zero_interest' => 111,
        'maybank_ezypay' => 112,
        'maybank_american_express_ezypay' => 115,
        'hsbc_instalment_plan' => 157,
        'cimb_instalment_plan' => 174,
        'hongleong_instalment_plan' => 179
    ),
    'required_inputs' => array(
        'payment_id' => 'payment method',
        'amount' => 'amount'
    ),
    'epp_online_required_inputs' => array(
        'plan' => 'plan',
        'payment_id' => 'bank',
        'amount' => 'amount'
    ),
    'payment_url' => 'https://www.mobile88.com/epayment/entry.asp',
    'sandbox_url' => 'https://www.mobile88.com/epayment/entry.asp', //no sandbox url, same as live
    'payment_requery_url' => 'https://www.mobile88.com/ePayment/enquiry.asp',
    'merchant_key' => env('MY_IPAY88_MKEY'),
    'merchant_code' => env('MY_IPAY88_MCODE'),
    'epp_merchant_key' => env('MY_IPAY88_EPP_MKEY'),
    'epp_merchant_code' => env('MY_IPAY88_EPP_MCODE'),
    'sandbox_mode' => env('ID_IPAY88_SANDBOX', 0),
    'default_payment_id' => 157
];