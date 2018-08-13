<?php
namespace App\Repositories\Payments;

use App\Events\Enrollments\EnrollUserEvent;
use App\Interfaces\{
    Payments\PaymentInterface,
    EWallet\EWalletInterface,
    Invoices\InvoiceInterface,
    Masters\MasterInterface,
    Sales\SaleExchangeInterface,
    Sales\SaleInterface,
    Stockists\StockistInterface,
    Settings\SettingsInterface,
    Campaigns\EsacVoucherInterface
};
use App\Models\{
    Campaigns\EsacVoucher,
    EWallets\EWallet,
    Invoices\Invoice,
    Locations\Country,
    Locations\LocationTypes,
    Locations\Location,
    Masters\MasterData,
    Payments\AeonPaymentFtpLog,
    Payments\PaymentModeDocumentDetail,
    Payments\PaymentModeProvider,
    Payments\PaymentModeSetting,
    Payments\Payment,
    Sales\Sale,
    Sales\SaleEsacVouchersClone,
    Stockists\ConsignmentDepositRefund,
    Users\User
};
use Illuminate\{
    Http\Request,
    Support\Facades\App,
    Support\Facades\Log,
    View\View,
    Support\Facades\Auth,
    Support\Facades\Config
};
use App\Repositories\BaseRepository;
use Mockery\Exception;
use Carbon\Carbon;

/**
 * Class PaymentRepository
 * @package App\Repositories\Payments
 *
 * Example usage :
 * In another repositories / other
 * $payment = new PaymentRepository();
 */

class PaymentRepository extends BaseRepository implements PaymentInterface
{
    private
        $saleObj,
        $saleEsacVouchersCloneObj,
        $countryObj,
        $esacVoucherObj,
        $paymentModeSettingsObj,
        $paymentModeProviderObj,
        $masterDataObj,
        $paymentObj,
        $eWalletRepositoryObj,
        $invoiceRepositoryObj,
        $saleRepositoryObj,
        $saleExchangeRepositoryObj,
        $eWalletObj,
        $invoiceObj,
        $locationObj,
        $locationTypesObj,
        $masterRepositoryObj,
        $settingsRepositoryObj,
        $esacVoucherRepositoryObj,
        $userObj,
        $aeonPaymentFtpLogObj,
        $consignmentDepositRefundObj,
        $paymentModeConfigCodes,
        $eppModeConfigCodes,
        $docStatusConfigCodes,
        $aeonStatusConfigCodes,
        $consignmentDepositRefundStatusConfigCodes,
        $stockistRepositoryObj,
        $saleOrderStatusConfigCodes,
        $locationTypeConfigCodes,
        $paymentModeDocumentObj,
        $eppDocStatusConfigCodes,
        $eppStatusConfigCodes,
        $stockistTransactionReleaseStatusConfigCodes,
        $aeonPaymentStockReleaseStatusConfigCodes
    ;

    /**
     * PaymentRepository constructor.
     *
     * @param Sale $sale
     * @param SaleEsacVouchersClone $saleEsacVouchersClone
     * @param EsacVoucher $esacVoucher
     * @param ConsignmentDepositRefund $consignmentDepositRefund
     * @param Country $country
     * @param PaymentModeProvider $paymentModeProvider
     * @param PaymentModeSetting $paymentMode
     * @param MasterData $masterData
     * @param Payment $payment
     * @param InvoiceInterface $invoiceInterface
     * @param EWalletInterface $eWalletInterface
     * @param EsacVoucherInterface $esacVoucherInterface
     * @param SaleInterface $saleInterface
     * @param SaleExchangeInterface $saleExchangeInterface
     * @param StockistInterface $stockistInterface
     * @param MasterInterface $masterInterface
     * @param SettingsInterface $settingsInterface
     * @param EWallet $eWallet
     * @param Invoice $invoice
     * @param Location $location
     * @param LocationTypes $locationTypes
     * @param User $user
     * @param AeonPaymentFtpLog $aeonPaymentFtpLog
     * @param PaymentModeDocumentDetail $paymentModeDocumentDetail
     */
    public function __construct(
        Sale $sale,
        SaleEsacVouchersClone $saleEsacVouchersClone,
        EsacVoucher $esacVoucher,
        ConsignmentDepositRefund $consignmentDepositRefund,
        Country $country,
        PaymentModeProvider $paymentModeProvider,
        PaymentModeSetting $paymentMode,
        MasterData $masterData,
        Payment $payment,
        InvoiceInterface $invoiceInterface,
        EWalletInterface $eWalletInterface,
        SaleInterface $saleInterface,
        SaleExchangeInterface $saleExchangeInterface,
        StockistInterface $stockistInterface,
        MasterInterface $masterInterface,
        SettingsInterface $settingsInterface,
        EsacVoucherInterface $esacVoucherInterface,
        EWallet $eWallet,
        Invoice $invoice,
        Location $location,
        LocationTypes $locationTypes,
        User $user,
        AeonPaymentFtpLog $aeonPaymentFtpLog,
        PaymentModeDocumentDetail $paymentModeDocumentDetail
    )
    {
        $this->saleObj = $sale;

        $this->saleEsacVouchersCloneObj = $saleEsacVouchersClone;

        $this->esacVoucherObj = $esacVoucher;

        $this->countryObj = $country;

        $this->paymentModeSettingsObj = $paymentMode;

        $this->paymentModeProviderObj = $paymentModeProvider;

        $this->masterDataObj = $masterData;

        $this->paymentObj = $payment;

        $this->invoiceRepositoryObj = $invoiceInterface;

        $this->eWalletRepositoryObj = $eWalletInterface;

        $this->saleRepositoryObj = $saleInterface;

        $this->saleExchangeRepositoryObj = $saleExchangeInterface;

        $this->stockistRepositoryObj = $stockistInterface;

        $this->masterRepositoryObj = $masterInterface;

        $this->settingsRepositoryObj = $settingsInterface;

        $this->esacVoucherRepositoryObj = $esacVoucherInterface;

        $this->eWalletObj = $eWallet;

        $this->invoiceObj = $invoice;

        $this->locationObj = $location;

        $this->locationTypesObj = $locationTypes;

        $this->userObj = $user;

        $this->aeonPaymentFtpLogObj = $aeonPaymentFtpLog;

        $this->paymentModeDocumentObj = $paymentModeDocumentDetail;

        $this->consignmentDepositRefundObj = $consignmentDepositRefund;

        $this->eppModeConfigCodes = Config::get('mappings.epp_mode');

        $this->paymentModeConfigCodes = Config::get('mappings.payment_mode');

        $this->docStatusConfigCodes = Config::get('mappings.aeon_payment_document_status');

        $this->aeonStatusConfigCodes = Config::get('mappings.aeon_payment_approval_status');

        $this->eppDocStatusConfigCodes = Config::get('mappings.epp_payment_document_status');

        $this->eppStatusConfigCodes = Config::get('mappings.epp_payment_approval_status');

        $this->consignmentDepositRefundStatusConfigCodes =
            Config::get('mappings.consignment_deposit_and_refund_status');

        $this->saleOrderStatusConfigCodes = Config::get('mappings.sale_order_status');

        $this->locationTypeConfigCodes = Config::get('mappings.locations_types');

        $this->stockistTransactionReleaseStatusConfigCodes =
            config('mappings.stockist_daily_transaction_release_status');

        $this->aeonPaymentStockReleaseStatusConfigCodes =
            config('mappings.aeon_payment_stock_release_status');
    }

    /**
     * Map payment object
     *
     * @param $countryCodes
     * @param string $paymentProvider - name of the payment provider, must be in iso_2 format
     * @return mixed
     * @throws \Exception
     */
    public function getPaymentObject(string $countryCodes, string $paymentProvider = '')
    {
        //here we will need to link with the model to get all the payments available based on channel

        $countriesMapping = config('payments.general.countries_mapping');
        //will resolved to the desired payment class based on the country
        $paymentClassName = 'App\Helpers\Classes\Payments\\'.$countriesMapping[$countryCodes].'\\'.ucfirst($paymentProvider);

        //try to resolve the payment class name and return the instance if found.
        if(class_exists($paymentClassName)){
            return App::make($paymentClassName);
        }

        throw new \Exception("Payment class not found");
    }

