<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Template for use in settings -> uploader
    |--------------------------------------------------------------------------
    */
    'generic_water_mark' => [
        [
            'filename' => 'https://s3-ap-southeast-1.amazonaws.com/nibsprod/watermark/img-watermark2.png',
            'position_x' => 'center',
            'margin_x' => 0,
            'position_y' => 'middle',
            'margin_y' => 0,
            'opacity' => 100,
            'shrink_to_fit' => true,
            'stretch_to_fit' => true
        ],
    ],

    'generic_resize_image' => [
        [
            'prefix' => '',
            'suffix' => '_thumbnail',
            'max_height' => 80,
            'max_width' => 100,
            'full_canvas' => true,
            'background_color' => [
                'red' => 255, 'green' => 255, 'blue' => 255
            ]
        ],
        [
            'prefix' => '',
            'suffix' => '_small',
            'max_height' => 45,
            'max_width' => 55,
            'full_canvas' => true,
            'background_color' => [
                'red' => 255, 'green' => 255, 'blue' => 255
            ]
        ],
        [
            'prefix' => '',
            'suffix' => '_medium',
            'max_height' => 180,
            'max_width' => 240,
            'full_canvas' => true,
            'background_color' => [
                'red' => 255, 'green' => 255, 'blue' => 255
            ]
        ],
        [
            'prefix' => '',
            'suffix' => '_large',
            'max_height' => 360,
            'max_width' => 480,
            'full_canvas' => true,
            'background_color' => [
                'red' => 255, 'green' => 255, 'blue' => 255
            ]
        ]
    ],

    'product_resize_image' => [
        [
            'prefix' => '',
            'suffix' => '_small',
            'max_height' => 160,
            'max_width' => 160,
            'full_canvas' => true,
            'background_color' => [
                'red' => 255, 'green' => 255, 'blue' => 255
            ]
        ],
        [
            'prefix' => '',
            'suffix' => '_medium',
            'max_height' => 400,
            'max_width' => 400,
            'full_canvas' => true,
            'background_color' => [
                'red' => 255, 'green' => 255, 'blue' => 255
            ]
        ],
        [
            'prefix' => '',
            'suffix' => '_large',
            'max_height' => 600,
            'max_width' => 600,
            'full_canvas' => true,
            'background_color' => [
                'red' => 255, 'green' => 255, 'blue' => 255
            ]
        ]
    ]
];