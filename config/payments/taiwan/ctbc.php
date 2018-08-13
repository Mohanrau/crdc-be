<?php

return [
    'supported_currency_codes' => [
        'TWD',
    ],
    'required_fields' => [
        'merchant_name' => 'merchantName',
        'merchant_id' => 'merchantID',
        'terminal_id' => 'terminalID',
        'merchant_key' => 'key',
        'response_url' => 'authResURL',
        'reference_no' => 'lidm',
        'amount' => 'purchAmt',
        'product_desc' => 'orderDetail',
        'tx_type' => 'txType',
        'option' => 'option',
        'auto_cap' => 'autoCap',
        'customize' => 'customize',
        'debug' => 'debug'
    ],
    'required_inputs' => [
        'amount' => 'amount'
    ],
    'payment_url' => env('TW_CTBC_PAYMENT_URL'),
    'sandbox_url' => env('TW_CTBC_SANDBOX_URL'),
    'sandbox_mode' => env('TW_CTBC_SANDBOX_MODE', 0),
    'merchant_name' => env('TW_CTBC_MERCHANT_NAME'),
    'merchant_code' => env('TW_CTBC_MERCHANT_CODE'),
    'merchant_id' => env('TW_CTBC_MERCHANT_ID'),
    'terminal_id' => env('TW_CTBC_TERMINAL_ID'),
    'merchant_key' => env('TW_CTBC_MERCHANT_KEY'),
];