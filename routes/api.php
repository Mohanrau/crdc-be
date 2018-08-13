<?php
/*
|-----------------------------------------------------------------------------------------------------------------------
| API Routes
|-----------------------------------------------------------------------------------------------------------------------
*/
Route::middleware('checkRequestAgent')->prefix('v1')->namespace('V1')->group(function(){

    //apply jsonApiMiddleware-------------------------------------------------------------------------------------------
    Route::middleware(['JsonApiMiddleware'])->group(function (){

        // user api ----------------------------------------------------------------------------------------------------
        Route::namespace('Users')->group(function () {
            Route::post('/register', 'UserController@register');

            Route::post('/login', 'UserController@login');

            Route::post('/refresh-token', 'UserController@refreshToken');

            Route::get('/guest', 'UserController@guest');
        });

        // Oauth api----------------------------------------------------------------------------------------------------
        Route::post('/oauth/token', '\Laravel\Passport\Http\Controllers\AccessTokenController@issueToken');

        Route::prefix('password')->namespace('ApiAuth')->group(function ()
        {
            Route::post('/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');

            Route::post('/reset', 'ResetPasswordController@reset');
        });

		//Countries-----------------------------------------------------------------------------------------------------
		Route::namespace('Locations')->group(function () {
			Route::post( '/countries-list', 'CountryController@index');
		});

        // api's for authenticated user only----------------------------------------------------------------------------
        Route::middleware('auth')->group(function()
        {
            // user list and update api---------------------------------------------------------------------------------
            Route::namespace('Users')->group(function () {
                Route::resource('/users', 'UserController');

                Route::get('/referrer', 'UserController@referrer');

                Route::post('/user/dashboard', 'UserController@userDashboard');

                Route::get('/user/types', 'UserController@getUserTypes');

                Route::post('/users-list', 'UserController@filterUsers');

                Route::post('/user-privileges', 'UserController@updatePrivileges');

                Route::get('/user-privileges/{userId}', 'UserController@userPrivilegeDetails');

                Route::get('/mail-notification', 'NotificationController@index');

                Route::post('/user/change-password', 'UserController@changePassword');

                Route::get('/user', 'UserController@index');

                Route::get('/user-locations/{userId}', 'UserController@checkUserLocationsAccess');

                Route::post('/get-oauth-token', 'UserController@getOauthAccessToken');

                Route::post('/mobile-code', 'MobileVerificationController@getCode');

                Route::post('/mobile-verify', 'MobileVerificationController@verifyMobile');

                Route::post('/email-code', 'EmailVerificationController@getCode');

                Route::post('/email-verify', 'EmailVerificationController@verifyEmail');
            });

            Route::namespace('Staff')->group(function (){
                Route::resource('/staff', 'StaffController',['except' => ['create', 'edit']]);
            });

            //Masters---------------------------------------------------------------------------------------------------
            Route::namespace('Masters')->group(function (){
                Route::resource('/masters', 'MasterController');

                Route::post('/master/keys', 'MasterController@getMasterDataByKeys');

                Route::post('/country/master/keys', 'MasterController@getMasterDataByCountry');

                Route::resource('/master-data', 'MasterDataController');
            });

            //Countries, States, Cities, Entities and Locations---------------------------------------------------------
            Route::namespace('Locations')->group(function (){
                Route::resource('/countries', 'CountryController', ['except' => ['create', 'edit']]);

                Route::post('/country/relation', 'CountryController@getCountryWithRelation');

                Route::resource('/entities', 'EntityController', ['except' => ['create', 'edit']]);

                Route::resource('/locations', 'LocationController', ['except' => ['create', 'edit']]);

                Route::post('/locations-types-list', 'LocationController@filterLocationsTypes');

                Route::get('/stock-locations/{locationId}', 'LocationController@stockLocationsByLocationId');

                Route::resource('/states', 'StateController', ['except' => ['create', 'edit']]);

                Route::resource('/cities', 'CityController', ['except' => ['create', 'edit']]);

                Route::get('/city-stock-locations/{cityId}', 'CityController@stockLocationsByCityId');

                Route::post('/locations-addresses-list', 'LocationController@filterLocationsAddresses');

                Route::post('/states-list', 'StateController@filterStates');

                Route::post('/zone-list', 'ZoneController@getZoneList');

                Route::post('/zone', 'ZoneController@createOrUpdate');

                Route::get('/zone/{id}', 'ZoneController@show');
                
                Route::delete('/zone/{id}', 'ZoneController@destroy');

                Route::post('/zone-stock-location', 'ZoneController@getStockLocation');
            });

            //Modules---------------------------------------------------------------------------------------------------
            Route::namespace('Modules')->group(function (){
                Route::resource('/modules', 'ModuleController');

                Route::post('/modules-list', 'ModuleController@index');

                Route::get('/operations-list', 'OperationController@index');
            });

            //Role-Groups-----------------------------------------------------------------------------------------------
            Route::namespace('Authorizations')->group(function (){
                Route::resource('/role-groups','RoleGroupController');
                Route::post('/role-groups/attach-roles', 'RoleGroupController@attachRolesToRoleGroup');

                Route::resource('/roles','RoleController');
                Route::post('/roles/attach-permissions', 'RoleController@attachPermissionsToRole');
            });

            //Currency--------------------------------------------------------------------------------------------------
            Route::namespace('Currency')->group(function (){
                Route::resource('/currencies', 'CurrencyController', ['except' => ['create', 'edit']]);

                Route::post('/currencies-conversions', 'CurrencyController@currenciesConversionsStore');

                Route::post('/currencies-conversions-list', 'CurrencyController@getCurrenciesConversionsList');

                Route::post('/currencies-conversions-rate', 'CurrencyController@getCurrenciesConversionsRate');
            });

            //Products--------------------------------------------------------------------------------------------------
            Route::namespace('Products')->group(function (){
                Route::resource('/product-categories', 'ProductCategoryController', ['except' => ['create', 'edit']]);

                Route::post('/product-categories-list', 'ProductCategoryController@filterProductCategories');

                Route::post('/products-list', 'ProductController@filterProducts');

                Route::post('/products-search', 'ProductController@searchProducts');

                Route::put('/product-details/{id}', 'ProductController@update');

                Route::post('/product-details', 'ProductController@productDetails');

                Route::post('/product-price', 'ProductController@productPrice');

                Route::delete('/product-image-delete/{id}', 'ProductController@deleteProductImage');
            });

            //Kitting---------------------------------------------------------------------------------------------------
            Route::namespace('Kitting')->group(function (){
                Route::post('/kitting-list', 'KittingController@filterKitting');

                Route::post('/kitting-details', 'KittingController@kittingDetails');

                Route::put('/kitting-details/{id}', 'KittingController@update');

                Route::post('/kitting', 'KittingController@createOrUpdateKitting');
            });

            //Product and Kitting---------------------------------------------------------------------------------------
            Route::namespace('ProductAndKitting')->group(function (){
                Route::post('/sales/search-product-kitting', 'ProductAndKittingController@searchProductOrKitting');

                Route::post('/search/product-kitting', 'ProductAndKittingController@searchProductOrKitting');

                Route::post('/search/product-kitting/enrollment', 'ProductAndKittingController@searchProductOrKittingEnrollment');
            });

            //Dummy Products--------------------------------------------------------------------------------------------
            Route::namespace('Dummy')->group(function (){
                Route::post('/dummy-list', 'DummyController@filterDummy');

                Route::post('/dummy-details', 'DummyController@dummyDetails');

                Route::post('/dummy', 'DummyController@createOrUpdateDummy');
            });

            //Promotion Free Items--------------------------------------------------------------------------------------
            Route::namespace('Promotions')->group(function (){
                Route::post('/promotion-free-item-list', 'PromotionFreeItemController@filterPromotionFreeItem');

                Route::post('/promotion-free-item-details', 'PromotionFreeItemController@PromotionFreeItemDetails');

                Route::post('/promotion-free-item', 'PromotionFreeItemController@createOrUpdatePromotionFreeItem');
            });

            //Settings--------------------------------------------------------------------------------------------------
            Route::namespace('Settings')->group(function (){
                Route::resource('/taxes', 'TaxController', ['except' => ['create', 'edit']]);

                Route::post('/country-dynamic-content/types', 'CountryDynamicContentController@getCountryDynamicContentByType');

                Route::post('/country-dynamic-content', 'CountryDynamicContentController@store');

                Route::post('/setting/rounding-adjustment', 'SettingController@getRoundingAdjustmentSetting');

                Route::get('/setting/giro-types/{countryId}', 'SettingController@getGiroTypeSetting');
            });

            //Member----------------------------------------------------------------------------------------------------
            Route::namespace('Members')->group(function (){
                Route::get('/member-dashboard', 'MemberController@getMemberDashboard');

                Route::post('/member-list', 'MemberController@filterMembers');

                Route::post('/member-details', 'MemberController@memberDetails');

                Route::put('/member/{userId}', 'MemberController@updateMember');

                Route::post('/member-ranks-list', 'MemberController@getMemberRanksList');

                Route::post('/member-ranks', 'MemberController@memberRanksStore');

                Route::post('/member-status-list', 'MemberController@getMemberStatusList');

                Route::post('/member-status', 'MemberController@memberStatusStore');

                Route::post('/member-migrate-list', 'MemberController@getMemberMigrateList');

                Route::post('/member-migrate', 'MemberController@memberMigrateStore');

                Route::post('/classic-member-verify','MemberController@verifyClassicMember');

                Route::post('/placement-network-performance', 'MemberController@getPlacementNetworkPerformance');

                Route::post('/member-campaign-report', 'MemberController@getMemberCampaignReport');

                Route::post('/member-email-validate', 'MemberController@memberEmailVerification');

                Route::post('/member-placement-tree','MemberTreeController@memberPlacementTree');

                Route::post('/member-placement-tree-outer','MemberTreeController@memberPlacementTreeOuter');

                Route::post('/member-tree','MemberTreeController@memberTree');

                Route::post('/member-tree-downline-verify','MemberTreeController@memberTreeDownlineVerify');

                Route::post('/same-member-tree-network-verify','MemberTreeController@sameMemberTreeNetworkVerify');

                Route::post('/member-placement-verify', 'MemberTreeController@verifyPlacement');

                Route::post('/sponsor-downline-listing', 'MemberTreeController@getSponsorDownlineListing');

                Route::post('/member-sponsor-search', 'MemberTreeController@searchSponsorNetwork');

                Route::post('/member-tree-assign', 'MemberTreeController@assignMemberTree');
            });

            //General---------------------------------------------------------------------------------------------------
            Route::namespace('General')->group(function (){
                Route::post('/cw-schedules-list','CwSchedulesController@cwSchedulesList');

                Route::post('/enrollment-ranks-list', 'CwSchedulesController@enrollmentRanksList');

                Route::post('/team-bonus-ranks-list', 'CwSchedulesController@teamBonusRanksList');
            });

            //Sales-----------------------------------------------------------------------------------------------------
            Route::namespace('Sales')->group(function (){
                Route::post('/sales', 'SaleController@create');

                Route::put('/sales/{saleId}', 'SaleController@update');

                Route::post('/sales-list', 'SaleController@filterSales');

                Route::post('/sales-details', 'SaleController@salesDetails');

                Route::post('/sales/sales-product-eligible', 'SaleController@getSalesProductDetails');

                Route::post('/sales/sale-cancellation-invoice-details', 'SaleCancellationController@getSalesCancellationInvoiceDetails');

                Route::post('/sales/sale-cancellation-list', 'SaleCancellationController@filterSalesCancellation');

                Route::post('/sales/sale-cancellation-details', 'SaleCancellationController@saleCancellationDetails');

                Route::post('/sales/sale-cancellation-create', 'SaleCancellationController@create');

                Route::post('/sales/sale-cancellation-batch-refund', 'SaleCancellationController@salesCancellationBatchRefund');

                Route::post('/sales/creditnote/downloadpdf', 'SaleController@downloadCreditNote');

                Route::post('/sales/lagacy-sale-cancellation-create', 'SaleCancellationController@createLegacySalesCancellation');

                //sales exchange api routes---------------------------
                Route::post('/sales-exchange', 'SaleExchangeController@create');

                Route::post('/sales-exchange-list', 'SaleExchangeController@filterSalesExchange');

                Route::post('/sales-exchange-details', 'SaleExchangeController@salesExchangeDetails');

                Route::post('/sales-exchange/exchangenote/downloadpdf', 'SaleExchangeController@getExchangeBill');

                //sales report
                Route::post('/sale-daily-report', 'SaleController@downloadSaleDailyReport');

                Route::post('/sale-mpos-report', 'SaleController@downloadSaleMposReport');

                Route::post('/sale-product-report', 'SaleController@downloadSaleProductReport');

                Route::post('/download-preorder-note', 'SaleController@downloadPreOrderNote');
            });

            //Invoices--------------------------------------------------------------------------------------------------
            Route::namespace('Invoices')->group(function (){
                Route::post('/invoice-list', 'InvoiceController@filterInvoices');

                Route::post('/invoice-details', 'InvoiceController@invoiceDetails');

                Route::post('/invoice/downloadpdf', 'InvoiceController@downloadInvoice');

                Route::post('/stockist-daily-invoice-transaction-list', 'InvoiceController@getStockistDailyInvoiceTransactionList');

                Route::post('/batch-release-stockist-daily-invoice-transaction', 'InvoiceController@batchReleaseStockistDailyInvoiceTransaction');

                Route::post('/download-auto-maintenance-invoice', 'InvoiceController@downloadAutoMaintenanceInvoice');

                Route::post('/tax-invoice-summary-report', 'InvoiceController@taxInvoiceSummaryReport');

                Route::post('/tax-invoice-details-report', 'InvoiceController@taxInvoiceDetailsReport');
            });

            //workflow api ---------------------------------------------------------------------------------------------
            Route::namespace('Workflows')->group(function () {
                Route::post('/workflow-tracking-details', 'WorkflowController@getTrackingWorkflowDetails');

                Route::post('/workflow-tracking-step-update', 'WorkflowController@updateWorkflowTrackingStep');
            });

            //Payments api ---------------------------------------------------------------------------------------------
            Route::namespace('Payments')->group(function () {

                //to get all the supported payments
                Route::post('/payments/sales', 'PaymentController@getSupportedSalePayments');

                // to make a sale payment
                Route::post('/payments/payment-make', 'PaymentController@makePayment');

                // to make a sale payment
                Route::post('/payments/share-payment-detail', 'PaymentController@sharePaymentDetail');

                // to retrieve the payment object
                Route::get('/payments/status/{payment_id}', 'PaymentController@getPaymentStatus');

                // to get epp payment listing
                Route::post('/payments/epp-payment-list', 'PaymentController@eppPaymentListing');

                // to update epp moto approve code
                Route::post('/payments/update-epp-moto-approve-code', 'PaymentController@updateEppMotoApproveCode');

                // to update epp moto approve code
                Route::post('/payments/epp-moto-sale-convert', 'PaymentController@eppPaymentSaleConvert');

                // to get aeon payment listing
                Route::post('/payments/aeon-payment-list', 'PaymentController@aeonPaymentListing');

                // to update aeon payment approve code
                Route::post('/payments/update-aeon-agreement-number', 'PaymentController@updateAeonAgreementNumber');

                // aeon payment cooling off release
                Route::post('/payments/aeon-payment-cooling-off-release', 'PaymentController@aeonPaymentCoolingOffRelease');

                // payment batch cancel
                Route::post('/payments/payment-batch-cancel', 'PaymentController@paymentBatchCancel');

                Route::post('/payment-document-details', 'PaymentController@paymentDocumentDetails');
            });

            //Bonus api ------------------------------------------------------------------------------------------------
            Route::namespace('Bonus')->group(function (){
                Route::post('/cw-bonus-report', 'BonusController@getCwBonusReport');

                Route::post('/bonus-statement', 'BonusController@getBonusStatement');

                Route::post('/yearly-income-report', 'BonusController@getYearlyIncomeReport');

                Route::post('/cp-37f-form', 'BonusController@getCp37fForm');

                Route::post('/self-billed-invoice', 'BonusController@getSelfBilledInvoice');

                Route::post('/self-billed-invoice-stockist', 'BonusController@getSelfBilledInvoiceStockist');

                Route::post('/stockist-commission', 'BonusController@getStockistCommissionStatement');

                Route::post('/sponsor-tree', 'BonusController@getSponsorTree');

                Route::post('/incentive-summary', 'BonusController@getIncentiveSummary');

                Route::post('/welcome-bonus-summary', 'BonusController@getWelcomeBonusSummary');

                Route::post('/welcome-bonus-detail', 'BonusController@getWelcomeBonusDetail');

                Route::post('/bonus-adjustment-listing', 'BonusController@getBonusAdjustmentListing');

                Route::post('/tw-77k-report', 'BonusController@get77kReport');

                Route::post('/tw-wht-report', 'BonusController@getWhtReport');
            });

            //Virel Integration api -----------------------------------------------------------------------------------
            Route::namespace('Virel')->group(function() {
                Route::post('/virel-user', 'VirelController@getUser');

                Route::post('/virel-member', 'VirelController@getMember');

                Route::get('/virel-product-categories', 'VirelController@getProductCategories');

                Route::get('/virel-products', 'VirelController@getProducts');

                Route::get('/virel-promo-products', 'VirelController@getPromoProducts');
            });

            //File Management api --------------------------------------------------------------------------------------
            Route::namespace('FileManagement')->group(function() {
                Route::post('/smart-library-list', 'SmartLibraryController@getSmartLibraryList');

                Route::post('/smart-library-product', 'SmartLibraryController@getSmartLibraryProductList');

                Route::post('/smart-library-file-type', 'SmartLibraryController@getSmartLibraryFileTypeList');

                Route::post('/smart-library', 'SmartLibraryController@createOrUpdate');

                Route::get('/smart-library/{id}', 'SmartLibraryController@show');
                
                Route::delete('/smart-library/{id}', 'SmartLibraryController@destroy');
            });

            //Stockist api ---------------------------------------------------------------------------------------------
            Route::namespace('Stockists')->group(function() {
                Route::post('/stockist-list', 'StockistController@filterStockists');

                Route::post('/stockist-details', 'StockistController@stockistDetails');

                Route::middleware('convertToNull')->post('/stockist', 'StockistController@createOrUpdateStockist');

                Route::post('/consignment-deposit-refund-validate', 'StockistController@consignmentDepositRefundValidate');

                Route::post('/consignment-deposit-refund-list', 'StockistController@filterConsignmentDepositRefund');

                Route::post('/consignment-deposit-refund-details', 'StockistController@consignmentDepositRefundDetails');

                Route::post('/consignment-deposit', 'StockistController@createConsignmentDeposit');

                Route::post('/consignment-refund', 'StockistController@createConsignmentRefund');

                Route::put('/consignment-deposit-refund/{consignmentDepositReturnId}', 'StockistController@updateConsignmentDepositReturn');

                Route::post('/consignment-return-validate', 'StockistController@consignmentReturnValidate');

                Route::post('/consignment-order-return-list', 'StockistController@getConsignmentOrderReturnByFilters');

                Route::post('/consignment-order-return-details', 'StockistController@consignmentOrderReturnDetails');

                Route::post('/consignment-order-return', 'StockistController@createConsignmentOrderReturn');

                Route::post('/consignment-return-product-validate', 'StockistController@consignmentReturnProductValidate');

                Route::post('/consignment/downloadpdf', 'StockistController@downloadConsignmentNote');

                Route::post('/stockist-daily-sale-payment-verification-list', 'StockistController@getSalesDailyPaymentVerificationLists');

                Route::post('/stockist-daily-sale-payment-verification-update', 'StockistController@batchUpdateStockistOutstandingPayment');

                Route::post('/stockist-outstanding-summary', 'StockistController@getStockistOutstandingSummary');

                Route::post('/daily-collection-report', 'StockistController@downloadDailyCollectionReport');

                Route::post('/download-consignment-deposit-receipt', 'StockistController@downloadDepositReceipt');

                Route::post('/stockist-consignment-stock-report', 'StockistController@downloadStockistConsignmentStockReport');
            });

            //e-Wallet api ---------------------------------------------------------------------------------------------
            Route::namespace('EWallet')->prefix('ewallet')->group( function() {

                Route::get('/', 'EWalletController@eWalletInfo');

                Route::get('/transaction/{id}', 'EWalletController@getTransaction');

                Route::post('/transactions', 'EWalletController@transactionListing');

                Route::post('/new-transaction', 'EWalletController@createTransaction');

                Route::post('/validate-mobile', 'EWalletController@eWalletValidateMobileNumber');

                Route::post('/activate-ewallet', 'EWalletController@eWalletActivation');

                Route::post('/validate-security-pin/', 'EWalletController@eWalletValidateSecurityPin');

                Route::post('/forgot-security-pin/', 'EWalletController@eWalletSetNewSecurityNumber');

                Route::post('/change-autowithdrawal', 'EWalletController@eWalletAutoWithdrawal');

                Route::post('/bank-payment', 'EWalletController@bankGIRO');

                Route::post('/bank-payment-history', 'EWalletController@bankGIROHistory');

                Route::post('/generate-bank-payment', 'EWalletController@generateBankGIROFile');

                Route::post('/submit-rejected-payment-records', 'EWalletController@submitRejectedGIRORecords');

                Route::post('/rejected-payment', 'EWalletController@listRejectedPaymentRecords');

                Route::get('/get-rejected-payment-sample-file', 'EWalletController@downloadRejectedPaymentSampleFile');

                Route::post('/rejected-payment-level-one', 'EWalletController@rejectedPaymentLevelOneApproval');

                Route::post('/rejected-payment-level-two', 'EWalletController@rejectedPaymentLevelTwoApproval');

                Route::post('/adjustment', 'EWalletController@eWalletAdjustmentListing');

                Route::post('/adjustment/create', 'EWalletController@eWalletAdjustmentCreate');

                Route::post('/adjustment/details', 'EWalletController@eWalletAdjustmentDetails');

                Route::put('/adjustment/level-one/{id}', 'EWalletController@eWalletAdjustmentLevelOneApproval');

                Route::put('/adjustment/level-two/{id}', 'EWalletController@eWalletAdjustmentLevelTwoApproval');
            });

            //Shop API -------------------------------------------------------------------------------------------------
            Route::namespace('Shop')->group(function () {
                Route::resource('/shop/favorite', 'ShopFavoritesController');

                Route::get('/shop/cart-clear', 'ShopCartController@clearCart');

                Route::get('/shop/categories', 'ShopDescriptiveController@categories');

                Route::get('/shop/cw-info', 'ShopDescriptiveController@memberCurrentCwDetails');

                Route::post('/shop/cart', 'ShopCartController@store');

                Route::post('/shop/cart-details', 'ShopCartController@details');

                Route::post('/shop/create-sale', 'ShopCartController@createSale');

                Route::post('/shop/create-cart-sale', 'ShopCartController@createCartSale');

                Route::post('/shop/product-and-kitting', 'ShopProductsController@getProductAndKitting');
            });

            //Campaign Management api ----------------------------------------------------------------------------------
            Route::namespace('Campaigns')->group(function() {
                Route::post('/esac-voucher-type-list', 'EsacVoucherTypeController@getEsacVoucherTypeList');

                Route::post('/esac-voucher-sub-type-list', 'EsacVoucherSubTypeController@getEsacVoucherSubTypeList');

                Route::post('/campaign-list', 'CampaignController@getCampaignList');

                Route::post('/esac-promotion-list', 'EsacPromotionController@getEsacPromotionList');

                Route::post('/esac-voucher-list', 'EsacVoucherController@getEsacVoucherList');

                Route::post('/esac-voucher-type', 'EsacVoucherTypeController@createOrUpdate');

                Route::get('/esac-voucher-type/{id}', 'EsacVoucherTypeController@show');
                
                Route::delete('/esac-voucher-type/{id}', 'EsacVoucherTypeController@destroy');

                Route::post('/esac-voucher-sub-type', 'EsacVoucherSubTypeController@createOrUpdate');

                Route::get('/esac-voucher-sub-type/{id}', 'EsacVoucherSubTypeController@show');
                
                Route::delete('/esac-voucher-sub-type/{id}', 'EsacVoucherSubTypeController@destroy');

                Route::post('/campaign', 'CampaignController@createOrUpdate');

                Route::get('/campaign/{id}', 'CampaignController@show');
                
                Route::delete('/campaign/{id}', 'CampaignController@destroy');

                Route::post('/esac-promotion', 'EsacPromotionController@createOrUpdate');

                Route::get('/esac-promotion/{id}', 'EsacPromotionController@show');
                
                Route::delete('/esac-promotion/{id}', 'EsacPromotionController@destroy');

                Route::post('/esac-voucher', 'EsacVoucherController@createOrUpdate');

                Route::get('/esac-voucher/{id}', 'EsacVoucherController@show');
                
                Route::delete('/esac-voucher/{id}', 'EsacVoucherController@destroy');
            });

            //Enrollment api--------------------------------------------------------------------------------------------
            Route::namespace('Enrollments')->group(function (){
                Route::post('/enrollment', 'EnrollmentController@create');

                Route::post('/enrollment-temp-data', 'EnrollmentController@getTempData');

                Route::get('/enrollment-types/{countryId}', 'EnrollmentController@getEnrollmentTypesByCountry');
            });

            //Integration api-------------------------------------------------------------------------------------------
            Route::namespace('Integrations')->group(function (){
                Route::post('/yonyou-integration-log-list', 'YonyouController@getYonyouIntegrationLogList');

                Route::post('/retry-failed-yonyou-integration', 'YonyouController@retryFailedYonyouIntegration');
            });
        });

        //Languages-----------------------------------------------------------------------------------------------------
        Route::namespace('Languages')->group(function (){
            Route::resource('/languages', 'LanguageController', ['except' => ['create', 'edit']]);

            Route::post('/languages-list', 'LanguageController@index');
        });

        //YY Products Import--------------------------------------------------------------------------------------------
        Route::middleware('api_key')->group(function (){
            Route::namespace('Products')->group(function (){
                Route::post('/products-import', 'ProductController@importYYProducts');
            });

            Route::namespace('Payments')->group(function (){
                Route::post('/payments/create-external-payment', 'PaymentController@createPaymentExternal');
            });

            Route::namespace('Sales')->group(function (){
                Route::post('/sales/create-external-sales', 'SaleController@createExpressSales');

                Route::post('/delivery-order', 'DeliveryOrderController@create');
            });
        });
    });

    //this section for api's which does not uses json body--------------------------------------------------------------
    Route::middleware('auth')->group(function(){

        Route::namespace('Uploader')->group(function () {
            Route::post('/file-upload-process', 'UploaderController@processUploadFile');

            Route::post('/file-upload-setting', 'UploaderController@getClientSetting');
        });

        Route::namespace('EWallet')->group( function() {
            Route::post('/ewallet/upload-rejected-payment-file', 'EWalletController@readRejectedGIROFile');
        });

    });

    //uploader api's----------------------------------------------------------------------------------------------------
    Route::namespace('Uploader')->group(function () {
        Route::get('file-download/{pathType}/{fileType}/{fileData}/{fileTime}/{fileHash}', 'UploaderController@downloadPrivateFile');
    });

    //process callback--------------------------------------------------------------------------------------------------
    Route::namespace('Payments')->group(function () {
        //some callback are in get method, some are post
        Route::any('/payments/callback/{salePaymentId}/{isBackendCall?}', 'PaymentController@processCallback');

        Route::get('/payments/redirect/{salePaymentId}', 'PaymentController@redirectPayments');
    });
});
