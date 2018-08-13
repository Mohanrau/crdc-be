<?php
return [
    /*
     * ------------------------------------------------------------------
     * member status - member_status key in master table
     * ------------------------------------------------------------------
     */
    'member_status' => [
        'active' => 'active',
        'resigned' => 'resigned',
        'suspended' => 'suspended',
        'terminated' => 'terminated',
        'expired' => 'expired'
    ],

    /*
     * ------------------------------------------------------------------
     * member sale activities status - member_sale_activities_status in master table
     * ------------------------------------------------------------------
     */
    'member_sale_activities_status' => [
        'active' => 'active',
        'inactive' => 'inactive'
    ],

    /*
     * ------------------------------------------------------------------
     * member preferred contact  - preferred_contact key in master table
     * ------------------------------------------------------------------
     */
    'preferred_contact' => [
        'email' => 'Email',
        'phone' => 'Phone'
    ],

    /*
     * ------------------------------------------------------------------
     * member martial statuses - martial_status key in master table
     * ------------------------------------------------------------------
     */
    'martial_status' => [
        'single' => 'Single',
        'married' => 'Married',
        'divorced' => 'Divorced'
    ],

    /*
     * ------------------------------------------------------------------
     * otp code type  - otp_code_type key in master table
     * ------------------------------------------------------------------
     */
    'otp_code_type' => [
        'email' => 'member-email',
        'phone' => 'member-phone',
        'evoucher-email' => 'evoucher-email',
        'evoucher-phone' => 'evoucher-phone',
        'guest-email' => 'guest-email',
        'guest-phone' => 'guest-phone',
    ],

    /*
     * ------------------------------------------------------------------
     * ewallet transaction type  - ewallet_transaction_type key in master table
     * ------------------------------------------------------------------
     */
    'ewallet_transaction_type' => [
        'general' => 'General',
        'withdraw' => 'Withdraw',
        'transfer' => 'Transfer',
        'purchase' => 'Purchase',
        'welcome-bonus' => 'Welcome Bonus',
        'bonus-commission' => 'Bonus Commission'
    ],

    /*
     * ------------------------------------------------------------------
     * ewallet amount type  - ewallet_amount_type key in master table
     * ------------------------------------------------------------------
     */
    'ewallet_amount_type' => [
        'debit' => 'Debit',
        'credit' => 'Credit'
    ],

    /*
     * ------------------------------------------------------------------
     * ewallet transaction status  - ewallet_transaction_status key in master table
     * ------------------------------------------------------------------
     */
    'ewallet_transaction_status' => [
        'pending' => 'In Process',
        'successful' => 'Successful',
        'rejected' => 'Rejected',
    ],

    /*
     * ------------------------------------------------------------------
     * member addresses type - address type in member_address_data
     * ------------------------------------------------------------------
     */
    'member_shipping_address' => [
        'address1' => 'Address 1',
        'address2' => 'Address 2',
        'address3' => 'Address 3',
        'address4' => 'Address 4',
        'postcode' => 'postcode',
        'city' => 'city',
        'state' => 'state',
        'country' => 'country'
    ],

    /*
     * ------------------------------------------------------------------
     * ibo member id country prefix
     * ------------------------------------------------------------------
     */
    'ibo_member_id_country_prefix' => [
        'MY' => '10',
        'TH' => '14',
        'BN' => '12',
        'SG' => '11',
        'TW' => '15',
        'HK' => '13',
        'KH' => '17',
        'PH' => '16',
        'ID' => '18'
    ],

    /*
     * ------------------------------------------------------------------
     * sales transactions types - sales_types key in master_data table
     * NOTE: This also defines the priority order when using a default sales type
     * in case of multiple sales types
     * TODO: Specify sales type ordering in DB on version 2
     * ------------------------------------------------------------------
     */
    'sale_types' => [
        'registration' => 'registration',
        'member-upgrade' => 'member upgrade',
        'ba-upgrade' => 'ba upgrade',
        'repurchase' => 'repurchase',
        'auto-ship' => 'auto-ship',
        'auto-maintenance' => 'auto-maintenance',
        'formation' => 'formation',
        'rental' => 'rental'
    ],

    /*
     * ------------------------------------------------------------------
     * sale order status - sale_order_status key in master table
     * ------------------------------------------------------------------
     */
    'sale_order_status' => [
        'pending' => 'pending',
        'pending-online' => 'pending-online',
        'completed' => 'completed',
        'pre-order' => 'pre-order',
        'cancelled' => 'canceled',
        'partially-cancelled' => 'partially cancelled',
        'void' => 'void',
        'rejected' => 'rejected'
    ],

    /*
     * ------------------------------------------------------------------
     * sale cancellation status - sale_cancellation_status key in master table
     * ------------------------------------------------------------------
     */
    'sale_cancellation_status' => [
        'pending-approval' => 'pending approval',
        'approved' => 'approved',
        'pending-refund' => 'pending refund',
        'completed' => 'completed',
        'rejected' => 'rejected'
    ],

    /*
     * ------------------------------------------------------------------
     * sales cancellation mode - sale_cancellation_mode key in master table
     * ------------------------------------------------------------------
     */
    'sale_cancellation_mode' => [
        'full' => 'full',
        'partial' => 'partial'
    ],

    /*
     * ------------------------------------------------------------------
     * legacy sales cancellation mode - legacy_sale_cancellation_mode key in master table
     * ------------------------------------------------------------------
     */
    'legacy_sale_cancellation_mode' => [
        'legacy' => 'legacy'
    ],

    /*
     * ------------------------------------------------------------------
     * sales cancellation type - sale_cancellation_type key in master table
     * ------------------------------------------------------------------
     */
    'sale_cancellation_type' => [
        'same day' => 'same day',
        'cooling off' => 'cooling off',
        'buy back' => 'buy back'
    ],

    /*
     * ------------------------------------------------------------------
     * payment mode - payment_mode key in master table
     * ------------------------------------------------------------------
     */
    'payment_mode' => [
        'cash' => 'cash',
        'stockist card (cash)' => 'stockist card (cash)',
        'credit card' => 'credit card',
        'online payment gateway' => 'online payment gateway',
        'e-wallet' => 'e-wallet',
        'epp (online)' => 'epp (online)',
        'epp (moto)' => 'epp (moto)',
        'epp (terminal)' => 'epp (terminal)',
        'mpos' => 'mpos',
        'direct banking' => 'direct banking',
        'aeon' => 'aeon',
        'discount voucher' => 'discount voucher',
        'house cheque' => 'house cheque',
        'nets' => 'nets',
        'ipay88' => 'ipay88'
    ],

    /*
     * ------------------------------------------------------------------
     * aeon payment approval status - aeon_payment_approval_status key in master table
     * ------------------------------------------------------------------
     */
    'aeon_payment_approval_status' => [
        'pending' => 'pending',
        'approved' => 'approved',
        'declined' => 'declined',
        'cancel' => 'cancel'
    ],

    /*
     * ------------------------------------------------------------------
     * aeon payment document status - aeon_payment_document_status key in master table
     * ------------------------------------------------------------------
     */
    'aeon_payment_document_status' => [
        'n' => 'n',
        'p' => 'p',
        'v' => 'v'
    ],

    /*
     * ------------------------------------------------------------------
     * epp payment approval status - epp_payment_approval_status key in master table
     * ------------------------------------------------------------------
     */
    'epp_payment_approval_status' => [
        'pending' => 'pending',
        'approved' => 'approved',
        'declined' => 'declined'
    ],

    /*
     * ------------------------------------------------------------------
     * epp payment document status - epp_payment_document_status key in master table
     * ------------------------------------------------------------------
     */
    'epp_payment_document_status' => [
        'n' => 'n',
        'p' => 'p',
        'v' => 'v'
    ],

    /*
     * ------------------------------------------------------------------
     * epp mode - epp_mode key in master table
     * ------------------------------------------------------------------
     */
    'epp_mode' => [
        'online' => 'online',
        'moto' => 'moto',
        'terminal' => 'terminal'
    ],

    /*
     * ------------------------------------------------------------------
     * consignment deposit and refund type - consignment_deposit_and_refund_type key in master table
     * ------------------------------------------------------------------
     */
    'consignment_deposit_and_refund_type' => [
        'deposit' => 'deposit',
        'refund' => 'refund'
    ],

    /*
     * ------------------------------------------------------------------
     * consignment deposit and refund status - consignment_deposit_and_refund_status key in master table
     * ------------------------------------------------------------------
     */
    'consignment_deposit_and_refund_status' => [
        'initial' => 'initial',
        'pending' => 'pending',
        'rejected' => 'rejected',
        'approved' => 'approved',
        'cancelled' => 'cancelled'
    ],

    /*
     * ------------------------------------------------------------------
     * consignment refund verification status - consignment_refund_verification_status key in master table
     * ------------------------------------------------------------------
     */
    'consignment_refund_verification_status' => [
        'pending' => 'pending',
        'rejected' => 'rejected',
        'verified' => 'verified'
    ],

    /*
     * ------------------------------------------------------------------
     * consignment order and return type - consignment_order_and_return_type key in master table
     * ------------------------------------------------------------------
     */
    'consignment_order_and_return_type' => [
        'order' => 'order',
        'return' => 'return'
    ],

    /*
     * ------------------------------------------------------------------
     * consignment order status - consignment_order_status key in master table
     * ------------------------------------------------------------------
     */
    'consignment_order_status' => [
        'pending' => 'pending',
        'rejected' => 'rejected',
        'approved' => 'approved'
    ],

    /*
     * ------------------------------------------------------------------
     * consignment return status - consignment_return_status key in master table
     * ------------------------------------------------------------------
     */
    'consignment_return_status' => [
        'pending' => 'pending',
        'rejected' => 'rejected',
        'verified' => 'verified'
    ],

    /*
     * ------------------------------------------------------------------
     * consignment warehouse receiving status - consignment_warehouse_receiving_status key in master table
     * ------------------------------------------------------------------
     */
    'consignment_warehouse_receiving_status' => [
        'pending' => 'pending',
        'received' => 'received'
    ],

    /*
     * ------------------------------------------------------------------
     * stockist daily transaction release status - stockist_daily_transaction_release_status key in master table
     * ------------------------------------------------------------------
     */
    'stockist_daily_transaction_release_status' => [
        'cancelled' => 'cancelled',
        'pending' => 'pending',
        'released' => 'released'
    ],

    /*
     * ------------------------------------------------------------------
     * aeon payment stock release status - aeon_payment_stock_release_status key in master table
     * ------------------------------------------------------------------
     */
    'aeon_payment_stock_release_status' => [
        'cancelled' => 'cancelled',
        'pending' => 'pending',
        'released' => 'released'
    ],

    /*
     * ------------------------------------------------------------------
     * amp cv allocation types - amp_cv_allocation_types key in master table
     * ------------------------------------------------------------------
     */
    'amp_cv_allocation_types' => [
        'amp' => 'amp',
        'sales' => 'sales',
        'rental' => 'rental'
    ],

    /*
     * ------------------------------------------------------------------
     * location types - locations type name in locations_types table
     * ------------------------------------------------------------------
     */
    'locations_types' => [
        'stockist' => 'stockist',
        'main_branch' => 'main_branch',
        'branch' => 'branch',
        'online' => 'online'
    ],

    /*
     * ------------------------------------------------------------------
     * amp cv allocation types - amp_cv_allocation_types key in master table
     * ------------------------------------------------------------------
     */
    'order_status' => [
        'preorder' => 'amp',
        'sales' => 'sales'
    ],

    /*
     * ------------------------------------------------------------------
     * user types
     * ------------------------------------------------------------------
     */
    'user_types' => [
        'root' => 'root',
        'back_office' => 'BackOffice',
        'member' => 'Member',
        'stockist' => 'Stockist',
        'guest' => 'Guest',
        'stockist_staff' => 'Stockist_staff'
    ],

    /*
     * ------------------------------------------------------------------
     * location type code
     * ------------------------------------------------------------------
     */
    'location_type_code' => [
        'main_branch' => 'main_branch',
        'branch' => 'branch',
        'stockist' => 'stockist',
        'online' => 'online'
    ],

    /*
     * ------------------------------------------------------------------
     * promotion free items promo types
     * ------------------------------------------------------------------
     */
    'promotion_free_items_promo_types' => [
        'foc' => 'foc',
        'pwp(f)' => 'pwp(f)',
        'pwp(n)' => 'pwp(n)'
    ],

    /*
     * ------------------------------------------------------------------
     * mapping cv names
     * ------------------------------------------------------------------
     * Note: If the acronym is changed here, it should reflect in ./setting.php['sale-type-cvs']
     */
    'cv_acronym' => [
        'wp_cv' => 'wp',
        'base_cv' => 'base',
        'cv1' => 'amp',
        'cv2' => 'stockist_base',
        'cv3' => 'stockist_wp',
        'cv4' => 'enrol_cv'
    ],

    /*
     * ------------------------------------------------------------------
     * mapping yy address (map in address json structure)
     * ------------------------------------------------------------------
     */
    'yy_address' => [
        'addr1' => 'addr1',
        'addr2' => 'addr2',
        'addr3' => 'addr3',
        'addr4' => 'addr4',
        'postcode' => 'postcode',
        'city' => 'city',
        'state' => 'state',
        'country' => 'country',
    ],

    /*
     * ------------------------------------------------------------------
     * mapping enrollments types
     * ------------------------------------------------------------------
     */
    'enrollment_types' => [
        'ba' => 'BA',
        'premier' => 'Premier Member',
        'member' => 'Member',
    ],

    /*
     * ------------------------------------------------------------------
     * sale delivery method
     * ------------------------------------------------------------------
     */
    'sale_delivery_method' => [
        'delivery' => 'delivery',
        'self pick-up' => 'self pick-up',
        'without shipping' => 'without shipping',
    ],

    /*
     * ------------------------------------------------------------------
     * mapping enrollments status
     * ------------------------------------------------------------------
     */
    'enrollment_status' => [
        'pending' => 'PENDING',
        'completed' => 'COMPLETED',
        'canceled' => 'CANCELED',
    ],

    /*
     * ------------------------------------------------------------------
     * mapping esac promotion entitled by
     * ------------------------------------------------------------------
     */
    'esac_promotion_entitled_by' => [
        'category' => 'category',
        'product' => 'product'
    ],

    /*
     * ------------------------------------------------------------------
     * mapping esac voucher status
     * ------------------------------------------------------------------
     */
    'esac_voucher_status' => [
        'new' => 'new',
        'processed' => 'processed',
        'void' => 'void'
    ],

    /*
     * ------------------------------------------------------------------
     * mapping smart library upload file type
     * ------------------------------------------------------------------
     */
    'smart_library_upload_file_type' => [
        'image' => 'image',
        'pdf' => 'pdf',
        'video' => 'video',
        'link' => 'link'
    ]
];