    /**
     * Return the supported payments for the specific country + location type
     *
     * @param int $countryId
     * @param int $locationId
     * @param array $excludePaymentModes
     * @param array $excludePaymentProviders
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSupportedPayments
    (
        int $countryId,
        int $locationId,
        array $excludePaymentModes = [],
        array $excludePaymentProviders = []
    )
    {
        //Get Payment Mode Master ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        //Get exclude payment mode id
        $excludePaymentModeId = [];

        collect($excludePaymentModes)->each(function($excludePaymentMode)
            use ($paymentMode, &$excludePaymentModeId) {
                if(isset($this->paymentModeConfigCodes[strtolower($excludePaymentMode)])){
                    array_push($excludePaymentModeId,
                        $paymentMode[$this->paymentModeConfigCodes[
                            strtolower($excludePaymentMode)]]);
                }
            });

        //Get Location Type Id by Location ID
        $locationTypeId = $this->locationObj->find($locationId)->location_types_id;

        $payments = $this->masterDataObj
            ->with(['paymentModeProvider.paymentModeSetting'
                    => function($settingQuery) use($countryId, $locationTypeId){
                        $settingQuery->where('country_id', $countryId);
                        $settingQuery->where('location_type_id', $locationTypeId);
                        $settingQuery->where('active', 1);
                    },
                'paymentModeProvider'
                    =>function($providerQuery) use($excludePaymentProviders){
                        $providerQuery->whereNotIn('code', $excludePaymentProviders);
                    }
            ])
            ->whereHas('paymentModeSetting',
                function($settingQuery) use ($countryId, $locationTypeId){
                    $settingQuery->where('country_id', $countryId);
                    $settingQuery->where('location_type_id', $locationTypeId);
                    $settingQuery->where('active', 1);
                }
            )
            ->whereNotIn('id', $excludePaymentModeId)
            ->orderBy("sort", "asc")
            ->get();

        $payments = collect($payments)->map(function($paymentsData){
            $paymentModeProviderFilter = collect($paymentsData->paymentModeProvider)
                ->map(function($paymentsProvider){
                    if(collect($paymentsProvider->paymentModeSetting)->isNotEmpty()){
                        return $paymentsProvider;
                    }
                });

            $paymentModeProviderRemove = collect($paymentModeProviderFilter)
                ->reject(function ($providerData) {
                    return empty($providerData);
                });

            $paymentModeProviderDetail = collect($paymentModeProviderRemove)
                ->map(function ($providerDetail) {
                    $providerDetail->payment_mode_setting = collect($providerDetail->paymentModeSetting)
                        ->map(function ($paymentSetting){
                            $paymentSetting->setting_detail = json_decode($paymentSetting->setting_detail);
                            return $paymentSetting;
                        });
                    return $providerDetail;
                });

            return [
                'id' => $paymentsData->id,
                'master_id' => $paymentsData->master_id,
                'title' => $paymentsData->title,
                'sort' => $paymentsData->sort,
                'created_by' => $paymentsData->created_by,
                'updated_by' => $paymentsData->updated_by,
                'payment_mode_provider' => $paymentModeProviderDetail
            ];
        });

        return $payments;
    }

    /**
     * Consolidated Make Payment Function
     *
     * @param string $payType
     * @param int $paymentId
     * @param array $fields
     * @param int $saleId
     * @param int $consignmentDepositId
     * @param int $ewalletId
     * @param bool $isShare
     * @return array
     * @throws \Exception
     */
    public function makePayment(
        string $payType,
        int $paymentId,
        array $fields,
        int $saleId = 0,
        int $consignmentDepositId = 0,
        int $ewalletId = 0,
        bool $isShare = false
    )
    {
        $result = [];
        $fields = collect($fields);

        if($payType == "sales"){

            $result = $this->salesPay(
                $saleId,
                $paymentId,
                $fields,
                $isShare
            );

        } else if($payType == "consignment_deposit"){

            $result = $this->consignmentDepositPay(
                $consignmentDepositId,
                $paymentId,
                $fields,
                $isShare
            );

        } else if($payType == "user_ewallets"){

            $result = $this->eWalletPay(
                $ewalletId,
                $paymentId,
                $fields,
                $isShare
            );

        }

        return $result;
    }

    /**
     * get third party share payment post data for a given paymentId
     *
     * @param int $paymentId
     * @return array|mixed
     */
    public function sharePaymentDetail(int $paymentId)
    {
        $payment = $this->paymentObj->find($paymentId);

        if($payment->mapping_model == 'sales'){

            $saleDetail = $this->saleRepositoryObj->find($payment->mapping_id);

            $paymentCountry = $saleDetail->country()->first();

            $locationTypeId = $saleDetail->transactionLocation()->first()->location_types_id;

            $userId =  $saleDetail->user_id;

        } else if ($payment->mapping_model == 'consignments_deposits_refunds') {

            $consignmentDetail = $this->consignmentDepositRefundObj->find($payment->mapping_id);

            $paymentCountry = $consignmentDetail->stockist->country()->first();

            $locationTypeId = $consignmentDetail->stockist->stockistLocation()->first()->location_types_id;

            $userId =  $consignmentDetail->stockist_user_id;

        } else {

            //For user_ewallets Model
            $eWalletDetail = $this->eWalletObj->find($payment->mapping_id);

            $paymentCountry = $eWalletDetail->user->member->country()->first();

            $userCountryEntityId = $eWalletDetail->user->member->country->entity->id;

            $locationTypeId = $this->locationTypesObj
                ->where('code', $this->locationTypeConfigCodes['online'])
                ->first()->id;

            $userId =  $eWalletDetail->user_id;

        }

        $paymentProvider = $this->paymentModeSettingsObj
            ->where('payment_mode_provider_id', $payment->paymentModeProvider->id)
            ->where('country_id', $paymentCountry->id)
            ->where('location_type_id', $locationTypeId)
            ->first();

        $paymentObject = $this->getPaymentObject($paymentCountry->code_iso_2, $paymentProvider->configuration_file_name);

        if($paymentObject->isFormGenerateRequired()){

            $paymentObject->setAdditionalParams(collect([]));

            $info = $this->getPaymentFormData($userId, $payment->id, $paymentCountry->currency->code, $payment->amount);

            $formData = $paymentObject->getFormData($info);
        }

        return [
            'form_data' => $formData,
            'payment' => $payment,
            'next_step' => true
        ];
    }

    /**
     * to get the payment status of the payment
     *
     * @param $id
     * @return mixed
     */
    public function getPaymentStatus($id)
    {
        return $this->paymentObj->find($id)->status;
    }

