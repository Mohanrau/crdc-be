<?php

return [
    'supported_countries' => [
        'SG',
        'BN',
        'MY'
    ],

    'yy_integration_status' => [
        'new' => 0,
        'error' => -1,
        'success' => 1,
        'queue' => 2,
        'running' => 3,
        'exclude' => 9
    ],

    'base_api_url' => env('YY_REST_API_URL'),
    'data_source' => env('YY_UAP_DATASOURCE'),
    'username' => env('YY_UAP_USERCODE'),
    'password' => env('YY_UAP_PASSWORD'),
    'source_type' => 'NIBS',
    'auth_api' => [
        'path' => env('YY_AUTH_URL')
    ],
    'sales_api'=> [
        'path' => env('YY_SALES_ORDER_URL')
    ],

    'sales_update_api'=> [
        'path' => env('YY_SALES_UPDATE_URL')
    ],

    'receipt_api' => [
        'path' => env('YY_SALES_RECEIPT_URL')
    ],

    'consignment_api' => [
        'path' => env('YY_CONSIGNMENT_URL')
    ],

    'collection_api' => [
        'path' => env('YY_COLLECTION_URL')
    ],

    'ewallet_api' => [
        'path' => env('YY_EWALLET_URL'),
        'cash_item'=> '0226'
    ],

    'remittance_api' => [
        'path' => env('YY_REMITTANCE_URL')
    ],

    'nc_trn_type' => [
        'online' => '30-Cxx-08',
        'stockist' => '30-Cxx-S01',
        'selfcollect' => '30-Cxx-07',
        'branch' => '30-Cxx-O1', //'30-Cxx-08',//'30-Cxx-O1',
        'receiptar' => 'D2',
        'consignmentdepositrefund' => 'F4-Cxx-C01',
        'collectionsettlement' => 'F4-Cxx-BKFEE', //'D4',
        'paymentsettlement' => 'D5',
        'remittancesettlement' => '36S4',
        'transferOrder' => '5X-Cxx-C01'
    ],

    'nc_virtual_payment_mode' => [
        'preOrder' => 'RBK',
        'peGain' => 'RPEG',
        'eWalletRefund' => 'PML',
        'depositRefund' => 'RCF'
    ],

    'nc_virtual_bank_account' => [
        'MYEG' => 'MYV01',
        'SGEK' => 'SGV01',
        'BNEK' => 'BNV01'
    ],

    'nc_wallet_virtual_bank_account' => [
        'MYEG' => 'MYW01',
        'SGEK' => 'SGW01',
        'BNEK' => 'BNW01'
    ],

    'nc_dummy_code' => [
        'deliveryFee' => 'DUMMY01',
        'adminFee' => 'DUMMY02',
        'otherFee' => 'DUMMY10'
    ],

    'nc_foc_type' => [
        'sac' => '01',
        'promo' => '03',
        'na' => '99'
    ],

    'nc_payment_mode' => [
        'aeon' => 'RML',
        'baiduri' => 'REC',
        'cash' => 'RCA',
        'credit_card' => 'RCC',
        'credit_card_mega' => 'RC2',
        'ctbc' => 'RCC',
        'direct_banking' => 'RBT',
        'discount_voucher' => 'RDV',
        'e-wallet' => 'RWL',
        'enets' => 'REC',
        'epp_moto' => 'REP',
        'epp_online_ipay88' => 'REI',
        'epp_terminal' => 'REP',
        'house_cheque' => 'RCH',
        'ipay88' => 'REC',
        'ips' => 'RCC',
        'mpos' => 'RMP'
    ],

    'nc_branch_customer_code' => [
        'MYEG' => '8000084',
        'SGEK' => '8000087',
        'BNEK' => '8000088'
    ],

    'nc_online_customer_code' => [
        'MYEG' => '8000093',
        'SGEK' => '8000094',
        'BNEK' => '8000095'
    ],

    /** ipay88 IPP bank account number, CIMB too troublesome and account don't want to use */
    'nc_ipay88_epp_bank_account' => [
        '111' => '3201641913',     //PBB
        '179' => '00300590241',    //HLB
        '112' => '514235664285',   //MBB
        '115' => '514235664285',   //AMEX
        '157' => '105-169171-101'  //HSBC
    ],

    'nc_dept_code' => [
        'MYEG' => '00009',//'00025', change to marketing
        'SGEK' => '00032',
        'BNEK' => '00001'
    ],

    'nc_warehouse_entity_code' => [
        'MYEG' => 'MYEW',
        'SGEK' => 'SGEK',
        'BNEK' => 'BNEK'
    ],

    'nc_warehouse_dept_code' => [
        'MYEG' => '00007',
        'SGEK' => '00032',
        'BNEK' => '00001'
    ],

    'nc_type_of_sales' => [
        'branch' => 'B01',
        'stockist' => 'B02',
        'online' => 'B03',
        'collectionSettlement' => 'B03'
    ],

    'nc_stockist_cost_centre' => [
        'MYEG' => '0503',
        'SGEK' => '1101',
        'BNEK' => '1201'
    ],

    'nc_country_warehouse' => [
        'MYEG' => 'DCWH02',
        'SGEK' => 'SGWH01',
        'BNEK' => 'BNWH01'
    ],

    'nc_receipt_dept_code' => [
        'MYEG' => '00009', //'00007', change to marketing
        'SGEK' => '00032',
        'BNEK' => '00001'
    ],

    'nibs_transaction_type' => [
        'sales' => 'CS',
        'cancellation' => 'CN',
        'consignmentOrder' => 'SCO',
        'consignmentReturn' => 'SCOR',
        'ewallet' => 'ECR',
        'consignmentDeposit' => 'SCD',
        'consignmentRefund' => 'SCR',
        'preOrderDeposit' => 'BK'

    ],

    'nc_consignment_warehouse_entity' => [
        'MYEG' => 'MYST',
        'SGEK' => 'SGST',
        'BNEK' => 'BNST'
    ],

    'nc_cc_type' => [
        'aeon' => '',
        'baiduri' => 'CCTS',
        'cash' => '',
        'credit_card' => '',
        'credit_card_mega' => '',
        'ctbc' => 'CCTS',
        'direct_banking' => '',
        'discount_voucher' => '',
        'e-wallet' => '',
        'enets' => 'CCTS',
        'epp_moto' => '',
        'epp_online_ipay88' => 'CCEP',
        'epp_terminal' => '',
        'house_cheque' => '',
        'ipay88' => 'CCECOM',
        'ips' => 'CCTS',
        'mpos' => 'CCMPOS'
    ],

    'yy_state_code' => [
        'MALAYSIA' => [
            'JOHOR' => 'JHR',
            'KEDAH' => 'KDH',
            'KELANTAN' => 'KLT',
            'LABUAN' => 'LAB',
            'LANGKAWI' => 'LAN',
            'MELAKA' => 'MK',
            'NEGERI SEMBILAN' => 'NG',
            'PAHANG' => 'PH',
            'PERAK' => 'PRK',
            'PERLIS' => 'PLS',
            'PULAU PINANG' => 'PG',
            'SABAH' => 'SB',
            'SARAWAK' => 'SWK',
            'SELANGOR' => 'SEL',
            'TERENGGANU' => 'TGN',
            'TIOMAN' => 'TIO',
            'WILAYAH PERSEKUTUAN' => 'WP'
        ]
    ],

    'yy_tax_code' => [
        'MYEG' => 'SSR',
        'SGEK' => 'SSR',
        'BNEK' => 'BNA'
    ],

    'yy_default_tax_code' => 'SSR'
];