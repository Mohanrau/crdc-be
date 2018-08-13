<?php

use Illuminate\Database\Seeder;
use App\Models\
{   Locations\Country,
    Settings\CountryDynamicContent
};

class CountryDynamicContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $taxpayerInfo = [
            [
                "index"=>1,
                "label"=>"Address 1",
                "map"=>"addr1",
                "type"=>"input",
                "order"=>2,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>2,
                "label"=>"Address 2",
                "map"=>"addr2",
                "type"=>"input",
                "order"=>2,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>3,
                "label"=>"Address 3",
                "map"=>"addr3",
                "type"=>"input",
                "order"=>2,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>4,
                "label"=>"Address 4",
                "map"=>"addr4",
                "type"=>"input",
                "order"=>2,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>5,
                "label"=>"postcode",
                "map"=>"postcode",
                "type"=>"input",
                "order"=>3,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>6,
                "label"=>"city",
                "map"=>"city",
                "type"=>"select",
                "key"=>"cities",
                "identifier"=>"cities",
                "order"=>5,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>7,
                "label"=>"country",
                "map"=>"country",
                "type"=>"select",
                "key"=>"countries",
                "trigger"=>"states",
                "order"=>6,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>8,
                "label"=>"state",
                "map"=>"state",
                "type"=>"select",
                "key"=>"states",
                "identifier"=>"states",
                "trigger"=>"cities",
                "order"=>4,
                "value"=>"",
                "id"=>"",
                "required"=>false
            ],
            [
                "index"=>9,
                "label"=>"Tax",
                "type"=>"radio",
                "order"=>1,
                "value"=>"",
                "trigger"=>"disableFields",
                "options"=>[
                    [
                        "label"=>"Yes",
                        "value"=>1
                    ],
                    [
                        "label"=>"No",
                        "value"=>0
                    ]
                ],
                "required"=>false
            ],
            [
                "index"=>10,
                "label"=>"Self Billed Invoice Approved",
                "type"=>"radio",
                "order"=>1,
                "value"=>"",
                "options"=>[
                    [
                        "label"=>"Yes",
                        "value"=>1
                    ],
                    [
                        "label"=>"No",
                        "value"=>0
                    ]
                ],
                "required"=>false
            ],
            [
                "index"=>11,
                "label"=>"Tax Company Name",
                "type"=>"input",
                "order"=>1,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>12,
                "label"=>"Tax Registration No",
                "type"=>"input",
                "order"=>1,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>13,
                "label"=>"Tax Registration Date",
                "type"=>"date",
                "order"=>1,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>14,
                "label"=>"Income Tax No",
                "type"=>"input",
                "order"=>1,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>15,
                "label"=>"Self-Billed Invoice Approval Code",
                "type"=>"input",
                "order"=>1,
                "value"=>"",
                "required"=>false
            ],
            [
                "index"=>16,
                "label"=>"Self-Billed Invoice Approval Date",
                "type"=>"date",
                "order"=>1,
                "value"=>"",
                "required"=>false
            ]
        ];

        $stockistPayoutOptions = [
            "selected" => 1,
            "bank_data" => [
                [
                    "id" => 1,
                    "title" => "BANK",
                    "fields" => [
                        [
                            "autofill" => "details.country.name",
                            "index" => 1,
                            "label" => "Bank Country",
                            "order" => 1,
                            "parent_id" => 1,
                            "readonly" => true,
                            "required" => false,
                            "type" => "input",
                            "value" => ""
                        ],
                        [
                            "identifier" => "countries_bank",
                            "index" => 2,
                            "key" => "banks",
                            "label" => "Bank Name",
                            "order" => 2,
                            "parent_id" => 1,
                            "required" => true,
                            "trigger" => 4,
                            "type" => "select",
                            "value" => ""
                        ],
                        [
                            "autofill" => "details.name",
                            "index" => 3,
                            "label" => "A\\/C Holder Name",
                            "order" => 3,
                            "parent_id" => 1,
                            "required" => true,
                            "type" => "input",
                            "value" => ""
                        ],
                        [
                            "index" => 4,
                            "inherit_column" => "swift_code",
                            "inherit_index" => 2,
                            "label" => "Swift Code",
                            "order" => 4,
                            "parent_id" => 1,
                            "readonly" => true,
                            "required" => false,
                            "type" => "input",
                            "value" => ""
                        ],
                        [
                            "identifier" => "bank_account_type",
                            "index" => 5,
                            "key" => "master",
                            "label" => "Account Type",
                            "order" => 5,
                            "parent_id" => 1,
                            "required" => true,
                            "type" => "masters-select",
                            "value" => ""
                        ],
                        [
                            "index" => 6,
                            "label" => "Bank Account No",
                            "order" => 6,
                            "parent_id" => 1,
                            "required" => true,
                            "type" => "input",
                            "value" => ""
                        ],
                        [
                            "autofill" => "details.ic_passport_number",
                            "index" => 7,
                            "label" => "Bank Account IC",
                            "order" => 7,
                            "parent_id" => 1,
                            "required" => true,
                            "type" => "input",
                            "value" => ""
                        ],
                        [
                            "autofill" => "details.country.code_iso_2",
                            "index" => 8,
                            "label" => "Payout Company",
                            "order" => 8,
                            "parent_id" => 1,
                            "readonly" => true,
                            "required" => false,
                            "type" => "input",
                            "value" => ""
                        ]
                    ]
                ]
            ]
        ];

        $stockistTaxInfo = [
            [
                "index"=>1,
                "label"=>"Address 1",
                "map"=>"addr1",
                "order"=>2,
                "required"=>false,
                "type"=>"input",
                "value"=>""
            ],
            [
                "index"=>2,
                "label"=>"Address 2",
                "map"=>"addr2",
                "order"=>2,
                "required"=>false,
                "type"=>"input",
                "value"=>""
            ],
            [
                "index"=>3,
                "label"=>"Address 3",
                "map"=>"addr3",
                "order"=>2,
                "required"=>false,
                "type"=>"input",
                "value"=>""
            ],
            [
                "index"=>4,
                "label"=>"Address 4",
                "map"=>"addr4",
                "order"=>2,
                "required"=>false,
                "type"=>"input",
                "value"=>""
            ],
            [
                "index"=>5,
                "label"=>"postcode",
                "map"=>"postcode",
                "order"=>3,
                "required"=>false,
                "type"=>"input",
                "value"=>""
            ],
            [
                "identifier"=>"cities",
                "index"=>6,
                "key"=>"cities",
                "label"=>"city",
                "map"=>"city",
                "order"=>5,
                "required"=>false,
                "type"=>"select",
                "value"=>""
            ],
            [
                "index"=>7,
                "key"=>"countries",
                "label"=>"country",
                "map"=>"country",
                "order"=>6,
                "required"=>false,
                "trigger"=>"states",
                "type"=>"select",
                "value"=>""
            ],
            [
                "id"=>"",
                "identifier"=>"states",
                "index"=>8,
                "key"=>"states",
                "label"=>"state",
                "map"=>"state",
                "order"=>4,
                "required"=>false,
                "type"=>"select",
                "value"=>""
            ],
            [
                "index"=>9,
                "label"=>"Tax Auto Generated",
                "options"=>[
                    [
                        "label"=>"Yes",
                        "value"=>1
                    ],
                    [
                        "label"=>"No",
                        "value"=>0
                    ]
                ],
                "order"=>1,
                "required"=>true,
                "type"=>"radio",
                "value"=>""
            ],
            [
                "index"=>10,
                "label"=>"Tax contract signed",
                "options"=>[
                    [
                        "label"=>"Yes",
                        "value"=>1
                    ],
                    [
                        "label"=>"No",
                        "value"=>0
                    ]
                ],
                "order"=>1,
                "required"=>false,
                "type"=>"radio",
                "value"=>""
            ],
            [
                "index"=>11,
                "label"=>"Tax company Name",
                "order"=>1,
                "required"=>false,
                "type"=>"input",
                "value"=>""
            ],
            [
                "index"=>12,
                "label"=>"Tax Registration No",
                "order"=>1,
                "required"=>false,
                "type"=>"input",
                "value"=>""
            ],
            [
                "index"=>13,
                "label"=>"Tax Registration Date",
                "order"=>1,
                "required"=>false,
                "type"=>"date",
                "value"=>""
            ],
            [
                "index"=>15,
                "label"=>"Self-Billed Invoice Approval Code",
                "order"=>1,
                "required"=>false,
                "type"=>"input",
                "value"=>""
            ],
            [
                "index"=>16,
                "label"=>"Self-Billed Invoice Approval Date",
                "order"=>1,
                "required"=>false,
                "type"=>"date",
                "value"=>""
            ]
        ];

        $data = [
            [
                "country_code" => "MY",
                "type" => "address",
                "content" => [
                    [
                        'title'=>'Permanent Address',
                        'fields'=>[
                            [
                                'index'=>1,
                                'label'=>'Address 1',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>1,
                                'value'=>'',
                                'min'=>6,
                                'helper'=>'Address must be greater then 6 character',
                                'required'=>true
                            ],
                            [
                                'index'=>2,
                                'label'=>'Address 2',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>3,
                                'label'=>'Address 3',
                                "map"=>"addr3",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>4,
                                'label'=>'Address 4',
                                "map"=>"addr4",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>5,
                                'label'=>'postcode',
                                "map"=>"postcode",
                                'type'=>'input',
                                'order'=>3,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>6,
                                'label'=>'city',
                                "map"=>"city",
                                'type'=>'select',
                                'key'=>'cities',
                                'identifier'=>'cities',
                                'order'=>5,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>7,
                                'label'=>'country',
                                "map"=>"country",
                                'type'=>'select',
                                'key'=>'countries',
                                'order'=>6,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>8,
                                'label'=>'state',
                                "map"=>"state",
                                'type'=>'select',
                                'key'=>'states',
                                'identifier'=>'states',
                                'trigger'=>'cities',
                                'order'=>4,
                                'value'=>'',
                                'required'=>true
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "MY",
                "type" => "member_payout_options",
                "content" => [
                    "selected" => 1,
                    "bank_data" => [
                        [
                            "id" => 1,
                            "title" => "BANK",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Bank Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Bank Name",
                                    "type" => "select",
                                    "key" => "banks",
                                    "identifier" => "countries_bank",
                                    "trigger" => 4,
                                    "order" => 2,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 3,
                                    "label" => "A/C Holder Name",
                                    "type" => "input",
                                    "order" => 3,
                                    "value" => "",
                                    "autofill" => "details.name",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Swift Code",
                                    "type" => "input",
                                    "order" => 4,
                                    "inherit_index" => 2,
                                    "inherit_column" => "swift_code",
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Account Type",
                                    "type" => "masters-select",
                                    "key" => "master",
                                    "identifier" => "bank_account_type",
                                    "order" => 5,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 6,
                                    "label" => "Bank Account No",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Bank Account IC",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "autofill" => "details.ic_passport_number",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 8,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 8,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 1
                                ]
                            ]
                        ],
                        [
                            "id" => 2,
                            "title" => "MPAY",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Card Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 2
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Card Holder Name",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 2
                                ],
                                [
                                    "index" => 3,
                                    "label" => "Card Number",
                                    "input_type" => "number",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 2
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Card Expiry Date",
                                    "placeholder" => "Month (MM)",
                                    "type" => "custom-select",
                                    "options" => [
                                        [
                                            "label" => "01",
                                            "value" => "01"
                                        ],
                                        [
                                            "label" => "02",
                                            "value" => "02"
                                        ],
                                        [
                                            "label" => "03",
                                            "value" => "03"
                                        ],
                                        [
                                            "label" => "04",
                                            "value" => "04"
                                        ],
                                        [
                                            "label" => "05",
                                            "value" => "05"
                                        ],
                                        [
                                            "label" => "06",
                                            "value" => "06"
                                        ],
                                        [
                                            "label" => "07",
                                            "value" => "07"
                                        ],
                                        [
                                            "label" => "08",
                                            "value" => "08"
                                        ],
                                        [
                                            "label" => "09",
                                            "value" => "09"
                                        ],
                                        [
                                            "label" => "10",
                                            "value" => "10"
                                        ],
                                        [
                                            "label" => "11",
                                            "value" => "11"
                                        ],
                                        [
                                            "label" => "12",
                                            "value" => "12"
                                        ]
                                    ],
                                    "order" => 10,
                                    "value" => "",
                                    "share" => [
                                        "index" => 5,
                                        "pos" => "P"
                                    ],
                                    "required" => false,
                                    "parent_id" => 2
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Card Expiry Year",
                                    "placeholder" => "Year (YYYY)",
                                    "type" => "custom-select",
                                    "options" => [
                                        [
                                            "label" => "2017",
                                            "value" => "17"
                                        ],
                                        [
                                            "label" => "2018",
                                            "value" => "18"
                                        ],
                                        [
                                            "label" => "2019",
                                            "value" => "19"
                                        ],
                                        [
                                            "label" => "2020",
                                            "value" => "20"
                                        ],
                                        [
                                            "label" => "2021",
                                            "value" => "21"
                                        ],
                                        [
                                            "label" => "2022",
                                            "value" => "22"
                                        ],
                                        [
                                            "label" => "2023",
                                            "value" => "23"
                                        ],
                                        [
                                            "label" => "2024",
                                            "value" => "24"
                                        ],
                                        [
                                            "label" => "2025",
                                            "value" => "25"
                                        ],
                                        [
                                            "label" => "2026",
                                            "value" => "26"
                                        ],
                                        [
                                            "label" => "2027",
                                            "value" => "27"
                                        ],
                                        [
                                            "label" => "2028",
                                            "value" => "28"
                                        ]
                                    ],
                                    "order" => 10,
                                    "value" => "",
                                    "share" => [
                                        "index" => 6,
                                        "pos" => "C"
                                    ],
                                    "required" => false,
                                    "parent_id" => 2
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 11,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 2
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "MY",
                "type" => "taxpayer_info",
                "content" => $taxpayerInfo
            ],
            [
                "country_code" => "TH",
                "type" => "address",
                "content" => [
                    [
                        'title'=>'Permanent Address',
                        'fields'=>[
                            [
                                'index'=>1,
                                'label'=>'Address 1',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>1,
                                'value'=>'',
                                'min'=>6,
                                'helper'=>'Address must be greater then 6 character',
                                'required'=>true
                            ],
                            [
                                'index'=>2,
                                'label'=>'Address 2',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>3,
                                'label'=>'Address 3',
                                "map"=>"addr3",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>4,
                                'label'=>'Address 4',
                                "map"=>"addr4",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>5,
                                'label'=>'postcode',
                                "map"=>"postcode",
                                'type'=>'input',
                                'order'=>3,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>6,
                                'label'=>'city',
                                "map"=>"city",
                                'type'=>'select',
                                'key'=>'cities',
                                'identifier'=>'cities',
                                'order'=>5,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>7,
                                'label'=>'country',
                                "map"=>"country",
                                'type'=>'select',
                                'key'=>'countries',
                                'order'=>6,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>8,
                                'label'=>'state',
                                "map"=>"state",
                                'type'=>'select',
                                'key'=>'states',
                                'identifier'=>'states',
                                'trigger'=>'cities',
                                'order'=>4,
                                'value'=>'',
                                'required'=>true
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "TH",
                "type" => "taxpayer_info",
                "content" => $taxpayerInfo
            ],
            [
                "country_code" => "TH",
                "type" => "member_payout_options",
                "content" => [
                    "selected" => 1,
                    "bank_data" => [
                        [
                            "id" => 1,
                            "title" => "BANK",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Bank Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Bank Name",
                                    "type" => "select",
                                    "key" => "banks",
                                    "identifier" => "countries_bank",
                                    "trigger" => 4,
                                    "order" => 2,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 3,
                                    "label" => "A/C Holder Name",
                                    "type" => "input",
                                    "order" => 3,
                                    "value" => "",
                                    "autofill" => "details.name",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Swift Code",
                                    "type" => "input",
                                    "order" => 4,
                                    "inherit_index" => 2,
                                    "inherit_column" => "swift_code",
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Account Type",
                                    "type" => "masters-select",
                                    "key" => "master",
                                    "identifier" => "bank_account_type",
                                    "order" => 5,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 6,
                                    "label" => "Bank Account No",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Bank Account IC",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "autofill" => "details.ic_passport_number",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 8,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 8,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "BN",
                "type" => "address",
                "content" => [
                    [
                        'title'=>'Permanent Address',
                        'fields'=>[
                            [
                                'index'=>1,
                                'label'=>'Address 1',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>1,
                                'value'=>'',
                                'min'=>6,
                                'helper'=>'Address must be greater then 6 character',
                                'required'=>true
                            ],
                            [
                                'index'=>2,
                                'label'=>'Address 2',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>3,
                                'label'=>'Address 3',
                                "map"=>"addr3",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>4,
                                'label'=>'Address 4',
                                "map"=>"addr4",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>5,
                                'label'=>'postcode',
                                "map"=>"postcode",
                                'type'=>'input',
                                'order'=>3,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>6,
                                'label'=>'city',
                                "map"=>"city",
                                'type'=>'select',
                                'key'=>'cities',
                                'identifier'=>'cities',
                                'order'=>5,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>7,
                                'label'=>'country',
                                "map"=>"country",
                                'type'=>'select',
                                'key'=>'countries',
                                'order'=>6,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>8,
                                'label'=>'state',
                                "map"=>"state",
                                'type'=>'select',
                                'key'=>'states',
                                'identifier'=>'states',
                                'trigger'=>'cities',
                                'order'=>4,
                                'value'=>'',
                                'required'=>true
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "BN",
                "type" => "taxpayer_info",
                "content" => $taxpayerInfo
            ],
            [
                "country_code" => "BN",
                "type" => "member_payout_options",
                "content" => [
                    "selected" => 1,
                    "bank_data" => [
                        [
                            "id" => 1,
                            "title" => "BANK",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Bank Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Bank Name",
                                    "type" => "select",
                                    "key" => "banks",
                                    "identifier" => "countries_bank",
                                    "trigger" => 4,
                                    "order" => 2,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 3,
                                    "label" => "A/C Holder Name",
                                    "type" => "input",
                                    "order" => 3,
                                    "value" => "",
                                    "autofill" => "details.name",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Swift Code",
                                    "type" => "input",
                                    "order" => 4,
                                    "inherit_index" => 2,
                                    "inherit_column" => "swift_code",
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Account Type",
                                    "type" => "masters-select",
                                    "key" => "master",
                                    "identifier" => "bank_account_type",
                                    "order" => 5,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 6,
                                    "label" => "Bank Account No",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Bank Account IC",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "autofill" => "details.ic_passport_number",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 8,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 8,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "SG",
                "type" => "address",
                "content" => [
                    [
                        'title'=>'Permanent Address',
                        'fields'=>[
                            [
                                'index'=>1,
                                'label'=>'Address 1',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>1,
                                'value'=>'',
                                'min'=>6,
                                'helper'=>'Address must be greater then 6 character',
                                'required'=>true
                            ],
                            [
                                'index'=>2,
                                'label'=>'Address 2',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>3,
                                'label'=>'Address 3',
                                "map"=>"addr3",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>4,
                                'label'=>'Address 4',
                                "map"=>"addr4",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>5,
                                'label'=>'postcode',
                                "map"=>"postcode",
                                'type'=>'input',
                                'order'=>3,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>6,
                                'label'=>'city',
                                "map"=>"city",
                                'type'=>'select',
                                'key'=>'cities',
                                'identifier'=>'cities',
                                'order'=>5,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>7,
                                'label'=>'country',
                                "map"=>"country",
                                'type'=>'select',
                                'key'=>'countries',
                                'order'=>6,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>8,
                                'label'=>'state',
                                "map"=>"state",
                                'type'=>'select',
                                'key'=>'states',
                                'identifier'=>'states',
                                'trigger'=>'cities',
                                'order'=>4,
                                'value'=>'',
                                'required'=>true
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "SG",
                "type" => "taxpayer_info",
                "content" => $taxpayerInfo
            ],
            [
                "country_code" => "SG",
                "type" => "member_payout_options",
                "content" => [
                    "selected" => 1,
                    "bank_data" => [
                        [
                            "id" => 1,
                            "title" => "BANK",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Bank Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Bank Name",
                                    "type" => "select",
                                    "key" => "banks",
                                    "identifier" => "countries_bank",
                                    "trigger" => 4,
                                    "order" => 2,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 3,
                                    "label" => "A/C Holder Name",
                                    "type" => "input",
                                    "order" => 3,
                                    "value" => "",
                                    "autofill" => "details.name",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Swift Code",
                                    "type" => "input",
                                    "order" => 4,
                                    "inherit_index" => 2,
                                    "inherit_column" => "swift_code",
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Account Type",
                                    "type" => "masters-select",
                                    "key" => "master",
                                    "identifier" => "bank_account_type",
                                    "order" => 5,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 6,
                                    "label" => "Bank Account No",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Bank Account IC",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "autofill" => "details.ic_passport_number",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 8,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 8,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "HK",
                "type" => "address",
                "content" => [
                    [
                        'title'=>'Permanent Address',
                        'fields'=>[
                            [
                                'index'=>1,
                                'label'=>'Address 1',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>1,
                                'value'=>'',
                                'min'=>6,
                                'helper'=>'Address must be greater then 6 character',
                                'required'=>true
                            ],
                            [
                                'index'=>2,
                                'label'=>'Address 2',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>3,
                                'label'=>'Address 3',
                                "map"=>"addr3",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>4,
                                'label'=>'Address 4',
                                "map"=>"addr4",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>5,
                                'label'=>'postcode',
                                "map"=>"postcode",
                                'type'=>'input',
                                'order'=>3,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>6,
                                'label'=>'city',
                                "map"=>"city",
                                'type'=>'select',
                                'key'=>'cities',
                                'identifier'=>'cities',
                                'order'=>5,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>7,
                                'label'=>'country',
                                "map"=>"country",
                                'type'=>'select',
                                'key'=>'countries',
                                'order'=>6,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>8,
                                'label'=>'state',
                                "map"=>"state",
                                'type'=>'select',
                                'key'=>'states',
                                'identifier'=>'states',
                                'trigger'=>'cities',
                                'order'=>4,
                                'value'=>'',
                                'required'=>true
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "HK",
                "type" => "taxpayer_info",
                "content" => $taxpayerInfo
            ],
            [
                "country_code" => "HK",
                "type" => "member_payout_options",
                "content" => [
                    "selected" => 1,
                    "bank_data" => [
                        [
                            "id" => 1,
                            "title" => "BANK",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Bank Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Bank Name",
                                    "type" => "select",
                                    "key" => "banks",
                                    "identifier" => "countries_bank",
                                    "trigger" => 4,
                                    "order" => 2,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 3,
                                    "label" => "A/C Holder Name",
                                    "type" => "input",
                                    "order" => 3,
                                    "value" => "",
                                    "autofill" => "details.name",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Swift Code",
                                    "type" => "input",
                                    "order" => 4,
                                    "inherit_index" => 2,
                                    "inherit_column" => "swift_code",
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Account Type",
                                    "type" => "masters-select",
                                    "key" => "master",
                                    "identifier" => "bank_account_type",
                                    "order" => 5,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 6,
                                    "label" => "Bank Account No",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Bank Account IC",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "autofill" => "details.ic_passport_number",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 8,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 8,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "KH",
                "type" => "address",
                "content" => [
                    [
                        'title'=>'Permanent Address',
                        'fields'=>[
                            [
                                'index'=>1,
                                'label'=>'Address 1',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>1,
                                'value'=>'',
                                'min'=>6,
                                'helper'=>'Address must be greater then 6 character',
                                'required'=>true
                            ],
                            [
                                'index'=>2,
                                'label'=>'Address 2',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>3,
                                'label'=>'Address 3',
                                "map"=>"addr3",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>4,
                                'label'=>'Address 4',
                                "map"=>"addr4",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>5,
                                'label'=>'postcode',
                                "map"=>"postcode",
                                'type'=>'input',
                                'order'=>3,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>6,
                                'label'=>'city',
                                "map"=>"city",
                                'type'=>'select',
                                'key'=>'cities',
                                'identifier'=>'cities',
                                'order'=>5,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>7,
                                'label'=>'country',
                                "map"=>"country",
                                'type'=>'select',
                                'key'=>'countries',
                                'order'=>6,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>8,
                                'label'=>'state',
                                "map"=>"state",
                                'type'=>'select',
                                'key'=>'states',
                                'identifier'=>'states',
                                'trigger'=>'cities',
                                'order'=>4,
                                'value'=>'',
                                'required'=>true
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "KH",
                "type" => "taxpayer_info",
                "content" => $taxpayerInfo
            ],
            [
                "country_code" => "KH",
                "type" => "member_payout_options",
                "content" => [
                    "selected" => 1,
                    "bank_data" => [
                        [
                            "id" => 1,
                            "title" => "BANK",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Bank Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Bank Name",
                                    "type" => "select",
                                    "key" => "banks",
                                    "identifier" => "countries_bank",
                                    "trigger" => 4,
                                    "order" => 2,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 3,
                                    "label" => "A/C Holder Name",
                                    "type" => "input",
                                    "order" => 3,
                                    "value" => "",
                                    "autofill" => "details.name",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Swift Code",
                                    "type" => "input",
                                    "order" => 4,
                                    "inherit_index" => 2,
                                    "inherit_column" => "swift_code",
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Account Type",
                                    "type" => "masters-select",
                                    "key" => "master",
                                    "identifier" => "bank_account_type",
                                    "order" => 5,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 6,
                                    "label" => "Bank Account No",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Bank Account IC",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "autofill" => "details.ic_passport_number",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 8,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 8,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "PH",
                "type" => "address",
                "content" => [
                    [
                        'title'=>'Permanent Address',
                        'fields'=>[
                            [
                                'index'=>1,
                                'label'=>'Address 1',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>1,
                                'value'=>'',
                                'min'=>6,
                                'helper'=>'Address must be greater then 6 character',
                                'required'=>true
                            ],
                            [
                                'index'=>2,
                                'label'=>'Address 2',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>3,
                                'label'=>'Address 3',
                                "map"=>"addr3",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>4,
                                'label'=>'Address 4',
                                "map"=>"addr4",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>5,
                                'label'=>'postcode',
                                "map"=>"postcode",
                                'type'=>'input',
                                'order'=>3,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>6,
                                'label'=>'city',
                                "map"=>"city",
                                'type'=>'select',
                                'key'=>'cities',
                                'identifier'=>'cities',
                                'order'=>5,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>7,
                                'label'=>'country',
                                "map"=>"country",
                                'type'=>'select',
                                'key'=>'countries',
                                'order'=>6,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>8,
                                'label'=>'state',
                                "map"=>"state",
                                'type'=>'select',
                                'key'=>'states',
                                'identifier'=>'states',
                                'trigger'=>'cities',
                                'order'=>4,
                                'value'=>'',
                                'required'=>true
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "PH",
                "type" => "taxpayer_info",
                "content" => $taxpayerInfo
            ],
            [
                "country_code" => "PH",
                "type" => "member_payout_options",
                "content" => [
                    "selected" => 1,
                    "bank_data" => [
                        [
                            "id" => 1,
                            "title" => "BANK",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Bank Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Bank Name",
                                    "type" => "select",
                                    "key" => "banks",
                                    "identifier" => "countries_bank",
                                    "trigger" => 4,
                                    "order" => 2,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 3,
                                    "label" => "A/C Holder Name",
                                    "type" => "input",
                                    "order" => 3,
                                    "value" => "",
                                    "autofill" => "details.name",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Swift Code",
                                    "type" => "input",
                                    "order" => 4,
                                    "inherit_index" => 2,
                                    "inherit_column" => "swift_code",
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Account Type",
                                    "type" => "masters-select",
                                    "key" => "master",
                                    "identifier" => "bank_account_type",
                                    "order" => 5,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 6,
                                    "label" => "Bank Account No",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Bank Account IC",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "autofill" => "details.ic_passport_number",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 8,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 8,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "ID",
                "type" => "address",
                "content" => [
                    [
                        'title'=>'Permanent Address',
                        'fields'=>[
                            [
                                'index'=>1,
                                'label'=>'Address 1',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>1,
                                'value'=>'',
                                'min'=>6,
                                'helper'=>'Address must be greater then 6 character',
                                'required'=>true
                            ],
                            [
                                'index'=>2,
                                'label'=>'Address 2',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>3,
                                'label'=>'Address 3',
                                "map"=>"addr3",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>4,
                                'label'=>'Address 4',
                                "map"=>"addr4",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>5,
                                'label'=>'postcode',
                                "map"=>"postcode",
                                'type'=>'input',
                                'order'=>3,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>6,
                                'label'=>'city',
                                "map"=>"city",
                                'type'=>'select',
                                'key'=>'cities',
                                'identifier'=>'cities',
                                'order'=>5,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>7,
                                'label'=>'country',
                                "map"=>"country",
                                'type'=>'select',
                                'key'=>'countries',
                                'order'=>6,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>8,
                                'label'=>'state',
                                "map"=>"state",
                                'type'=>'select',
                                'key'=>'states',
                                'identifier'=>'states',
                                'trigger'=>'cities',
                                'order'=>4,
                                'value'=>'',
                                'required'=>true
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "ID",
                "type" => "taxpayer_info",
                "content" => $taxpayerInfo
            ],
            [
                "country_code" => "ID",
                "type" => "member_payout_options",
                "content" => [
                    "selected" => 1,
                    "bank_data" => [
                        [
                            "id" => 1,
                            "title" => "BANK",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Bank Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Bank Name",
                                    "type" => "select",
                                    "key" => "banks",
                                    "identifier" => "countries_bank",
                                    "trigger" => 4,
                                    "order" => 2,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 3,
                                    "label" => "A/C Holder Name",
                                    "type" => "input",
                                    "order" => 3,
                                    "value" => "",
                                    "autofill" => "details.name",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Swift Code",
                                    "type" => "input",
                                    "order" => 4,
                                    "inherit_index" => 2,
                                    "inherit_column" => "swift_code",
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Account Type",
                                    "type" => "masters-select",
                                    "key" => "master",
                                    "identifier" => "bank_account_type",
                                    "order" => 5,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 6,
                                    "label" => "Bank Account No",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Bank Account IC",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "autofill" => "details.ic_passport_number",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 8,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 8,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                "country_code" => "MY",
                "type" => "stockist_payout_options",
                "content" => $stockistPayoutOptions
            ],
            [
                "country_code" => "MY",
                "type" => "stockist_tax_info",
                "content" => $stockistTaxInfo
            ],
            [
                "country_code" => "SG",
                "type" => "stockist_payout_options",
                "content" => $stockistPayoutOptions
            ],
            [
                "country_code" => "SG",
                "type" => "stockist_tax_info",
                "content" => $stockistTaxInfo
            ],
            [
                "country_code" => "TH",
                "type" => "stockist_payout_options",
                "content" => $stockistPayoutOptions
            ],
            [
                "country_code" => "TH",
                "type" => "stockist_tax_info",
                "content" => $stockistTaxInfo
            ],
            [
                "country_code" => "KH",
                "type" => "stockist_payout_options",
                "content" => $stockistPayoutOptions
            ],
            [
                "country_code" => "KH",
                "type" => "stockist_tax_info",
                "content" => $stockistTaxInfo
            ],
            [
                "country_code" => "PH",
                "type" => "stockist_payout_options",
                "content" => $stockistPayoutOptions
            ],
            [
                "country_code" => "PH",
                "type" => "stockist_tax_info",
                "content" => $stockistTaxInfo
            ],
            [
                "country_code" => "BN",
                "type" => "stockist_payout_options",
                "content" => $stockistPayoutOptions
            ],
            [
                "country_code" => "BN",
                "type" => "stockist_tax_info",
                "content" => $stockistTaxInfo
            ],
            [
                "country_code" => "TW",
                "type" => "stockist_payout_options",
                "content" => $stockistPayoutOptions
            ],
            [
                "country_code" => "TW",
                "type" => "stockist_tax_info",
                "content" => $stockistTaxInfo
            ],
            [
                "country_code" => "ID",
                "type" => "stockist_payout_options",
                "content" => $stockistPayoutOptions
            ],
            [
                "country_code" => "ID",
                "type" => "stockist_tax_info",
                "content" => $stockistTaxInfo
            ],
        ];

        foreach ($data as $item)
        {
            $country = Country::where('code_iso_2',$item['country_code'])->first();

            CountryDynamicContent::updateOrCreate(
                [
                    'type' => $item['type'],
                    'country_id' => $country->id
                ],
                [
                    'content' => json_encode($item['content'])
                ]
            );
        }

        $data = [
            [
                "type" => "address",
                "content" => [
                    [
                        'title'=>'Permanent Address',
                        'fields'=>[
                            [
                                'index'=>1,
                                'label'=>'市/縣',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>1,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>2,
                                'label'=>'鄉鎮/市區',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>3,
                                'label'=>'里/村',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>4,
                                'label'=>'鄰',
                                "map"=>"addr1",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>5,
                                'label'=>'路/街',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>6,
                                'label'=>'段',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>7,
                                'label'=>'巷',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>8,
                                'label'=>'弄',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>9,
                                'label'=>'號',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>10,
                                'label'=>'樓之',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>11,
                                'label'=>'室',
                                "map"=>"addr2",
                                'type'=>'input',
                                'order'=>2,
                                'value'=>'',
                                'required'=>false
                            ],
                            [
                                'index'=>12,
                                'label'=>'postcode',
                                "map"=>"postcode",
                                'type'=>'input',
                                'order'=>3,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>13,
                                'label'=>'city',
                                "map"=>"city",
                                'type'=>'select',
                                'key'=>'cities',
                                'identifier'=>'cities',
                                'order'=>5,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>14,
                                'label'=>'country',
                                "map"=>"country",
                                'type'=>'select',
                                'key'=>'countries',
                                'order'=>6,
                                'value'=>'',
                                'required'=>true
                            ],
                            [
                                'index'=>15,
                                'label'=>'state',
                                "map"=>"state",
                                'type'=>'select',
                                'key'=>'states',
                                'identifier'=>'states',
                                'trigger'=>'cities',
                                'order'=>4,
                                'value'=>'',
                                'required'=>true
                            ]
                        ]
                    ]
                ]
            ],
            [
                "type" => "member_payout_options",
                "content" => [
                    "selected" => 1,
                    "bank_data" => [
                        [
                            "id" => 1,
                            "title" => "BANK",
                            "fields" => [
                                [
                                    "index" => 1,
                                    "label" => "Bank Country",
                                    "type" => "input",
                                    "order" => 1,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.name",
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 2,
                                    "label" => "Bank Name",
                                    "type" => "select",
                                    "key" => "banks",
                                    "identifier" => "countries_bank",
                                    "trigger" => 4,
                                    "order" => 2,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 3,
                                    "label" => "A/C Holder Name",
                                    "type" => "input",
                                    "order" => 3,
                                    "value" => "",
                                    "autofill" => "details.name",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 4,
                                    "label" => "Swift Code",
                                    "type" => "input",
                                    "order" => 4,
                                    "inherit_index" => 2,
                                    "inherit_column" => "swift_code",
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 5,
                                    "label" => "Account Type",
                                    "type" => "masters-select",
                                    "key" => "master",
                                    "identifier" => "bank_account_type",
                                    "order" => 5,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 6,
                                    "label" => "Bank Account No",
                                    "type" => "input",
                                    "order" => 6,
                                    "value" => "",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 7,
                                    "label" => "Bank Account IC",
                                    "type" => "input",
                                    "order" => 7,
                                    "value" => "",
                                    "autofill" => "details.ic_passport_number",
                                    "required" => true,
                                    "parent_id" => 1
                                ],
                                [
                                    "index" => 8,
                                    "label" => "Payout Company",
                                    "type" => "input",
                                    "order" => 8,
                                    "value" => "",
                                    "readonly" => true,
                                    "required" => false,
                                    "autofill" => "details.country.code_iso_2",
                                    "parent_id" => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                "type" => "taxpayer_info",
                "content" => $taxpayerInfo
            ]
        ];

        foreach ($data as $item)
        {
            $country = Country::where('code_iso_2','TW')->first();

            CountryDynamicContent::updateOrCreate(
                [
                    'type' => $item['type'],
                    'country_id' => $country->id
                ],
                [
                    'content' => json_encode($item['content'])
                ]
            );
        }
    }
}