    /**
     * make sale payment
     *
     * @param int $saleId
     * @param int $paymentModeId
     * @param $fields
     * @param bool $isShare
     * @return array|mixed
     * @throws \Exception
     */
    public function salesPay
    (
        int $saleId,
        int $paymentModeId,
        $fields,
        bool $isShare = false
    )
    {
        $returnValues = array(); //return array

        $sale = $this->saleRepositoryObj->find($saleId);

        $amount = $fields->where('name','amount')->first()['value'];

        //for now we will just insert into the payment table
        $salePaymentResults = $this->insertPaymentRecord(
            $saleId,
            'sales',
            $paymentModeId,
            $sale->country->id,
            $amount,
            $fields,
            false,
            $isShare
        );

        $paymentObject = $salePaymentResults['paymentObject'];

        $salePayment = $salePaymentResults['payment'];

        //if nothing is created, throw an exception
        if(!$salePayment->id){
            throw new Exception('Sales Payment not created');
        }

        //Need convert sale stats to pre-order after payment was maked if sale status is equal pending
        $saleOrderStatus = $this->masterRepositoryObj
            ->getMasterDataByKey(['sale_order_status']);

        $saleOrderStatus = $saleOrderStatus['sale_order_status']->first();

        $saleOrderPendingStatusId = $saleOrderStatus
                ->where('title',strtoupper($this->saleOrderStatusConfigCodes['pending']))
                ->pluck('id');

        if($sale->order_status_id == $saleOrderPendingStatusId[0]){

            $saleOrderPreOrderStatusId = $saleOrderStatus
                ->where('title',strtoupper($this->saleOrderStatusConfigCodes['pre-order']))
                ->pluck('id');

            //update the sales to be pre-order
            $sale->order_status_id = $saleOrderPreOrderStatusId[0];

            $sale->save();
        }

        $salePayments = $sale->salePayments()->get(); // this is the full payments details for the sale

        $totalPaid = 0; // total paid from the sales payments (confirmed)

        $totalPaidPending = 0; //virtually paid but not paid in db

        $salePayments->each(function($payment) use (&$totalPaid, &$totalPaidPending){
            ($payment->status == 1) ?
                $totalPaid += $payment->amount :
                    $totalPaidPending += $payment->amount;
        });

        //now if this is manual type of payment, we will not try to convert this into an invoice if this is a final payment
        if($paymentObject->isManual()){

            $returnValues['sale_generated'] = $this->actualSalesGenerated($sale, true);

        } else {
            //if this is not a manual type of payment, and required to generate frontend form
            //then will pass the information needed to the frontend
            if($paymentObject->isFormGenerateRequired()){
                // lets format the information needed
                $info = $this->getPaymentFormData($sale->user_id, $salePayment->id, $sale->country->currency->code, $amount);

                $returnValues['form_data'] = $paymentObject->getFormData($info);
            }

            $returnValues['sale_generated'] = false;
        }

        //Get Epp Motor Payment Mode ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $eppMotoPaymentProviderIds = $this->paymentModeProviderObj
            ->where('master_data_id', $paymentMode[$this->paymentModeConfigCodes['epp (moto)']])
            ->pluck('id');

        //Add Status Title Display Indicator
        $eppMotoPaymentProviderIds = $eppMotoPaymentProviderIds->toArray();

        $salePayment->is_approve = (in_array($salePayment->payment_mode_provider_id, $eppMotoPaymentProviderIds)) ?
            true : false;

        collect($salePayments)->each(function ($salePaymentDetail)
            use ($eppMotoPaymentProviderIds){
                $salePaymentDetail->is_approve = (in_array($salePaymentDetail->payment_mode_provider_id, $eppMotoPaymentProviderIds)) ?
                    true : false;
            });

        //if this is an auto payment, the sales is not generated at this moment
        return array_merge($returnValues, [
            'payment' => $salePayment,
            'payments' => $salePayments,
            'total_paid' => $totalPaid,
            'total_paid_pending' => $totalPaidPending,
            'next_step' => ($paymentObject->isManual()) ? false : true
        ]);
    }

    /**
     * To make a payment to a consignment deposit
     *
     * @param int $consignmentDepositId
     * @param int $paymentModeId
     * @param $fields
     * @param bool $isShare
     * @return array
     * @throws \Exception
     */
    public function consignmentDepositPay(
        int $consignmentDepositId,
        int $paymentModeId,
        $fields,
        bool $isShare = false
    )
    {
        $returnValues = array(); //return array

        $consignmentDeposit = $this->consignmentDepositRefundObj->find($consignmentDepositId);

        $amount = $fields->where('name','amount')->first()['value'];

        //for now we will just insert into the payment table
        $paymentResults = $this->insertPaymentRecord(
            $consignmentDepositId,
            'consignments_deposits_refunds',
            $paymentModeId,
            $consignmentDeposit->stockist->country->id,
            $amount,
            $fields,
            false,
            $isShare
        );

        $paymentObject = $paymentResults['paymentObject'];

        $consignmentDepositPayment = $paymentResults['payment'];

        $consignmentDepositPayments = $consignmentDeposit->payments()->get();

        $totalPaid = $totalPaidPending = 0;

        $consignmentDepositPayments->each(function($payment) use (&$totalPaid, &$totalPaidPending){
            ($payment->status == 1) ?
                $totalPaid += $payment->amount :
                    $totalPaidPending += $payment->amount;
        });

        if($paymentObject->isManual()){

            $returnValues['consignment_generated'] = $this->consignmentDepositGenerated($consignmentDeposit);

        } else {

            if($paymentObject->isFormGenerateRequired()){

                $info = $this->getPaymentFormData(
                    $consignmentDeposit->stockist->stockist_user_id,
                    $consignmentDepositPayment->id,
                    $consignmentDeposit->stockist->country->currency->code,
                    $amount
                );

                $returnValues['form_data'] = $paymentObject->getFormData($info);
            }

            $returnValues['consignment_generated'] = false;
        }

        return array_merge($returnValues, [
            'payment' => $consignmentDepositPayment,
            'payments' => $consignmentDepositPayments,
            'total_paid' => $totalPaid,
            'total_paid_pending' => $totalPaidPending,
            'next_step' => ($paymentObject->isManual()) ? false : true
        ]);
    }

    /**
     * To make a payment to topup E-Wallet
     *
     * @param int $eWalletId
     * @param int $paymentModeId
     * @param $fields
     * @param bool $isShare
     * @return array
     * @throws \Exception
     */
    public function eWalletPay(
        int $eWalletId,
        int $paymentModeId,
        $fields,
        bool $isShare = false
    )
    {
        $returnValues = array(); //return array

        $eWalletDetail = $this->eWalletObj->find($eWalletId);

        $amount = $fields->where('name','amount')->first()['value'];

        //for now we will just insert into the payment table
        $paymentResults = $this->insertPaymentRecord(
            $eWalletId,
            'user_ewallets',
            $paymentModeId,
            $eWalletDetail->user->member->country_id,
            $amount,
            $fields,
            false,
            $isShare
        );

        $paymentObject = $paymentResults['paymentObject'];

        $eWalletPayment = $paymentResults['payment'];

        if($paymentObject->isManual()){

            $returnValues['e_wallet_topup'] = $this->eWalletTopUp($eWalletDetail, $eWalletPayment);

        } else {

            if($paymentObject->isFormGenerateRequired()){

                $info = $this->getPaymentFormData(
                    $eWalletDetail->user->id,
                    $eWalletPayment->id,
                    $eWalletDetail->user->member->country->currency->code,
                    $amount
                );

                $returnValues['form_data'] = $paymentObject->getFormData($info);
            }

            $returnValues['e_wallet_topup'] = false;
        }

        return array_merge($returnValues, [
            'payment' => $eWalletPayment,
            'total_paid' => $eWalletPayment->amount,
            'next_step' => ($paymentObject->isManual()) ? false : true
        ]);
    }

