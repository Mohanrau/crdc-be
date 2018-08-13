<?php

return [
    'aeon_gpg_secure_key' => env('MY_AEON_GPG_SECURE_KEY'),
    'aeon_gpg_passphrase_path' => env('MY_AEON_GPG_PASSPHRASE_PATH'),
    'aeon_upload_file_directory' => env('MY_AEON_UPLOAD_FILE_DIRECTORY'),
    'aeon_download_file_directory' => env('MY_AEON_DOWNLOAD_FILE_DIRECTORY'),
    'aeon_historical_file_directory' => env('MY_AEON_HISTORICAL_FILE_DIRECTORY'),
    'aeon_request_file_name_prefix' => env('MY_AEON_REQUEST_FILE_NAME_PREFIX'),
    'aeon_request_file_name_suffix' => env('MY_AEON_REQUEST_FILE_NAME_SUFFIX'),
    'aeon_response_file_name_prefix' => env('MY_AEON_RESPONSE_FILE_NAME_PREFIX'),
    'aeon_response_file_name_suffix' => env('MY_AEON_RESPONSE_FILE_NAME_SUFFIX'),
    'required_inputs' => array(
        'agent_code' => 'agent code',
        'amount' => 'amount'
    ),
];