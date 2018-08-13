<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],

    ],

    'subpath' => [
        'invoice' => [
            'storage_path' => storage_path('app/public/invoices/'),
            'absolute_url_path' => 'invoices/'
        ],
        'bonuses' => [
            'storage_path' => storage_path('app/public/bonuses/'),
            'absolute_url_path' => 'bonuses/'
        ],
        'credit_note' => [
            'storage_path' => storage_path('app/public/credit_note/'),
            'absolute_url_path' => 'credit_note/'
        ],
        'consignment_note' => [
            'storage_path' => storage_path('app/public/consignment_note/'),
            'absolute_url_path' => 'consignment_note/'
        ],
        'exchange_bill' => [
            'storage_path' => storage_path('app/public/exchange_bill/'),
            'absolute_url_path' => 'exchange_bill/'
        ],
        'giro_payment' => [
            'storage_path' => storage_path('app/public/giro_payment/'),
            'absolute_url_path' => 'giro_payment/'
        ],
        'rejected_payment' => [
            'storage_path' => storage_path('app/public/rejected_payment/'),
            'absolute_url_path' => 'rejected_payment/'
        ],
        'stockists' => [
            'storage_path' => storage_path('app/public/stockists/'),
            'absolute_url_path' => 'stockists/'
        ],
        'sales' => [
            'storage_path' => storage_path('app/public/sales/'),
            'absolute_url_path' => 'sales/'
        ],
    ]
];