    /**
     * Payment Process Callback Function
     *
     * @param int $paymentId
     * @param $isBackendCall - If is backend call, we should pass the info back differently
     * @param Request $request
     * @return bool|\Illuminate\Contracts\View\Factory|View
     * @throws \Exception
     */
    public function processCallback(int $paymentId, $isBackendCall, Request $request)
    {
        //we will be able to determine what is the callback data and process the data from the respective
        //payment provider. We will need to know the status and the callback data
        // example returned result array( 'success' => true, $rawCallbackdata )
        $payment = $this->paymentObj->find($paymentId);

        //To Prevent Double Update From Third Party Payment Callback
        if($payment->status != 2){
            return ($payment->status == 1) ?
                view('payments/payment_success') :
                    view('payments/payment_failed');
        }

        //Get Mapping Object Detail
        if($payment->mapping_model == 'sales'){

            $saleDetail = $this->saleRepositoryObj->find($payment->mapping_id);

            $paymentCountry = $saleDetail->country()->first();

            $locationTypeId = $saleDetail->transactionLocation()->first()->location_types_id;

        } else if ($payment->mapping_model == 'consignments_deposits_refunds') {

            $consignmentDetail = $this->consignmentDepositRefundObj->find($payment->mapping_id);

            $paymentCountry = $consignmentDetail->stockist->country()->first();

            $locationTypeId = $consignmentDetail->stockist->stockistLocation()->first()->location_types_id;

        } else {

            //For user_ewallets Model
            $eWalletDetail = $this->eWalletObj->find($payment->mapping_id);

            $paymentCountry = $eWalletDetail->user->member->country()->first();

            $userCountryEntityId = $eWalletDetail->user->member->country->entity->id;

            $locationTypeId = $this->locationTypesObj
                ->where('code', $this->locationTypeConfigCodes['online'])
                ->first()->id;
        }

        $paymentProvider = $this->paymentModeSettingsObj
            ->where('payment_mode_provider_id', $payment->paymentModeProvider->id)
            ->where('country_id', $paymentCountry->id)
            ->where('location_type_id', $locationTypeId)
            ->first();

        $paymentObject = $this->getPaymentObject($paymentCountry->code_iso_2, $paymentProvider->configuration_file_name);

        //second level validation
        if($paymentObject->isManual()){ //ensure this is not a manual payment
            return false;
        }

        $results = $paymentObject->processCallback($request, $isBackendCall);

        //@todo push notification to the frontend that we have got a results
        //the results should have key of success - boolean, data - all the callback data
        if($results['success']){

            //we will have to update the sales with the returned data
            $payment->status = 1;

            //just in case returned data is in in json
            if(!is_array($results['data'])){
                $results['data'] = json_decode($results['data']);
            }

            $paymentDetail = json_decode($payment->payment_detail, true);
            $paymentDetail['payment_response'] = $results['data'];
            $payment->payment_detail = json_encode($paymentDetail);

            $payment->save();

            if($payment->mapping_model == 'sales'){
                $sale = $this->saleRepositoryObj->find($payment->mapping_id);

                $this->actualSalesGenerated($sale, true);

            } else if($payment->mapping_model == 'consignments_deposits_refunds'){
                $consignmentDeposit = $this->consignmentDepositRefundObj->find($payment->mapping_id);

                $this->consignmentDepositGenerated($consignmentDeposit);

            } else {
                //For user_ewallets Model
                $eWalletDetail = $this->eWalletObj->find($payment->mapping_id);

                $this->eWalletTopUp($eWalletDetail, $payment);
            }

            //if printout is set, we should only printout what is on the backend requirements and stop the app
            if(isset($results['printout'])){
                echo $results['printout'];
                die;
            }

            return view('payments/payment_success'); // show a payment success page

        } else{

            $payment->status = (isset($results['data']['payStatus']))
                ? $results['data']['payStatus'] : 0; // 0 indicates fail

            $paymentDetail = json_decode($payment->payment_detail, true);

            $paymentDetail['payment_response'] = $results['data'];

            $payment->payment_detail = json_encode($paymentDetail);

            $payment->save();

            return view('payments/payment_failed');
        }
    }

    /**
     * This function is used to redirect payments to the respective payment gateway
     *
     * @param int $paymentId
     * @param Request $request
     * @return $this|bool
     * @throws \Exception
     */
    public function redirectPayments(int $paymentId, Request $request)
    {
        $payment = $this->paymentObj->find($paymentId);

        //Get Mapping Object Detail
        if($payment->mapping_model == 'sales'){

            $saleDetail = $this->saleRepositoryObj->find($payment->mapping_id);

            $paymentCountry = $saleDetail->country()->first();

            $locationTypeId = $saleDetail->transactionLocation()->first()->location_types_id;

            $userId = $saleDetail->user_id;

        } else if ($payment->mapping_model == 'consignments_deposits_refunds') {

            $consignmentDetail = $this->consignmentDepositRefundObj->find($payment->mapping_id);

            $paymentCountry = $consignmentDetail->stockist->country()->first();

            $locationTypeId = $consignmentDetail->stockist->stockistLocation()->first()->location_types_id;

            $userId = $consignmentDetail->stockist->stockist_user_id;

        } else {

            //For user_ewallets Model
            $eWalletDetail = $this->eWalletObj->find($payment->mapping_id);

            $paymentCountry = $eWalletDetail->user->member->country()->first();

            $userCountryEntityId = $eWalletDetail->user->member->country->entity->id;

            $locationTypeId = $this->locationTypesObj
                ->where('code', $this->locationTypeConfigCodes['online'])
                ->first()->id;

            $userId = $eWalletDetail->user->id;
        }

        $paymentProvider = $this->paymentModeSettingsObj
            ->where('payment_mode_provider_id', $payment->paymentModeProvider->id)
            ->where('country_id', $paymentCountry->id)
            ->where('location_type_id', $locationTypeId)
            ->first();

        $paymentObject = $this->getPaymentObject($paymentCountry->code_iso_2, $paymentProvider->configuration_file_name);

        if($paymentObject->isFormGenerateRequired()){

            // lets format the information needed
            $info = $this->getPaymentFormData($userId, $payment->id, $payment->currency->code, $payment->amount);

            $formData = $paymentObject->getFormData($info);

            return view('payments/redirect')->with('formData', $formData);
        }

        return false;
    }

    /**
     * To generate standard info when generating data
     *
     * @param int $userId
     * @param int $paymentId
     * @param string $currencyCode
     * @param float $amount
     * @return array
     */
    private function getPaymentFormData(int $userId, int $paymentId, string $currencyCode, float $amount)
    {
        $user = $this->userObj->find($userId);

        $paymentDetail = $this->paymentObj->find($paymentId);

        if($paymentDetail->mapping_model == 'sales'){
            $productDesc = 'Sales';
        } else if($paymentDetail->mapping_model == 'consignments_deposits_refunds'){
            $productDesc = 'Consignment Deposit';
        } else {
            $productDesc = 'E-Wallet Top-up';
        }

        // IMPORTANT : The key must be standard across all the auto payment type
        return array(
            'reference_no' => 'salespayment-'.$paymentId, //we need payment id to ensure correctness
            'currency_code' => $currencyCode,
            'amount' => $amount,
            'product_desc' => 'Pay For ' . $productDesc,
            'user_name' => $user->name,
            'user_contact' => (!empty($user->mobile)) ? $user->mobile : '',
            'user_email' => $user->email,
            'sale_payment_id' => $paymentId
        );
    }

    /**
     * To insert payment record
     *
     * @param string $mappingId
     * @param string $mappingModel
     * @param int $paymentModeId
     * @param int $countryId
     * @param $amount
     * @param $fields
     * @param bool $isExternal
     * @param bool $isShare
     * @return array
     * @throws \Exception
     */
    private function insertPaymentRecord
    (
        string $mappingId,
        string $mappingModel,
        int $paymentModeId,
        int $countryId,
        $amount,
        $fields,
        bool $isExternal = false,
        bool $isShare = false
    )
    {
        //Get Country ISO 2 Code and Currency ID
        $countryDetail = $this->countryObj->find($countryId);

        $countryIso2 = $countryDetail->code_iso_2;

        $currencyId = $countryDetail->currency->id;

        //lets get the desired payment providers
        $paymentMode = $this->paymentModeSettingsObj->find($paymentModeId);

        //once we have the payment provider, we will pay using the payment providers
        $paymentObject = $this->getPaymentObject($countryIso2, $paymentMode->configuration_file_name);

        // if there is an additional params, try set the additional information
        if($fields->count() > 0){ // if more than one(default: amount), there should be more params here
            $keyValueParameters = $fields->mapWithKeys(function($field){
                return [$field['name'] => $field['value']];
            });
            $paymentObject->setAdditionalParams($keyValueParameters);
        }

        //Form Mapping Object Array
        if($mappingModel == 'sales'){

            $saleDetail = $this->saleRepositoryObj->find($mappingId);

            $paymentMember = $saleDetail->member()->first();

            $paymentCountry = $saleDetail->country()->first();

            $paymentLocation = $saleDetail->transactionLocation()->first();

        } else if ($mappingModel == 'consignments_deposits_refunds') {

            $consignmentDetail = $this->consignmentDepositRefundObj->find($mappingId);

            $paymentMember = $consignmentDetail->stockist->member()->first();

            $paymentCountry = $consignmentDetail->stockist->country()->first();

            $paymentLocation = $consignmentDetail->stockist->stockistLocation()->first();

        } else {

            //For user_ewallets Model
            $eWalletDetail = $this->eWalletObj->find($mappingId);

            $paymentMember = $eWalletDetail->user->member()->first();

            $paymentCountry = $eWalletDetail->user->member->country()->first();

            $userCountryEntityId = $eWalletDetail->user->member->country->entity->id;

            $locationTypeDetail = $this->locationTypesObj
                ->where('code', $this->locationTypeConfigCodes['online'])
                ->first();

            $paymentLocation = $this->locationObj
                ->where('location_types_id', $locationTypeDetail->id)
                ->where('entity_id', $userCountryEntityId)
                ->active()
                ->first();
        }

        $mappingObjects = [
            "members" => $paymentMember,
            "country" => $paymentCountry,
            "location" => $paymentLocation,
            "amount" => $amount,
            "fields" => $fields->toArray()
        ];

        $manualPaymentStatus = 1;

        if($paymentObject->isManual()){
            // manual payment with validation, we will need to do a validation on the data that is inserted if there is
            $manualPaymentStatus = ($paymentObject->validateManualPayment()) ? 1 : 0;
        }

        //Combine payment detail object with frontend field object and pre-insert all payment details
        $paymentDetailArray = [
            'fields' => '',
            'payment_response' => '',
            'payment_inputs' => $fields->toArray()
        ];

        if($paymentObject->isCreationGeneratePaymentDetail()){

            $prePaymentRecord = $paymentObject->generatePaymentDetailJson(
                $this->settingsRepositoryObj,
                $this->masterRepositoryObj,
                $mappingObjects
            );

            $paymentDetailArray['payment_response'] = $prePaymentRecord;
        }

        //To hide privacy info
        $duplicatePaymentInputRecord = $paymentObject->modifyPaymentInputData($fields->toArray());

        $paymentDetailArray['fields'] = $duplicatePaymentInputRecord;

        $paymentDetails = collect($paymentDetailArray);

        //from the payment object we will know if it is automatic or manual, if manual there wont be any callbacks
        $paymentData = array(
            'payment_mode_provider_id' => $paymentMode->paymentModeProvider->id,
            'mapping_id' => $mappingId,
            'mapping_model' => $mappingModel,
            'currency_id' => $currencyId,
            'amount' => $amount,
            'status' => ($paymentObject->isManual()) ? $manualPaymentStatus : 2, //1 = paid, if auto this is 2 until we received a callback
            'is_external' => $isExternal,
            'payment_detail' => $paymentDetails->toJson(),
            //For sale cancellation to proceed refund when it was third party payment gateway
            'is_third_party_refund' => $paymentObject->isThirdPartyRefund(),
            'is_share' => $isShare
        );

        $payment = Auth::user()->createdBy($this->paymentObj)->create($paymentData);

        //after creation, if this is a manual type, check if there is anything need to be tie to the payment
        if($paymentObject->isManual() && $manualPaymentStatus){
            $status = $paymentObject->processManualPayment($payment->id);
            if($status){
                $payment->status = 1;
                $payment->save();
            }
        }

        return [
            'payment' => $payment,
            'paymentObject' => $paymentObject
        ];
    }

