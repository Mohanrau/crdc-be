<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Template for use in settings -> uploader
    |--------------------------------------------------------------------------
    */

    'api_key' => env('CLOUD_SMS_API_KEY'),

    'api_passname' => env('CLOUD_SMS_PASSNAME'),

    'api_password' => env('CLOUD_SMS_PASSWORD'),

    'api_url' => env('CLOUD_SMS_URL'),

    'api_success_response' => 'Message Sent',

    'api_error_response' => [
        '-21' => 'country_code is invalid or mobile_no invalid',
        '-23' => 'passname is invalid',
        '-24' => 'password is invalid',
        '-25' => 'Account Suspended',
        '-26' => 'Mobile empty',
        '-30' => 'content_type is invalid or content_type not corrrect',
        '-32' => 'Message content empty',
        '-40' => 'sender id must be alphanumeric',
        '-60' => 'insufficient prepaid credit or zero balance left'
    ]
];