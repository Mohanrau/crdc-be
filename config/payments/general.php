<?php

return [
    'countries_mapping' => [
        'MY' => 'Malaysia',
        'ID' => 'Indonesia',
        'BN' => 'Brunei',
        'PH' => 'Philippines',
        'KH' => 'Cambodia',
        'SG' => 'Singapore',
        'TW' => 'Taiwan',
        'HK' => 'Hongkong',
        'TH' => 'Thailand'
    ],
    'payment_common_required_inputs' => [
        'cash' => [
            'amount' => 'amount'
        ],
        'stockist_card_cash' => [
            'amount' => 'amount'
        ],
        'discount_voucher' => [
            'amount' => 'amount',
            'voucher_number' => 'voucher no.',
            'presented_by' => 'presented by'
        ],
        'mpos' => [
            'amount' => 'amount',
            'approval_code' => 'approval code',
            'terminal_id' => 'terminal id'
        ],
        'direct_banking' => [
            'amount' => 'amount',
            'bank_receipt_no' => 'bank receipt no.'
        ],
        'house_cheque' => [
            'amount' => 'amount',
            'cheque_no' => 'cheque no.'
        ],
        'ewallet' => [
            'amount' => 'amount',
            'ibo_id' => 'ibo id',
            'pin_number' => 'pin number'
        ],
        'epp_terminal' => [
            'issuing_bank' => 'issuing bank',
            'tenure' => 'tenure',
            'amount' => 'amount',
            'approval_code' => 'approval code'
        ],
        'epp_moto' => [
            'amount' => 'amount',
            'issuing_bank' => 'issuing bank',
            'tenure' => 'tenure',
            'cardholder_name' => 'cardholder name',
            'card_number' => 'card number',
            'cvv_code' => 'cvv code',
            'expiry_date_month' => 'expiry date month',
            'expiry_date_year' => 'expiry date year'
        ],
        'credit_card' => [
            'amount' => 'amount',
            'approval_code' => 'approval code'
        ]
    ],
];