    /**
     * get EPP filtered by the following parameters
     *
     * @param int $countryId
     * @param $dateFrom
     * @param $dateTo
     * @param string $text
     * @param int $locationTypeId
     * @param int $eppModeId
     * @param int $approvalStatusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function eppPaymentListing(
        int $countryId,
        $dateFrom = '',
        $dateTo = '',
        string $text = '',
        int $locationTypeId = 0,
        int $eppModeId = 0,
        int $approvalStatusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        //Get EPP Payment Mode ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode', 'epp_mode'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $eppMode = array_change_key_case($settingsData['epp_mode']->pluck('id','title')->toArray());

        $eppOnlinePaymentId = $paymentMode[$this->paymentModeConfigCodes['epp (online)']];

        $eppMotoPaymentId = $paymentMode[$this->paymentModeConfigCodes['epp (moto)']];

        $eppTerminalPaymentId = $paymentMode[$this->paymentModeConfigCodes['epp (terminal)']];

        if($eppModeId == $eppMode[$this->eppModeConfigCodes['online']]){

            $eppPaymentModeIds = [
                $eppOnlinePaymentId
            ];

        } else if ($eppModeId == $eppMode[$this->eppModeConfigCodes['moto']]){
            
            $eppPaymentModeIds = [
                $eppMotoPaymentId
            ];

        } else if ($eppModeId == $eppMode[$this->eppModeConfigCodes['terminal']]){
            
            $eppPaymentModeIds = [
                $eppTerminalPaymentId
            ];

        } else {

            $eppPaymentModeIds = [
                $eppOnlinePaymentId,
                $eppMotoPaymentId,
                $eppTerminalPaymentId
            ];
        }

        $data = $this->paymentObj
            ->with(['sale.member', 'sale.channel', 'sale.invoices', 'sale.transactionLocation', 'paymentModeProvider', 'sale.orderStatus'])
            ->join('payments_modes_providers', function ($join)
                use ($eppPaymentModeIds){
                    $join->on('payments.payment_mode_provider_id', '=', 'payments_modes_providers.id')
                        ->where(function ($paymentProvidersQuery) use ($eppPaymentModeIds) {
                            $paymentProvidersQuery->whereIn(
                                'payments_modes_providers.master_data_id', $eppPaymentModeIds);
                        });
                })
            ->join('sales', function ($join)
                use ($countryId){
                    $join->on('payments.mapping_id', '=', 'sales.id')
                        ->where(function ($saleQuery) use ($countryId) {
                            $saleQuery->where('payments.mapping_model', 'sales');
                            $saleQuery->where('sales.country_id', $countryId);
                        });
                });

        if ($text != '') {

            $data = $data
                ->join('users', function ($userJoin){
                    $userJoin->on('users.id', '=', 'sales.user_id');
                })
                ->join('members', function ($memberJoin){
                    $memberJoin->on('users.id', '=', 'members.user_id');
                })
                ->where(function ($query) use($text) {
                    $query->orWhere('sales.document_number', 'like','%' . $text . '%')
                        ->orWhere('users.old_member_id', 'like','%' . $text . '%')
                        ->orWhere('members.name', 'like','%' . $text . '%')
                        ->orWhere('members.ic_passport_number', 'like','%' . $text . '%');
                });
        }

        if($locationTypeId > 0){
            $data = $data
                ->where('sales.channel_id', $locationTypeId);
        }

        if($approvalStatusId > 0){
            $data = $data
                ->where('payments.payment_detail', 'like','%"approval_status":' . $approvalStatusId . ',%');
        }

        //check the dates if given
        if ($dateFrom != '' and $dateTo != ''){
            $data = $data
                ->where('payments.created_at','>=', date('Y-m-d  H:i:s',strtotime($dateFrom.'00:00:00')))
                ->where('payments.created_at','<=', date('Y-m-d  H:i:s',strtotime($dateTo.'23:59:59')));
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data->select('payments.*');

        $data = $data->orderBy($orderBy, $orderMethod);

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        collect($data)->map(function ($payments){

            $payments->payment_detail = json_decode($payments->payment_detail, true);

            $paymentDetail = $payments->payment_detail;

            $paymentDetailResponse = $payments->payment_detail['payment_response'];

            $approvalStatusDetails = $this->masterDataObj
                ->find($paymentDetailResponse['approval_status']);

            $docStatusDetails = $this->masterDataObj
                ->find($paymentDetailResponse['doc_status']);

            $approvedByDetails = $this->userObj
                ->find($paymentDetailResponse['approved_by']);

            $convertedByDetails = $this->userObj->find(
                (isset($paymentDetailResponse['converted_by'])) ? $paymentDetailResponse['converted_by'] : 0
            );

            $paymentDetailResponse['approvalStatus'] = $approvalStatusDetails;

            $paymentDetailResponse['docStatus'] = $docStatusDetails;

            $paymentDetailResponse['approvedBy'] = $approvedByDetails;

            $paymentDetailResponse['convertedBy'] = $convertedByDetails;

            $paymentDetail['payment_response'] = $paymentDetailResponse;

            $payments->payment_detail = $paymentDetail;

            return $payments;
        });

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * update epp moto approve code
     *
     * @param int $paymentId
     * @param string $approveCode
     * @return mixed
     */
    public function updateEppMotoApproveCode(int $paymentId, string $approveCode)
    {
        $returnValues = [];

        //Get Master Data ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('epp_payment_approval_status'));

        //Get approved status ID
        $approvalStatus = array_change_key_case($settingsData['epp_payment_approval_status']
            ->pluck('id','title')->toArray());

        $approvedId = $approvalStatus[$this->eppStatusConfigCodes['approved']];

        //Get Payment Detail
        $paymentDetail = $this->paymentObj->find($paymentId);

        $eppPaymentDetail = json_decode($paymentDetail->payment_detail, true);

        //Update Payment Response Detail
        $eppPaymentDetailResponse = $eppPaymentDetail['payment_response'];

        $eppPaymentDetailResponse['approval_code'] = $approveCode;

        $eppPaymentDetailResponse['approval_status'] = $approvedId;

        $eppPaymentDetailResponse['approved_by'] = Auth::id();

        $eppPaymentDetailResponse['approved_date'] = date('Y-m-d');

        $eppPaymentDetail['payment_response'] = $eppPaymentDetailResponse;

        //Update Payment Input Field
        $eppPaymentInputFields = $eppPaymentDetail['fields'];

        $approveCodeField = [
            'name' => 'approve_code',
            'type' => 'field',
            'text' => 'Approve Code',
            'order' => 9,
            'value' => $approveCode
        ];

        array_push($eppPaymentInputFields, $approveCodeField);

        $eppPaymentDetail['fields'] = $eppPaymentInputFields;

        $updatedEppPaymentDetail = json_encode($eppPaymentDetail);

        $paymentDetail->update(
            array(
                'payment_detail' => $updatedEppPaymentDetail,
                'status' => 1,
                'updated_by' => Auth::id()
            )
        );

        $returnValues['sale_generated'] = false;

        $sale = $this->saleRepositoryObj->find($paymentDetail->mapping_id);

        //Get Updated Payment Record
        $eppPayment = $sale->salePayments()->find($paymentId);

        $eppPaymentDetail = json_decode($eppPayment->payment_detail, true);

        $eppPaymentDetailResponse = $eppPaymentDetail['payment_response'];

        $approvalStatusDetails = $this->masterDataObj
            ->find($eppPaymentDetailResponse['approval_status']);

        $docStatusDetails = $this->masterDataObj
            ->find($eppPaymentDetailResponse['doc_status']);

        $approvedByDetails = $this->userObj
            ->find($eppPaymentDetailResponse['approved_by']);

        $convertedByDetails = $this->userObj->find(
            (isset($eppPaymentDetailResponse['converted_by'])) ? $eppPaymentDetailResponse['converted_by'] : 0
        );

        $eppPaymentDetailResponse['approvalStatus'] = $approvalStatusDetails;

        $eppPaymentDetailResponse['docStatus'] = $docStatusDetails;

        $eppPaymentDetailResponse['approvedBy'] = $approvedByDetails;

        $eppPaymentDetailResponse['convertedBy'] = $convertedByDetails;

        $eppPaymentDetail['payment_response'] = $eppPaymentDetailResponse;

        $eppPayment->payment_detail = $eppPaymentDetail;

        return array_merge($returnValues, [
            'payment' => $eppPayment,
            'payments' => $sale->salePayments()->get(),
            'next_step' => false
        ]); 
    }

