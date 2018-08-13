<?php

return [
    /*
    |--------------------------------------------------------------------------
    | E-Wallet settings
    |--------------------------------------------------------------------------
    */

    "transaction_number_start" => 00000000001,

    "giro_bank_payment_batch_start" => 000000001,

    "giro_rejected_payment_file_no" => 00000000001,

    "giro_rejected_unique_value_col" => 'AZ',

    "giro_rejected_unique_value_row" => '999',

    "giro_rejected_unique_value_cell" => 'AZ999',

    'giro_rejected_file_columns' => [
        "ibo_id",
        "ibo_name",
        "rejected_amount",
        "registered_country_currency",
        "registered_country_total",
        "remarks"
    ],

    'giro_types' => [
        "MY" => [
            "my" => "MY",
            "mpay" => "MPAY"
        ],
        "TH" => [
            "hsbc" => "HSBC"
        ],
        "BN" => [
            "bn" => "BN"
        ],
        "SG" => [
            "sg" => "SG"
        ],
        "TW" => [
            "tw" => "TW"
        ],
        "HK" => [
            "hk" => "HK"
        ],
        "KH" => [
            "anz_online" => "ANZ ONLINE",
            "wings" => "WINGS"
        ],
        "PH" => [
            "bdo_online" => "BDO ONLINE",
            "bdo_cash_card" => "BDO CASH CARD",
            "dragonpay" => "DRAGONPAY",
            "hsbc_online" => "HSBC ONLINE"
        ],
        "ID" => [
            "cimb" => "CIMB",
            "bca" => "BCA",
            "hsbc" => "HSBC"
        ]
    ],

    'supported_countries' => [
        'SG',
        'BN',
        'MY'
    ]
];