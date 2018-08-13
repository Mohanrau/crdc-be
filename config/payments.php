<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment API credentials and details
    |--------------------------------------------------------------------------
    |
    | Storing all the details of each payment gateway here
    |
    */

    'malaysia' => [
        'ipay88' => [
            'merchant_key' => env('MY_IPAY88_MKEY', '5Mb154IrY8'),
            'merchant_code' => env('MY_IPAY88_MCODE', 'M08669_S0002')
        ]
    ]
];