    /**
     * batch covert epp payment to valid sales order
     *
     * @param array $salePaymentIds
     * @return mixed
     */
    public function eppPaymentSaleConvert(array $salePaymentIds)
    {
        //Get Master Data ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('epp_payment_document_status'));

        //Get Document status ID
        $documentStatus = array_change_key_case($settingsData['epp_payment_document_status']
            ->pluck('id','title')->toArray());

        $processDocumentCode = $this->eppDocStatusConfigCodes['p'];

        $processDocumentId = $documentStatus[$processDocumentCode];

        collect($salePaymentIds)
            ->each(function ($id) use ($processDocumentId){

                $eppMotoPayment = $this->paymentObj
                    ->find($id);

                $eppMotoPaymentDetail = json_decode($eppMotoPayment->payment_detail, true);

                $eppMotoPaymentDetailResponse = $eppMotoPaymentDetail['payment_response'];

                $eppMotoPaymentDetailResponse['doc_status'] = $processDocumentId;

                $eppMotoPaymentDetailResponse['converted_by'] = Auth::id();

                $eppMotoPaymentDetailResponse['converted_date'] = date('Y-m-d');

                $eppMotoPaymentDetail['payment_response'] = $eppMotoPaymentDetailResponse;

                $updatedQppMotoPaymentDetail = json_encode($eppMotoPaymentDetail);

                $eppMotoPayment->update(
                    array(
                        'payment_detail' => $updatedQppMotoPaymentDetail,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'updated_by' => Auth::id()
                    )
                );

                $sale = $this->saleRepositoryObj->find($eppMotoPayment->mapping_id);

                $this->actualSalesGenerated($sale, true);
            });

        return ['result' => true];
    }

    /**
     * get aeon filtered by the following parameters
     *
     * @param int $countryId
     * @param $dateFrom
     * @param $dateTo
     * @param string $text
     * @param int $locationTypeId
     * @param int $approvalStatusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function aeonPaymentListing(
        int $countryId,
        $dateFrom = '',
        $dateTo = '',
        string $text = '',
        int $locationTypeId = 0,
        int $approvalStatusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        //Get Aeon Payment Mode ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $aeonPaymentCode = $this->paymentModeConfigCodes['aeon'];

        $aeonPaymentId = $paymentMode[$aeonPaymentCode];

        $data = $this->paymentObj
            ->with(['sale.member', 'sale.channel', 'sale.invoices', 'sale.orderStatus'])
            ->join('payments_modes_providers', function ($join)
                use ($aeonPaymentId){
                    $join->on('payments.payment_mode_provider_id', '=', 'payments_modes_providers.id')
                        ->where(function ($paymentProvidersQuery) use ($aeonPaymentId) {
                            $paymentProvidersQuery->where(
                                'payments_modes_providers.master_data_id', $aeonPaymentId);
                        });
                })
            ->join('sales', function ($join)
                use ($countryId){
                    $join->on('payments.mapping_id', '=', 'sales.id')
                        ->where(function ($saleQuery) use ($countryId) {
                            $saleQuery->where('payments.mapping_model', 'sales');
                            $saleQuery->where('sales.country_id', $countryId);
                        });
                });

        if ($text != '') {

            $data = $data
                ->join('users', function ($userJoin){
                    $userJoin->on('users.id', '=', 'sales.user_id');
                })
                ->join('members', function ($memberJoin){
                    $memberJoin->on('users.id', '=', 'members.user_id');
                })
                ->where(function ($query) use($text) {
                    $query->orWhere('sales.document_number', 'like','%' . $text . '%')
                        ->orWhere('users.old_member_id', 'like','%' . $text . '%')
                        ->orWhere('members.name', 'like','%' . $text . '%')
                        ->orWhere('members.ic_passport_number', 'like','%' . $text . '%');
                });
        }

        if($locationTypeId > 0){
            $data = $data
                ->where('sales.channel_id', $locationTypeId);
        }

        if($approvalStatusId > 0){
            $data = $data
                ->where('payments.payment_detail', 'like','%"approval_status":' . $approvalStatusId . ',%');
        }

        //check the dates if given
        if ($dateFrom != '' and $dateTo != ''){
            $data = $data
                ->where('payments.created_at','>=', date('Y-m-d  H:i:s',strtotime($dateFrom.'00:00:00')))
                ->where('payments.created_at','<=', date('Y-m-d  H:i:s',strtotime($dateTo.'23:59:59')));
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data->select('payments.*');

        $data = $data->orderBy($orderBy, $orderMethod);

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        collect($data)->map(function ($payments){
            $payments->payment_detail = json_decode($payments->payment_detail, true);

            $paymentDetail = $payments->payment_detail;

            $paymentDetailResponse = $payments->payment_detail['payment_response'];

            $approvalStatusDetails = $this->masterDataObj
                ->find($paymentDetailResponse['approval_status']);

            $docStatusDetails = $this->masterDataObj
                ->find($paymentDetailResponse['doc_status']);

            $convertedByDetails = $this->userObj
                ->find($paymentDetailResponse['converted_by']);

            $paymentDetailResponse['approvalStatus'] = $approvalStatusDetails;

            $paymentDetailResponse['docStatus'] = $docStatusDetails;

            $paymentDetailResponse['convertedBy'] = $convertedByDetails;

            $paymentDetail['payment_response'] = $paymentDetailResponse;

            $payments->payment_detail = $paymentDetail;

            return $payments;
        });

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * update aeon application agreement number
     *
     * @param int $paymentId
     * @param string $agreementNumber
     * @param $approvedAmount
     * @return mixed
     */
    public function updateAeonAgreementNumber(int $paymentId, string $agreementNumber, $approvedAmount)
    {
        $returnValues = [];

        //Get Master Data ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('aeon_payment_approval_status', 'aeon_payment_document_status'));

        //Get approved status ID
        $approvalStatus = array_change_key_case($settingsData['aeon_payment_approval_status']
            ->pluck('id','title')->toArray());

        $approvedId = $approvalStatus[$this->aeonStatusConfigCodes['approved']];

        //Get Document status ID
        $documentStatus = array_change_key_case($settingsData['aeon_payment_document_status']
            ->pluck('id','title')->toArray());

        $processDocumentId = $documentStatus[$this->docStatusConfigCodes['p']];

        //Get Payment Detail
        $paymentDetail = $this->paymentObj->find($paymentId);

        $aeonPaymentDetail = json_decode($paymentDetail->payment_detail, true);

        $aeonPaymentDetailResponse = $aeonPaymentDetail['payment_response'];

        $aeonPaymentDetailResponse['agreement_no'] = $agreementNumber;

        $aeonPaymentDetailResponse['doc_status'] = $processDocumentId;

        $aeonPaymentDetailResponse['approved_amount'] = $approvedAmount;

        $aeonPaymentDetailResponse['approval_status'] = $approvedId;

        $aeonPaymentDetailResponse['approval_date'] = date('Y-m-d');

        $aeonPaymentDetail['payment_response'] = $aeonPaymentDetailResponse;

        $updatedAeonPaymentDetail = json_encode($aeonPaymentDetail);

        $paymentDetail->update(
            array(
                'amount' => $approvedAmount,
                'payment_detail' => $updatedAeonPaymentDetail,
                'status' => 1,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_by' => Auth::id()
            )
        );

        $sale = $this->saleRepositoryObj->find($paymentDetail->mapping_id);

        $returnValues['sale_generated'] = $this->actualSalesGenerated($sale, true);

        return array_merge($returnValues, [
            'payment' => $sale->salePayments()->find($paymentId),
            'payments' => $sale->salePayments()->get(),
            'next_step' => false
        ]);
    }

