<?php

use Illuminate\Database\Seeder;
use App\Models\Masters\Master;
use App\Models\Masters\MasterData;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $masterData = [
            /**
             * ---------------------------------------------------------------------------------------------------------
             * products keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                'title' => 'Dummy Code',
                'key' => 'dummy_code',
                'active' => 1,
                'master_data' => [
                    [
                        'title' => 'dummy code' //for ibs data migration purpose
                    ]
                ]
            ],
            [
                'title' => 'Cv Config',
                'key' => 'cv_config',
                'active' => 1,
                'master_data' => [
                    [
                        'title' => 'Cv not to be counted towards FOC & PWP(F)'
                    ],
                    [
                        'title' => 'Cv not to be counted towards PWP(N)'
                    ]
                ]
            ],
            [
                'title' => 'Product Type',
                'key' => 'product_type',
                'active' => 1,
                'master_data' => [
                    [
                        'title' => 'Lingerie'
                    ],
                    [
                        'title' => 'HA'
                    ]
                ]
            ],
            [
                //Promotion Free Items Member Types --------------------
                'title' => 'Promotion Free Items Promo Types',
                'key' => 'promotion_free_items_promo_types',
                'active' => 1,
                'master_data' => [
                    ['title' => 'FOC'],
                    ['title' => 'PWP(F)'],
                    ['title' => 'PWP(N)']
                ]
            ],
            [
                //Promotion Free Items Member Types ------------------------------------------
                'title' => 'Promotion Free Items Members Types',
                'key' => 'promotion_free_items_member_types',
                'active' => 1,
                'master_data' => [
                    ['title' => 'Brand Ambassador'],
                    ['title' => 'Premium Member'],
                    ['title' => 'Member']
                ]
            ],
            [
                'title' => 'Another Sizes',
                'key' => 'another_sizes',
                'active' => 1,
                'master_data' => [
                    ["title" => "EXTRA SMALL"],
                    ["title" => "EXTRA LARGE"]
                ]
            ],
            [
                "title" => "Product Additional Requirements",
                "key" => "product_additional_requirements",
                'active' => 1,
                'master_data' => [
                    ["title" => "Address"],
                    ["title" => "Size"],
                    ["title" => "Corporate Sales"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * members keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                //Member Module --------------------------------------------------------------
                'title' => 'IC/Passport Type',
                'key' => 'ic_passport_type',
                'active' => 1,
                'master_data' => [
                    ["title" => "Passport"],
                    ["title" => "PAN no"],
                    ["title" => "NRIC"],
                    ["title" => "Driving License"]
                ]
            ],
            [
                //Member Information --------------------
                'title' => 'Gender',
                'key' => 'gender',
                'active' => 1,
                'master_data' => [
                    ['title' => 'Male'],
                    ['title' => 'Female']
                ]
            ],
            [
                'title' => 'Salutation',
                'key' => 'salutation',
                'active' => 1,
                'master_data' => [
                    ["title" => "Mr"],
                    ["title" => "Ms"]
                ]
            ],
            [
                'title' => 'Ethnic Group',
                'key' => 'ethnic_group',
                'active' => 1,
                'master_data' => [
                    ["title" => "Thai"],
                    ["title" => "Khmer"],
                    ["title" => "Cham"],
                    ["title" => "Vietnamese"],
                    ["title" => "Aboriginal"],
                    ["title" => "Filipino"],
                    ["title" => "Indonesia"],
                    ["title" => "Malay"],
                    ["title" => "Chinese"],
                    ["title" => "Indian"],
                    ["title" => "Others"]
                ]
            ],
            [
                'title' => 'Religion',
                'key' => 'religion',
                'active' => 1,
                'master_data' => [
                    ["title" => "Muslim"],
                    ["title" => "Christian"],
                    ["title" => "Buddhist"],
                    ["title" => "Hindu"],
                    ["title" => "Others"]
                ]
            ],
            [
                'title' => 'Martial Status',
                'key' => 'martial_status',
                'active' => 1,
                'master_data' => [
                    ['title' => 'Single'],
                    ['title' => 'Married'],
                    ["title" => "Divorced"]
                ]
            ],
            [
                'title' => 'Education',
                'key' => 'education',
                'active' => 1,
                'master_data' => [
                    ["title" => "Primary School"],
                    ["title" => "Secondary School"],
                    ["title" => "University"],
                    ["title" => "College"]
                ]
            ],
            [
                'title' => 'Occupation',
                'key' => 'occupation',
                'active' => 1,
                'master_data' => [
                    ["title" => "Management"],
                    ["title" => "Technical"],
                    ["title" => "Business"],
                    ["title" => "Project Manager"],
                    ["title" => "Student"]
                ]
            ],
            [
                'title' => 'Industry',
                'key' => 'industry',
                'active' => 1,
                'master_data' => [
                    ["title" => "Technology"],
                    ["title" => "Engineering"],
                    ["title" => "Finance"],
                    ["title" => "Direct Selling"]
                ]
            ],
            [
                'title' => 'Salary Range',
                'key' => 'salary_range',
                'active' => 1,
                'master_data' => [
                    ["title" => "Below 1000"],
                    ["title" => "1000  - 2000"]
                ]
            ],
            [
                'title' => 'Annual Revenue',
                'key' => 'annual_revenue',
                'active' => 1,
                'master_data' => [
                    ["title" => "< 30000"],
                    ["title" => "30001 - 60000"],
                    ["title" => "60001 - 10000"]
                ]
            ],
            [
                'title' => 'Relationships',
                'key' => 'relationships',
                'active' => 1,
                'master_data' => [
                    ["title" => "Wife"],
                    ["title" => "Husband"],
                    ["title" => "Sister"],
                    ["title" => "Son"],
                    ["title" => "Daughter"],
                    ["title" => "Father"],
                    ["title" => "Mother"],
                    ["title" => "Brother"],
                    ["title" => "None"]
                ]
            ],
            [
                'title' => 'Bank Type',
                'key' => 'bank_type',
                'active' => 1,
                'master_data' => [
                    ['title' => 'BANK'],
                    ['title' => 'MPAY']
                ]
            ],
            [
                'title' => 'Bank Account Type',
                'key' => 'bank_account_type',
                'active' => 1,
                'master_data' => [
                    ['title' => 'Bank Account']
                ]
            ],
            [
                "title" => "Verification Failed Reason",
                "key" => "verification_failed_reason",
                'active' => 1,
                'master_data' => [
                    ["title" => "Document is not clear"],
                    ["title" => "Uploaded document is incomplete"],
                    ["title" => "Incorrect document"],
                    ["title" => "Published information do not match the document"]
                ]
            ],
            [
                "title" => "Member Status",
                "key" => "member_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "ACTIVE"],
                    ["title" => "RESIGNED"],
                    ["title" => "SUSPENDED"],
                    ["title" => "TERMINATED"],
                    ["title" => "EXPIRED"]
                ]
            ],
            [
                "title" => "Member Status Update Reason",
                "key" => "member_status_update_reason",
                'active' => 1,
                'master_data' => [
                    ["title" => "Member's request – personal reason"],
                    ["title" => "Company's decision"],
                    ["title" => "Violation of Elken's Rules & Regulations"],
                    ["title" => "Investigation in progress"],
                    ["title" => "Member has made the purchase"]
                ]
            ],
            [
                "title" => "Member Migrate Reason",
                "key" => "member_migrate_reason",
                'active' => 1,
                'master_data' => [
                    ["title" => "Member's request – relocation"],
                    ["title" => "Company's decision"],
                    ["title" => "System Error"]
                ]
            ],
            [
                "title" => "Preferred Contact",
                "key" => "preferred_contact",
                "active" => 1,
                "master_data" => [
                    ["title" => "Email"],
                    ["title" => "Phone"]
                ]
            ],
            [
                "title" => "Member Sale Activities Status",
                "key" => "member_sale_activities_status",
                "active" => 1,
                "master_data" => [
                    ["title" => "ACTIVE"],
                    ["title" => "INACTIVE"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * sales keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                'title' => 'Sale Types',
                'key' => 'sale_types',
                'active' => 1,
                'master_data' => [
                    ['title' => 'Registration'],
                    ['title' => 'Member Upgrade'],
                    ['title' => 'BA Upgrade'],
                    ['title' => 'Auto-maintenance'],
                    ['title' => 'Auto-ship'],
                    ['title' => 'Formation'],
                    ['title' => 'Repurchase'],
                    ['title' => 'Rental']
                ]
            ],
            [
                "title" => "Sales Delivery Method",
                "key" => "sale_delivery_method",
                'active' => 1,
                'master_data' => [
                    ["title" => "SELF PICK-UP"],
                    ["title" => "DELIVERY"],
                    ["title" => "WITHOUT SHIPPING"]
                ]
            ],
            [
                "title" => "Sales Delivery Status",
                "key" => "sale_delivery_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "FULL"],
                    ["title" => "PARTIAL"]
                ]
            ],
            [
                "title" => "Sales Order Status",
                "key" => "sale_order_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "PENDING"],
                    ["title" => "COMPLETED"],
                    ["title" => "PRE-ORDER"],
                    ["title" => "CANCELED"],
                    ["title" => "PARTIALLY CANCELLED"],
                    ["title" => "VOID"],
                    ["title" => "PENDING-ONLINE"],
                    ["title" => "REJECTED"]
                ]
            ],
            [
                "title" => "Sales Channel",
                "key" => "sale_channel",
                'active' => 1,
                'master_data' => [
                    ["title" => "STOCKIST"],
                    ["title" => "ONLINE"],
                    ["title" => "BRANCH"]
                ]
            ],
            [
                "title" => "Rounding Adjustment",
                "key" => "rounding_adjustment",
                'active' => 1,
                'master_data' => [
                    ["title" => "Round to nearest 5-cent"],
                    ["title" => "Round-up to dollar"],
                    ["title" => "Round-up to nearest RP"]
                ]
            ],
            [
                "title" => "Sales Cancellation Reason",
                "key" => "sale_cancellation_reason",
                'active' => 1,
                'master_data' => [
                    ["title" => "Cooling off-period"],
                    ["title" => "Damaged"],
                    ["title" => "Defective/quality issue"],
                    ["title" => "Expiry related"],
                    ["title" => "Others"],
                    ["title" => "Wrong item ordered"],
                ]
            ],
            [
                "title" => "Sales Cancellation Type",
                "key" => "sale_cancellation_type",
                'active' => 1,
                'master_data' => [
                    ["title" => "SAME DAY"],
                    ["title" => "COOLING OFF"],
                    ["title" => "BUY BACK"]
                ]
            ],
            [
                "title" => "Sales Cancellation Mode",
                "key" => "sale_cancellation_mode",
                'active' => 1,
                'master_data' => [
                    ["title" => "FULL"],
                    ["title" => "PARTIAL"]
                ]
            ],
            [
                "title" => "Legacy Sales Cancellation Mode",
                "key" => "legacy_sale_cancellation_mode",
                'active' => 1,
                'master_data' => [
                    ["title" => "LEGACY"]
                ]
            ],
            [
                "title" => "Sales Cancellation Status",
                "key" => "sale_cancellation_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "PENDING APPROVAL"],
                    ["title" => "APPROVED"],
                    ["title" => "REJECTED"],
                    ["title" => "PENDING REFUND"],
                    ["title" => "COMPLETED"]
                ]
            ],
            [
                "title" => "Product Exchange reason",
                "key" => "product_exchange_reason",
                'active' => 1,
                'master_data' => [
                    ["title" => "Back Order"],
                    ["title" => "Change Sizes/Flavor/language"],
                    ["title" => "Discontinued product"],
                    ["title" => "Others"],
                    ["title" => "Shelf life issue"],
                    ["title" => "System error"],
                    ["title" => "Wrong product code"]
                ]
            ],
            [
                "title" => "Amp Cv Allocation Types",
                "key" => "amp_cv_allocation_types",
                'active' => 1,
                'master_data' => [
                    ["title" => "Amp"],
                    ["title" => "Sales"],
                    ["title" => "Rental"]
                ]
            ],
            [
                "title" => "shipping type",
                "key" => "shipping_type",
                "active" => 1,
                "master_data" => [
                    ["title" => "Delivery"],
                    ["title" => "pick-up"],
                    ["title" => "Continue without shipping"]
                ]
            ],
            [
                "title" => "Shipping Method Info",
                "key" => "shipping_method_info",
                "active" => 1,
                "master_data" => [
                    ["title" => "Delivery - Standard Delivery"],
                    ["title" => "Self Pick-up - Collection made by customer itself"],
                    ["title" => "Without Shipping - Choose shipping method later"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * delivery orders keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                "title" => "Delivery Order Services",
                "key" => "delivery_order_services",
                'active' => 1,
                'master_data' => [
                    ["title" => "abxex"],
                    ["title" => "citylink"],
                    ["title" => "fedex"],
                    ["title" => "fmglobal"],
                    ["title" => "fmmulti"],
                    ["title" => "gdex"],
                    ["title" => "jone"],
                    ["title" => "nationwide"],
                    ["title" => "spccserv"],
                    ["title" => "swiftlog"],
                    ["title" => "tasco"],
                    ["title" => "tntt"],
                    ["title" => "utslog"]
                ]
            ],
            [
                "title" => "Delivery Order Status",
                "key" => "delivery_order_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "pending"],
                    ["title" => "picking"],
                    ["title" => "partial-in-transit"],
                    ["title" => "in-transit"],
                    ["title" => "delivered"]
                ]
            ],
            [
                "title" => "Delivery Order Status Code",
                "key" => "delivery_order_status_code",
                "active" => 1,
                "master_data" => [
                    ["title" => "Hold"],
                    ["title" => "Release"],
                    ["title" => "Picked"],
                    ["title" => "Packed"],
                    ["title" => "Shipped"],
                    ["title" => "Received"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * Geo locations keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                "title" => "country code",
                "key" => "country_code",
                'active' => 1,
                'master_data' => [
                    ["title" => "(MY+60)"],
                    ["title" => "(SG+65)"],
                    ["title" => "(TW+886)"],
                    ["title" => "(BN+673)"],
                    ["title" => "(TH+66)"],
                    ["title" => "(HK+852)"],
                    ["title" => "(KH+855)"],
                    ["title" => "(PH+63)"],
                    ["title" => "(ID+62)"],
                    ["title" => "(CN+86)"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * payments keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                "title" => "Payment Mode",
                "key" => "payment_mode",
                'active' => 1,
                'master_data' => [
                    ["title" => "Cash"],
                    ["title" => "Stockist Card (Cash)"],
                    ["title" => "Credit Card"],
                    ["title" => "Online Payment Gateway"],
                    ["title" => "E-Wallet"],
                    ["title" => "EPP (Online)"],
                    ["title" => "EPP (Moto)"],
                    ["title" => "EPP (Terminal)"],
                    ["title" => "MPOS"],
                    ["title" => "Direct Banking"],
                    ["title" => "AEON"],
                    ["title" => "Discount Voucher"],
                    ["title" => "House Cheque"],
                    ["title" => "NETS"],
                ]
            ],
            [
                "title" => "Aeon Payment Approval Status",
                "key" => "aeon_payment_approval_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "PENDING"],
                    ["title" => "APPROVED"],
                    ["title" => "DECLINED"],
                    ["title" => "CANCEL"]
                ]
            ],
            [
                "title" => "Aeon Payment Document Status",
                "key" => "aeon_payment_document_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "N"], //New
                    ["title" => "P"], //Process
                    ["title" => "V"] //Void
                ]
            ],
            [
                "title" => "EPP Payment Approval Status",
                "key" => "epp_payment_approval_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "PENDING"],
                    ["title" => "APPROVED"],
                    ["title" => "DECLINED"]
                ]
            ],
            [
                "title" => "EPP Payment Document Status",
                "key" => "epp_payment_document_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "N"], //New
                    ["title" => "P"], //Process
                    ["title" => "V"] //Void
                ]
            ],
            [
                "title" => "EPP Mode",
                "key" => "epp_mode",
                'active' => 1,
                'master_data' => [
                    ["title" => "ONLINE"],
                    ["title" => "MOTO"],
                    ["title" => "TERMINAL"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * e wallet keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                "title" => "e-Wallet Amount type",
                "key" => "ewallet_amount_type",
                'active' => 1,
                'master_data' => [
                    ["title" => "Debit"],
                    ["title" => "Credit"]
                ]
            ],
            [
                "title" => "e-Wallet transaction type",
                "key" => "ewallet_transaction_type",
                'active' => 1,
                'master_data' => [
                    ["title" => "General"],
                    ["title" => "Withdraw"],
                    ["title" => "Transfer"],
                    ["title" => "Purchase"],
                    ["title" => "Welcome Bonus"],
                    ["title" => "Bonus Commission"],
                ]
            ],
            [
                "title" => "e-Wallet Adjustment Reasons",
                "key" => "ewallet_adjustment_reasons",
                'active' => 1,
                'master_data' => [
                    ["title" => "Top Up Sales"],
                    ["title" => "TMS"],
                    ["title" => "CS Case On Hold"],
                    ["title" => "Stockist Price Increase Top Up"],
                    ["title" => "AR Outstanding"],
                    ["title" => "ROPI"],
                    ["title" => "Deduct On Behalf"],
                    ["title" => "SG Crisis"],
                    ["title" => "Loan"],
                    ["title" => "Commission Underpaid"],
                    ["title" => "Commission Recall"],
                    ["title" => "Commission Double Paid"],
                    ["title" => "Welcome Bonus Claw Back"],
                    ["title" => "Sales Cancellation Refund"],
                ]
            ],
            [
                "title" => "e-Wallet Transaction Status",
                "key" => "ewallet_transaction_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "In Process"],
                    ["title" => "Successful"],
                    ["title" => "Rejected"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * stockists keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                "title" => "Yearly Report Type",
                "key" => "yearly_report_type",
                'active' => 1,
                'master_data' => [
                    ["title" => "Statement"],
                    ["title" => "Summary"],
                    ["title" => "CP58"],
                    ["title" => "Lembaga Hasil Dalam Negeri"]
                ]
            ],
            [
                "title" => "Stockist Status",
                "key" => "stockist_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "ACTIVE"],
                    ["title" => "RESIGNATION"],
                    ["title" => "SUSPENDED"],
                    ["title" => "TERMINATION"]
                ]
            ],
            [
                "title" => "Stockist Type",
                "key" => "stockist_type",
                'active' => 1,
                'master_data' => [
                    ["title" => "SERVICE POINT"],
                    ["title" => "STOCKIST CENTER"],
                    ["title" => "SERVICE PARTNER"],
                    ["title" => "BUSINESS AGENT"]
                ]
            ],
            [
                "title" => "Consignment Deposit And Refund Type",
                "key" => "consignment_deposit_and_refund_type",
                'active' => 1,
                'master_data' => [
                    ["title" => "DEPOSIT"],
                    ["title" => "REFUND"]
                ]
            ],
            [
                "title" => "Consignment Deposit And Refund Status",
                "key" => "consignment_deposit_and_refund_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "INITIAL"],
                    ["title" => "PENDING"],
                    ["title" => "REJECTED"],
                    ["title" => "APPROVED"],
                    ["title" => "CANCELLED"]
                ]
            ],
            [
                "title" => "Consignment Refund Verification Status",
                "key" => "consignment_refund_verification_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "PENDING"],
                    ["title" => "REJECTED"],
                    ["title" => "VERIFIED"]
                ]
            ],
            [
                "title" => "Consignment Order And Return Type",
                "key" => "consignment_order_and_return_type",
                'active' => 1,
                'master_data' => [
                    ["title" => "ORDER"],
                    ["title" => "RETURN"]
                ]
            ],
            [
                "title" => "Consignment Order Status",
                "key" => "consignment_order_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "PENDING"],
                    ["title" => "REJECTED"],
                    ["title" => "APPROVED"]
                ]
            ],
            [
                "title" => "Consignment Return Status",
                "key" => "consignment_return_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "PENDING"],
                    ["title" => "REJECTED"],
                    ["title" => "VERIFIED"]
                ]
            ],
            [
                "title" => "Consignment Warehouse Receiving Status",
                "key" => "consignment_warehouse_receiving_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "PENDING"],
                    ["title" => "RECEIVED"]
                ]
            ],
            [
                "title" => "Stockist Daily Transaction Release Status",
                "key" => "stockist_daily_transaction_release_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "CANCELLED"],
                    ["title" => "PENDING"],
                    ["title" => "RELEASED"]
                ]
            ],
            [
                "title" => "Aeon Payment Stock Release Status",
                "key" => "aeon_payment_stock_release_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "CANCELLED"],
                    ["title" => "PENDING"],
                    ["title" => "RELEASED"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * eSac voucher, campaigns keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                "title" => "Voucher Period",
                "key" => "voucher_period",
                'active' => 1,
                'master_data' => [
                    ["title" => "30-days"],
                    ["title" => "60-days"],
                    ["title" => "90-days"],
                    ["title" => "120-days"],
                    ["title" => "180-days"]
                ]
            ],
            [
                "title" => "Campaign Reward Value Multiplier",
                "key" => "campaign_reward_value_multiplier",
                'active' => 1,
                'master_data' => [
                    ["title" => "Fixed Value"],
                    ["title" => "For Each Sale Item Quantity"],
                    ["title" => "For Every Sale Item Quantity Fulfilment"],
                    ["title" => "For Each Team Bonus Rank Quantity"],
                    ["title" => "For Every Team Bonus Rank Quantity Fulfilment"],
                    ["title" => "For Each Enrollment Rank Quantity"],
                    ["title" => "For Every Enrollment Rank Quantity Fulfilment"]
                ]
            ],
            [
                "title" => "eSac Promotion Entitled By",
                "key" => "esac_promotion_entitled_by",
                "active" => 1,
                "master_data" => [
                    ["title" => "Category"],
                    ["title" => "Product"]
                ]
            ],
            [
                "title" => "eSac Voucher Status",
                "key" => "esac_voucher_status",
                "active" => 1,
                "master_data" => [
                    ["title" => "New"],
                    ["title" => "Processed"],
                    ["title" => "Void"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * smart library keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                "title" => "Smart Library Upload File Type",
                "key" => "smart_library_upload_file_type",
                "active" => 1,
                "master_data" => [
                    ["title" => "Image"],
                    ["title" => "PDF"],
                    ["title" => "Video"],
                    ["title" => "Link"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * otp  keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                "title" => "User Otp Code Type",
                "key" => "otp_code_type",
                "active" => 1,
                "master_data" => [
                    ["title" => "member-email"],
                    ["title" => "member-phone"],
                    ["title" => "evoucher-email"],
                    ["title" => "evoucher-phone"],
                    ["title" => "guest-email"],
                    ["title" => "guest-phone"]
                ]
            ],

            /**
             * ---------------------------------------------------------------------------------------------------------
             * enrollment keys
             * ---------------------------------------------------------------------------------------------------------
             */
            [
                "title" => "Legal Age for Enrollment",
                "key" => "age_for_enrollment",
                "active" => 1,
                "master_data" => [
                    ["title" => "18"],
                    ["title" => "20"]
                ]
            ],
            [
                "title" => "Translated Name",
                "key" => "translated_name",
                "active" => 1,
                "master_data" => [
                    ["title" => "true"],
                    ["title" => "false"]
                ]
            ],
            [
                "title" => "Tin Number",
                "key" => "tin_number",
                "active" => 1,
                "master_data" => [
                    ["title" => "true"],
                    ["title" => "false"]
                ]
            ],
            [
                "title" => "Enrollment Status",
                "key" => "enrollment_status",
                'active' => 1,
                'master_data' => [
                    ["title" => "PENDING"],
                    ["title" => "COMPLETED"],
                    ["title" => "CANCELED"],
                ]
            ],
        ];

        foreach ($masterData as $data)
        {
            $masterData = $data['master_data'];

            unset($data['master_data']);

            $master = Master::updateOrCreate($data);

            collect($masterData)->each(function($data) use ($master){
                MasterData::updateOrCreate([
                    'master_id' => $master->id,
                    'title' => $data['title']
                ]);
            });
        }
    }
}
