<?php
return [
    /*
    |--------------------------------------------------------------------------
    | API settings
    |--------------------------------------------------------------------------
    */

    //api key
    'api-key' => env('API_KEY'),

    // default records count for pagination
    'records' => 20,

    //pagination count per page
    'count-per-page' => 20,

    //image file types for upload
    'pic-file-types' => ['jpg', 'jpeg', 'gif', 'png'],

    //doc file types for upload
    'doc-file-types' => ['doc', 'docx', 'pdf', 'xls','xlsx'],

    //all file type allowed for upload
    'all-file-types' => ['jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'pdf', 'xls','xlsx'],

    //products and kitting sorting order
    'products-kitting-sorting-order' => [
        1 => 'best-selling',
        2 => 'new-to-old',
        3 => 'old-to-new',
        4 => 'price-to-low',
        5 => 'price-to-high',
        6 => 'cv-to-low',
        7 => 'cv-to-high'
    ],

    //delivery order status codes
    'delivery-order-status-codes' => [
        'Hold',
        'Release',
        'Picked',
        'Packed',
        'Shipped',
        'Received'
    ],

    // sale type cv calculation configuration
    // for sales type mapped in config.mappings.sales_types to its relevant config.mappings.cv_acronym
    'sale-type-cvs' => [
        'registration' => ['wp', 'enrol_cv'],
        'member-upgrade' => ['wp', 'enrol_cv'],
        'ba-upgrade' => ['wp', 'enrol_cv'],
        'repurchase' => ['base'],
        'auto-ship' => ['base'],
        'auto-maintenance' => ['amp'],
        'formation' => ['wp', 'amp'],
        'rental' => ['base']
    ],

    // cvs used for cv mechanisms
    'cv-mechanism' => [
        'eligible' => ['wp', 'base', 'stockist_base', 'stockist_wp'],
        'upgrade' => ['enrol_cv'],
        'amp' => ['amp'],
        'welcome_pack' => ['wp'],
        'base' => ['base']
    ],

    // cv requirement
    'cv-requirement' => [
        'inactive-user' => [ 'eligible' => 60 ]
    ],

    //set to Y or N to use working folder for image processing (stamping/resize)
    'uploader-work-enable' => env('UPLOADER_WORK_ENABLE', 'N'),

    //file system for working folder, must be file system of local adaptor
    'uploader-work-disk' => env('UPLOADER_WORK_FILESYSTEM_DISK', 'public'),

    //uploader working file base path to prepend to each file when retrival
    'uploader-work-root' => env('UPLOADER_WORK_ROOT', 'storage/'),

    //uploader working file base path to prepend to each file when retrival
    'uploader-work-path' => env('UPLOADER_WORK_PATH', 'uploads/'),

    //uploader temp file (before save) system driver
    'uploader-temp-disk' => env('UPLOADER_TEMP_FILESYSTEM_DISK', 'public'),

    //uploader temp file (before save) base path to prepend to each file when retrival
    'uploader-temp-root' => env('UPLOADER_TEMP_ROOT', 'storage/'),

    //uploader temp file (before save) base path to prepend to each filetype setting temp_path
    'uploader-temp-path' => env('UPLOADER_TEMP_PATH', 'uploads/'),

    //uploader temp file (before save) base link to prepend to each uploaded file to form fully qualified url
    'uploader-temp-link' => env('UPLOADER_TEMP_LINK', 'http://ielken.lan/'),
    
    //uploader target file (after save) system driver
    'uploader-file-disk' => env('UPLOADER_FILE_FILESYSTEM_DISK', 'public'),

    //uploader target file (after save) base path to prepend to each file when retrival
    'uploader-file-root' => env('UPLOADER_FILE_ROOT', 'storage/'),

    //uploader target file (after save) base path to prepend to each filetype setting file_path
    'uploader-file-path' => env('UPLOADER_FILE_PATH', 'uploads/'),

    //uploader target file (after save) base link to prepend to each uploaded file to form fully qualified url
    'uploader-file-link' => env('UPLOADER_FILE_LINK', 'http://ielken.lan/'),

    //uploader private file link
    'uploader-private-file-link' => env('UPLOADER_PRIVATE_FILE_LINK', 'http://ielken.lan/api/v1/file-download/'),

    //TODO below is detailed information on how to configure the uploader filetype settings.
    /*
        'file_type' => [ //unique key
            'temp_path' => 'string' //the path relative to root of the temp storage, do not starts with '/' or ends with '/'
            'file_path' => 'string' //the path relative to root of the target storage, do not starts with '/' or ends with '/'
            'public' => true|false //true=public(can access file via browser); fales=private (must use console/program)
            'secret_key' => 'string //required if public=false, hash key, run this command in PowerShell [guid]::NewGuid()
            'validity_period' => int //required if public=false, number of seconds a generated link valid to download the file; <= 0 to disable expiry check
            'server_validate' => 'string' //backend validation rules, not limited to mimes checking
            'client_validate' => 'string' //value for quasar uploader component extensions property
            'rename_file' => true|false //true=use driver generated random name, false=use submitted filename, overwrite existing file            
            'resize_image' => [ //string(setting key) or array(each item will generate one additional new file)
                [
                    'prefix' => 'string' //prefix to prepend to file name
                    'suffix' => 'string' //suffix to append to file name
                    'max_eight' => int //maximum height (pixel) of generated image
                    'max_width' => int //maximum width (pixel) of generated image
                    'full_canvas' => true|false //true=exact resolution; false=without padding empty area
                    'empty_color' => [ //color to fill padding empty area, null for transparent
                        'red' => 255, 'green' => 255, 'blue' => 255
                    ]
                ]
            ],
            'water_mark' => [ //string(setting key) or array(watermark will be applied based on order of item in array)
                'filename' => 'string' //fully qualified url to watermark image
                'position_x' => 'string' //horizontal position of the watermark (left, center, right)
                'margin_x' => int //margin (pixel); depend on positionX (left margin, not used, right margin)
                'position_y' => 'string' //vertical position of the watermark (top, middle, bottom)
                'margin_y' => int //margin (pixel); depend on positionY (top margin, not used, bottom margin)
                'opacity' => int //[0~100]; 0 = fully transparent, 100 = fully opaque
                'shrink_to_fit' => bool //true to shrink oversized water mark before stamp to the image
                'stretch_to_fit' => bool //true to stretch (aspect ratio is preserved) water mark before stamp to the image
            ]
        ]
    */
    'uploader' => [
        'smart_library_thumbnail' => [
            'temp_path' => 'smart_library/thumbnail/temp', 
            'file_path' => 'smart_library/thumbnail',
            'public' => true,
            'secret_key' => '',
            'validity_period' => 0,
            'server_validate' => 'required|file|mimes:jpg,jpeg,png',
            'client_validate' => '.jpg,.jpeg,.png',
            'rename_file' => true,
            'resize_image' => [],
            'water_mark' => []
        ],
        'smart_library_file' => [
            'temp_path' => 'smart_library/file/temp', 
            'file_path' => 'smart_library/file',
            'public' => true,
            'secret_key' => '',
            'validity_period' => 300,
            'server_validate' => 'required|file',
            'client_validate' => '.*',
            'rename_file' => true,
            'resize_image' => [],
            'water_mark' => []
        ],
        'member_ic_passport' => [
            'temp_path' => 'member/ic_passport/temp', 
            'file_path' => 'member/ic_passport',
            'public' => false,
            'secret_key' => 'b5887977-ccf4-4351-854a-637dcaf01c4d',
            'validity_period' => 300,
            'server_validate' => 'required|file|mimes:jpg,jpeg,png',
            'client_validate' => '.jpg,.jpeg,.png',
            'rename_file' => true,
            'resize_image' => [],
            'water_mark' => 'uploader.generic_water_mark'
        ],
        'member_avatar' => [
            'temp_path' => 'member/avatar/temp',
            'file_path' => 'member/avatar',
            'public' => true,
            'secret_key' => '',
            'validity_period' => 0,
            'server_validate' => 'required|file|mimes:jpg,jpeg,png',
            'client_validate' => '.jpg,.jpeg,.png',
            'rename_file' => true,
            'resize_image' => [],
            'water_mark' => []
        ],
        'product_category_web_image' => [
            'temp_path' => 'product_category/web_image/temp', 
            'file_path' => 'product_category/web_image',
            'public' => true,
            'secret_key' => '',
            'validity_period' => 0,
            'server_validate' => 'required|file|mimes:jpg,jpeg,png',
            'client_validate' => '.jpg,.jpeg,.png',
            'rename_file' => true,
            'resize_image' => [],
            'water_mark' => []
        ],
        'product_category_mobile_image' => [
            'temp_path' => 'product_category/mobile_image/temp', 
            'file_path' => 'product_category/mobile_image',
            'public' => true,
            'secret_key' => '',
            'validity_period' => 0,
            'server_validate' => 'required|file|mimes:jpg,jpeg,png',
            'client_validate' => '.jpg,.jpeg,.png',
            'rename_file' => true,
            'resize_image' => [],
            'water_mark' => []
        ],
        'product_standard_image' => [
            'temp_path' => 'product/standard_image/temp', 
            'file_path' => 'product/standard_image',
            'public' => true,
            'secret_key' => '',
            'validity_period' => 0,
            'server_validate' => 'required|file|mimes:jpg,jpeg,png',
            'client_validate' => '.jpg,.jpeg,.png',
            'rename_file' => true,
            'resize_image' => 'uploader.product_resize_image',
            'water_mark' => []
        ],
        'product_kitting_image' => [
            'temp_path' => 'product/kitting_image/temp', 
            'file_path' => 'product/kitting_image',
            'public' => true,
            'secret_key' => '',
            'validity_period' => 0,
            'server_validate' => 'required|file|mimes:jpg,jpeg,png',
            'client_validate' => '.jpg,.jpeg,.png',
            'rename_file' => true,
            'resize_image' => 'uploader.product_resize_image',
            'water_mark' => []
        ],
        'ewallet_audit_file' => [
            'temp_path' => 'ewallet/audit_file/temp', 
            'file_path' => 'ewallet/audit_file',
            'public' => false,
            'secret_key' => '41ca8127-4490-4c00-8f84-86151e7bb035',
            'validity_period' => 300,
            'server_validate' => 'required|file|mimes:xls,xlsx',
            'client_validate' => '.xls,.xlsx',
            'rename_file' => true,
            'resize_image' => [],
            'water_mark' => []
        ],
        'ewallet_giro_payment_file' => [
            'temp_path' => 'ewallet/giro_payment_file/temp',
            'file_path' => 'ewallet/giro_payment_file',
            'public' => false,
            'secret_key' => '8d862c28-be52-491f-bf36-62f88ad2c910',
            'validity_period' => 300,
            'server_validate' => 'required|file|mimes:xls,xlsx',
            'client_validate' => '.xls,.xlsx',
            'rename_file' => true,
            'resize_image' => [],
            'water_mark' => []
        ],
        'ewallet_rejected_payment_file' => [
            'temp_path' => 'ewallet/rejected_payment_file/temp',
            'file_path' => 'ewallet/rejected_payment_file',
            'public' => false,
            'secret_key' => '13fd362c-bcff-4d97-aefa-e8282f0986d1',
            'validity_period' => 300,
            'server_validate' => 'required|file|mimes:xls,xlsx,csv,txt',
            'client_validate' => '.xls,.xlsx,.csv',
            'rename_file' => true,
            'resize_image' => [],
            'water_mark' => []
        ]
    ],

    'logo_url' => "https://nibsprod.s3.ap-southeast-1.amazonaws.com/bonus/report_image/logo.png",

    'footer_url' => "https://nibsprod.s3.ap-southeast-1.amazonaws.com/bonus/report_image/footer.jpg",

    // Guest Login Settings---------------------------------------------------------------------------------------------
    'guest' => [
        'email' => env('GUEST_USER_EMAIL', 'guest@elken.com')
    ],

    'yy-integration' => env('YY_INTEGRATION', 'N')
];