    /**
     * batch release aeon payment from cooling off period
     *
     * @param array $salePaymentIds
     * @return mixed
     */
    public function aeonPaymentCoolingOffRelease(array $salePaymentIds)
    {
        //Get Master Data ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('stockist_daily_transaction_release_status', 'aeon_payment_stock_release_status'));

        $stockistReleaseStatusValues = array_change_key_case(
            $settingsData['stockist_daily_transaction_release_status']->pluck('id','title')->toArray()
        );

        $aeonReleaseStatusValues = array_change_key_case(
            $settingsData['aeon_payment_stock_release_status']->pluck('id','title')->toArray()
        );

        $releaseStockistReleaseStatusId = $stockistReleaseStatusValues[
            $this->stockistTransactionReleaseStatusConfigCodes['released']];

        $releasedAeonReleaseStatusId = $aeonReleaseStatusValues[
            $this->aeonPaymentStockReleaseStatusConfigCodes['released']];

        collect($salePaymentIds)
            ->each(function ($id) use ($releasedAeonReleaseStatusId, $releaseStockistReleaseStatusId){

                $aeonPayment = $this->paymentObj
                    ->find($id);

                $aeonPaymentDetail = json_decode($aeonPayment->payment_detail, true);

                $aeonPaymentDetailResponse = $aeonPaymentDetail['payment_response'];

                $aeonPaymentDetailResponse['converted_by'] = Auth::id();

                $aeonPaymentDetailResponse['converted_date'] = date('Y-m-d');

                $aeonPaymentDetail['payment_response'] = $aeonPaymentDetailResponse;

                $updatedAeonPaymentDetail = json_encode($aeonPaymentDetail);

                $aeonPayment->update(
                    array(
                        'payment_detail' => $updatedAeonPaymentDetail,
                        'updated_by' => Auth::id()
                    )
                );

                $sale = $this->saleRepositoryObj->find($aeonPayment->mapping_id);

                //Update Invoice Release Table
                $invoice = $sale->invoices()->first();

                if($invoice){
                    $invoice->update(
                        array(
                            'aeon_payment_stock_release_status_id' => $releasedAeonReleaseStatusId,
                            'aeon_release_date' => date('Y-m-d'),
                            'stockist_daily_transaction_status_id' => $releaseStockistReleaseStatusId,
                            'released_date' => date('Y-m-d'),
                            'updated_by' => Auth::id()
                        )
                    );
                }
            });

        return ['result' => true];
    }

