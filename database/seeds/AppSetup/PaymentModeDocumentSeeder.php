<?php

use App\Models\Locations\Country;
use App\Models\Payments\PaymentModeDocumentDetail;
use App\Models\Payments\PaymentModeProvider;
use Illuminate\Database\Seeder;

class PaymentModeDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                "country_code" => "MY",
                "payment_mode" => "aeon",
                "content" => [
                    'data' => [
                        [
                            'document_title' => 'AEON-Xpress Easy Payment Application Form',
                            'document_type' => 'Original',
                            'employed' => 'Yes',
                            'self_employed' => 'Yes'
                        ],
                        [
                            'document_title' => 'AEON Direct Debit Service Application Form',
                            'document_type' => 'Original',
                            'employed' => 'Yes',
                            'self_employed' => 'Yes'
                        ],
                        [
                            'document_title' => 'Identification Card',
                            'document_type' => 'Copy',
                            'employed' => 'Yes',
                            'self_employed' => 'Yes'
                        ],
                        [
                            'document_title' => 'Latest 1 Month Salary Slip',
                            'document_type' => 'Copy',
                            'employed' => 'Yes',
                            'self_employed' => ''
                        ],
                        [
                            'document_title' => 'Bank Saving Account Passbook/Latest Bank Statement - 1 Month',
                            'document_type' => 'Copy',
                            'employed' => 'Yes',
                            'self_employed' => ''
                        ],
                        [
                            'document_title' => 'Bank Saving Account Passbook/Latest Bank Statement - 3 Months',
                            'document_type' => 'Copy',
                            'employed' => '',
                            'self_employed' => 'Yes'
                        ],
                        [
                            'document_title' => 'Business Registration Form',
                            'document_type' => 'Copy',
                            'employed' => '',
                            'self_employed' => 'Yes'
                        ],
                        [
                            'document_title' => 'Latest Business Registration',
                            'document_type' => 'Copy',
                            'employed' => '',
                            'self_employed' => 'Yes'
                        ],
                    ],
                    'mailing_address' => 'Elken Global Sdn Bhd \r\n
                    Yayasan Elken \r\n
                    Global Sales Processing & Projects \r\n 
                    EPP / AEON Section Lot 12, 1 st Floor, \r\n
                    Jalan 1/137C, Batu 5, \r\n
                    Jalan Kelang Lama, \r\n
                    58200 Kuala Lumpur.'
                ],

            ],
            [
                "country_code" => "TH",
                "payment_mode" => "aeon",
                "content" => [
                    'data' => [
                        [
                            'document_title' => 'Signed Agreement Form',
                            'document_type' => 'Original',
                            'new_member' => 'Yes',
                            'old_member' => 'Yes'
                        ],
                        [
                            'document_title' => 'Identification Card',
                            'document_type' => 'Copy',
                            'new_member' => 'Yes',
                            'old_member' => 'Yes'
                        ],
                        [
                            'document_title' => 'Income Proof',
                            'document_type' => 'Copy',
                            'new_member' => 'Yes',
                            'old_member' => ''
                        ],
                        [
                            'document_title' => 'Bank Statement (6-months) / Saving Bank',
                            'document_type' => 'Copy',
                            'new_member' => 'Yes',
                            'old_member' => ''
                        ],
                        [
                            'document_title' => 'AEON Member Card',
                            'document_type' => 'Copy',
                            'new_member' => '',
                            'old_member' => 'Yes'
                        ],
                        [
                            'document_title' => 'Applicant acknowledged receipt Distributor Bill',
                            'document_type' => 'Original',
                            'new_member' => 'Yes',
                            'old_member' => 'Yes'
                        ]
                    ],
                    'mailing_address' => 'Sales Processing Department \r\n
                    BANGKOK \r\n
                    No.1, Fortunetown, \r\n
                    20th Floor, \r\n
                    Ratchadapisek Rd., Dindaeng \r\n
                    DinDaeng, \r\n
                    Bangkok 10400, Thailand'
                ],
            ],
            [
                "country_code" => "KH",
                "payment_mode" => "aeon",
                "content" => [
                    'data' => [
                        [
                            'document_title' => 'Signed Agreement Form (White & Blue copy)',
                            'document_type' => 'Original'
                        ],
                        [
                            'document_title' => 'Identification Card',
                            'document_type' => 'Copy'
                        ],
                        [
                            'document_title' => 'Income Proof',
                            'document_type' => 'Copy'
                        ],
                        [
                            'document_title' => 'Bank Statement / Saving Bank',
                            'document_type' => 'Copy'
                        ],
                        [
                            'document_title' => 'Applicant acknowledged receipt Distributor Bill',
                            'document_type' => 'Original'
                        ],
                        [
                            'document_title' => 'LOA - Address SAME with AEON Agreement address (if any)',
                            'document_type' => 'Original'
                        ],
                    ],
                    'mailing_address' => 'Sales Processing Department \r\n
                    PHNOM PENH \r\n
                    #28, E2, \r\n
                    The iCON Professional \r\n
                    Building, \r\n
                    216, Norodom Blvd, \r\n
                    Tonle Bassac, Chamkarmon, \r\n
                    Phnom Penh, Cambodia'
                ],
            ]
        ];

        foreach ($data as $item)
        {
            $country = Country::where('code_iso_2',$item['country_code'])->first();

            $paymentMode = PaymentModeProvider::where('code', $item['payment_mode'])->first();

            PaymentModeDocumentDetail::updateOrCreate([
                'country_id' => $country->id,
                'payment_mode_provider_id' => $paymentMode->id
            ],
            [
                'country_id' => $country->id,
                'payment_mode_provider_id' => $paymentMode->id,
                'document_data' => json_encode($item['content'])
            ]);
        }
    }
}
