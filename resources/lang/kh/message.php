<?php
return [

    /*
    |-------------------------------------------------------------------------------------------------------------------
    | Custom Messages
    |-------------------------------------------------------------------------------------------------------------------
    */

    'user' =>[
        'locations_access_forbidden' => 'This user does not have access to locations module.',
        'resource_not_belong' => 'This resource not belong to you.',
        'un_authorized' => 'This action is not authorized',
        'email_unique' => 'This email already taken.',
    ],

    'status' => [
        'success' => 'created successfully',
        'failed' => 'operation failed',
    ],

    //login message-----------------------------------------------------------------------------------------------------
    'login' => [
        'failed' => 'You provide wrong password, please try again.',
        'wrong-email' => 'The credentials you provided does not exist in our records',
    ],

    //guest message-----------------------------------------------------------------------------------------------------
    'guest' => [
        'failed' => 'The system dose not allow guest to access at the moment, please try again later'
    ],

    // delete messages--------------------------------------------------------------------------------------------------
    'delete' => [
        'success' => 'Record Deleted Successfully',
        'fail' => 'Delete Failed',
    ],

    //mysql error message-----------------------------------------------------------------------------------------------
    'db' => [
        'duplicate_entry' => ' Integrity constraint violation duplicate entry'
    ],

    //products validations messages-------------------------------------------------------------------------------------
    'product' => [
        'un-available' => 'This product is not available in your country',
        'un-available-in-country' => 'This sku (:name, :sku) is not available in your country',
        'promo-dates' => 'Product promo with effective date :effective_date and expiry date :expire_date is invalid date rang.',
        'import-success' => 'products imported successfully',
        'kitting-product-un-available' => 'This product (:name , :sku ) is not available in your country, so its not available for this kitting',
        'un-available-in-shop' => 'The product is not available in shop',
        'country-not-exists' => 'The given country (:code) for this productId (:productID) is not exists in IBS System, please contact the admin',
        'either-should-exist' => 'The product or kitting should be set'
    ],

    'empty' => 'sorry no data',

    //member validations messages---------------------------------------------------------------------------------------
    'member' => [
        'members-only-area' => 'only members can make request to this API',
        'not-sponsor-child' => 'User :userId is not sponsor child of logged in user.',
        'verification-email-sent' => 'Email Sent',
        'verification-email-subject' => 'Email Verification Code',
        'email-verified' => 'Email has been verified.',
        'otp-code' => 'Email Verification Code is :otp. This code will be expired in 5 minutes.',
        'otp-validated' => 'OTP Validated.'
    ],

    'member-migrate' => [
        'different-country' => 'New country and current country must be different.',
    ],

    'member-rank' => [
        'different-enrollment-rank' => 'New enrollment rank and current enrollment rank must be different.',
        'different-highest-rank' => 'New highest rank and current highest rank must be different.',
    ],

    'member-status' => [
        'different-status' => 'new status and current status must be different.',
    ],

    'member-placement-verify' =>[
        'is-not-valid-upline' => 'The placement user does not belong to the sponsor user id',

        'occupied' => 'The placement has been occupied',

        'valid' => 'The placement is available',
    ],

    //workflow messages-------------------------------------------------------------------------------------------------
    'workflow' => [
        'completed' => ':name for :documentId is already completed.',
    ],

    //cw validation messages--------------------------------------------------------------------------------------------
    'cw' => [
        'current_or_previous_cw' => 'You need to choose current CW or previous CW - 1 back date',
        'cw' => 'CW'
    ],

    //sale cancellation validation messages-----------------------------------------------------------------------------
    'sales-cancellation' => [
        'invalid-member-invoice' => 'This member and invoice is not match.',
        'invoice-in-sale-cancellation-process' => 'This invoice was under sales cancellations process.',
        'max-product-quantity' => ':productName quantity may not be greater than :max.',
        'un-available-product-cancel' => 'This invoice has been fully cancel.',
        'un-available-sale-cancellation-refund' => 'This cancellation batchs unavailable be refund.',
    ],

    'sales' => [
        'sales-is-not-in-pre-order-status' => 'This sale has not in pre-order status.',
        'not-completed' => 'This sale is not completed, its in :status status',
        'minimum-amp-cv' => 'Minimum :minimumCv AMP CV required.',
        'minimum-enrollment-upgrade-cv' => 'Minimum :minimumCv Upgrade CV required.'
    ],

    //sale validation messages------------------------------------------------------------------------------------------
    'sales_exchange' => [
        'qty_exceed' => 'The return quantity of product (:name , :sku ) exceeded the available quantity',

    ],

    // currency conversion messages-------------------------------------------------------------------------------------
    'currency-conversion' => [
        'invalid-cw-id' => 'The given cw is duplicate entry with same currencies convert.'
    ],

    // stockist log messages--------------------------------------------------------------------------------------------
    'stockist-log-message' => [
        'initial' => 'Initial'
    ],

    // stockist payment validation messages-----------------------------------------------------------------------------
    'stockist-payment-validation-message' => [
        'zero-adjustment-amount' => 'Total of adjustment amount must be zero.'
    ],

    // consignment Transaction messages---------------------------------------------------------------------------------
    'consignment-transaction-message' => [
        'consignment-deposit-amount-range' => 'Consignment deposit amount must within the deposit range.',
        'consignment-refund-capping-amount' => 'Capping amount is less than minimum capping amount.',
        'consignment-order-deposit-limit-amount' => 'Order amount is bigger than deposit limit amount.',
        'consignment-return-under-pending-status' => 'This stockist had a consignment return under pending status.',
        'un-available-quantity-return-product' => 'Return quantity of this product (:name , :sku ) can not more than :quantity.',
        'block-consignment-return' => 'Another consignment transaction is in process.',
        'invalid-update-id' => 'This consignment transaction cannot be update.',
    ],

    // aeon payment cooling off release messages------------------------------------------------------------------------
    'aeon-payment-cooling-off-release' => [
        'invalid-aeon-payment-id' => 'Agreement number has been submitted.',
        'aeon-payment-released' => 'This aeon payment has been released.',
        'invalid-aeon-payment-status' => 'This aeon payment is not yet been approved.',
        'invalid-sale-order-status' => 'This sale is cancelled.'
    ],

    // epp moto payment update approve code----------------------------------------------------------------------------
    'epp-moto-payment-update-approve-code' => [
        'invalid-epp-moto-payment-id' => 'Approval code has been submitted.',
        'invalid-epp-moto-payment-status' => 'This epp payment has been approved.',
        'epp-moto-payment-not-approved' => 'This epp payment not yet approve.',
        'epp-moto-payment-converted' => 'This epp payment has been converted.',
        'invalid-sale-order-status' => 'This sale is cancelled.'
    ],

    // aeon payment update agreement number-----------------------------------------------------------------------------
    'aeon-payment-update-agreement-number' => [
        'invalid-aeon-payment-id' => 'Agreement number has been submitted.',
        'invalid-aeon-payment-status' => 'This aeon payment has been approved.',
        'invalid-approved-amount' => 'Approved amount is bigger than payment amount.',
        'invalid-sale-order-status' => 'This sale is cancelled.'
    ],

    // make payment messages -------------------------------------------------------------------------------------------
    'make-payment' => [
        'skip-downline-make-full-payment' => 'Cannot make full sale payment for same sponsor and downline user.',
        'amount-excess' => 'Pay amount was excess total amount.',
        'single-payment-trasnaction' => 'This transaction only allow make one payment.',
        'required-input-field' => 'The :name field is required.',
        'ewallet-invalid-ibo-id' => 'Invalid IBO ID',
        'ewallet-invalid-pin-no' => 'Invalid Pin No.',
        'ewallet-insufficient-balance' => 'Insufficient balance to make payment, current balance : :balance.',
        'invalid-payment-record' => 'Invalid Payment Record.',
        'invalid-card-number' => 'Invalid Card Number',
        'epp-eligibility-invalid-tenure' => 'Invalid epp payment tenure.',
        'epp-eligibility-insufficient-loan-amount' => 'Invalid requested epp amount. Min requested amount : :amount.'
    ],

    // country and location messages -----------------------------------------------------------------------------------
    'country' => [
        'already-set' => 'Country is already set',
        'not-set' => 'Country is not set',
        'not-exists-relation' => 'This :name relation is not exists in Country Model',
    ],

    // country and location messages -----------------------------------------------------------------------------------
    'location' => [
        'already-set' => 'Location is already set'
    ],

    // query building --------------------------------------------------------------------------------------------------
    'query-building' => [
        'unknown-union-type' => "The union type defined is not known",
        'active-status-cannot-change' => "Cannot change status now",
        'invalid-ordering-type' => "Invalid ordering type"
    ],

    // e-wallet --------------------------------------------------------------------------------------------------------
    'e-wallet' => [
        'not-activated' => "e-Wallet is not been activated yet",
        'inactive' => "e-Wallet is inactive",
        'blocked' => "e-Wallet is blocked",
        'withdraw' => "Fund Withdrawal",
        'withdraw-amount-check' => "Amount cannot be less than USD 10",
        'adjustment-amount-check' => "Failed: Insufficient Funds",
        'transfer-to' => "Fund Transfer To - :details",
        'received-from' => "Fund Receive From - :details",
        'adjustment' => "Bonus Adjustment - :details",
        'giro-rejected' => "Bank GIRO Rejected - :details",
        'otp-code' => 'Elken Mobile Verification Code is :otp. This code will be expired in 5 minutes.',
        'otp-code-valid' => 'The code is valid',
        'number_validated' => 'Mobile Number Validated.',
        'rejected_payment_submit_error' => "Submitted file have error(s). Please fix them and upload again.",
        'rejected_payment_file_no_error' => "Submitted file is incorrect. Please download sample file to get correct file template."
    ],

    // Console task scheduling message----------------------------------------------------------------------------------
    'console-task-scheduling' => [
        'current-cw-update' => 'Current cw update successfully.',
        'aeon-respond-receive' => 'Aeon payment status update successfully.',
        'aeon-send-request' => 'Send aeon payment request file successfully.',
        'stockist-daily-sales-payment' => 'Stockist daily sales payment update successfully.',
        'stockist-commission-daily-update' => 'Stockist commission daily update successfully.',
        'payment-transaction-query' => 'Payment transaction query run successfully.',
        'update-member-expiry-status' => 'Member expiry status update successfully.'
    ],

    // Dummy setup  message---------------------------------------------------------------------------------------------
    'dummy' => [
        'un-available-dummy-product-setup' => 'This product (:name , :sku ) was exist in other dummy.',
    ],

    // Campaign message ------------------------------------------------------------------------------------------------
    'campaign' => [
        'invalid-to' => ':to value must either be zero OR not less than :from value.',
        'exclude-product' => 'Selected product is not valid for the redemption. (exclusion list)',
        'include-product' => 'Selected product is not valid for the redemption. (not included)',
        'exclude-kitting' => 'Selected kitting is not valid for the redemption. (exclusion list)',
        'include-kitting' => 'Selected kitting is not valid for the redemption. (not included)',
        'max-purchase-qty' => 'Total sale purchase quantity cannot more than total voucher max purchase quantity.',
        'min-purchase-amount' => 'Total sale purchase amount cannot less than total voucher min purchase amount.',
        'cannot-delete-used-master' => 'Deleting not allowed because the :master (:name) is currently in use.',
        'cannot-edit-used-master' => 'Editing not allowed because the :master is currently in use'
    ],

    //foreign validation -----------------------------------------------------------------------------------------------
    'foreign' => [
        'not_belong' => 'This given foreign key (:foreign) is not belongs to a given (:primary) primary key',
    ],

    //mobile verification-----------------------------------------------------------------------------------------------
    'mobile' => [
        'code' => 'Elken Enrollment Mobile Verification code : :code',
        'unique_id' => 'Elken Enrollment Unique id : :unique_id, please keep this unique id to continue enrollment later.',
        'verified' => 'This mobile :number is verified',
        'max_send_count' => 'You reached the max tries of verifying phone, you can try after 24 hours',
        'sms_failed' => 'Something went wrong, we can not send sms to your phone. please contact support',
        'wrong_code' => 'The code you sent is invalid',
        'exists' => 'This mobile already used. please contact support.',
        'not_exists' => 'This mobile number does not exist.',
        'already_sent' => 'The sms has already been sent. Please wait 5 minutes before you retry.'
    ],

    //email verification-----------------------------------------------------------------------------------------------
    'email' => [
        'code' => 'Elken Enrollment Email Verification code : :code',
        'unique_id' => 'Elken Enrollment Unique id : :unique_id, please keep this unique id to continue enrollment later.',
        'verified' => 'This email :email is verified',
        'max_send_count' => 'You reached the max tries of verifying email, you can try after 24 hours',
        'email_failed' => 'Something went wrong, we can not send email to your email address. please contact support',
        'wrong_code' => 'The code you sent is invalid',
        'already_sent' => 'The email has already been sent. Please wait 5 minutes before you retry.'
    ],

    //stockist commission ----------------------------------------------------------------------------------------------
    'stockist_commission_not_found' => 'The stockist :user does not have a stockist commission in commission week :cw',

    //cart -------------------------------------------------------------------------------------------------------------
    'cart' => [
        'empty' => 'Your cart is empty',
        'checkout' => 'Proceed to checkout',
        'cannot-checkout' => 'Unable to checkout'
    ],

    //cv ---------------------------------------------------------------------------------------------------------------
    'cv' => [
        'cannot-filter-sales-type-after-cv' => 'Cannot filter sales types after cv filtration',
        'minimum_cv_requirement' => 'Minimum :cv :eligibleType required',
        'to_next_rank' => ':cv RNK required to achieve the next rank type',
        'calculation' => ':cv :eligibleType',
        'eligible' => 'Eligible',
        'upgrade' => 'RNK',
        'append' => 'Amp',
        'wp' => 'WP',
        'base' => 'Base',
        'amp' => 'AMP',
        'stockist_base' => 'Stockist Base',
        'stockist_wp' => 'Stockist WP',
        'enrol_cv' => 'Enrol CV'
    ],

    //payment cancellation ---------------------------------------------------------------------------------------------
    'payment-cancellation' => [
        'payment-completed' => 'This payment is completed.',
        'payment-cancelled' => 'This payment is cancelled.',
        'aeon-payment-approved' => 'This aeon payment is approved.',
        'invalid-aeon-payment' => 'This is not a aeon payment record.',
        'epp-moto-payment-approved' => 'This epp moto payment is approved.',
        'invalid-epp-moto-payment' => 'This is not a epp moto payment record.',
        'invalid-sale-order-status' => 'This sale is cancelled.'
    ],

    //enrollment message -----------------------------------------------------------------------------------------------
    'enrollment' => [
        'sponsor_required' => 'You need to choose your sponsor member',
        'cant_resume' => 'This Enrollment already :status',
    ],

    //master message ---------------------------------------------------------------------------------------------------
    'master' => [
        'invalid-sale-type' => 'The sale type is invalid',
        'master-not-found' => 'Master not found'
    ],

    //zone -------------------------------------------------------------------------------------------------------------
    'zone' => [
        'postcode-duplicated' => 'The zone postcode :postcode is duplicated',
        'stock-location-duplicated' => 'The zone stock location (:effectiveDate, :expiryDate) is duplicated'
    ]
];