    /**
     * batch payment cancel
     *
     * @param string $paymentMode
     * @param array $salePaymentIds
     * @return mixed
     */
    public function paymentBatchCancel(string $paymentMode, array $salePaymentIds)
    {
        //Get Master Data ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'aeon_payment_approval_status',
                'aeon_payment_document_status',
                'epp_payment_approval_status',
                'epp_payment_document_status'
            ));

        $aeonApprovalStatus = array_change_key_case($settingsData['aeon_payment_approval_status']
            ->pluck('id','title')->toArray());

        $aeonDocumentStatus = array_change_key_case($settingsData['aeon_payment_document_status']
            ->pluck('id','title')->toArray());

        $eppApprovalStatus = array_change_key_case($settingsData['epp_payment_approval_status']
            ->pluck('id','title')->toArray());

        $eppDocumentStatus = array_change_key_case($settingsData['epp_payment_document_status']
            ->pluck('id','title')->toArray());

        $aeonCancelId = $aeonApprovalStatus[$this->aeonStatusConfigCodes['cancel']];

        $aeonVoidDocumentId = $aeonDocumentStatus[$this->docStatusConfigCodes['v']];

        $eppMotoDeclinedId = $eppApprovalStatus[$this->eppStatusConfigCodes['declined']];

        $eppMotoVoidDocumentId = $eppDocumentStatus[$this->eppDocStatusConfigCodes['v']];

        $paymentRecords = [];

        collect($salePaymentIds)->each(function ($id)
            use (
                $paymentMode,
                $aeonCancelId,
                $aeonVoidDocumentId,
                $eppMotoDeclinedId,
                $eppMotoVoidDocumentId,
                &$paymentRecords
            ){
                $payment = $this->paymentObj
                    ->find($id);

                $paymentDetail = json_decode($payment->payment_detail, true);

                $paymentDetailResponse = $paymentDetail['payment_response'];

                if($paymentMode == 'epp_moto'){

                    $paymentDetailResponse['approval_code'] = '';

                    $paymentDetailResponse['approval_status'] = $eppMotoDeclinedId;

                    $paymentDetailResponse['doc_status'] = $eppMotoVoidDocumentId;

                } else if ($paymentMode == 'aeon') {

                    $paymentDetailResponse['agreement_no'] = '';

                    $paymentDetailResponse['approval_status'] = $aeonCancelId;

                    $paymentDetailResponse['doc_status'] = $aeonVoidDocumentId;
                }

                $paymentDetail['payment_response'] = $paymentDetailResponse;

                $updatedPaymentDetail = json_encode($paymentDetail);

                $payment->update(
                    array(
                        'payment_detail' => $updatedPaymentDetail,
                        'status' => 0,
                        'updated_by' => Auth::id()
                    )
                );

                //Get Updated Payment Record
                $payment = $this->paymentObj
                    ->with(['createdBy', 'updatedBy'])
                    ->find($id);

                if(in_array($paymentMode, ['epp_moto', 'aeon'])){

                    $paymentDetail = json_decode($payment->payment_detail, true);

                    $paymentDetailResponse = $paymentDetail['payment_response'];

                    $approvalStatusDetails = $this->masterDataObj
                        ->find($paymentDetailResponse['approval_status']);

                    $docStatusDetails = $this->masterDataObj
                        ->find($paymentDetailResponse['doc_status']);

                    $paymentDetailResponse['approvalStatus'] = $approvalStatusDetails;

                    $paymentDetailResponse['docStatus'] = $docStatusDetails;

                    $paymentDetail['payment_response'] = $paymentDetailResponse;

                    $payment->payment_detail = $paymentDetail;

                }

                array_push($paymentRecords, $payment);
            });

        return [
            'result' => true,
            'payments' => $paymentRecords
        ];
    }

    /**
     * pre-order sale convert to actual sale
     *
     * @param Sale $sale
     * @param bool $skipConvertRentalSaleOrder
     * @return boolean
     */
    public function actualSalesGenerated(Sale $sale, bool $skipConvertRentalSaleOrder = true)
    {
        if ($skipConvertRentalSaleOrder){
            if ($sale->is_rental_sale_order) {
                //Generate Sale Workflow
                $this->saleRepositoryObj->createSaleWorkflow($sale->id);
            }
        }

        //check does invoice exist
        $saleInvoice = $this->invoiceObj
            ->where('sale_id', $sale->id)->first();

        if(!$saleInvoice){
            $conversion = false;

            //check if the amount is totalled up to the sales amount, if yes, generate an invoice
            $totalPaidAmount = collect(
                $this->paymentObj
                    ->where('mapping_id', $sale->id)
                    ->where('mapping_model', 'sales')
                    ->where('status', 1)
                    ->get()
                )->sum('amount');

            if($totalPaidAmount == $sale->total_gmp && !$sale->is_product_exchange){

                //Check Esac Redemption
                $invoiceProceed = true;

                if($sale->is_esac_redemption){

                    $invalidEsacVoucher = $sale->esacVouchers->where('voucher_status', '!=', 'N')->first();

                    if($invalidEsacVoucher){

                        $invoiceProceed = false;

                        //Update Sale Order Status to Rejected
                        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
                            array('sale_order_status'));

                        $saleOrderStatus = array_change_key_case(
                            $settingsData['sale_order_status']->pluck('id','title')->toArray()
                        );

                        $saleOrderRejectedStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['rejected']];

                        $sale->order_status_id = $saleOrderRejectedStatusId;

                        $sale->save();

                    } else {

                        collect($sale->esacVouchers->pluck('id'))->each(function ($esacVoucherId){
                            $this->esacVoucherRepositoryObj
                                ->updateStatus($esacVoucherId, 'P');
                        });
                    }
                }

                if($invoiceProceed){

                    //generate an invoice
                    $conversion = $this->invoiceRepositoryObj->generateInvoice($sale);

                    //check if this sales is enrollment sales
                    if (!is_null($sale->enrollmentSale()->first())){

                        //fire event to enroll the new user
                        event(new EnrollUserEvent($sale, Auth::user()->identifier()->identifier));

                    } else if($conversion && !$sale->is_esac_redemption && !$sale->user()->first()->isGuest()) { //Insert AMP Cv Allocations

                        $this->saleRepositoryObj->createAmpCvAllocations($sale->id);

                        $this->saleRepositoryObj->insertPurchaseCv($sale->id);

                        $this->saleRepositoryObj->saleAccumulationCalculation($sale->user_id);
                    }
                }
            }

            //check if this sale is PE, so we can generate the invoice
            if ($sale->is_product_exchange){
                $saleExchange = $sale->saleExchange()->first();

                $saleExchangeTotal = $saleExchange->exchange_amount_total - $saleExchange-> return_amount_total;

                if (round($saleExchangeTotal, 2) == round($totalPaidAmount, 2)){
                    //generate an invoice
                    $conversion = $this->invoiceRepositoryObj->generateInvoice($sale);

                    //change Pe status to completed------
                    $completeStatusId = $this->masterDataObj->getIdByTitle(config('mappings.sale_order_status.completed'), 'sale_order_status');

                    $saleExchange->order_status_id = $completeStatusId;

                    $saleExchange->save();

                    //deduct product returned qty
                    $this->saleExchangeRepositoryObj->deductReturnedProductQty($saleExchange);

                    //generate exchange bill and credit note
                    $this->saleExchangeRepositoryObj->generateExchangeBillAndCreditNote($saleExchange);
                }
            }

        } else {
            $conversion = true;
        }

        return $conversion;
    }

    /**
     * generate and convert consignment deposit document
     *
     * @param ConsignmentDepositRefund $consignmentDeposit
     * @return boolean
     */
    private function consignmentDepositGenerated(ConsignmentDepositRefund $consignmentDeposit)
    {
        //To prevent update twice from payment callback
        if(!empty($consignmentDeposit->workflow_tracking_id)){
            return true;
        }

        $fullPaid = false;

        //check if the amount is totalled up to the deposit amount, if yes, update the status
        $totalPaidAmount = collect(
            $this->paymentObj
                ->where('mapping_id', $consignmentDeposit->id)
                ->where('mapping_model', 'consignments_deposits_refunds')
                ->where('status', 1)
                ->get()
            )->sum('amount');

        if($totalPaidAmount == $consignmentDeposit->amount){

            //Get consignment deposit status
            $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
                array('consignment_deposit_and_refund_status')
            );

            $depositRefundStatus = array_change_key_case(
                $masterSettingsDatas['consignment_deposit_and_refund_status']->pluck('id','title')->toArray()
            );

            //Create Consignment Note Number
            $documentNumber = $this->settingsRepositoryObj
                ->getRunningNumber('consignment_deposit',$consignmentDeposit->stockist->country_id, 0);

            //update consignment deposit status
            $pendingCode = $this->consignmentDepositRefundStatusConfigCodes['pending'];

            $consignmentDeposit->update([
                'document_number' => $documentNumber,
                'status_id' => $depositRefundStatus[$pendingCode]
            ]);

            //Create Workflow
            $this->stockistRepositoryObj->createConsignmentWorkflow($consignmentDeposit->id, 'deposit');

            $fullPaid = true;
        }

        return $fullPaid;
    }

    /**
     * topup e-wallet balance
     *
     * @param ConsignmentDepositRefund $eWalletDetail
     * @param Payment $eWalletPayment
     * @return boolean
     */
    private function eWalletTopUp(EWallet $eWalletDetail, Payment $eWalletPayment)
    {
        //To prevent update twice from payment callback
        if($eWalletPayment->mapping_model == 'user_ewallet_transactions'){
            return true;
        }

        $transaction = $this->eWalletRepositoryObj->createNewTransaction([
            "ewallet_id" => $eWalletDetail->id,
            "currency_id" => $eWalletPayment->currency_id,
            "amount_type_id" => $this->masterDataObj->getIdByTitle("Credit", "ewallet_amount_type"),
            "amount" => $eWalletPayment->amount,
            "recipient_email" => $eWalletDetail->user->email,
            "recipient_reference" => "EWallet Top Up",
            "transaction_details" => "EWallet Top Up"
        ]);

        $transactionId = $transaction->get('id');

        //Update Payment Mapping ID and Mapping Model
        $eWalletPayment->update([
            "mapping_id" => $transactionId,
            "mapping_model" => "user_ewallet_transactions"
        ]);

        return true;
    }

    /**
     * payment api for external use
     *
     * @param array $inputs
     * @return mixed
     * @throws \Exception
     */
    public function externalPayment(array $inputs)
    {
        $inputCollection = collect($inputs);

        $amount = $inputCollection->get('amount');

        $sale = $this->saleObj->find($inputCollection->get('sale_id'));

        $countryId = $sale->country_id;

        $locationTypesId = $sale->transactionLocation->location_types_id;

        $paymentModeSettings = $this->paymentModeProviderObj
            ->where('code', $inputCollection->get('payment_mode'))
            ->first()
            ->paymentModeSetting()
            ->where([
                'location_type_id' => $locationTypesId,
                'country_id' => $countryId
            ])
            ->first();

        $fields = json_decode($paymentModeSettings->setting_detail)[0]->fields;

        $fields[0] = (array)$fields[0];

        $fields = collect($fields)->transform( function($item, $key) use($amount) {
            if ($item['name'] == 'amount') {
                $item['value'] = $amount;
            }
            return $item;
        });

        return $this->insertPaymentRecord(
            $sale->id,
            'sales',
            $paymentModeSettings->id,
            $countryId,
            $amount,
            $fields,
            true,
            false
        );
    }

    /**
     * Get Payment Mode Document Details
     *
     * @param int $countryId
     * @param int $paymentModeProviderId
     * @return mixed
     */
    public function getPaymentModeDocumentDetails(int $countryId, int $paymentModeProviderId)
    {
        return $this->paymentModeDocumentObj->where('country_id', $countryId)
                    ->where('payment_mode_provider_id', $paymentModeProviderId)->first();
    }
}