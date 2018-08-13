<?php
namespace App\Repositories\Sales;

use App\Interfaces\{
    Kitting\KittingInterface,
    Masters\MasterDataInterface,
    Masters\MasterInterface,
    Members\MemberInterface,
    Products\ProductInterface,
    Promotions\PromotionFreeItemsInterface,
    Sales\SaleInterface,
    Settings\SettingsInterface,
    Invoices\InvoiceInterface,
    Workflows\WorkflowInterface,
    General\CwSchedulesInterface,
    Campaigns\EsacVoucherInterface
};
use App\Models\{
    Invoices\LegacyInvoice,
    Invoices\Invoice,
    Kitting\KittingPrice,
    Locations\Location,
    Locations\LocationAddresses,
    Locations\StockLocation,
    Locations\City,
    Locations\State,
    Locations\Country,
    Masters\MasterData,
    Members\Member,
    Members\MemberActiveRecord,
    Members\MemberEnrollmentRankUpgradeLog,
    Products\Product,
    Products\ProductPrice,
    Products\ProductRentalPlan,
    Products\ProductCategory,
    Sales\LegacySaleCancellationKittingClone,
    Sales\LegacySaleCancellationProduct,
    Sales\LegacySaleCancellationProductClone,
    Sales\Sale,
    Sales\SaleExchangeKitting,
    Sales\SaleProduct,
    Sales\SaleProductClone,
    Sales\SaleCancellation,
    Sales\SaleCancellationProduct,
    Sales\CreditNote,
    Sales\SaleKittingClone,
    Sales\SalePromotionFreeItemClone,
    Sales\SalePromotionFreeItemOptionClone,
    Sales\SalePromotionFreeItemOptionProductClone,
    Sales\SaleShippingAddress,
    Sales\SaleEsacVouchersClone,
    Sales\SaleCancellationEsacVoucher,
    Sales\SaleCorporateSale,
    Sales\SaleAccumulation,
    Bonus\AmpCvAllocation,
    Bonus\EnrollmentRank,
    Users\User,
    Payments\Payment,
    Payments\PaymentModeProvider,
    Campaigns\EsacPromotion,
    Campaigns\EsacVoucher,
    Sales\SaleExchange,
    Stockists\Stockist,
    Stockists\StockistConsignmentProduct,
    Payments\PaymentModeSetting
};
use App\{
    Events\Sales\SalesCreatedEvent,
    Helpers\Traits\AccessControl,
    Repositories\BaseRepository,
    Helpers\Classes\PdfCreator,
    Helpers\Classes\MemberAddress,
    Helpers\Classes\Uploader,
    Services\Sales\CommissionService
};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{
    Auth,
    Storage,
    Config
};
use \PhpOffice\PhpSpreadsheet\{
    Spreadsheet,
    Writer\Xlsx
};
use Carbon\Carbon;

class SaleRepository extends BaseRepository implements SaleInterface
{
    use AccessControl;

    private
        $saleProductsObj,
        $saleProductCloneObj,
        $saleKittingCloneObj,
        $salePromotionCloneObj,
        $saleShippingObj,
        $saleCancellationObj,
        $saleCancellationProductObj,
        $saleOrderStatusConfigCodes,
        $saleCancellationStatusConfigCodes,
        $stockistDailyTransactionReleaseStatusConfigCodes,
        $promotionFreeItemsPromoTypesConfigCodes,
        $salePwpOptionCloneObj,
        $salePwpOptionProductCloneObj,
        $transactionTypeConfigCodes,
        $memberStatusConfigCodes,
        $memberRepositoryObj,
        $productRepositoryObj,
        $kittingRepositoryObj,
        $masterRepositoryObj,
        $masterDataRepositoryObj,
        $promoFreeItemsRepositoryObj,
        $settingRepositoryObj,
        $locationObj,
        $locationAddressesObj,
        $stockLocationObj,
        $masterDataObj,
        $memberObj,
        $memberEnrollmentRankUpgradeLogObj,
        $memberActiveRecordObj,
        $invoiceRepositoryObj,
        $workflowRepositoryObj,
        $creditNoteObj,
        $cityObj,
        $stateObj,
        $countryObj,
        $userObj,
        $cwSchedulesRepositoryObj,
        $ampCvAllocationObj,
        $enrollmentRankObj,
        $memberAddress,
        $uploader,
        $ampCvAllocationTypesConfigCodes,
        $legacySaleCancellationModeConfigCodes,
        $esacPromotionObj,
        $esacVoucherObj,
        $paymentObj,
        $paymentModeProviderObj,
        $esacVoucherRepositoryObj,
        $saleEsacVouchersCloneObj,
        $saleCancellationEsacVoucherObj,
        $saleCorporateSaleObj,
        $saleAccumulationObj,
        $legacyInvoiceObj,
        $legacySaleCancellationKittingCloneObj,
        $legacySaleCancellationProductObj,
        $legacySaleCancellationProductCloneObj,
        $productObj,
        $invoiceObj,
        $saleExchangeObj,
        $productRentalPlanObj,
        $cvAcronymCodes,
        $saleTypeCvSettings,
        $saleCancellationTypeConfigCodes,
        $paymentModeConfigCodes,
        $paymentModeSettingObj,
        $stockistObj,
        $stockistConsignmentProductObj,
        $productCategoryObj,
        $commissionService
    ;

    /**
     * SaleRepository constructor.
     *
     * @param Sale $model
     * @param SaleProduct $saleProduct
     * @param SaleProductClone $productClone
     * @param SaleKittingClone $kittingClone
     * @param SalePromotionFreeItemClone $salePromotionFreeItemClone
     * @param SaleCancellation $saleCancellation
     * @param SaleCancellationProduct $saleCancellationProduct
     * @param SaleShippingAddress $saleShippingAddress
     * @param SalePromotionFreeItemOptionClone $salePromotionFreeItemOptionClone
     * @param SalePromotionFreeItemOptionProductClone $salePromotionFreeItemOptionProductClone
     * @param MemberInterface $memberInterface
     * @param ProductInterface $productInterface
     * @param KittingInterface $kittingInterface
     * @param MasterInterface $masterInterface
     * @param MasterDataInterface $masterDataInterface
     * @param PromotionFreeItemsInterface $promotionFreeItemsInterface
     * @param SettingsInterface $settingsInterface
     * @param InvoiceInterface $invoiceInterface
     * @param WorkflowInterface $workflowInterface
     * @param CwSchedulesInterface $cwSchedulesInterface
     * @param Location $location
     * @param LocationAddresses $locationAddresses
     * @param StockLocation $stockLocation
     * @param Member $member
     * @param City $city
     * @param State $state
     * @param MemberEnrollmentRankUpgradeLog $memberEnrollmentRankUpgradeLog,
     * @param MemberActiveRecord $memberActiveRecord,
     * @param Country $country
     * @param User $user
     * @param MasterData $masterData
     * @param CreditNote $creditNote
     * @param AmpCvAllocation $ampCvAllocation
     * @param EnrollmentRank $enrollmentRank
     * @param MemberAddress $memberAddress
     * @param Uploader $uploader
     * @param EsacPromotion $esacPromotion
     * @param EsacVoucher $esacVoucher
     * @param Payment $payment
     * @param PaymentModeProvider $paymentModeProvider
     * @param EsacVoucherInterface $esacVoucherInterface
     * @param SaleEsacVouchersClone $saleEsacVouchersClone
     * @param SaleCancellationEsacVoucher $saleCancellationEsacVoucher
     * @param SaleCorporateSale $saleCorporateSale
     * @param SaleAccumulation $saleAccumulation
     * @param LegacyInvoice $legacyInvoice
     * @param LegacySaleCancellationKittingClone $legacySaleCancellationKittingClone
     * @param LegacySaleCancellationProduct $legacySaleCancellationProduct
     * @param LegacySaleCancellationProductClone $legacySaleCancellationProductClone
     * @param Product $product
     * @param Invoice $invoice
     * @param SaleExchange $saleExchange
     * @param ProductRentalPlan $productRentalPlan
     * @param PaymentModeSetting $paymentModeSetting
     * @param Stockist $stockist
     * @param StockistConsignmentProduct $stockistConsignmentProduct
     * @param ProductCategory $productCategoryObj
     * @param CommissionService $commissionService
     */
    public function __construct(
        Sale $model,
        SaleProduct $saleProduct,
        SaleProductClone $productClone,
        SaleKittingClone $kittingClone,
        SalePromotionFreeItemClone $salePromotionFreeItemClone,
        SaleCancellation $saleCancellation,
        SaleCancellationProduct $saleCancellationProduct,
        SaleShippingAddress $saleShippingAddress,
        SalePromotionFreeItemOptionClone $salePromotionFreeItemOptionClone,
        SalePromotionFreeItemOptionProductClone $salePromotionFreeItemOptionProductClone,
        MemberInterface $memberInterface,
        ProductInterface $productInterface,
        KittingInterface $kittingInterface,
        MasterInterface $masterInterface,
        MasterDataInterface $masterDataInterface,
        PromotionFreeItemsInterface $promotionFreeItemsInterface,
        SettingsInterface $settingsInterface,
        InvoiceInterface $invoiceInterface,
        WorkflowInterface $workflowInterface,
        CwSchedulesInterface $cwSchedulesInterface,
        Location $location,
        LocationAddresses $locationAddresses,
        StockLocation $stockLocation,
        Member $member,
        City $city,
        State $state,
        MemberEnrollmentRankUpgradeLog $memberEnrollmentRankUpgradeLog,
        MemberActiveRecord $memberActiveRecord,
        Country $country,
        User $user,
        MasterData $masterData,
        CreditNote $creditNote,
        AmpCvAllocation $ampCvAllocation,
        EnrollmentRank $enrollmentRank,
        MemberAddress $memberAddress,
        Uploader $uploader,
        EsacPromotion $esacPromotion,
        EsacVoucher $esacVoucher,
        Payment $payment,
        PaymentModeProvider $paymentModeProvider,
        EsacVoucherInterface $esacVoucherInterface,
        SaleEsacVouchersClone $saleEsacVouchersClone,
        SaleCancellationEsacVoucher $saleCancellationEsacVoucher,
        SaleCorporateSale $saleCorporateSale,
        SaleAccumulation $saleAccumulation,
        LegacyInvoice $legacyInvoice,
        LegacySaleCancellationKittingClone $legacySaleCancellationKittingClone,
        LegacySaleCancellationProduct $legacySaleCancellationProduct,
        LegacySaleCancellationProductClone $legacySaleCancellationProductClone,
        Product $product,
        Invoice $invoice,
        SaleExchange $saleExchange,
        ProductRentalPlan $productRentalPlan,
        PaymentModeSetting $paymentModeSetting,
        Stockist $stockist,
        StockistConsignmentProduct $stockistConsignmentProduct,
        ProductCategory $productCategoryObj,
        CommissionService $commissionService
    )
    {
        parent::__construct($model);

        $this->saleProductsObj = $saleProduct;

        $this->saleProductCloneObj = $productClone;

        $this->salePromotionCloneObj = $salePromotionFreeItemClone;

        $this->saleKittingCloneObj = $kittingClone;

        $this->saleCancellationObj = $saleCancellation;

        $this->saleCancellationProductObj = $saleCancellationProduct;

        $this->saleShippingObj = $saleShippingAddress;

        $this->salePwpOptionCloneObj = $salePromotionFreeItemOptionClone;

        $this->salePwpOptionProductCloneObj = $salePromotionFreeItemOptionProductClone;

        $this->memberRepositoryObj = $memberInterface;

        $this->productRepositoryObj = $productInterface;

        $this->kittingRepositoryObj = $kittingInterface;

        $this->masterRepositoryObj = $masterInterface;

        $this->masterDataRepositoryObj = $masterDataInterface;

        $this->promoFreeItemsRepositoryObj = $promotionFreeItemsInterface;

        $this->settingRepositoryObj = $settingsInterface;

        $this->invoiceRepositoryObj = $invoiceInterface;

        $this->workflowRepositoryObj = $workflowInterface;

        $this->cwSchedulesRepositoryObj = $cwSchedulesInterface;

        $this->locationObj = $location;

        $this->locationAddressesObj = $locationAddresses;

        $this->stockLocationObj = $stockLocation;

        $this->memberObj = $member;

        $this->cityObj = $city;

        $this->stateObj = $state;

        $this->memberEnrollmentRankUpgradeLogObj = $memberEnrollmentRankUpgradeLog;

        $this->memberActiveRecordObj = $memberActiveRecord;

        $this->countryObj = $country;

        $this->userObj = $user;

        $this->masterDataObj = $masterData;

        $this->creditNoteObj = $creditNote;

        $this->ampCvAllocationObj = $ampCvAllocation;

        $this->enrollmentRankObj = $enrollmentRank;

        $this->memberAddress = $memberAddress;

        $this->uploader = $uploader;

        $this->esacPromotionObj = $esacPromotion;

        $this->esacVoucherObj = $esacVoucher;

        $this->esacVoucherRepositoryObj = $esacVoucherInterface;

        $this->saleEsacVouchersCloneObj = $saleEsacVouchersClone;

        $this->saleCancellationEsacVoucherObj = $saleCancellationEsacVoucher;

        $this->saleCorporateSaleObj = $saleCorporateSale;

        $this->saleAccumulationObj = $saleAccumulation;

        $this->legacyInvoiceObj = $legacyInvoice;

        $this->legacySaleCancellationKittingCloneObj = $legacySaleCancellationKittingClone;

        $this->legacySaleCancellationProductObj = $legacySaleCancellationProduct;

        $this->legacySaleCancellationProductCloneObj = $legacySaleCancellationProductClone;

        $this->productObj = $product;

        $this->productRentalPlanObj = $productRentalPlan;

        $this->paymentModeSettingObj = $paymentModeSetting;

        $this->transactionTypeConfigCodes = Config::get('mappings.sale_types');

        $this->saleOrderStatusConfigCodes = Config::get('mappings.sale_order_status');

        $this->saleCancellationStatusConfigCodes = Config::get('mappings.sale_cancellation_status');

        $this->stockistDailyTransactionReleaseStatusConfigCodes = Config::get('mappings.stockist_daily_transaction_release_status');

        $this->promotionFreeItemsPromoTypesConfigCodes =
            config('mappings.promotion_free_items_promo_types');
            
        $this->paymentObj = $payment;

        $this->paymentModeProviderObj = $paymentModeProvider;

        $this->memberStatusConfigCodes = Config::get('mappings.member_status');

        $this->ampCvAllocationTypesConfigCodes = Config::get('mappings.amp_cv_allocation_types');

        $this->legacySaleCancellationModeConfigCodes = Config::get('mappings.legacy_sale_cancellation_mode');

        $this->invoiceObj = $invoice;

        $this->saleExchangeObj = $saleExchange;

        $this->stockistObj = $stockist;

        $this->stockistConsignmentProductObj = $stockistConsignmentProduct;

        $this->cvAcronymCodes = Config('mappings.cv_acronym');

        $this->saleTypeCvSettings = Config('setting.sale-type-cvs');

        $this->saleCancellationTypeConfigCodes = config('mappings.sale_cancellation_type');

        $this->paymentModeConfigCodes = Config::get('mappings.payment_mode');

        $this->productCategoryObj = $productCategoryObj;

        $this->commissionService = $commissionService;
    }

    /**
     * get sales filtered by the following parameters
     *
     * @param int $countryId
     * @param string $text
     * @param $dateFrom
     * @param $dateTo
     * @param int $channel
     * @param int $deliveryMethod
     * @param int $deliveryStatus
     * @param int $orderStatus
     * @param int $esacRedemption
     * @param int $corporateSales
     * @param int $rentalSaleOrder
     * @param int $withTrashed
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getSalesByFilters(
        int $countryId,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $channel = 0,
        int $deliveryMethod = 0,
        int $deliveryStatus = 0,
        int $orderStatus = 0,
        int $esacRedemption = -1,
        int $corporateSales = -1,
        int $rentalSaleOrder = -1,
        int $withTrashed = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->with([
                'member',
                'country',
                'orderStatus',
                'channel',
                'invoices',
                'deliveryMethod',
                'deliveryStatus',
                'saleExchange',
                'createdBy',
                'transactionLocation'
            ])
            ->where('sales.country_id', $countryId);
        ;

        $country = $this->countryObj->find($countryId);

        //check the granted location give for the user if he back_office,stockist or member
        if (
            $this->isUser('back_office') or
            $this->isUser('stockist') or
            $this->isUser('stockist_staff')
        )
        {
            $this->applyLocationQuery($data, $countryId, 'transaction_location_id');
        }
        elseif ($this->isUser('member'))
        {
            $data = $data
                ->where('user_id', Auth::id());
        }

        //Included Soft Delete Record
        if($withTrashed == 1){
            $data = $data->withTrashed();
        }

        //check if channel is applied
        if ($channel > 0) {
            $data = $data
                ->where('channel_id', $channel);
        }

        //check if delivery method is applied
        if ($deliveryMethod > 0) {
            $data = $data
                ->where('delivery_method_id', $deliveryMethod);
        }

        //check if delivery method is applied
        if ($deliveryStatus > 0) {
            $data = $data
                ->where('delivery_status_id', $deliveryStatus);
        }

        //check if order status is applied
        if ($orderStatus > 0) {
            $data = $data
                ->where('order_status_id', $orderStatus);
        }

        //check if esac redemption is applied
        if ($esacRedemption > -1) {
            $data = $data
                ->where('is_esac_redemption', $esacRedemption);
        }

        //check if corporate sales is applied
        if ($corporateSales > -1) {
            $data = $data
                ->where('is_corporate_sales', $corporateSales);
        }

        if ($rentalSaleOrder == -1) { //Default not display rental sale order record
            $data = $data
                ->where('is_rental_sale_order', 0);
        } else if ($rentalSaleOrder != 2) { // when $rentalSaleOrder is equal 2, it will include both record
            $data = $data
                ->where('is_rental_sale_order', $rentalSaleOrder);
        }

        //search on the invoices, member ....etc
        if ($text != '') {

            //TODO optmize the seraching for sales for large data. -JALALA

            //check if text match invoice number
            $invoices = $this->invoiceRepositoryObj
                ->getInvoicesByFilters($countryId, $text);

            $invoices = $invoices['data']
                ->where('invoice_number', $text)
                ->count();

            // check if text match old member id
            $user = $this->userObj
                ->where('old_member_id', 'like', '%' . $text . '%')
                ->count();

            //check sale doc number
            $saleDocumentNumber = $this->modelObj
                ->where('document_number', $text)
                ->first();

            if ($invoices > 0)
            {
                $data = $data
                    ->select('sales.*')
                    ->join('invoices', function ($join) use ($text) {
                        $join->on('invoices.sale_id', '=', 'sales.id')
                            ->where(function ($query) use ($text) {
                                $query
                                    ->where('invoices.invoice_number', $text);;
                            });
                    });
            }
            elseif ($user > 0)
            {
                $data = $data
                    ->select('sales.*')
                    ->join('users', function ($join) use ($text) {
                        $join->on('users.id', '=', 'sales.user_id')
                            ->where(function ($query) use ($text) {
                                $query
                                    ->where('users.old_member_id', 'like', '%' . $text . '%');;
                            });
                    });
            }
            elseif($saleDocumentNumber != null)
            {
                $data = $data
                    ->where('document_number', $text);

            }
            else
            {
                $data = $data
                    ->select('sales.*')
                    ->join('members', function ($join) use ($text){
                        $join->on('members.user_id', '=', 'sales.user_id')
                            ->where(function ($query) use ($text) {
                                $query
                                    ->where('members.ic_passport_number', 'like','%' . $text . '%')
                                    ->orWhere('members.name', 'like','%' . $text . '%');
                            });
                    });
            }
        }

        //check the dates if given
        if ($dateFrom != '' and $dateTo != ''){
            $data = $data
                ->where('transaction_date','>=', $dateFrom)
                ->where('transaction_date','<=', $dateTo);
            ;
        }

        if ($channel > 0 || $deliveryMethod > 0 || $deliveryStatus > 0 || $orderStatus > 0 || $text != '') {
            $totalRecords = collect(['total' => $data->count()]);
        }else{
            $totalRecords = collect(['total' => $data->count()]);
        }

        $data = $data->orderBy($orderBy, $orderMethod);

        $data->select('sales.*');

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     *  get sales details for a given id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get sales details for a given salesId
     *
     * @param int $saleId
     * @return array|mixed
     */
    public function saleDetails(int $saleId)
    {
        $sales = $this->modelObj->with(['esacVouchers'])->findOrFail($saleId);

        //products - loose products-------------------------------------------------------------------------------------
        $products = $sales->getSaleProducts($this->productRepositoryObj, $sales->country_id);

        //kitting-------------------------------------------------------------------------------------------------------
        $kitting = $sales->getSaleKitting($this->productRepositoryObj, $sales->country_id);

        //promotions----------------------------------------------------------------------------------------------------
        $promotions = $sales->getSalePromotions();

        $promotionSelected = $sales->getSaleSelectedPromotions($this->productRepositoryObj, $sales->country_id);

        $promotionSelectedCollection = new Collection();

        foreach($promotionSelected as $items)
        {
            foreach($items as $item)
            {
                $promotionSelectedCollection->push($item);
            }
        }

        //other sales details-------------------------------------------------------------------------------------------
        $salePayments = $sales->salePayments()->get();

        $invoice = $sales->invoices()->first();

        $shipping = $sales->getSaleShippingAddress();

        $productsEligibleCVs = $sales->getSaleProductsEligibleCVs();

        $kittingEligibleCVs = $sales->getSaleKittingEligibleCVs();

        $saleAmpCVs = $sales->getSaleAmpCVs();

        $saleBaseCVs = $sales->getSaleBaseCVs($this->masterRepositoryObj, $this->transactionTypeConfigCodes);

        $saleWpCVs = $sales->getSaleWpCVs($this->masterRepositoryObj, $this->transactionTypeConfigCodes);

        $esacVouchers = $sales->esacVouchers;

        $esacVoucherTypeId = 0;

        if (count($esacVouchers) > 0) {
            $esacVoucherTypeId = $esacVouchers[0]['voucher_type_id'];
        }

        $sponsorMember = $this->userObj->where('id', $sales->sponsor_id)->first();

        $downlineMember = $this->userObj->where('id', $sales->user_id)->first();

        $orderStatus = $this->masterDataObj->where('id', $sales->order_status_id)->first();

        $deliveryMethod = $this->masterDataObj->where('id', $sales->delivery_method_id)->first();

        $locationAddress = $this->locationAddressesObj->where('id', $sales->self_collection_point_id)->first();

        $selfCollectionPoint = null;

        if ($locationAddress !== null) {
            $locationAddressCountry = $this->countryObj->where('id', $locationAddress->country_id)->first();

            $locationAddressState = $this->stateObj->where('id', $locationAddress->state_id)->first();

            $locationAddressCity = $this->cityObj->where('id', $locationAddress->city_id)->first();

            $locationAddressTelephone = $this->countryObj->where('id', $locationAddress->telephone_code_id)->first();

            $selfCollectionPoint = [
                'address_data' => $locationAddress->address_data,
                'country_name' => optional($locationAddressCountry)->name,
                'state_name' => optional($locationAddressState)->name,
                'city_name' => optional($locationAddressCity)->name,
                'telephone_code' => optional($locationAddressTelephone)->call_code,
                'telephone_num' => $locationAddress->telephone_num,
                'location_detail' => $locationAddress->location()->first()
            ];
        }

        //Get EPP Payment Record
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $eppMotoPaymentProviderIds = $this->paymentModeProviderObj
            ->where('master_data_id', $paymentMode[$this->paymentModeConfigCodes['epp (moto)']])
            ->pluck('id');

        $eppMotoPayment = $sales->salePayments()
            ->with(['updatedBy', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->whereIn('payment_mode_provider_id', $eppMotoPaymentProviderIds)
            ->first();

        if($eppMotoPayment){

            $eppMotoPayment->payment_detail = json_decode($eppMotoPayment->payment_detail, true);

            $paymentDetail = $eppMotoPayment->payment_detail;

            $paymentDetailResponse = $eppMotoPayment->payment_detail['payment_response'];

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

            $eppMotoPayment->payment_detail = $paymentDetail;
        }

        //Add Status Title Display Indicator
        $eppMotoPaymentProviderIds = $eppMotoPaymentProviderIds->toArray();

        collect($salePayments)->each(function ($salePayment)
            use ($eppMotoPaymentProviderIds){
                $salePayment->is_approve = (in_array($salePayment->payment_mode_provider_id, $eppMotoPaymentProviderIds)) ?
                    true : false;
            });

        $workflow = (!empty($sales->workflow_tracking_id)) ?
            $this->workflowRepositoryObj
                ->getTrackingWorkflowDetails($sales->workflow_tracking_id)['workflow'] : [];

        $saleDetailObj = [
            'sales_data' => [
                'sale_id' => $sales->id,
                'country_id' => $sales->country_id,
                'document_number' => $sales->document_number,
                'self_pick_up_number' => $sales->self_pick_up_number,
                'sponsor_member_id' => ($sales->sponsor_id > 0)? $sales->sponsor_id : null,
                'sponsor_member' => $sponsorMember,
                'downline_member_id' => $sales->user_id,
                'downline_member' => $downlineMember,
                'order_status_id' => $sales->order_status_id,
                'order_status' => optional($orderStatus)->title,
                'delivery_method_id' => $sales->delivery_method_id,
                'delivery_method' => optional($deliveryMethod)->title,
                'self_collection_point' => $selfCollectionPoint,
                'location_id' => $sales->transaction_location_id,
                'location' => $this->locationObj->find($sales->transaction_location_id),
                'stock_location_id' => $sales->stock_location_id,
                'stock_location' => $this->stockLocationObj->find($sales->stock_location_id),
                'invoice_id' => optional($invoice)->id,
                'invoice' => $invoice,
                'transaction_date' => $sales->transaction_date,
                'cw_id' => $sales->cw_id,
                'cw' => !is_null($sales->cw) ? $sales->cw->toArray() : [],
                'tax_rate' => $sales->tax_rate,
                'remarks' => $sales->remarks,
                'skip_downline' => $sales->skip_downline,
                'is_esac_redemption' => $sales->is_esac_redemption,
                'is_corporate_sales' => $sales->is_corporate_sales,
                'corporate_sales' => $sales->saleCorporateSale,
                'cvs' => [
                    'total_cv' => $sales->total_cv,
                    'total_qualified_cv' => $sales->total_qualified_cv,
                    'total_amp_cv' => $saleAmpCVs,
                    'total_base_cv' => $saleBaseCVs,
                    'total_wp_cv' => $saleWpCVs,
                    'products' => $productsEligibleCVs,
                    'kitting' => $kittingEligibleCVs
                ],
                'order_fees' => [
                    'total_nmp' => $sales->total_amount,
                    'admin_fee' => $sales->admin_fees,
                    'delivery_fee' => $sales->delivery_fees,
                    'tax_amount' => $sales->tax_amount,
                    'total_gmp' => $sales->total_gmp,
                    'rounding_adjustment' => $sales->rounding_adjustment,
                    'other_fee' => $sales->other_fees,
                    'total_esac_voucher_value' => $esacVouchers->sum('voucher_value')
                ],
                'products' => $products,
                'kittings' => $kitting,
                'promotion' => $promotions,
                'selected' =>
                    [
                        'promotions' => $promotionSelectedCollection,
                        'shipping' => $shipping,
                        'payments' =>  [
                            'paid' => $salePayments,
                            'unpaid' =>  []
                        ],
                        'additional_requirements' =>  [
                            'sizes' =>  [],
                            'addresses' =>  [],
                            'evoucher' =>  []
                        ]
                    ],
                'voucher_type_id' => $esacVoucherTypeId,
                'esac_vouchers' => $esacVouchers->pluck('id')->toArray(),
                'esac_vouchers_list' => $esacVouchers,
                'max_purchase_qty' => $esacVouchers->sum('max_purchase_qty'),
                'min_purchase_amount' => $esacVouchers->sum('min_purchase_amount'),
                'delivery_order' => $sales->deliveryOrder,
                'is_rental_sale_order' => $sales->is_rental_sale_order,
                'has_virtual_invoice' => ($saleAmpCVs > 0) ? true : false,
                'is_epp_moto_payment' => ($eppMotoPayment) ? true : false,
                'epp_moto_payment' => ($eppMotoPayment) ? $eppMotoPayment : []
            ]
        ];

        if(!empty($workflow)){
            $saleDetailObj['workflow'] = $workflow;
        }

        return $saleDetailObj;
    }

    /**
     * create new sales
     *
     * @param array $data
     * @param bool $orderCompleteStatus
     * @return array|mixed
     */
    public function createSale(array $data, bool $orderCompleteStatus = false)
    {
        $order = $data['sales_data'];

        //esac redemption handling: get the flag and overwrite complete status,
        // this flag is used later to generate invoice
        $esacRedemption = (isset($order['is_esac_redemption']) ? $order['is_esac_redemption'] : false);
        $corporateSale = (isset($order['is_corporate_sales']) ? $order['is_corporate_sales'] : false);

        $totalEsacVoucherValue = (isset($order['order_fees']['total_esac_voucher_value']) ?
            $order['order_fees']['total_esac_voucher_value'] : 0);

        if ($esacRedemption && floatval($order['order_fees']['total_gmp']) <= 0) {
            $orderCompleteStatus = true;
        }

        $selfCollectionPointId =
            (isset($order['selected']['shipping']['self_collection_point_id'])) ?
                $order['selected']['shipping']['self_collection_point_id'] : null;

        //get the pre-order status id-----------------------------------------------------------------------------------
        $orderStatus = $this->masterRepositoryObj
            ->getMasterDataByKey(['sale_order_status']);

        $orderStatus = $orderStatus['sale_order_status']->first();

        //set order status----------------------------------------------------------------------------------------------
        if ($orderCompleteStatus){
            $orderStatus = $orderStatus
                ->where('title',strtoupper($this->saleOrderStatusConfigCodes['completed']))
                ->pluck('id');
        }else{
            if($order['status'] == 'save') {
                $orderStatus = $orderStatus
                    ->where('title', strtoupper($this->saleOrderStatusConfigCodes['pre-order']))
                    ->pluck('id');
            } else if($order['status'] == 'pending'){
                $orderStatus = $orderStatus
                    ->where('title', strtoupper($this->saleOrderStatusConfigCodes['pending']))
                    ->pluck('id');
            } else if($order['status'] == 'pending-online'){
                $orderStatus = $orderStatus
                    ->where('title', strtoupper($this->saleOrderStatusConfigCodes['pending-online']))
                    ->pluck('id');
            }
        }

        //get channel id based on transaction location------------------------------------------------------------------
        $location = $this->locationObj->find($order['location_id']);

        $channel = $location->locationType()->first();

        //extract some var for obj--------------------------------------------------------------------------------------
        $countryId =  $order['country_id'];

        //generate pre_order document number----------------------------------------------------------------------------
        $documentNumber = $this->settingRepositoryObj
            ->getRunningNumber('pre_order',$order['country_id'], $order['location_id']);

        //check the sponsor id if its set to check on skip_downline-----------------------------------------------------
        $skipDownLine = 0;
        if(isset($order['sponsor_member_id']) and $order['sponsor_member_id'] > 0){
            if ($order['sponsor_member_id'] === $order['downline_member_id']){
                $skipDownLine = 1;
            }
        }

        //get country default tax---------------------------------------------------------------------------------------
        $country = $this->countryObj->find($countryId);

        $countryTax = $country->taxes()->default()->active()->first();

        //is PE
        $isProductExchange = (isset($order['is_product_exchange']) ? $order['is_product_exchange'] :  0 );

        //create new sales record---------------------------------------------------------------------------------------
        $saleData = [
            'country_id' => $countryId,
            'document_number' => $documentNumber,
            'channel_id' => $channel->id,
            'sponsor_id' => (isset($order['sponsor_member_id']) ? $order['sponsor_member_id'] :  null ),
            'user_id' => $order['downline_member_id'],
            'cw_id' => $order['cw_id'],
            'tax_rate' => $countryTax->rate,
            'transaction_location_id' => $order['transaction_location_id'] ?? $order['location_id'],
            'stock_location_id' => $order['stock_location_id'],
            'remarks' => $order['remarks'],
            'is_product_exchange' => $isProductExchange,
            'skip_downline' => $skipDownLine,
            'transaction_date' => date('Y-m-d'),

            //total section----------------------------------
            'total_amount' => $order['order_fees']['total_nmp'],
            'tax_amount' => $order['order_fees']['tax_amount'],
            'total_gmp' => $order['order_fees']['total_gmp'],
            'rounding_adjustment' => $order['order_fees']['rounding_adjustment'],
            'total_cv' => ($esacRedemption ? 0 : $order['cvs']['total_cv']),
            'total_qualified_cv' => ($esacRedemption ? 0 : $order['cvs']['total_qualified_cv']),

            //order status and delivery-------------------------
            'order_status_id' => $orderStatus[0],
            'delivery_method_id' => $order['selected']['shipping']['sale_delivery_method'],
            'self_collection_point_id' => $selfCollectionPointId,

            //esac section--------------------------------------
            'is_esac_redemption' => $esacRedemption,
            'is_corporate_sales' => $corporateSale,
            'is_rental_sale_order' => (isset($order['is_rental_sale_order']) ? $order['is_rental_sale_order'] :  0 ),
            'total_esac_voucher_value' => $totalEsacVoucherValue,
        ];

        $sales = Auth::user()->createdBy($this->modelObj)->create($saleData);

        $locationArray[] = $order['location_id'];

        //sales products------------------------------------------------------------------------------------------------
        if (isset($order['products']) and !empty($order['products'])){

            collect($order['products'])->each(function ($product)
            use ($order, $sales, $locationArray, $esacRedemption, $isProductExchange){

                $effectivePrice = optional($this->productRepositoryObj
                    ->productEffectivePricing(
                        $order['country_id'],
                        $product['product_id'],
                        $locationArray
                    ))
                    ->toArray()
                ;

                //add the product to the cloning table
                $productClone = $this->cloneProduct($sales->id, $product['product_id'], $sales->country_id);

                //get the eligible cv if not PE
                if (!$isProductExchange){
                    $eligibleCv = $this->getSaleMappingCv(
                        $product['product_id'],
                        'product',
                        $product['quantity'],
                        (!empty($product['transaction_type'])) ? $product['transaction_type']: 0,
                        $sales->country_id,
                        $order['location_id']
                    );
                }else{
                    $eligibleCv = 0;
                }

                $salesProduct = [
                    'sale_id' => $sales->id,
                    'product_id' => $productClone->id,
                    'product_price_id' => $effectivePrice['id'],
                    'transaction_type_id' => (isset($product['transaction_type'])) ? $product['transaction_type'] : NULL,
                    'quantity' => $product['quantity'],
                    'available_quantity' => $product['quantity'],

                    'gmp_price_gst'=> $effectivePrice['gmp_price_tax'],
                    'rp_price'=> $effectivePrice['rp_price'],
                    'rp_price_gst'=> $effectivePrice['rp_price_tax'],
                    'nmp_price'=> $effectivePrice['nmp_price'],

                    'average_price_unit'=>  $effectivePrice['gmp_price_tax'],

                    'effective_date'=> $effectivePrice['effective_date'],
                    'expiry_date'=> $effectivePrice['expiry_date'],

                    'total' => $product['quantity'] * $effectivePrice['gmp_price_tax'],

                    'base_cv' => (($esacRedemption or $isProductExchange) ? 0 : $effectivePrice['base_cv']),
                    'wp_cv'=> (($esacRedemption or $isProductExchange) ? 0 : $effectivePrice['base_cv']),
                    'cv1'=> (($esacRedemption or $isProductExchange) ? 0 : $effectivePrice['cv_1']),
                    'cv2'=> (($esacRedemption or $isProductExchange) ? 0 : $effectivePrice['cv_2']),
                    'cv3'=> (($esacRedemption or $isProductExchange) ? 0 : $effectivePrice['cv_3']),
                    'cv4'=> (($esacRedemption or $isProductExchange) ? 0 : $effectivePrice['cv_4']),
                    'cv5'=> (($esacRedemption or $isProductExchange) ? 0 : $effectivePrice['cv_5']),
                    'cv6'=> (($esacRedemption or $isProductExchange) ? 0 : $effectivePrice['cv_6']),
                    'eligible_cv' => (($esacRedemption or $isProductExchange) ? 0 : $eligibleCv['itemCv']),
                    'virtual_invoice_cv' => (($esacRedemption or $isProductExchange) ? 0 : $eligibleCv['virtualInvoiceCv']),

                    'welcome_bonus_l1'=> $effectivePrice['bonuses']['welcome_bonus_1'],
                    'welcome_bonus_l2'=> $effectivePrice['bonuses']['welcome_bonus_2'],
                    'welcome_bonus_l3'=> $effectivePrice['bonuses']['welcome_bonus_3'],
                    'welcome_bonus_l4'=> $effectivePrice['bonuses']['welcome_bonus_4'],
                    'welcome_bonus_l5'=> $effectivePrice['bonuses']['welcome_bonus_5'],
                ];

                //create sales product record
                $this->saleProductsObj->create($salesProduct);
            });
        }

        //sales kitting-------------------------------------------------------------------------------------------------
        if (isset($order['kittings']) and !empty($order['kittings']))
        {
            collect($order['kittings'])->each(function ($kitting)
            use ($sales, $countryId, $order, $locationArray, $esacRedemption, $isProductExchange){

                $kittingData = $this->kittingRepositoryObj
                    ->kittingDetails($countryId, $kitting['kitting_id']);

                if (!$isProductExchange){
                    $eligibleCv = $this->getSaleMappingCv(
                        $kitting['kitting_id'],
                        'kitting',
                        $kitting['quantity'],
                        (!empty($kitting['transaction_type'])) ? $kitting['transaction_type']: 0,
                        $sales->country_id,
                        $order['location_id']
                    );
                }else{
                    $eligibleCv = 0;
                }


                //clone kitting to saleKittingClone
                $kittingClone = [
                    'sale_id' => $sales->id,
                    'kitting_id' => $kittingData['kitting_id'],
                    'country_id' => $kittingData['country_id'],
                    'name' => $kittingData['name'],
                    'code' => $kittingData['code'],
                    'quantity' => $kitting['quantity'],
                    'available_quantity' => $kitting['quantity'],
                    'transaction_type_id' => (isset($kitting['transaction_type'])) ? $kitting['transaction_type']: NULL,
                    'currency_id' => $kittingData['kitting_price']->currency_id,
                    'gmp_price_gst' => $kittingData['kitting_price']->gmp_price_gst,
                    'rp_price' => $kittingData['kitting_price']->rp_price,
                    'rp_price_gst' => $kittingData['kitting_price']->rp_price_gst,
                    'nmp_price' => $kittingData['kitting_price']->nmp_price,
                    'effective_date' => $kittingData['kitting_price']->effective_date,
                    'expiry_date' => $kittingData['kitting_price']->expiry_date,
                    'base_cv' => (($esacRedemption or $isProductExchange) ? 0 : $kittingData['kitting_price']->base_cv),
                    'wp_cv' => (($esacRedemption or $isProductExchange) ? 0 : $kittingData['kitting_price']->wp_cv),
                    'cv1' => (($esacRedemption or $isProductExchange) ? 0 : $kittingData['kitting_price']->cv1),
                    'cv2' => (($esacRedemption or $isProductExchange) ? 0 : $kittingData['kitting_price']->cv2),
                    'cv3' => (($esacRedemption or $isProductExchange) ? 0 : $kittingData['kitting_price']->cv3),
                    'cv4' => (($esacRedemption or $isProductExchange) ? 0 : $kittingData['kitting_price']->cv4),
                    'cv5' => (($esacRedemption or $isProductExchange) ? 0 : $kittingData['kitting_price']->cv5),
                    'cv6' => (($esacRedemption or $isProductExchange) ? 0 : $kittingData['kitting_price']->cv6),
                    'eligible_cv' => (($esacRedemption or $isProductExchange) ? 0 : $eligibleCv['itemCv']),
                    'virtual_invoice_cv' => (($esacRedemption or $isProductExchange) ? 0 : $eligibleCv['virtualInvoiceCv']),
                    'welcome_bonus_l1' => $kittingData['kitting_price']->welcome_bonus_l1,
                    'welcome_bonus_l2' => $kittingData['kitting_price']->welcome_bonus_l2,
                    'welcome_bonus_l3' => $kittingData['kitting_price']->welcome_bonus_l3,
                    'welcome_bonus_l4' => $kittingData['kitting_price']->welcome_bonus_l4,
                    'welcome_bonus_l5' => $kittingData['kitting_price']->welcome_bonus_l5,
                ];

                $kittingClone =  $this->saleKittingCloneObj->create($kittingClone);

                $j = 1;

                $totalKittingPrices = 0;

                //calculate the total gmp for a given kitting
                $totalGmpPrice = $this->kittingRepositoryObj->calculateKittingTotalGmp(
                    $order['country_id'],
                    $kittingData,
                    $locationArray
                );

                //kitting products---------------------------------------------------
                collect($kittingData['kitting_products'])->each(
                    function ($product)
                    use ($sales, $order ,$kitting ,$totalGmpPrice ,$locationArray,
                        $kittingData, $kittingClone, $totalKittingPrices, $j
                    )
                    {
                        $effectivePrice = optional($this->productRepositoryObj
                            ->productEffectivePricing(
                                $order['country_id'],
                                $product['product']->id,
                                $locationArray
                            ))
                            ->toArray();

                        //fallback to active price
                        if ($effectivePrice == null)
                        {
                            $effectivePrice =  optional($this->productRepositoryObj
                                ->productEffectivePricing(
                                    $order['country_id'],
                                    $product['product']->id
                                ))
                                ->toArray();
                        }

                        $averagePriceUnit = $totalPromoPrice = $totalPromoPriceGst = $averagePriceUnitGst = 0;

                        $productQuantity = ($product['quantity']> 0) ? $product['quantity'] : $product['foc_qty'];

                        //do the ratio for each product inside kitting
                        if ($effectivePrice['gmp_price_tax'] > 0) {
                            $ratio = number_format(((($effectivePrice['gmp_price_tax'] * $product['quantity'] ) / $totalGmpPrice) * 100), 2);

                            $totalPromoPriceGst =  number_format((($kittingData['kitting_price']['gmp_price_gst'] * $ratio) / 100),2, '.', '');

                            $totalPromoPrice =  number_format((($kittingData['kitting_price']['nmp_price'] * $ratio) / 100),2, '.', '');

                            $averagePriceUnitGst = number_format(($totalPromoPriceGst / $productQuantity),7, '.', '');

                            $averagePriceUnit = number_format(($totalPromoPrice/ $productQuantity),7, '.', '');
                        }

                        //clone product to salesProductClone
                        $productClone = $this->cloneProduct($sales->id, $product['product']->id, $sales->country_id);

                        //save the product in sales products table
                        $salesProduct = [
                            'sale_id' => $sales->id,
                            'product_id' => $productClone->id,
                            'product_price_id' => $effectivePrice['id'],
                            'transaction_type_id' => (isset($kitting['transaction_type'])) ? $kitting['transaction_type']: NULL,
                            'quantity' => intval($product['quantity']) * intval($kittingClone->quantity),
                            'available_quantity' => intval($productQuantity) * intval($kittingClone->quantity),
                            'foc_qty' => intval($product['foc_qty']) * intval($kittingClone->quantity),
                            'mapping_id' => $kittingClone->id,
                            'mapping_model' => 'sales_kitting_clone',
                            'gmp_price_gst' => isset($effectivePrice['gmp_price_tax'])?
                                $effectivePrice['gmp_price_tax'] : $effectivePrice['gmp_price_gst'],
                            'nmp_price' => $averagePriceUnit,
                            'average_price_unit'=> $averagePriceUnitGst,
                            'total'=> $totalPromoPriceGst * $kittingClone->quantity,
                        ];

                        //create sales product record
                        $this->saleProductsObj->create($salesProduct);
                    }
                );
            });
        }

        //sales promotions----------------------------------------------------------------------------------------------
        if (isset($order['promotion']) and !empty($order['promotion']))
        {
            collect($order['promotion'])->each(function ($promo) use ($order, $sales, $locationArray, $countryTax) {

                $promotion = $this->promoFreeItemsRepositoryObj->find($promo['promo_id']);

                $promoClone = $this->salePromotionCloneObj->create(
                    [
                        'sale_id' => $sales->id,
                        'promotion_free_items_id' => $promotion->id,
                        'name' => $promotion->name,
                        'start_date' => $promotion->start_date,
                        'end_date' => $promotion->end_date,
                        'promo_type_id' => $promotion->promo_type_id,
                        'from_cv_range' => $promotion->from_cv_range,
                        'to_cv_range' => $promotion->to_cv_range,
                        'pwp_value' => $promotion->pwp_value,
                        'min_purchase_qty' => $promotion->min_purchase_qty,
                        'options_relation' => $promotion->options_relation
                    ]
                );

                //get all promotion products---------------------------------------
                $promotionProducts = $promotion
                    ->promotionFreeItemOptionProducts()
                    ->get();

                $promotionOptions = $promotion
                    ->promotionOptions()
                    ->get();

                collect($promotionOptions)->each(function ($promotionOption) use ($sales, $promotion, $promoClone){

                    $explodOptions = explode(',', $promotionOption['option_products']);

                    $optionData = '';

                    foreach ($explodOptions as $option)
                    {
                        if ($option == "") break;

                        if ($option > 0) //
                        {
                            $clonedProduct = $this->cloneProduct($sales->id, $option, $sales->country_id);

                            $promotionProduct = $promotion
                                ->promotionFreeItemOptionProducts()
                                ->where('option_id', $promotionOption['option_id'])
                                ->where('product_id', $option)
                                ->first();

                            //insert this new cloned product to sales pwp foc cloned table
                            $salePwpProductCloned = $this->salePwpOptionProductCloneObj->create(
                                [
                                    'sale_id' => $sales->id,
                                    'promo_id' => $promoClone->id,
                                    'option_id' => $promotionOption['option_id'],
                                    'product_id' => $option,
                                    'product_clone_id' => $clonedProduct->id,
                                    'quantity' => $promotionProduct['quantity']
                                ]
                            );

                            $optionData .= $salePwpProductCloned->product_clone_id.',';
                        }else{
                            $optionData .= $option.',';
                        }
                    }

                    $this->salePwpOptionCloneObj->create(
                        [
                            'sale_id' => $sales->id,
                            'promo_id' => $promoClone->id,
                            'option_id' => $promotionOption['option_id'],
                            'option_products' => $optionData
                        ]
                    );
                });

                //clone promotion products
                collect($order['selected']['promotions'])
                    ->where('promo_id', $promo['promo_id'])
                    ->each(function ($promoItem) use($sales, $order, $locationArray, $promoClone, $countryTax)
                    {
                        $effectivePrice = optional($this->productRepositoryObj
                            ->productEffectivePricing(
                                $order['country_id'],
                                $promoItem['product_id'],
                                $locationArray
                            ))
                            ->toArray();

                        //get the promotion first
                        $salePromotion = $sales
                            ->salePromotionFreeItemClone()
                            ->where('promotion_free_items_id', $promoItem['promo_id'])
                            ->first();

                        $promoType = $salePromotion->promotionType()->first();

                        //clone product to salesProductClone
                        $productClone = $salePromotion
                            ->salePromotionFreeItemOptionProductClone()
                            ->where('product_id', $promoItem['product_id'])
                            ->where('option_id', $promoItem['option_id'])
                            ->first();

                        //save the product in sales products table
                        $salesProduct = [
                            'sale_id' => $sales->id,
                            'product_id' => $productClone['product_clone_id'],
                            'mapping_id' => $promoClone->id,
                            'mapping_model' => 'sales_promotion_free_items_clone',
                            'gmp_price_gst'=> $promoClone['pwp_value'],
                            'nmp_price'=> number_format(($promoClone['pwp_value'] - ($promoClone['pwp_value'] * ($countryTax->rate /100))),2, '.', ''),
                            'average_price_unit' => $promoClone['pwp_value'],
                            'total'=> ($promoType->title == 'PWP(N)') ? $promoClone['pwp_value'] * $promoItem['selected_quantity'] : $promoClone['pwp_value'],
                            'quantity' => $promoItem['selected_quantity'],
                            'available_quantity' => $promoItem['selected_quantity'],
                            'option_id'=> $promoItem['option_id'],
                            'set_id'=> $productClone['id'],
                            'set_key' => $promoItem['set_key'],
                            'operator'=> $promoItem['operator'],
                        ];

                        //create sales product record
                        $this->saleProductsObj->create($salesProduct);
                    }
                );
            });
        }

        //sales shipping address----------------------------------------------------------------------------------------
        if (isset($order['selected']) and !empty($order['selected'])){

            //check if shipping sets
            if (isset($order['selected']['shipping'])){
                $shippingData = $order['selected']['shipping'];

                //get shipping address method ....................
                $shippingMethodMaster = $this->masterRepositoryObj
                    ->getMasterDataByKey(['sale_delivery_method'])
                    ->pop()->pluck('id','title')->flip()->get($shippingData['sale_delivery_method']);

                $shippingMethod = [
                    'sale_id' => $sales->id,
                    'country_id' => null,
                    'delivery_method_id' => $shippingData['sale_delivery_method'],
                    'recipient_name' => '',
                    'mobile' => '',
                    'address' => json_encode([]),
                    'shipping_index' => 0
                    ]
                ;

                //if the delivery method is not equal to self pickup, then insert shipping address
                if (strtolower($shippingMethodMaster) === strtolower(config::get('mappings.sale_delivery_method.delivery'))) {
                    $countryCallId = (isset($shippingData['recipient_mobile_country_code_id']) && $shippingData['recipient_mobile_country_code_id'] > 0)
                        ? $shippingData['recipient_mobile_country_code_id']
                        : null;

                    $shippingMethod['country_id'] = $countryCallId;

                    $shippingMethod['delivery_method_id'] = $shippingData['sale_delivery_method'];

                    $shippingMethod['recipient_name'] = $shippingData['recipient_name'];

                    $shippingMethod['mobile'] = $shippingData['recipient_mobile_phone_number'];

                    $shippingMethod['address'] = json_encode($shippingData['recipient_addresses']);

                    $shippingMethod['shipping_index'] = $shippingData['recipient_selected_shipping_index'];
                }

                $this->saleShippingObj->create($shippingMethod);
            }
        }

        //esac vouchers-------------------------------------------------------------------------------------------------
        if (isset($order['esac_vouchers']) && count($order['esac_vouchers']) > 0) {
            $sales->esacVouchers()
                ->sync($order['esac_vouchers']);

            collect($order['esac_vouchers'])->each(function ($esacVoucherId) use ($order, $sales) {
                $voucherClone = $this->cloneEsacVoucher($sales->id, $esacVoucherId);
            });
        }

        //corporate sales-----------------------------------------------------------------------------------------------
        if ($corporateSale) {
            $corporateSaleData = [
                'sale_id' => $sales->id,
                'company_name' => $order['corporate_sales']['company_name'],
                'company_reg_number' => $order['corporate_sales']['company_reg_number'],
                'company_address' => json_encode($order['corporate_sales']['company_address']),
                'company_email' => $order['corporate_sales']['company_email'],
                'person_in_charge' => $order['corporate_sales']['person_in_charge'],
                'contact_country_code_id' => $order['corporate_sales']['contact_country_code_id'],
                'contact_number' => $order['corporate_sales']['contact_number']
            ];

            $saleCorporateSale = $this->saleCorporateSaleObj
                ->where('sale_id', $sales->id)
                ->first();
            
            if (empty($saleCorporateSale)) {
                $this->saleCorporateSaleObj->create($corporateSaleData);
            }
            else {
                $saleCorporateSale->update($corporateSaleData);
            }
        }

        //special case for esac redemption, generate invoice if it is comepleted
        if ($esacRedemption && $orderCompleteStatus) {
            $this->invoiceRepositoryObj->generateInvoice(
                $this->find($sales->id)
            );
        }

        //broadcast the new sales created
        event(new SalesCreatedEvent($sales));

        return $this->saleDetails($sales->id);
    }

    /**
     * Create sale workflow
     *
     * @param int $saleId
     * @return mixed
     */
    public function createSaleWorkflow(int $saleId)
    {
        $saleDetails = $this->modelObj->find($saleId);

        if(empty($saleDetails->workflow_tracking_id)){

            $saleWorkflowSettings = $this->settingRepositoryObj->getSettingDataByKey(
                array(
                    'rental_sale_order_workflow'
                ));

            $saleWorkflow = collect(json_decode(
                $saleWorkflowSettings['rental_sale_order_workflow'][0]['value']));

            $workflowCode = $saleWorkflow['rental_sale_order'];

            $workflowMappingTable = 'Sale';

            $workflowDetail = $this->workflowRepositoryObj
                ->listWorkflowSteps($workflowCode);

            $workflowTrackingDetail = $this->workflowRepositoryObj->copyWorkflows(
                $saleId, $workflowMappingTable,
                $workflowDetail->id, $saleDetails->user_id
            );

            //Update Workflow ID
            $saleDetails->update(
                array(
                    'workflow_tracking_id' => $workflowTrackingDetail['workflow']['workflow_tracking_id'],
                    'updated_by' => Auth::id()
                )
            );
        }
    }

    /**
     * update sales
     *
     * @param array $data
     * @param int $saleId
     * @return array|mixed
     */
    public function updateSale(array $data, int $saleId)
    {
        $order = $data['sales_data'];

        //Get sale detail
        $sale = $this->find($saleId);

        //get the order status List
        $orderStatusList = $this->masterRepositoryObj
            ->getMasterDataByKey(['sale_order_status']);

        $orderStatus = array_change_key_case(
            $orderStatusList['sale_order_status']->pluck('id','title')->toArray());

        //set order status
        $orderStatusId = $sale->order_status_id;

        if($order['status'] == 'save'){

            $orderStatusId = $orderStatus[$this->saleOrderStatusConfigCodes['pre-order']];

        } else if($order['status'] == 'cancel'){

            $orderStatusId = $orderStatus[$this->saleOrderStatusConfigCodes['pending']];

        } else if($order['status'] == 'cancelPreOrder') {

            $orderStatusId = $orderStatus[$this->saleOrderStatusConfigCodes['void']];

        }

        if ($sale->is_esac_redemption && ($order['status'] == 'cancel') || $order['status'] == 'cancelPreOrder') {
            collect($sale->saleEsacVouchersClone)->each(function ($esacVoucherClone) {
                $this->esacVoucherRepositoryObj
                   ->updateStatus($esacVoucherClone['voucher_id'], 'N');
            });
        }

        //Set sale member downline Id
        $userId = $order['downline_member_id'];

        //Set sponser member id
        $sponsorId = (isset($order['sponsor_member_id']) && (!empty($order['sponsor_member_id'])) ?
            $order['sponsor_member_id'] :  null );

        $skipDownline = ($sponsorId == $userId) ? 1 : 0;

        //Update Status and Member Downline Id
        $sale->update([
            'user_id' => $userId,
            'order_status_id' => $orderStatusId,
            'stock_location_id' => $order['stock_location_id'],
            'remarks' => $order['remarks'],
            'sponsor_id' => $sponsorId,
            'skip_downline' => $skipDownline,
            'updated_by' => Auth::id()
        ]);

        //Soft delete for all pending sales record
        if($order['status'] == 'cancel'){
            $sale->delete();
        }

        //sale shipping address
        if (isset($order['selected']) and !empty($order['selected'])){

            //check if shipping sets
            if (isset($order['selected']['shipping'])){
                $shippingData = $order['selected']['shipping'];

                //get shipping address method
                $masterData = $this->masterDataRepositoryObj
                    ->find($shippingData['sale_delivery_method']);

                $countryCallId = ($shippingData['recipient_mobile_country_code_id'] > 0) ?
                    $shippingData['recipient_mobile_country_code_id'] :
                    null;

                $addressDatas = [
                    'sale_id' => $saleId,
                    'country_id' => $countryCallId,
                    'delivery_method_id' => $shippingData['sale_delivery_method'],
                    'recipient_name' => $shippingData['recipient_name'],
                    'mobile' => $shippingData['recipient_mobile_phone_number'],
                    'address' => ($masterData['title'] != Config::get('mappings.sale_delivery_method.pickup')) ?
                        json_encode($shippingData['recipient_addresses']) : json_encode('[]'),
                    'shipping_index' => $shippingData['recipient_selected_shipping_index']
                ];

                //Get Address Record
                $saleAddress = $this->saleShippingObj
                    ->where('sale_id', $saleId)->first();

                //Create or Update Address Record
                ($saleAddress) ?
                    $saleAddress->update($addressDatas) :
                    $this->saleShippingObj->create($addressDatas);

                $sale->update([
                    'delivery_method_id' => $shippingData['sale_delivery_method'],
                    'updated_by' => Auth::id()
                ]);

            }
        }

        //Insert Purchase and AMP CV
        if(!$sale->is_product_exchange && !$sale->is_esac_redemption){

            $invoice = $sale->invoices()->first();

            if($invoice){
                $this->createAmpCvAllocations($saleId);

                $this->insertPurchaseCv($saleId);
            }
        }

        return $this->saleDetails($saleId);
    }

    /**
     * clone product to saleProductClone
     *
     * @param int $saleId
     * @param int $productId
     * @param int|null $countryId
     * @return mixed
     */
    private function cloneProduct(int $saleId, int $productId, int $countryId = null)
    {
        $product = $this->productRepositoryObj->find($productId);

        $productNameObj = null;

        if ($countryId){
            $productNameObj = $product->getProductName($countryId);
        }

        return $this->saleProductCloneObj->create(
            [
                'sale_id' => $saleId,
                'product_id' => $product->id,
                'name' => ($productNameObj) ? $productNameObj->name : $product->name,
                'sku' => $product->sku,
                'uom' => $product->uom
            ]
        );
    }

    /**
     * clone esac voucher to saleEsacVoucherClone
     *
     * @param int $saleId
     * @param int $esacVoucherId
     * @return mixed
     */
    private function cloneEsacVoucher(int $saleId, int $esacVoucherId)
    {
        $esacVoucher = $this->esacVoucherObj
            ->findOrFail($esacVoucherId);

        return $this->saleEsacVouchersCloneObj->create(
            [
                'country_id' => $esacVoucher->country_id,
                'sale_id' => $saleId,
                'voucher_id' => $esacVoucher->id,
                'campaign_id' => $esacVoucher->campaign_id,
                'campaign_name' => $esacVoucher->campaign->name,
                'from_campaign_cw_schedule_id' => $esacVoucher->campaign->from_cw_schedule_id,
                'to_campaign_cw_schedule_id' => $esacVoucher->campaign->to_cw_schedule_id,
                'promotion_id' => $esacVoucher->promotion_id,
                'promotion_taxable' => $esacVoucher->esacPromotion->taxable,
                'promotion_entitled_by' => $esacVoucher->esacPromotion->entitled_by,
                'promotion_max_purchase_qty' => $esacVoucher->esacPromotion->max_purchase_qty,
                'voucher_type_id' => $esacVoucher->voucher_type_id,
                'voucher_type_name' => $esacVoucher->esacVoucherType->name,
                'voucher_sub_type_id' => $esacVoucher->voucher_sub_type_id,
                'voucher_sub_type_name'=> $esacVoucher->esacVoucherSubType->name,
                'voucher_number' => $esacVoucher->voucher_number,
                'voucher_value' => $esacVoucher->voucher_value,
                'voucher_status' => $esacVoucher->voucher_status,
                'voucher_remarks' => $esacVoucher->voucher_remarks,
                'voucher_period_id' => $esacVoucher->voucher_period_id,
                'member_user_id' => $esacVoucher->member_user_id,
                'issued_date' => $esacVoucher->issued_date,
                'expiry_date' => $esacVoucher->expiry_date,
                'max_purchase_qty' => $esacVoucher->max_purchase_qty,
                'min_purchase_amount' => $esacVoucher->min_purchase_amount
            ]
        );
    }

    /**
     * eligible Sales Product
     *
     * @param int $downLineMemberId
     * @param int $countryId
     * @param int $locationId
     * @param array $products
     * @param array $kittings
     * @param array $parameter
     * @return array
     */
    public function eligibleSalesPromo(
        int $downLineMemberId,
        int $countryId,
        int $locationId,
        array $products = array(),
        array $kittings = array(),
        array $parameter
    )
    {
        $esacRedemption = (isset($parameter['is_esac_redemption']) ? $parameter['is_esac_redemption'] : false);

        $rentalSaleOrder = (isset($parameter['is_rental_sale_order']) ? $parameter['is_rental_sale_order'] : false);

        $member = $this->memberRepositoryObj
            ->find($downLineMemberId, ['enrollmentRank']);

        $gmpPriceTax = $nmpPrice = $totalCv = $esacVoucherAmount = $totalAmpCv = $totalBaseCv = $totalWpCv = $totalUpgradeCv = 0;

        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_types','cv_config', 'promotion_free_items_member_types', 'promotion_free_items_promo_types'));

        $saleType = $settingsData['sale_types']->pluck('title','id')->toArray();

        $cvConfig = $settingsData['cv_config']->pluck('title','id')->toArray();

        $promoMemberTypes = array_change_key_case($settingsData['promotion_free_items_member_types']->pluck('id','title')->toArray());

        $promoTypes = $settingsData['promotion_free_items_promo_types']->pluck('title','id')->toArray();

        //Define promotion filter conditions
        $productDetailsFilter = array(
            'category' => array(),
            'product' => array(),
            'kitting' => array(),
        );

        $salesTypeFilter = array(
            $this->transactionTypeConfigCodes['registration'] => $productDetailsFilter,
            $this->transactionTypeConfigCodes['member-upgrade'] => $productDetailsFilter,
            $this->transactionTypeConfigCodes['ba-upgrade'] => $productDetailsFilter,
            $this->transactionTypeConfigCodes['repurchase'] => $productDetailsFilter,
            $this->transactionTypeConfigCodes['auto-ship'] => $productDetailsFilter,
            $this->transactionTypeConfigCodes['auto-maintenance'] => $productDetailsFilter,
            $this->transactionTypeConfigCodes['formation'] => $productDetailsFilter,
            $this->transactionTypeConfigCodes['rental'] => $productDetailsFilter
        );

        $promoFilter = [
            'foc' => $salesTypeFilter,
            'pwp' => $salesTypeFilter,
        ];

        $productUnitCv = $kittingUnitCv = [];

        if(isset($products)){
            foreach($products as $product){

                $productDetail = $this->productRepositoryObj->productDetails($countryId, $product['product_id']);

                $productDetail['effective_price'] = $this->productRepositoryObj->productEffectivePricing(
                    $countryId, $product['product_id'], array($locationId));

                //Get product sales type and qty
                $product['transaction_type'] = (!empty($product['transaction_type'])) ?
                    $product['transaction_type'] : 0;

                $productQuantity = (isset($product['quantity']) && $product['quantity'] > 0) ? $product['quantity'] : 1;

                //Get cv of each product
                $productSaleCv = $this->getSaleMappingCv(
                    $product['product_id'], "product", $productQuantity,
                    $product['transaction_type'], $countryId, $locationId);

                $productCv = $productSaleCv['itemCv'];

                $productAmpCv = $productSaleCv['virtualInvoiceCv'];

                $productBaseCv = $productSaleCv['itemBaseCv'];

                $productWpCv = $productSaleCv['itemWpCv'];

                $productUpgradeCv = $productSaleCv['itemUpgradeCv'];

                array_push($productUnitCv, [
                    'product_id' => $product['product_id'],
                    'unit_cv' => ($esacRedemption ? 0 : $productCv)
                ]);

                //calculate gross and net member price
                $productGmpPriceTax = $productDetail['effective_price']['gmp_price_gst'];

                $gmpPriceTax += ($productGmpPriceTax * $productQuantity);

                $productNmpPrice = $productDetail['effective_price']['nmp_price'];

                $nmpPrice += ($productNmpPrice * $productQuantity);

                //calculate total cv
                $totalCv += $productCv * $productQuantity;

                $totalAmpCv += $productAmpCv * $productQuantity;

                $totalBaseCv += $productBaseCv * $productQuantity;

                $totalWpCv += $productWpCv * $productQuantity;

                $totalUpgradeCv += $productUpgradeCv * $productQuantity;

                $productFilter = array(
                    'product_id' => $product['product_id'],
                    'qty' => $productQuantity,
                    'total_cv' => ($productCv + $productAmpCv) * $productQuantity,
                );

                $categoryFilter = array(
                    'category_id' => $productDetail['categories']['id'],
                    'qty' => $productQuantity,
                    'total_cv' => ($productCv + $productAmpCv) * $productQuantity,
                );

                if(isset($saleType[$product['transaction_type']])){

                    $productSaleType = strtolower($saleType[$product['transaction_type']]);

                    //Define promotion filter conditions
                    if(isset($productDetail['general']['cv_config']) && count($productDetail['general']['cv_config']) > 0){

                        if(count($productDetail['general']['cv_config']) == 1){

                            if(strtolower($cvConfig[$productDetail['general']['cv_config'][0]]) == strtolower("Cv not to be counted towards FOC & PWP(F)")){

                                array_push($promoFilter['pwp'][$productSaleType]['category'], $categoryFilter);

                                array_push($promoFilter['pwp'][$productSaleType]['product'], $productFilter);

                            } else if(strtolower($cvConfig[$productDetail['general']['cv_config'][0]]) == strtolower("Cv not to be counted towards PWP(N)")){

                                array_push($promoFilter['foc'][$productSaleType]['category'], $categoryFilter);

                                array_push($promoFilter['foc'][$productSaleType]['product'], $productFilter);

                            }
                        }
                    } else {
                        array_push($promoFilter['foc'][$productSaleType]['category'], $categoryFilter);

                        array_push($promoFilter['foc'][$productSaleType]['product'], $productFilter);

                        array_push($promoFilter['pwp'][$productSaleType]['category'], $categoryFilter);

                        array_push($promoFilter['pwp'][$productSaleType]['product'], $productFilter);
                    }
                }
            }
        }

        if(isset($kittings)){
            foreach($kittings as $kitting){

                $kittingDetail = $this->kittingRepositoryObj->kittingDetails($countryId, $kitting['kitting_id']);

                //Get kitting sales type and qty
                $kitting['transaction_type'] = (!empty($kitting['transaction_type'])) ?
                    $kitting['transaction_type'] : 0;

                $kittingQuantity = (isset($kitting['quantity']) && $kitting['quantity'] > 0) ? $kitting['quantity'] : 1;

                //Get cv of each kitting
                $kittingSaleCv = $this->getSaleMappingCv(
                    $kitting['kitting_id'], "kitting", $kittingQuantity,
                    $kitting['transaction_type'], $countryId, NULL);

                $kittingCv = $kittingSaleCv['itemCv'];

                $kittingAmpCv = $kittingSaleCv['virtualInvoiceCv'];

                $kittingBaseCv = $kittingSaleCv['itemBaseCv'];

                $kittingWpCv = $kittingSaleCv['itemWpCv'];

                $kittingUpgradeCv = $kittingSaleCv['itemUpgradeCv'];

                array_push($kittingUnitCv, [
                    'kitting_id' => $kitting['kitting_id'],
                    'unit_cv' => ($esacRedemption ? 0 : $kittingCv)
                ]);

                //calculate gross and net member price
                $kittingGmpPriceTax = $kittingDetail['kitting_price']['gmp_price_gst'];

                $gmpPriceTax += ($kittingGmpPriceTax * $kittingQuantity);

                $kittingNmpPrice = $kittingDetail['kitting_price']['nmp_price'];

                $nmpPrice += ($kittingNmpPrice * $kittingQuantity);

                //calculate total cv
                $totalCv += $kittingCv * $kittingQuantity;

                $totalAmpCv += $kittingAmpCv * $kittingQuantity;

                $totalBaseCv += $kittingBaseCv * $kittingQuantity;

                $totalWpCv += $kittingWpCv * $kittingQuantity;

                $totalUpgradeCv += $kittingUpgradeCv * $kittingQuantity;

                $kittingFilter = array(
                    'kitting_id' => $kitting['kitting_id'],
                    'qty' => $kittingQuantity,
                    'total_cv' => ($kittingCv + $kittingAmpCv) * $kittingQuantity,
                );

                if(isset($saleType[$kitting['transaction_type']])){
                    $kittingSaleType = strtolower($saleType[$kitting['transaction_type']]);

                    //Define promotion filter conditions
                    if(isset($kittingDetail['general']['cv_config']) && count($kittingDetail['general']['cv_config']) > 0){

                        if(count($kittingDetail['general']['cv_config']) == 1){

                            if(strtolower($cvConfig[$kittingDetail['general']['cv_config'][0]]) == strtolower("Cv not to be counted towards FOC & PWP(F)")){

                                array_push($promoFilter['pwp'][$kittingSaleType]['kitting'], $kittingFilter);

                            } else if(strtolower($cvConfig[$kittingDetail['general']['cv_config'][0]]) == strtolower("Cv not to be counted towards PWP(N)")){

                                array_push($promoFilter['foc'][$kittingSaleType]['kitting'], $kittingFilter);

                            }
                        }
                    } else {
                        array_push($promoFilter['foc'][$kittingSaleType]['kitting'], $kittingFilter);

                        array_push($promoFilter['pwp'][$kittingSaleType]['kitting'], $kittingFilter);
                    }
                }
            }
        }

        //Map member type
        $enrollmentCode = $member->enrollmentRank->rank_code;

        switch (strtolower($enrollmentCode)) {
            case "diamond":
            case "platinum":
            case "gold":
            case "silver":
                $memberTypeId = $promoMemberTypes["brand ambassador"];
                break;

            case "premier":
                $memberTypeId = $promoMemberTypes["premium member"];
                break;

            case "member":
                $memberTypeId = $promoMemberTypes["member"];
                break;

            default:
                $memberTypeId = -1;
        }

        if ($esacRedemption) {
            $esacVoucherAmount = $this
                ->esacVoucherObj
                ->whereIn('id', $parameter['esac_vouchers'])
                ->sum('voucher_value');
        } else {
            if(!$rentalSaleOrder){
                if(count($products) > 0 || count($kittings) > 0){
                    $promoDatas = $this->promoFreeItemsRepositoryObj->retrievePromotionDetails(
                        $saleType,
                        $promoTypes,
                        $countryId,
                        $memberTypeId,
                        date('Y-m-d'),
                        $promoFilter
                    );
                } else {
                    $promoDatas = collect([
                        'qualifyCampaignCv' => 0,
                        'promoData' => []
                    ]);
                }
            }
        }

        $parameter['promotion'] = (($esacRedemption || $rentalSaleOrder) ? [] : $promoDatas['promoData']);

        $parameter['cvs']['total_qualified_cv'] = (($esacRedemption || $rentalSaleOrder) ? 0 : $promoDatas['qualifyCampaignCv']);

        $parameter['cvs']['total_cv'] = ($esacRedemption ? 0 : $totalCv);

        //Reset Wp, Base and Amp CV
        $parameter['cvs']['total_amp_cv'] = ($esacRedemption ? 0 : $totalAmpCv);

        $parameter['cvs']['total_base_cv'] = ($esacRedemption ? 0 : $totalBaseCv);

        $parameter['cvs']['total_wp_cv'] = ($esacRedemption ? 0 : $totalWpCv);

        $parameter['cvs']['total_upgrade_cv'] = ($esacRedemption ? 0 : $totalUpgradeCv);

        //Reset Product and kitting unit CV
        $parameter['cvs']['products'] = $productUnitCv;

        $parameter['cvs']['kittings'] = $kittingUnitCv;

        $parameter['order_fees']['product_fee'] = number_format($nmpPrice, 2, '.', '');

        $parameter['order_fees']['product_tax'] = number_format(($gmpPriceTax - $nmpPrice), 2, '.', '');

        $parameter['order_fees']['promo_fee_with_tax'] = 0;

        $parameter['order_fees']['total_esac_voucher_value'] = $esacVoucherAmount;

        $this->calculateTotalFees($countryId, $parameter['order_fees']);

        $promoIds = collect($parameter['promotion'])->pluck('promo_id')->toArray();

        if(isset($parameter['selected']['promotions'])){
            $lastSelectedPromos = $parameter['selected']['promotions'];

            $newSelectedPromos = [];

            collect($lastSelectedPromos)->each(function($selectedPromo)
                use (&$newSelectedPromos, $promoIds) {
                    if(in_array($selectedPromo['promo_id'], $promoIds)){
                        array_push($newSelectedPromos, $selectedPromo);
                    }
                });

            $parameter['selected']['promotions'] = $newSelectedPromos;

            $parameter = $this->calculatePromoPrice($newSelectedPromos, $parameter);
        }

        return $parameter;
    }

    /**
     * Calculate Promotion Price by given selected promotion
     *
     * @param array $selectedPromos
     * @param array $parameter
     * @return array
     */
    public function calculatePromoPrice(array $selectedPromos = array(), array $parameter)
    {
        if(isset($selectedPromos)){

            //Map promo type from master data table
            $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
                array('promotion_free_items_promo_types'));

            $promoTypes = array_change_key_case($settingsData['promotion_free_items_promo_types']->pluck('id','title')->toArray());

            $selectedPromo = collect($selectedPromos)
                ->map(function ($selectedPromoData) use ($promoTypes){
                    $promoDetail = $this->promoFreeItemsRepositoryObj
                        ->find($selectedPromoData['promo_id']);

                    $selectedPromoData['promo_type_id'] = $promoDetail->promo_type_id;

                    $selectedQuantity = (isset($selectedPromoData['selected_quantity'])) ?
                        $selectedPromoData['selected_quantity'] : 0;

                    if($promoDetail->promo_type_id == $promoTypes['pwp(f)']){
                        $selectedPromoData['pwp_value'] = floatval($promoDetail->pwp_value);
                    } else {
                        $selectedPromoData['pwp_value'] = floatval($selectedQuantity) * floatval($promoDetail->pwp_value);
                    }

                    return $selectedPromoData;
                });

            $selectedPwpFixAmount = $selectedPromo
                ->where('promo_type_id', $promoTypes['pwp(f)'])
                ->sum('pwp_value');

            $selectedPwpNumberAmount = $selectedPromo
                ->where('promo_type_id', $promoTypes['pwp(n)'])
                ->sum('pwp_value');

            $parameter['order_fees']['promo_fee_with_tax'] =
                floatval($selectedPwpFixAmount) + floatval($selectedPwpNumberAmount);

            $this->calculateTotalFees($parameter['country_id'], $parameter['order_fees']);
        }

        return $parameter;
    }

    /**
     * Calculate admin fees
     *
     * @param int $downLineMemberId
     * @param array $parameter
     * @return array
     */
    public function calculateSalesAdminFees(int $downLineMemberId, array $parameter)
    {
        $member = $this->memberRepositoryObj
            ->find($downLineMemberId);

        $this->calculateTotalFees($parameter['country_id'], $parameter['order_fees']);

        return $parameter;
    }

    /**
     * Calculate delivery fees
     *
     * @param int $downLineMemberId
     * @param array $parameter
     * @return array
     */
    public function calculateSalesDeliveryFees(int $downLineMemberId, array $parameter)
    {
        $member = $this->memberRepositoryObj
            ->find($downLineMemberId);

        $this->calculateTotalFees($parameter['country_id'], $parameter['order_fees']);

        return $parameter;
    }

    /**
     * Calculate other fees
     *
     * @param array $parameter
     * @return array
     */
    public function calculateOtherFees(array $parameter)
    {
        $this->calculateTotalFees($parameter['country_id'], $parameter['order_fees']);

        return $parameter;
    }

    /**
     *  Calculate total fees
     *
     * @param int $countryId
     * @param array $parameter
     */
    private function calculateTotalFees(int $countryId, array &$parameter)
    {
        //Get Tax Detail
        $countryDetail = $this->countryObj->where('id', $countryId)->first();

        $countryTaxDetail = $countryDetail->taxes()->first();

        $countryTaxRate = (!empty($countryTaxDetail)) ? $countryTaxDetail->rate : 0;

        //Calculate Fees Amount
        $parameter['product_fee'] = number_format(
            ((isset($parameter['product_fee'])) ? $parameter['product_fee'] : 0), 2, '.', '');

        $parameter['admin_fee'] = number_format(
            ((isset($parameter['admin_fee'])) ? $parameter['admin_fee'] : 0), 2, '.', '');

        $parameter['delivery_fee'] = number_format(
            ((isset($parameter['delivery_fee'])) ? $parameter['delivery_fee'] : 0), 2, '.', '');

        $parameter['other_fee'] = number_format(
            ((isset($parameter['other_fee'])) ? $parameter['other_fee'] : 0), 2, '.', '');

        //Calculate Tax Amount
        $parameter['product_tax'] = number_format(
            ((isset($parameter['product_tax'])) ? $parameter['product_tax'] : 0), 2, '.', '');

        $parameter['admin_fee_tax'] = number_format(
            (floatval($countryTaxRate) * floatval($parameter['admin_fee']) / 100), 2, '.', '');

        $parameter['delivery_fee_tax'] = number_format(
            (floatval($countryTaxRate) * floatval($parameter['delivery_fee']) / 100), 2, '.', '');

        $parameter['other_fee_tax'] = number_format(
            (floatval($countryTaxRate) * floatval($parameter['other_fee']) / 100), 2, '.', '');

        //Calculate Promo Fees Amount
        $parameter['promo_fee_with_tax'] = number_format(
            ((isset($parameter['promo_fee_with_tax'])) ? $parameter['promo_fee_with_tax'] : 0), 2, '.', '');

        $parameter['promo_fee'] = number_format(
            ((floatval($parameter['promo_fee_with_tax']) /
                    (floatval($countryTaxRate) + 100)) * 100), 2, '.', '');

        $parameter['promo_fee_tax'] = number_format(
            (floatval($parameter['promo_fee_with_tax']) - floatval($parameter['promo_fee'])), 2, '.', '');

        //Calculate Total Tax, Total NMP, GMP and Rounding Adjustment
        if (empty($parameter['total_esac_voucher_value'])) {
            $totalEsacVoucherAmount = 0.00;
        }
        else {
            $totalEsacVoucherAmount = floatval($parameter['total_esac_voucher_value']);
        }

        $taxAmount = floatval($parameter['product_tax']) + 
            floatval($parameter['admin_fee_tax']) +
            floatval($parameter['delivery_fee_tax']) + 
            floatval($parameter['other_fee_tax']) +
            floatval($parameter['promo_fee_tax']);
        
        $totalNmp = floatval($parameter['product_fee']) + 
            floatval($parameter['promo_fee']) - 
            $totalEsacVoucherAmount;

        $totalAmount = floatval($parameter['product_fee']) + 
            floatval($parameter['admin_fee']) +
            floatval($parameter['delivery_fee']) + 
            floatval($parameter['other_fee']) +
            floatval($parameter['promo_fee']) + 
            $taxAmount - $totalEsacVoucherAmount;
        
        $totalGmp = floatval($this->roundingAdjustment($countryId, floatval($totalAmount)));

        if($totalGmp <= 0) {
            $taxAmount = 0;
            $totalNmp = 0;
            $totalAmount = 0;
            $totalGmp = 0;
        }

        $roundingAjustment = $totalGmp - $totalAmount;

        $parameter['tax_amount'] = number_format($taxAmount, 2, '.', '');

        $parameter['total_nmp'] = number_format($totalNmp, 2, '.', '');

        $parameter['total_gmp'] = number_format($totalGmp, 2, '.', '');

        $parameter['rounding_adjustment'] = number_format($roundingAjustment, 2, '.', '');
    }

    /**
     * Calculate amount rounding
     *
     * @param int $countryId
     * @param float $amount
     * @return string $payAmount
     */
    public function roundingAdjustment(int $countryId, float $amount)
    {
        //Retrieve rounding setup
        $roundingSetting = $this->settingRepositoryObj->getRoundingAdjustment($countryId);

        $roundType = NULL;

        if(count($roundingSetting) > 0){
            $roundType = strtolower($roundingSetting[0]->master_data->title);
        }

        switch (strtolower($roundType)) {
            case "round to nearest 5-cent":
                $payAmount = bcmul(round($amount / 0.05), 0.05, 2);
                break;

            case "round-up to dollar":
                $payAmount = ceil($amount);
                break;

            case "round-up to nearest rp":
                $payAmount = round($amount);
                break;

            default:
                $payAmount = $amount;
        }

        return number_format($payAmount, 2, '.', '');
    }

    /**
     * get sales Cv by given saleItemId(productId or kittingId), saleItemType(product or kitting), saleQty, transaction type, countryId and locationId
     *
     * @param int $saleItemId
     * @param string $saleItemType
     * @param int $saleQty
     * @param int $transactionTypeId
     * @param int $countryId
     * @param int $locationId
     * @return array
     */
    private function getSaleMappingCv(
        int $saleItemId,
        string $saleItemType,
        int $saleQty,
        int $transactionTypeId,
        int $countryId,
        int $locationId = NULL
    )
    {
        switch (strtolower($saleItemType)) {
            case "kitting":
                $price = (new KittingPrice)->forceFill(
                    optional(
                        $this->kittingRepositoryObj
                            ->kittingDetails($countryId, $saleItemId)['kitting_price']
                    )->toArray()
                );
                break;

            case "product":
                $price = (new ProductPrice)->forceFill(
                    optional(
                        $this->productRepositoryObj
                            ->productEffectivePricing(
                                $countryId,
                                $saleItemId,
                                array($locationId)
                            )
                    )->toArray()
                );
                break;

            default:
                $price = new ProductPrice;
        }

        $itemCv = $this->commissionService->calculateCv(
            $price,
            (new MasterData)->forceFill(['id' => $transactionTypeId])
        )->toArray();

        return collect([
            "virtualInvoiceCv" => $itemCv['amp'],
            "itemCv" => $itemCv['eligible'],
            "totalItemCv" => $itemCv['eligible'] * $saleQty,
            "itemBaseCv" => $itemCv['base'],
            "itemWpCv" => $itemCv['welcome_pack'],
            "itemUpgradeCv" => $itemCv['upgrade']
        ]);
    }

    /**
     * Calculates CV for the sales type
     *
     * @param array $price price array containing cvs
     *      $price = [
     *          'base_cv' => (float) Base Cv
     *          'wp_cv' => (float) Welcome Pack Cv
     *          'cv1' => (float) AMP cv
     *      ]
     * @param array $saleTypes
     * @param int $transactionTypeId master data id of the products general setting
     * @deprecated now calculation is provided by service \App\Services\EShop\ShoppingCartService
     * @see \App\Services\EShop\ShoppingCartService
     * @return float
     */
    public function calculateCv(array $price, array $saleTypes, int $transactionTypeId) 
    {
        // Map CVs keys to acronyms because settings uses acronym
        $cvAcronym = collect($price)
            ->filter(function ($value, $key) {
                return isset($this->cvAcronymCodes[$key]);
            })
            ->keyBy(function ($value, $key) {
                return isset($this->cvAcronymCodes[$key]) ? $this->cvAcronymCodes[$key] : $key;
            });

        // return cv calculation if transection type is valid
        return isset($saleTypes[$transactionTypeId])
            ? collect($this->saleTypeCvSettings)
                // filter out the sales type settings to use for the $transactionTypeId
                // using the config.mappings.sales_types keys and config.setting.sale-type-cvs setting
                ->filter(function ($item, $salesTypeKey) use ($saleTypes, $transactionTypeId) {
                    return $this->transactionTypeConfigCodes[$salesTypeKey] === strtolower($saleTypes[$transactionTypeId]);
                })
                ->values()
                // Sum up only those cv which is specified in the settings to sum up
                ->pipe(function ($cvsForSalesType) use ($cvAcronym) {
                    $cvsForSalesTypeKeys = $cvsForSalesType->values()->first();

                    return $cvAcronym->filter(function ($item, $key) use ($cvsForSalesTypeKeys) {
                        return in_array($key, $cvsForSalesTypeKeys);
                    })->values()->sum();
                })
            : 0;
    }

    /**
     * get sale cancellation invoice details for a given userId, invoiceId
     *
     * @param string $saleCancellationMethod
     * @param int $userId
     * @param int $invoiceId
     * @param int $countryId
     * @return mixed
     */
    public function getSalesCancellationInvoiceDetails(string $saleCancellationMethod, int $userId, int $invoiceId, int $countryId)
    {
        //get sale cancellation type and status
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(array('sale_cancellation_type'));

        $saleCancellationTitlesTypes = array_change_key_case($masterSettingsDatas['sale_cancellation_type']->pluck('id','title')->toArray());

        $saleCancellationIdsTypes = $masterSettingsDatas['sale_cancellation_type']->pluck('title','id');

        //get member details
        $member = $this->memberRepositoryObj
            ->find($userId, ['status','user','country']);

        if($saleCancellationMethod == 'normal'){
            //get invoice details
            $invoice = $this->invoiceRepositoryObj->invoiceDetails($invoiceId);

            //Get Avaliable Product Quantity
            $products = collect($invoice['sale']['products'])
                ->map(function($productData){

                    $productData['available_quantity_snapshot'] = $productData['available_quantity'];

                    return $productData;
                });

            $kitting = collect($invoice['sale']['kitting'])
                ->map(function($kittingData){

                    $kittingProducts = collect($kittingData['kitting_products'])
                        ->map(function($kittingProductData){

                            $kittingProductData['available_quantity_snapshot'] = $kittingProductData['available_quantity'];

                            return $kittingProductData;
                        });

                    $kittingData['kitting_products'] = $kittingProducts;

                    $kittingData['available_quantity_snapshot'] = $kittingData['available_quantity'];

                    return $kittingData;
                });

            $promotions = collect($invoice['sale']['promotions'])
                ->map(function($promotionData){

                    $promotionData['available_quantity_snapshot'] = $promotionData['available_quantity'];

                    return $promotionData;
                });

            $esacVouchers = collect($invoice['sale']['esac_vouchers'])
                ->map(function($esacVoucherData){

                    $esacVoucherData['available_quantity_snapshot'] = $esacVoucherData['available_quantity'];

                    return $esacVoucherData;
                });

            //TODO ALSON :: Need map back users login country
            $countryId = $invoice['sale']['country_id'];

        } else {

            $esacVouchers = [];

            $invoice = [
                'sale' => [
                    'products' => [],
                    'kitting' => [],
                    'promotions' => [],
                    'esac_vouchers' => [],
                ]
            ];
        }

        $saleCancellationSettings = $this->settingRepositoryObj
            ->getSalesCancellationPolicy($countryId);

        $coolingOffDay = $saleCancellationSettings['cooling_off_day'];

        $buyBackDay =  $saleCancellationSettings['buy_back_day'];

        $buyBackPercentage =  $saleCancellationSettings['buy_back_percentage'];

        if($saleCancellationMethod == 'normal'){

            //check same day cancellation
            $today = Carbon::now()->format('Y-m-d');

            $invoiceDate = $invoice['sale']['invoice']['invoice_date'];

            $workflowCode = NULL;

            $saleCancellationTypes = array();

            if ($invoiceDate == $today) {

                $saleCancellationTypes = array(
                    'id' => $saleCancellationTitlesTypes['same day'],
                    'title' => $saleCancellationIdsTypes[$saleCancellationTitlesTypes['same day']],
                );

                $salePayments = $invoice['sale']['sale_payments'];

                $refundPaymentCount = collect($salePayments)
                    ->where('is_third_party_refund', 1)
                    ->where('status', 1)
                    ->count();

                $workflowCode = ($refundPaymentCount > 0) ?
                    $saleCancellationSettings['cancellation_workflow']['same_day_cancellation_with_refund'] :
                    $saleCancellationSettings['cancellation_workflow']['same_day_cancellation'];

            } else {

                $buyback = true;

                $joinDate = $member->join_date;

                if (!empty($joinDate)) {
                    $coolingOffDate = date('Y-m-d', strtotime($joinDate . ' + ' . $coolingOffDay . ' days'));

                    if ($coolingOffDate >= $today) {
                        $workflowCode = $saleCancellationSettings['cancellation_workflow']['cooling_off_cancellation'];

                        $saleCancellationTypes = array(
                            'id' => $saleCancellationTitlesTypes['cooling off'],
                            'title' => $saleCancellationIdsTypes[$saleCancellationTitlesTypes['cooling off']],
                        );

                        $buyback = false;
                    }
                }

                if ($buyback) {
                    $buyBackDate = date('Y-m-d', strtotime($invoiceDate . ' + ' . $buyBackDay . ' days'));

                    if ($buyBackDate >= $today) {
                        $workflowCode = $saleCancellationSettings['cancellation_workflow']['buy_back_cancellation'];

                        $saleCancellationTypes = array(
                            'id' => $saleCancellationTitlesTypes['buy back'],
                            'title' => $saleCancellationIdsTypes[$saleCancellationTitlesTypes['buy back']],
                        );
                    }
                }
            }

            $invoice['sale']['products'] = $products;

            $invoice['sale']['kitting'] = $kitting;

            $invoice['sale']['promotions'] = $promotions;

            $invoice['sale']['esac_vouchers'] = $esacVouchers;

            unset($invoice['sale']['sale_cancellation']);

        } else {

            $workflowCode = $saleCancellationSettings['cancellation_workflow']['buy_back_cancellation'];

            $saleCancellationTypes = array(
                'id' => $saleCancellationTitlesTypes['buy back'],
                'title' => $saleCancellationIdsTypes[$saleCancellationTitlesTypes['buy back']],
            );
        }

        $workflow = (!empty($workflowCode)) ?
            $this->workflowRepositoryObj->listWorkflowSteps($workflowCode) : NULL;

        return collect(array_merge(
            [
                'buy_back_percentage' => $buyBackPercentage,
                'sale_cancellation_type' => $saleCancellationTypes,
                'sale_cancellation_mode' => (empty($esacVouchers)) ? 'both' : 'full',
                'member' => $member,
                'workflow' => $workflow
            ], $invoice
        ));
    }

    /**
     * To verify sale user id that order on be behalf by sponsord before insert sale purchase cv and amp cv record
     *
     * @param int $saleId
     * @return array
     */
    private function saleCvAllocationVerify(int $saleId)
    {
        //Get AMP Allocation Type Master Data Value
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey([
            'amp_cv_allocation_types'
        ]);

        $ampCvAllocationType = array_change_key_case($settingsData['amp_cv_allocation_types']
            ->pluck('id','title')->toArray());

        //Get Sale Type ID
        $saleTypeCode = $this->ampCvAllocationTypesConfigCodes['sales'];

        $saleTypeId = $ampCvAllocationType[$saleTypeCode];

        //Get AMP Type ID
        $ampTypeCode = $this->ampCvAllocationTypesConfigCodes['amp'];

        $ampTypeId = $ampCvAllocationType[$ampTypeCode];

        //Get Sale Details
        $sales = $this->find($saleId);

        //Verify Purchase Cv Record
        $salePurchaseCv = $this->ampCvAllocationObj
            ->where('type_id', $saleTypeId)
            ->where('sale_id', $saleId)
            ->where('user_id', $sales->user_id)
            ->active()
            ->first();

        //Verify AMP Cv Allocation Record
        $saleAmpCvAllocation = $this->ampCvAllocationObj
            ->where('type_id', $ampTypeId)
            ->where('sale_id', $saleId)
            ->where('user_id', $sales->user_id)
            ->active()
            ->first();

        return [
            'memberVerify' => ($sales->user_id == $sales->sponsor_id) ? false : true,
            'purchaseCvVerify' => ($salePurchaseCv) ? false : true,
            'ampCvAllocationVerify' => ($saleAmpCvAllocation) ? false : true
        ];
    }

    /**
     * Insert Purchase Cv by given saleId
     *
     * @param int $saleId
     * @return boolean
     */
    public function insertPurchaseCv(int $saleId)
    {
        //To verify sale user id that order on be behalf by sponsor
        $saleVerify = $this->saleCvAllocationVerify($saleId);

        if(!$saleVerify['memberVerify'] || !$saleVerify['purchaseCvVerify']){
            return false;
        }

        //Get Master Data Value
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey([
            'sale_types', 'amp_cv_allocation_types'
        ]);

        //Get Sale Type
        $saleType = array_change_key_case($settingsData['sale_types']
            ->pluck('id','title')->toArray());

        $rentalCode = $this->transactionTypeConfigCodes['rental'];

        $rentalId = $saleType[$rentalCode];

        //Get AMP Allocation Type
        $ampCvAllocationType = array_change_key_case($settingsData['amp_cv_allocation_types']
            ->pluck('id','title')->toArray());

        $saleTypeCode = $this->ampCvAllocationTypesConfigCodes['sales'];

        $saleTypeId = $ampCvAllocationType[$saleTypeCode];

        $rentalAmpAllocationTypeCode = $this->ampCvAllocationTypesConfigCodes['rental'];

        $rentalAmpAllocationTypeId = $ampCvAllocationType[$rentalAmpAllocationTypeCode];

        //get sale details
        $saleDetail = $this->saleDetails($saleId);

        $saleCountryId = $saleDetail['sales_data']['country_id'];

        $saleUserId = $saleDetail['sales_data']['downline_member_id'];

        $saleCwId = $saleDetail['sales_data']['invoice']['cw_id'];

        $saleCwName = $saleDetail['sales_data']['invoice']['cw']['cw_name'];

        $saleProducts = $saleDetail['sales_data']['products'];

        $saleKittings = $saleDetail['sales_data']['kittings'];

        $totalSalesCv = $saleDetail['sales_data']['cvs']['total_cv'];

        //Insert Into Amp Cv Allocation Table
        $this->insertAmpCvAllocationRecord(
            $saleTypeId,
            $saleId,
            $saleUserId,
            $saleCwId,
            $totalSalesCv
        );

        $this->updateEnrollmentRank($saleUserId, "upgrade");

        //Get rental sale type product
        $rentalProduct = collect($saleProducts)
            ->where('transaction_type_id', $rentalId)
            ->first();

        if($rentalProduct){

            $masterProductId = $rentalProduct['master_product_id'];

            $productRentalCvRecord = $this->productRentalPlanObj
                ->with('productRentalCvAllocation')
                ->where('product_id', $masterProductId)
                ->where('country_id', $saleCountryId)
                ->first();

            if($productRentalCvRecord){

                //Insert record into amp allocation table
                $rentalCvAllocationPlans = $productRentalCvRecord->productRentalCvAllocation;

                $rentalNextInsertCwId = $saleCwId;

                $rentalNextInsertCwName = $saleCwName;

                collect($rentalCvAllocationPlans)
                    ->sortBy('cw_number')
                    ->each(function($rentalCvAllocationPlan)
                    use (
                        $rentalAmpAllocationTypeId, $saleId,
                        $saleUserId, &$rentalNextInsertCwId, &$rentalNextInsertCwName
                    ){
                        $this->insertAmpCvAllocationRecord(
                            $rentalAmpAllocationTypeId,
                            $saleId,
                            $saleUserId,
                            $rentalNextInsertCwId,
                            $rentalCvAllocationPlan['allocate_cv']
                        );

                        $nextCwList = $this->cwSchedulesRepositoryObj
                            ->getNextCwByCwName($rentalNextInsertCwName, 1);

                        $rentalNextInsertCwId = $nextCwList['data'][0]->id;

                        $rentalNextInsertCwName = $nextCwList['data'][0]->cw_name;
                    });

                //Upgrade alloment rank to gold
                $this->rentalTypeProductEnrollmentGoldRankUpgrade($saleUserId, $saleCwId);
            }
        }

        $this->memberActiveVerify($saleUserId);
    }

    /**
     * Remove Sale Cancellation Cv by given saleCancellationId
     *
     * @param int $saleCancellationId
     */
    public function removeSaleCancellationCv(int $saleCancellationId)
    {
        //Get Master Data Value
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey([
            'amp_cv_allocation_types'
        ]);

        //Get AMP Allocation Type
        $ampCvAllocationType = array_change_key_case($settingsData['amp_cv_allocation_types']
            ->pluck('id','title')->toArray());

        $saleTypeCode = $this->ampCvAllocationTypesConfigCodes['sales'];

        $saleTypeId = $ampCvAllocationType[$saleTypeCode];

        $rentalAmpAllocationTypeCode = $this->ampCvAllocationTypesConfigCodes['rental'];

        $rentalAmpAllocationTypeId = $ampCvAllocationType[$rentalAmpAllocationTypeCode];

        //get sale cancellation details
        $saleCancellationDetail = $this->saleCancellationDetail($saleCancellationId);

        $saleId = $saleCancellationDetail['sale']['id'];

        $saleCancellationUserId = $saleCancellationDetail['sale']['user_id'];

        //get sales purchase cv record -- To verify does sale cancel before this
        $salePuchaseCvCondition = $this->ampCvAllocationObj
            ->active()
            ->where('user_id', $saleCancellationUserId)
            ->where('type_id', $saleTypeId)
            ->where('sale_id', $saleId);

        $salePurchaseCvCount = $salePuchaseCvCondition->count();

        if($salePurchaseCvCount == 1){
            //get sale purchase detail
            $salePurchaseCvRecord = $salePuchaseCvCondition->first();

            //Insert Into Amp Cv Allocation Table
            $this->insertAmpCvAllocationRecord(
                $saleTypeId,
                $saleId,
                $saleCancellationUserId,
                $salePurchaseCvRecord->cw_id,
                intval($salePurchaseCvRecord->cv) * -1
            );
        }

        //To roll back enrollment rank
        //Get Current Cw
        $currentCwSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('current_cw_id'));

        $currentCwId = $currentCwSettings['current_cw_id'][0]->value;

        $saleCancellationCwId = $saleCancellationDetail['sale_cancellation']['cw_id'];

        //Rollback enrollment rank
        if($currentCwId == $saleCancellationCwId){

            //Get last enrollment upgrade log
            $lastEnrollmentUpgradeLog = $this->memberEnrollmentRankUpgradeLogObj
                ->with(['previousCwEnrollmentRank', 'fromEnrollmentRank', 'toEnrollmentRank'])
                ->where('user_id', $saleCancellationUserId)
                ->where('cw_id', $currentCwId)
                ->first();

            if($lastEnrollmentUpgradeLog){
                $this->updateEnrollmentRank($saleCancellationUserId, "rollback");
            }
        }

        //Update rental cv allcation to inactive
        $this->ampCvAllocationObj
            ->active()
            ->where('type_id', $rentalAmpAllocationTypeId)
            ->where('user_id', $saleCancellationUserId)
            ->where('sale_id', $saleId)
            ->update(['active' => 0]);

        $this->rentalTypeProductEnrollmentGoldRankUpgrade($saleCancellationUserId);

        $this->memberActiveVerify($saleCancellationUserId);
    }

    /**
     * Get the current CWs Upgrade CV's for user
     *
     * @param int $userId
     * @return array
     * [
     *   'ampCvToUpgradeEachBaLevel' = <amount>,
     *   'upgradeAmpCv' = <amount>,
     *   'baUpgradeCv' = <amount>,
     *   'memberUpgradeCv' = <amount>,
     *   'currentCwId' = <id>,
     *   'currentCwLog' = <{@see \App\Models\Members\MemberEnrollmentRankUpgradeLog}>
     * ]
     */
    public function currentCwUpgradeCvForUser(int $userId)
    {

        //Get mapping id
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            [
                'sale_types', 'sale_order_status'
            ]
        );

        $saleOrderStatus = array_change_key_case(
            $settingsData['sale_order_status']->pluck('id','title')->toArray()
        );

        $saleType = array_change_key_case(
            $settingsData['sale_types']->pluck('id','title')->toArray()
        );

        $saleCompleteStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];

        $autoMaintenanceId = $saleType[$this->transactionTypeConfigCodes['auto-maintenance']];

        $memberUpgradeId = $saleType[$this->transactionTypeConfigCodes['member-upgrade']];

        $baUpgradeId = $saleType[$this->transactionTypeConfigCodes['ba-upgrade']];

        //Get Current CW ID and amp cv to upgrade each rank of ba enrollment
        $systemSettings = $this->settingRepositoryObj->getSettingDataByKey(
            array('current_cw_id', 'amp_cv_to_upgrade_each_rank_of_ba_enrollment')
        );

        $currentCwId = $systemSettings['current_cw_id'][0]->value;

        $ampCvToUpgradeEachBaLevel = $systemSettings['amp_cv_to_upgrade_each_rank_of_ba_enrollment'][0]->value;

        //Get all completed sales record within same cw
        $completedSaleIdLists = $this->modelObj
            ->join(
                'invoices',
                function ($join) {
                    $join->on('invoices.sale_id', '=', 'sales.id');
                }
            )
            ->where("invoices.cw_id", $currentCwId)
            ->where("sales.user_id", $userId)
            ->where("sales.order_status_id", $saleCompleteStatusId)
            ->pluck("sales.id");

        //Calculate member upgrade cv
        $memberUpgradeProducts = $this->saleProductsObj
            ->whereIn('transaction_type_id', [$memberUpgradeId, $autoMaintenanceId])
            ->whereIn('sale_id', $completedSaleIdLists)
            ->get();

        $memberUpgradeKittings = $this->saleKittingCloneObj
            ->whereIn('transaction_type_id', [$memberUpgradeId, $autoMaintenanceId])
            ->whereIn('sale_id', $completedSaleIdLists)
            ->get();

        $memberUpgradeCv = collect($memberUpgradeProducts)
            ->where('transaction_type_id', $memberUpgradeId)
            ->sum(function ($memberUpgradeProduct) {
                return $memberUpgradeProduct['cv4'] * $memberUpgradeProduct['quantity'];
            });

        $memberUpgradeCv += collect($memberUpgradeKittings)
            ->where('transaction_type_id', $memberUpgradeId)
            ->sum(function ($memberUpgradeKitting) {
                return $memberUpgradeKitting['cv4'] * $memberUpgradeKitting['quantity'];
            });

        //Calculate ba upgrade cv
        $baUpgradeProducts = $this->saleProductsObj
            ->whereIn('transaction_type_id', [$baUpgradeId, $autoMaintenanceId])
            ->whereIn('sale_id', $completedSaleIdLists)
            ->get();

        $baUpgradeKittings = $this->saleKittingCloneObj
            ->whereIn('transaction_type_id', [$baUpgradeId, $autoMaintenanceId])
            ->whereIn('sale_id', $completedSaleIdLists)
            ->get();

        $baUpgradeCv = collect($baUpgradeProducts)
            ->where('transaction_type_id', $baUpgradeId)
            ->sum(function ($baUpgradeProduct) {
                return $baUpgradeProduct['cv4'] * $baUpgradeProduct['quantity'];
            });

        $baUpgradeCv += collect($baUpgradeKittings)
            ->where('transaction_type_id', $baUpgradeId)
            ->sum(function ($baUpgradeKitting) {
                return $baUpgradeKitting['cv4'] * $baUpgradeKitting['quantity'];
            });

        $upgradeAmpCv = collect($baUpgradeProducts)
            ->where('transaction_type_id', $autoMaintenanceId)
            ->sum(function ($baUpgradeProduct) {
                return $baUpgradeProduct['virtual_invoice_cv'] * $baUpgradeProduct['quantity'];
            });

        $upgradeAmpCv += collect($baUpgradeKittings)
            ->where('transaction_type_id', $autoMaintenanceId)
            ->sum(function ($baUpgradeKitting) {
                return $baUpgradeKitting['virtual_invoice_cv'] * $baUpgradeKitting['quantity'];
            });

        //Get previous cw enrollment rank id
        $enrollmentUpgradeLog = $this->memberEnrollmentRankUpgradeLogObj
            ->where('user_id', $userId)
            ->where('cw_id', $currentCwId)
            ->orderBy('created_by', 'asc')
            ->with('previousCwEnrollmentRank')
            ->first();

        return [
            'ampCvToUpgradeEachBaLevel' => $ampCvToUpgradeEachBaLevel,
            'upgradeAmpCv' => $upgradeAmpCv,
            'baUpgradeCv' => $baUpgradeCv,
            'memberUpgradeCv' => $memberUpgradeCv,
            'currentCwId' => $currentCwId,
            'currentCwLog' => $enrollmentUpgradeLog
        ];
    }

    /**
     * Upgrade Enrollment Rank
     *
     * @param int $userId
     * @param string $type
     */
    private function updateEnrollmentRank(int $userId, string $type)
    {
        $currentCwUpgradeCv = $this->currentCwUpgradeCvForUser($userId);

        $ampCvToUpgradeEachBaLevel = $currentCwUpgradeCv['ampCvToUpgradeEachBaLevel'];

        $upgradeAmpCv = $currentCwUpgradeCv['upgradeAmpCv'];

        $baUpgradeCv = $currentCwUpgradeCv['baUpgradeCv'];

        $memberUpgradeCv = $currentCwUpgradeCv['memberUpgradeCv'];

        $currentCwId = $currentCwUpgradeCv['currentCwId'];

        $enrollmentUpgradeLog = $currentCwUpgradeCv['currentCwLog'];

        //Get Member Enrollment Details
        $memberDetail = $this->memberObj
            ->where('user_id', $userId)
            ->with('enrollmentRank')
            ->first();

        if(empty($memberDetail->enrollment_rank_id)){

            $newLevelEnrollementRank = $this->enrollmentRankObj
                ->where('entitlement_lvl', 0)
                ->first();

            $memberNewEnrollmentData = [
                'member_data' => [
                    'details' => [
                        'enrollment_rank_id' => $newLevelEnrollementRank->id
                    ]
                ]
            ];

            $this->memberRepositoryObj->update($memberNewEnrollmentData, $userId);

            $memberDetail = $this->memberObj
                ->where('user_id', $userId)
                ->with('enrollmentRank')
                ->first();
        }

        //Rollback enrollment ranking to previous cw ranking during sale cancellation
        if($type == "rollback"){

            //Rollback to previous enrollment rank Id
            $memberUpdateData = [
                'member_data' => [
                    'details' => [
                        'enrollment_rank_id' => $enrollmentUpgradeLog->previousCwEnrollmentRank['id']
                    ]
                ]
            ];

            $this->memberRepositoryObj->update($memberUpdateData, $userId);

            //Insert Upgrade Log
            $enrollmentLogData = [
                'user_id' => $userId,
                'cw_id' => $currentCwId,
                'previous_cw_enrollment_rank_id' => $enrollmentUpgradeLog->previousCwEnrollmentRank['id'],
                'from_enrollment_rank_id' => $memberDetail->enrollment_rank_id,
                'to_enrollment_rank_id' => $enrollmentUpgradeLog->previousCwEnrollmentRank['id'],
            ];

            $this->memberEnrollmentRankUpgradeLogObj->create($enrollmentLogData);

            //Get New Member Enrollment Details
            $memberDetail = $this->memberObj
                ->where('user_id', $userId)
                ->with('enrollmentRank')
                ->first();
        }

        $enrollmentDetail = ($enrollmentUpgradeLog) ?
            $enrollmentUpgradeLog->previousCwEnrollmentRank :
            $memberDetail->enrollmentRank;

        $enrollmentLevel = $enrollmentDetail['entitlement_lvl'];

        //Update Member EnrollmentId if buy upgrade transaction product
        if($memberUpgradeCv > 0){

            //member upgrade sale type only allow upgrade entitlement level from 0 to 1
            if($enrollmentLevel == 0){

                //Get enrollemnt entitlement level 1 detail
                $firstLevelEnrollementRank = $this->enrollmentRankObj
                    ->where('entitlement_lvl', 1)
                    ->first();

                if($memberUpgradeCv >= $firstLevelEnrollementRank->cv){

                    //upgrade enrollment rank level to 1
                    $memberUpdateData = [
                        'member_data' => [
                            'details' => [
                                'enrollment_rank_id' => $firstLevelEnrollementRank->id
                            ]
                        ]
                    ];

                    $this->memberRepositoryObj->update($memberUpdateData, $userId);

                    //Insert Upgrade Log
                    $enrollmentLogData = [
                        'user_id' => $userId,
                        'cw_id' => $currentCwId,
                        'previous_cw_enrollment_rank_id' => $enrollmentDetail['id'],
                        'from_enrollment_rank_id' => $memberDetail->enrollment_rank_id,
                        'to_enrollment_rank_id' => $firstLevelEnrollementRank->id,
                    ];

                    $this->memberEnrollmentRankUpgradeLogObj->create($enrollmentLogData);
                }
            }
        }

        if($baUpgradeCv > 0){

            //Get BA enrollemnt ranks
            $baEnrollementRanks = $this->enrollmentRankObj
                ->where('entitlement_lvl', '>', 1)
                ->where('cv', '<=', $baUpgradeCv)
                ->orderBy('cv', 'desc')
                ->first();

            if($baEnrollementRanks){

                if($baEnrollementRanks->entitlement_lvl > $enrollmentLevel){

                    //upgrade enrollment rank
                    $memberUpdateData = [
                        'member_data' => [
                            'details' => [
                                'enrollment_rank_id' => $baEnrollementRanks->id
                            ]
                        ]
                    ];

                    $this->memberRepositoryObj->update($memberUpdateData, $userId);

                    //Insert Upgrade Log
                    $enrollmentLogData = [
                        'user_id' => $userId,
                        'cw_id' => $currentCwId,
                        'previous_cw_enrollment_rank_id' => $enrollmentDetail['id'],
                        'from_enrollment_rank_id' => $memberDetail->enrollment_rank_id,
                        'to_enrollment_rank_id' => $baEnrollementRanks->id,
                    ];

                    $this->memberEnrollmentRankUpgradeLogObj->create($enrollmentLogData);
                }
            }
        }

        if($upgradeAmpCv >= $ampCvToUpgradeEachBaLevel){

            //Get Latest Member Enrollment Details
            $memberDetail = $this->memberObj
                ->where('user_id', $userId)
                ->with('enrollmentRank')
                ->first();

            $latestEnrollmentDetail = $memberDetail->enrollmentRank;

            $enrollmentLevel = $latestEnrollmentDetail['entitlement_lvl'];

            //Amp cv only allow ba level upgrade
            if($enrollmentLevel >= 2){

                $upgradeLevel = intdiv($upgradeAmpCv, $ampCvToUpgradeEachBaLevel);

                $newEnrollmentLevel = $upgradeLevel + $enrollmentLevel;

                //Get Upgrade Enrollment Record
                $ampEnrollmentRank = $this->enrollmentRankObj
                    ->where('entitlement_lvl', '>=', $newEnrollmentLevel)
                    ->orderBy('entitlement_lvl', 'asc')
                    ->first();

                //Get the top level
                if(!$ampEnrollmentRank){
                    $ampEnrollmentRank = $this->enrollmentRankObj
                        ->orderBy('entitlement_lvl', 'desc')
                        ->first();
                }

                if($enrollmentLevel != $ampEnrollmentRank->entitlement_lvl){

                    //upgrade enrollment rank
                    $memberUpdateData = [
                        'member_data' => [
                            'details' => [
                                'enrollment_rank_id' => $ampEnrollmentRank->id
                            ]
                        ]
                    ];

                    $this->memberRepositoryObj->update($memberUpdateData, $userId);

                    //Insert Upgrade Log
                    $enrollmentLogData = [
                        'user_id' => $userId,
                        'cw_id' => $currentCwId,
                        'previous_cw_enrollment_rank_id' => $enrollmentDetail['id'],
                        'from_enrollment_rank_id' => $memberDetail->enrollment_rank_id,
                        'to_enrollment_rank_id' => $ampEnrollmentRank->id,
                    ];

                    $this->memberEnrollmentRankUpgradeLogObj->create($enrollmentLogData);
                }
            }
        }
    }

    /**
     * Enrollment gold rank upgrade for those bought rental type product
     *
     * @param int $userId
     */
    private function rentalTypeProductEnrollmentGoldRankUpgrade(int $userId)
    {
        //Get current cw id
        $systemSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('current_cw_id'));

        $currentCwId = $systemSettings['current_cw_id'][0]->value;

        //Get mapping id
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey([
            'sale_types', 'sale_order_status'
        ]);

        $saleOrderStatus = array_change_key_case($settingsData['sale_order_status']
            ->pluck('id','title')->toArray());

        $saleType = array_change_key_case($settingsData['sale_types']
            ->pluck('id','title')->toArray());

        $saleCompleteStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];

        $rentalSaleTypeId = $saleType[$this->transactionTypeConfigCodes['rental']];

        //Get all completed sales record within same cw
        $completedSaleIdLists = $this->modelObj
            ->join('invoices', function ($join){
                $join->on('invoices.sale_id', '=', 'sales.id');
            })
            ->where("invoices.cw_id", $currentCwId)
            ->where("sales.user_id", $userId)
            ->where("sales.order_status_id", $saleCompleteStatusId)
            ->pluck("sales.id");

        //Get rental product record
        $rentalProduct = $this->saleProductsObj
            ->with('product')
            ->where('transaction_type_id', $rentalSaleTypeId)
            ->whereIn('sale_id', $completedSaleIdLists)
            ->first();

        if($rentalProduct){
            //Get member enrollment rank
            $memberDetail = $this->memberObj
                ->where('user_id', $userId)
                ->with('enrollmentRank')
                ->first();

            //Get enrollment gold rank detail
            $goldEnrollment = $this->enrollmentRankObj
                ->where('rank_code', 'Gold')
                ->first();

            $memberEnrollmentLevel = $memberDetail->enrollmentRank['entitlement_lvl'];

            if($memberEnrollmentLevel < $goldEnrollment->entitlement_lvl){
                //Get previous cw enrollment rank id
                $enrollmentUpgradeLog = $this->memberEnrollmentRankUpgradeLogObj
                    ->where('user_id', $userId)
                    ->where('cw_id', $currentCwId)
                    ->first();

                //Insert Enrollment Rank Log
                $enrollmentLogData = [
                    'user_id' => $userId,
                    'cw_id' => $currentCwId,
                    'previous_cw_enrollment_rank_id' => ($enrollmentUpgradeLog) ?
                        $enrollmentUpgradeLog->previous_cw_enrollment_rank_id :
                        $memberDetail->enrollment_rank_id,
                    'from_enrollment_rank_id' => $memberDetail->enrollment_rank_id,
                    'to_enrollment_rank_id' => $goldEnrollment->id,
                ];

                $this->memberEnrollmentRankUpgradeLogObj->create($enrollmentLogData);

                //Upgrade Enrollment Rank to Gold
                $memberUpdateData = [
                    'member_data' => [
                        'details' => [
                            'enrollment_rank_id' => $goldEnrollment->id
                        ]
                    ]
                ];

                $this->memberRepositoryObj->update($memberUpdateData, $userId);
            }
        }
    }

    /**
     * Update and verify member active record by given userId
     *
     * @param int $userId
     */
    private function memberActiveVerify(int $userId)
    {
        //Get current cw id
        $systemSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('current_cw_id', 'sales_amp_cv_per_allcation'));

        $currentCwId = $systemSettings['current_cw_id'][0]->value;

        $currentCwDetail = $this->cwSchedulesRepositoryObj->find($currentCwId);

        $currentCwName = $currentCwDetail->cw_name;

        $eachAmpAllocationCv = $systemSettings['sales_amp_cv_per_allcation'][0]->value;

        //Get mapping id
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey([
            'sale_types', 'sale_order_status'
        ]);

        $saleOrderStatus = array_change_key_case($settingsData['sale_order_status']
            ->pluck('id','title')->toArray());

        $saleCompleteStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];

        //Get all completed sales record within same cw
        $completedSaleIdRecords = $this->modelObj
            ->join('invoices', function ($join){
                $join->on('invoices.sale_id', '=', 'sales.id');
            })
            ->where("invoices.cw_id", $currentCwId)
            ->where("sales.user_id", $userId)
            ->where("sales.order_status_id", $saleCompleteStatusId)
            ->get();

        $completedSaleIdLists = collect($completedSaleIdRecords)->pluck("sales.id");

        //Get sale product record
        $saleProducts = $this->saleProductsObj
            ->where('transaction_type_id', '>', 0)
            ->whereIn('sale_id', $completedSaleIdLists)
            ->get();

        //Get sale kitting record
        $saleKittings = $this->saleKittingCloneObj
            ->where('transaction_type_id', '>', 0)
            ->whereIn('sale_id', $completedSaleIdLists)
            ->get();

        $totalSalesCv = collect($completedSaleIdRecords)->sum('total_cv');

        if($totalSalesCv >= $eachAmpAllocationCv){

            //Check does cw exist
            $memberActiveDetail = $this->memberActiveRecordObj
                ->where('user_id', $userId)
                ->where('cw_id', $currentCwId)
                ->where('is_active', 1)
                ->first();

            if(!$memberActiveDetail){

                //Get next cw id
                $nextCwList = $this->cwSchedulesRepositoryObj
                    ->getNextCwByCwName($currentCwName, 0);

                $insertCwLists = [
                    $currentCwId, $nextCwList['data'][0]->id
                ];

                //Insert active cw Id
                collect($insertCwLists)->each(function($insertCwId)
                use($userId){
                    $this->memberActiveRecordObj->create([
                        'user_id' => $userId,
                        'cw_id' => $insertCwId,
                        'is_active' => 1
                    ]);
                });

                //Update member active until cw
                $memberUpdateData = [
                    'member_data' => [
                        'details' => [
                            'active_until_cw_id' => $nextCwList['data'][0]->id,
                            'expiry_date' => Carbon::parse($nextCwList['data'][0]->date_to)->addMonths(12)->format('Y-m-d')
                        ]
                    ]
                ];

                $this->memberRepositoryObj->update($memberUpdateData, $userId);
            }
        }
    }

    /**
     * Create Auto Maintenance Purchase Cv in future CW by given saleId
     *
     * @param int $saleId
     * @return boolean
     */
    public function createAmpCvAllocations(int $saleId)
    {
        //To verify sale user id that order on be behalf by sponsor
        $saleVerify = $this->saleCvAllocationVerify($saleId);

        if(!$saleVerify['memberVerify'] || !$saleVerify['ampCvAllocationVerify']){
            return false;
        }

        //Get each amp allocaiton cv
        $systemSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('sales_amp_cv_per_allcation'));

        $eachAmpAllocationCv = $systemSettings['sales_amp_cv_per_allcation'][0]->value;

        //Get Master Data Value
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey([
            'sale_types', 'sale_order_status', 'amp_cv_allocation_types'
        ]);

        //Get Sale Type
        $saleType = array_change_key_case($settingsData['sale_types']
            ->pluck('id','title')
            ->toArray());

        $autoMaintenanceCode = $this->transactionTypeConfigCodes['auto-maintenance'];

        $autoMaintenanceId = $saleType[$autoMaintenanceCode];

        $formationCode = $this->transactionTypeConfigCodes['formation'];

        $formationId = $saleType[$formationCode];

        //Get AMP Allocation Type
        $ampCvAllocationType = array_change_key_case($settingsData['amp_cv_allocation_types']
            ->pluck('id','title')->toArray());

        $ampTypeCode = $this->ampCvAllocationTypesConfigCodes['amp'];

        $ampTypeId = $ampCvAllocationType[$ampTypeCode];

        //get sale details
        $saleDetail = $this->saleDetails($saleId);

        $saleUserId = $saleDetail['sales_data']['downline_member_id'];

        $saleProducts = $saleDetail['sales_data']['products'];

        $saleKittings = $saleDetail['sales_data']['kittings'];

        //Filter Amp and Formation and calculate total amp cv
        $ampCv = collect($saleProducts)
            ->whereIn('transaction_type_id', [$autoMaintenanceId, $formationId])
            ->sum(function ($saleProduct) {
                return $saleProduct['base_price']['cv1'] * $saleProduct['quantity'];
            });

        $ampCv += collect($saleKittings)
            ->whereIn('transaction_type_id', [$autoMaintenanceId, $formationId])
            ->sum(function ($saleKitting) {
                return $saleKitting['kitting_price']['cv1'] * $saleKitting['quantity'];
            });

        $totalQualifyCw = intdiv($ampCv, intval($eachAmpAllocationCv));

        if($totalQualifyCw > 0){

            //Get future cw based on sale invoice cw
            $saleCwId = $saleDetail['sales_data']['invoice']['cw_id'];

            $saleCwDetail = $this->cwSchedulesRepositoryObj->find($saleCwId);

            $futureCwList = $this->cwSchedulesRepositoryObj
                ->getNextCwByCwName($saleCwDetail->cw_name, 0);

            $futureCwIds = collect($futureCwList['data'])->pluck('id');

            //Check amp allocation table whether active in future CW
            $ampCvAllocationRecord = $this->ampCvAllocationObj
                ->join('cw_schedules', function ($join){
                    $join->on('cw_schedules.id', '=', 'amp_cv_allocations.cw_id');
                })
                ->where('amp_cv_allocations.type_id', $ampTypeId)
                ->where('amp_cv_allocations.user_id', $saleUserId)
                ->whereIn('amp_cv_allocations.cw_id', $futureCwIds)
                ->active()
                ->orderBy('cw_schedules.cw_name', 'desc')
                ->select('amp_cv_allocations.*', 'cw_schedules.cw_name')
                ->first();

            //Check Enough CW Record
            if(!empty($ampCvAllocationRecord)){
                $verifyCwName = $ampCvAllocationRecord->cw_name;
            } else {
                $verifyCwName = $saleCwDetail->cw_name;
            }

            $futureCwListing = $this->cwSchedulesRepositoryObj
                ->getNextCwByCwName($verifyCwName, 0);

            $totalActiveCw = intval($totalQualifyCw) * 2;

            if($futureCwListing['total'] < $totalActiveCw){
                return false;
            }

            if(!empty($ampCvAllocationRecord)){

                $nextCwAllocationList = $this->cwSchedulesRepositoryObj
                    ->getNextCwByCwName($ampCvAllocationRecord->cw_name, 2);

                $nextInsertCwName = $nextCwAllocationList['data'][1]->cw_name;

                $nextInsertCwId = $nextCwAllocationList['data'][1]->id;

            } else {

                //Check whether have purchase other product/kitting rather than amp product
                $totalExcludeProductSaleCv = floatval($saleDetail['sales_data']['cvs']['total_cv']) - $ampCv;

                if($totalExcludeProductSaleCv >= intval($eachAmpAllocationCv)){
                    $nextCwAllocationList = $this->cwSchedulesRepositoryObj
                        ->getNextCwByCwName($saleCwDetail->cw_name, 2);

                    $nextInsertCwName = $nextCwAllocationList['data'][1]->cw_name;

                    $nextInsertCwId = $nextCwAllocationList['data'][1]->id;

                } else {

                    //Get sale order complete status ID
                    $saleOrderStatus = array_change_key_case(
                        $settingsData['sale_order_status']->pluck('id','title')->toArray()
                    );

                    $saleCompleteStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];

                    //Check other sale within same cw id
                    $sameCwSales = $this->modelObj
                        ->where('user_id', $saleUserId)
                        ->where('cw_id', $saleCwId)
                        ->where('total_cv', '>', 0)
                        ->where('order_status_id', $saleCompleteStatusId)
                        ->where('id', '!=', $saleId)
                        ->first();

                    if(!empty($sameCwSales)){

                        $nextCwAllocationList = $this->cwSchedulesRepositoryObj
                            ->getNextCwByCwName($saleCwDetail->cw_name, 2);

                        $nextInsertCwName = $nextCwAllocationList['data'][1]->cw_name;

                        $nextInsertCwId = $nextCwAllocationList['data'][1]->id;

                    } else {

                        $nextInsertCwName = $saleCwDetail->cw_name;

                        $nextInsertCwId = $saleCwId;
                    }
                }
            }

            //insert amp cv allocation
            $this->insertAmpCvAllocationRecord(
                $ampTypeId,
                $saleId,
                $saleUserId,
                $nextInsertCwId,
                $eachAmpAllocationCv
            );

            $memberActiveCwLists = [];

            array_push($memberActiveCwLists, $nextInsertCwId);

            for($count = 0; $count < $totalQualifyCw - 1; $count++){

                //get next 2 cw id and cw name
                $ampCwList = $this->cwSchedulesRepositoryObj
                    ->getNextCwByCwName($nextInsertCwName, 2);

                $nextInsertCwName = $ampCwList['data'][1]->cw_name;

                $nextInsertCwId = $ampCwList['data'][1]->id;

                //insert amp cv allocation
                $this->insertAmpCvAllocationRecord(
                    $ampTypeId,
                    $saleId,
                    $saleUserId,
                    $nextInsertCwId,
                    $eachAmpAllocationCv
                );

                array_push($memberActiveCwLists, $ampCwList['data'][0]->id, $ampCwList['data'][1]->id);
            }

            //get active until cw detail
            $activeCwDetails = $this->cwSchedulesRepositoryObj
                ->getNextCwByCwName($nextInsertCwName, 1);

            $activeCwId = $activeCwDetails['data'][0]->id;

            //Update member active until cw id
            $memberUpdateData = [
                'member_data' => [
                    'details' => [
                        'active_until_cw_id' => $activeCwId,
                        'expiry_date' => Carbon::parse($activeCwDetails['data'][0]->date_to)->addMonths(12)->format('Y-m-d')
                    ]
                ]
            ];

            $this->memberRepositoryObj->update($memberUpdateData, $saleUserId);

            array_push($memberActiveCwLists, $activeCwId);

            //insert member active record
            collect($memberActiveCwLists)->each(function($memberActiveCwId)
            use ($saleUserId){
                $this->memberActiveRecordObj->create([
                    'user_id' => $saleUserId,
                    'cw_id' => $memberActiveCwId,
                    'is_active' => 1
                ]);
            });
        }
    }

    /**
     * insert amp cv allocation record by the following parameters
     *
     * @param int $ampTypeId
     * @param int $saleId = NULL
     * @param int $saleUserId
     * @param int $ampCwId
     * @param int $insertCv
     */
    private function insertAmpCvAllocationRecord
    (
        int $ampTypeId,
        int $saleId = NULL,
        int $saleUserId,
        int $ampCwId,
        int $insertCv
    )
    {
        $ampCvData = [
            'type_id' => $ampTypeId,
            'sale_id' => $saleId,
            'user_id' => $saleUserId,
            'cw_id' => $ampCwId,
            'cv' => $insertCv,
            'active' => 1,
        ];

        $this->ampCvAllocationObj->create($ampCvData);
    }

    /**
     * Swap or Remove Auto Maintenance Purchase Cv in future CW by given saleCancellationId
     *
     * @param int $saleCancellationId
     */
    public function swapAmpCvAllocations(int $saleCancellationId)
    {
        //Get each amp allocaiton cv
        $systemSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('sales_amp_cv_per_allcation'));

        $eachAmpAllocationCv = $systemSettings['sales_amp_cv_per_allcation'][0]->value;

        //Get Related Master Data Detail
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_types', 'sale_order_status', 'member_status', 'amp_cv_allocation_types'));

        //Get AMP Allocation Type
        $ampCvAllocationType = array_change_key_case($settingsData['amp_cv_allocation_types']
            ->pluck('id','title')->toArray());

        $ampTypeCode = $this->ampCvAllocationTypesConfigCodes['amp'];

        $ampTypeId = $ampCvAllocationType[$ampTypeCode];

        //get sale cancellation details
        $saleCancellationDetail = $this->saleCancellationDetail($saleCancellationId);

        $saleId = $saleCancellationDetail['sale']['id'];

        $saleCancellationUserId = $saleCancellationDetail['sale']['user_id'];

        //Get future cw based on sale invoice cw
        $saleCwId = $saleCancellationDetail['sale']['invoice']['cw_id'];

        $saleCwName = $saleCancellationDetail['sale']['invoice']['cw']['cw_name'];

        //get sale future cw
        $saleFutureCwList = $this->cwSchedulesRepositoryObj
            ->getNextCwByCwName($saleCwName, 0);

        $saleFutureCwIds = collect($saleFutureCwList['data'])->pluck('id');

        //Check amp allocation table whether active in future CW
        $saleAmpCvRecords = $this->ampCvAllocationObj
            ->whereIn('cw_id', $saleFutureCwIds)
            ->where('type_id', $ampTypeId)
            ->where('user_id', $saleCancellationUserId)
            ->active()
            ->first();

        if(!empty($saleAmpCvRecords)){

            //Get Sale Type
            $saleType = array_change_key_case($settingsData['sale_types']->pluck('id','title')->toArray());

            //Registration Sale Type
            $registrationCode = $this->transactionTypeConfigCodes['registration'];

            $registrationId = $saleType[$registrationCode];

            //Auto Maintenance Sale Type
            $autoMaintenanceCode = $this->transactionTypeConfigCodes['auto-maintenance'];

            $autoMaintenanceId = $saleType[$autoMaintenanceCode];

            //Formation Sale Type
            $formationCode = $this->transactionTypeConfigCodes['formation'];

            $formationId = $saleType[$formationCode];

            //get cancellation product and kitting
            $saleCancellationProducts = $saleCancellationDetail['sale']['products'];

            $saleCancellationKittings = $saleCancellationDetail['sale']['kitting'];

            //Stop it if only registration sale type.
            $registrationSaleTypeCancel = true;

            //Count Non Registration Sale Type Product
            $nonRegistrationSaleCancellationProducts = collect($saleCancellationProducts)
                ->whereNotIn('transaction_type_id', [$registrationId])
                ->count();

            $nonRegistrationSaleCancellationProducts += collect($saleCancellationKittings)
                ->whereNotIn('transaction_type_id', [$registrationId])
                ->count();

            if($nonRegistrationSaleCancellationProducts > 0){

                //Count Formation Sale Type Product
                $formationSaleCancellationProducts = collect($saleCancellationProducts)
                    ->where('transaction_type_id', $formationId)
                    ->count();

                $formationSaleCancellationProducts += collect($saleCancellationKittings)
                    ->where('transaction_type_id', $formationId)
                    ->count();

                if($formationSaleCancellationProducts == 0){
                    $registrationSaleTypeCancel = false;
                }

                //Filter Amp and Formation and calculate total amp cv
                $cancellationAmpCv = collect($saleCancellationProducts)
                    ->whereIn('transaction_type_id', [$autoMaintenanceId, $formationId])
                    ->sum(function ($saleCancellationProduct) {
                        return $saleCancellationProduct['base_price']['cv1'] *
                            $saleCancellationProduct['cancellation_quantity'];
                    });

                $cancellationAmpCv += collect($saleCancellationKittings)
                    ->whereIn('transaction_type_id', [$autoMaintenanceId, $formationId])
                    ->sum(function ($saleCancellationKitting) {
                        return $saleCancellationKitting['kitting_price']['cv1'] *
                            $saleCancellationKitting['cancellation_quantity'];
                    });

                //Get how much CW need to revert
                $totalRemoveRecord = intdiv($cancellationAmpCv, intval($eachAmpAllocationCv));

                $removeRecordDetails = $this->ampCvAllocationObj
                    ->join('cw_schedules', function ($join){
                        $join->on('cw_schedules.id', '=', 'amp_cv_allocations.cw_id');
                    })
                    ->where('amp_cv_allocations.type_id', $ampTypeId)
                    ->where('amp_cv_allocations.sale_id', $saleId)
                    ->where('amp_cv_allocations.user_id', $saleCancellationUserId)
                    ->orderBy('cw_schedules.cw_name', 'asc')
                    ->limit($totalRemoveRecord)
                    ->select('amp_cv_allocations.*', 'cw_schedules.cw_name')
                    ->get();

                $removedAmpIds = $removeRecordDetails->pluck('id');

                //Get future amp cv allocations records
                $ampCvAllocationRecordQuery = $this->ampCvAllocationObj
                    ->join('cw_schedules', function ($join){
                        $join->on('cw_schedules.id', '=', 'amp_cv_allocations.cw_id');
                    })
                    ->where('amp_cv_allocations.type_id', $ampTypeId)
                    ->where('amp_cv_allocations.user_id', $saleCancellationUserId)
                    ->whereIn('amp_cv_allocations.cw_id', $saleFutureCwIds)
                    ->whereNotIn('amp_cv_allocations.id', $removedAmpIds)
                    ->active()
                    ->orderBy('cw_schedules.cw_name', 'asc')
                    ->select('amp_cv_allocations.*', 'cw_schedules.cw_name');

                $ampCvAllocationRecordsCount = $ampCvAllocationRecordQuery->count();

                $ampCvAllocationRecords = $ampCvAllocationRecordQuery->get();

                //Update current amp cv allocations records to inactive
                $this->ampCvAllocationObj
                    ->active()
                    ->where('type_id', $ampTypeId)
                    ->where('user_id', $saleCancellationUserId)
                    ->where(function ($query) use ($saleId, $saleFutureCwIds){
                        $query->orWhereIn('cw_id', $saleFutureCwIds)
                            ->orWhere('sale_id', $saleId);
                    })
                    ->update(['active' => 0]);

                //Remove member active record
                $this->memberActiveRecordObj
                    ->where('user_id', $saleCancellationUserId)
                    ->whereIn('cw_id', $saleFutureCwIds)
                    ->delete();

                //Check does sale cw have other cv?
                $saleOrderStatus = array_change_key_case(
                    $settingsData['sale_order_status']->pluck('id','title')->toArray()
                );

                $saleCompleteStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];

                //Check other sale within same cw id
                $sameCwSales = $this->modelObj
                    ->where('user_id', $saleCancellationUserId)
                    ->where('cw_id', $saleCwId)
                    ->where('total_cv', '>', intval($eachAmpAllocationCv))
                    ->where('order_status_id', $saleCompleteStatusId)
                    ->where('id', '!=', $saleId)
                    ->first();

                //Get Last Cw Details
                $saleLastCwDetail = $this->cwSchedulesRepositoryObj
                    ->getCwSchedulesList(
                        'custom_past',
                        [
                            'custom_cw_name' => $saleCwName,
                            'sort' => 'cw_name',
                            'order' => 'desc',
                            'limit' => 1,
                            'offset' => 0
                        ]
                    );

                //Check amp allocation table whether active in sale CW
                $saleCwAmpCvRecords = $this->ampCvAllocationObj
                    ->where('type_id', $ampTypeId)
                    ->where('sale_id', '!=', $saleId)
                    ->whereIn('cw_id', [$saleCwId, $saleLastCwDetail['data'][0]->id])
                    ->where('user_id', $saleCancellationUserId)
                    ->active()
                    ->first();

                if(empty($saleCwAmpCvRecords) && empty($sameCwSales)){

                    $nextInsertCwName = $saleCwName;

                    $nextInsertCwId = $saleCwId;

                } else {
                    if(isset($removeRecordDetails[0])){
                        $nextInsertCwName = $removeRecordDetails[0]->cw_name;

                        $nextInsertCwId = $removeRecordDetails[0]->cw_id;
                    } else {
                        $nextInsertCwName = $saleCwName;

                        $nextInsertCwId = $saleCwId;
                    }
                }

                //Check Enough CW Record
                $futureCwListing = $this->cwSchedulesRepositoryObj
                    ->getNextCwByCwName($nextInsertCwName, 0);

                $totalActiveCw = intval($ampCvAllocationRecordsCount) * 2;

                if($futureCwListing['total'] < $totalActiveCw){
                    return false;
                }

                $memberActiveCwLists = [];

                //Create new amp cv allocations record
                foreach($ampCvAllocationRecords as $ampCvAllocationRecord){

                    //insert amp cv allocation
                    $this->insertAmpCvAllocationRecord(
                        $ampTypeId,
                        $ampCvAllocationRecord->sale_id,
                        $saleCancellationUserId,
                        $nextInsertCwId,
                        $eachAmpAllocationCv
                    );

                    //get next 2 cw id and cw name
                    $ampCwList = $this->cwSchedulesRepositoryObj
                        ->getNextCwByCwName($nextInsertCwName, 2);

                    array_push($memberActiveCwLists, $nextInsertCwId, $ampCwList['data'][0]->id);

                    $nextInsertCwName = $ampCwList['data'][1]->cw_name;

                    $nextInsertCwId = $ampCwList['data'][1]->id;
                }

                //get active until cw
                if(!empty($ampCvAllocationRecords->toArray())){
                    $activeCwId = $ampCwList['data'][0]->id;
                } else {
                    if(empty($saleCwAmpCvRecords) && empty($sameCwSales)){
                        $activeCwId = $saleLastCwDetail['data'][0]->id;
                    } else {
                        $activeCwId = $saleCwId;
                    }
                }

                //Get Active CW Detail
                $activeCwRecord = $this->cwSchedulesRepositoryObj->find($activeCwId);

                //Update member active until cw id
                $memberUpdateData = [
                    'member_data' => [
                        'details' => [
                            'active_until_cw_id' => $activeCwId,
                            'expiry_date' => Carbon::parse($activeCwRecord->date_to)->addMonths(12)->format('Y-m-d')
                        ]
                    ]
                ];

                $this->memberRepositoryObj->update($memberUpdateData, $saleCancellationUserId);

                //insert member active record
                collect($memberActiveCwLists)->each(function($memberActiveCwId)
                use ($saleCancellationUserId){
                    $this->memberActiveRecordObj->create([
                        'user_id' => $saleCancellationUserId,
                        'cw_id' => $memberActiveCwId,
                        'is_active' => 1
                    ]);
                });
            }

            //Update Member status to inactive
            if($registrationSaleTypeCancel){
                $memberStatus = array_change_key_case(
                    $settingsData['member_status']->pluck('id','title')->toArray());

                $terminatedCode = $this->memberStatusConfigCodes['terminated'];

                $terminatedId = $memberStatus[$terminatedCode];

                $memberUpdateData = [
                    'member_data' => [
                        'details' => [
                            'status_id' => $terminatedId,
                            'enrollment_rank_id' => NULL
                        ]
                    ]
                ];

                $this->memberRepositoryObj->update($memberUpdateData, $saleCancellationUserId);
            }
        }
    }

    /**
     * Calculate Sale Accumulate Cv within Cw
     *
     * @param int $userId
     */
    public function saleAccumulationCalculation(int $userId)
    {
        //Get Current Cw
        $systemSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('current_cw_id'));

        $currentCwId = $systemSettings['current_cw_id'][0]->value;

        //Get Sale Completed Status Id
        $mastersData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_order_status'));

        $saleOrderStatus = array_change_key_case(
            $mastersData['sale_order_status']->pluck('id','title')->toArray());

        $completedStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];

        $totalBaseCv = $totalAmpCv = $totalEnrollmentCv = 0;

        //Retrieve Completed Sale Records
        $invoiceDatas = $this->invoiceObj
            ->join('sales', function ($join)
                use ($userId){
                    $join->on('sales.id', '=', 'invoices.sale_id')
                        ->where(function ($salesQuery) use ($userId) {
                            $salesQuery->where('sales.user_id', $userId);
                        });
                })
            ->where('invoices.cw_id', $currentCwId)
            ->select('invoices.*')
            ->get();

        $invoiceDatas->each(function($invoiceData)
            use(&$totalBaseCv, &$totalAmpCv, &$totalEnrollmentCv){

                $sale = $invoiceData->sale()->first();

                $totalAmpCv += $sale->getSaleAmpCVs();

                $totalBaseCv += $sale->getSaleBaseCVs($this->masterRepositoryObj, $this->transactionTypeConfigCodes);

                $totalEnrollmentCv += $sale->getSaleEnrollementCVs($this->masterRepositoryObj, $this->transactionTypeConfigCodes);
            });

        $accumulationData = [
            'user_id' => $userId,
            'cw_id' => $currentCwId,
            'base_cv' => $totalBaseCv,
            'amp_cv' => $totalAmpCv,
            'enrollment_cv' => $totalEnrollmentCv
        ];

        //Update Or Create Sale Accumulation Record.
        $accumulationDetail = $this->saleAccumulationObj
            ->where('cw_id', $currentCwId)
            ->where("user_id", $userId)
            ->first();

        ($accumulationDetail) ?
            $accumulationDetail->update($accumulationData) :
                $this->saleAccumulationObj->create($accumulationData);
    }

    /**
     * get sales cancellation filtered by the following parameters
     *
     * @param int $countryId
     * @param string $text
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|NULL $statusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getSalesCancellationByFilters(
        int $countryId,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $statusId = NULL,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->saleCancellationObj
            ->with(['transactionLocation', 'createdBy', 'creditNote', 'cw',
                'member', 'member.country', 'sale.country', 'sale.cw', 'sale.transactionLocation',
                'cancellationMode', 'cancellationStatus', 'invoice.cw', 'legacyInvoice.cw',
                'legacyInvoice.transactionLocation'
            ]);

        //check the granted location give for the user if he back_office,stockist or member
        if (
            $this->isUser('back_office') or
            $this->isUser('stockist') or
            $this->isUser('stockist_staff')
        )
        {
            $this->applyLocationQuery($data, $countryId, 'sales_cancellations.transaction_location_id');
        }

        $data = $data
            ->leftjoin('sales', function ($join)
            use ($countryId, $data){
                $join->on('sales_cancellations.sale_id', '=', 'sales.id')
                    ->where(function ($saleQuery) use ($countryId) {
                        $saleQuery->where('sales.country_id', $countryId);
                    });

                //check the granted location give for the user if he back_office,stockist or member
                if (
                    $this->isUser('back_office') or
                    $this->isUser('stockist') or
                    $this->isUser('stockist_staff')
                )
                {
                    $this->applyLocationQuery($join, $countryId, 'sales.transaction_location_id');
                }

            })
            ->leftjoin('legacies_invoices', function ($join)
            use ($countryId){
                $join->on('sales_cancellations.legacy_invoice_id', '=', 'legacies_invoices.id')
                    ->where(function ($legacyInvoicesQuery) use ($countryId) {
                        $legacyInvoicesQuery->where('legacies_invoices.country_id', $countryId);
                    });
            });

        $data = $data
            ->where(function ($countryQuery) use ($countryId) {
                $countryQuery->Orwhere('sales.country_id', $countryId);
                $countryQuery->Orwhere('legacies_invoices.country_id', $countryId);
            });

        if ($text != '') {
            $data = $data
                ->join('users', function ($join){
                    $join->on('users.id', '=', 'sales_cancellations.user_id');
                })
                ->join('members', function ($join){
                    $join->on('users.id', '=', 'members.user_id');
                })
                ->leftjoin('invoices', function ($join){
                    $join->on('sales_cancellations.sale_id', '=', 'invoices.sale_id');
                })
                ->where(function ($textQuery) use($text) {
                    $textQuery->orWhere('users.old_member_id', 'like','%' . $text . '%')
                        ->orWhere('members.name', 'like','%' . $text . '%')
                        ->orWhere('members.ic_passport_number', 'like','%' . $text . '%')
                        ->orWhere('invoices.invoice_number', 'like','%' . $text . '%')
                        ->orWhere('legacies_invoices.invoice_number', 'like','%' . $text . '%');
                });
        }

        //check the status if given
        if($statusId > 0){
            $data = $data->where('cancellation_status_id', $statusId);
        }

        //check the dates if given
        if ($dateFrom != '' and $dateTo != ''){
            $data = $data
                ->where('sales_cancellations.transaction_date','>=', $dateFrom)
                ->where('sales_cancellations.transaction_date','<=', $dateTo);
        }

        //sorting
        if ($orderBy == "members.id"){

            if ($text == ''){
                $data = $data
                    ->join('users', function ($join){
                        $join->on('users.id', '=', 'sales_cancellations.user_id');
                    })
                    ->orderBy('users.old_member_id', $orderMethod);
            }

        } else if ($orderBy == "members.name"){

            if ($text == ''){
                $data = $data
                    ->join('members', function ($join){
                        $join->on('sales_cancellations.user_id', '=', 'members.user_id');
                    })
                    ->orderBy('members.name', $orderMethod);
            }

        } else if ($orderBy == "invoice.cw.cw_name"){

            if ($text == ''){
                $data = $data
                    ->leftjoin('invoices', function ($join){
                        $join->on('sales_cancellations.sale_id', '=', 'invoices.sale_id');
                    });
            }

            $data = $data
                ->join('cw_schedules', function ($join){
                    $join->on('cw_schedules.id', '=', 'invoices.cw_id');
                })
                ->orderBy('cw_schedules.cw_name', $orderMethod);

        } else if(in_array($orderBy, ['transaction_location.code', 'transaction_location.name'])){

            $data = $data
                ->join('locations', function ($join){
                    $join->on('locations.id', '=', 'sales_cancellations.transaction_location_id');
                });

            $data = ($orderBy == 'transaction_location.code') ?
                $data->orderBy('locations.name', $orderMethod) :
                $data->orderBy('locations.code', $orderMethod);

        } else if ($orderBy == "credit_note.credit_note_number"){

            $data = $data
                ->leftjoin('credit_notes', function ($join){
                    $join->on('credit_notes.mapping_id', '=', 'sales_cancellations.id')
                        ->where('credit_notes.mapping_model', 'sales_cancellations');
                })
                ->orderBy('credit_notes.credit_note_number', $orderMethod);

        } else if ($orderBy == "cw.cw_name"){

            $data = $data
                ->join('cw_schedules', function ($join){
                    $join->on('cw_schedules.id', '=', 'sales_cancellations.cw_id');
                })
                ->orderBy('cw_schedules.cw_name', $orderMethod);

        } else if ($orderBy == "cancellation_mode.title"){

            $data = $data
                ->join('master_data', function ($join){
                    $join->on('master_data.id', '=', 'sales_cancellations.cancellation_mode_id');
                })
                ->orderBy('master_data.title', $orderMethod);

        } else if ($orderBy == "cancellation_status.title"){

            $data = $data
                ->join('master_data', function ($join){
                    $join->on('master_data.id', '=', 'sales_cancellations.cancellation_status_id');
                })
                ->orderBy('master_data.title', $orderMethod);

        } else {

            $data = $data->orderBy($orderBy, $orderMethod);
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data->select('sales_cancellations.*');

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get sale cancellation detail by given saleCancellationId
     *
     * @param int $saleCancellationId
     * @return mixed
     */
    public function saleCancellationDetail(int $saleCancellationId)
    {
        $cancellationDetails = $this->saleCancellationObj
            ->with('creditNote', 'cw', 'cancellationMode',
                'cancellationStatus', 'cancellationReason','cancellationType',
                'stockLocation', 'transactionLocation')
            ->find($saleCancellationId);

        if(!$cancellationDetails->is_legacy){

            $cancellationProducts = $cancellationDetails->saleCancelProducts()->get();

            $cancellationEsacVouchers = $cancellationDetails->saleCancelEsacVoucher()->get();

            $invoiceDetails = $this->getSalesCancellationInvoiceDetails(
                'normal', $cancellationDetails->user_id, $cancellationDetails->invoice_id, 0);

            $products = collect($invoiceDetails['sale']['products'])
                ->map(function($productData) use ($cancellationProducts){

                    $cancellationProductDetail = $cancellationProducts
                        ->where('sale_product_id', $productData['id'])
                        ->first();

                    $productData['available_quantity_snapshot'] =
                        (isset($cancellationProductDetail->available_quantity_snapshot)) ?
                            $cancellationProductDetail->available_quantity_snapshot : 0;

                    $productData['cancellation_quantity'] =
                        (isset($cancellationProductDetail->quantity)) ?
                            $cancellationProductDetail->quantity : 0;

                    return $productData;
                });

            $kitting = collect($invoiceDetails['sale']['kitting'])
                ->map(function($kittingData) use ($cancellationProducts){

                    $kittingProducts = collect($kittingData['kitting_products'])
                        ->map(function($kittingProductData) use ($cancellationProducts){

                            $cancellationProductDetail = $cancellationProducts
                                ->where('sale_product_id', $kittingProductData['product_id'])
                                ->first();

                            $kittingProductData['available_quantity_snapshot'] =
                                (isset($cancellationProductDetail->available_quantity_snapshot)) ?
                                    $cancellationProductDetail->available_quantity_snapshot : 0;

                            $kittingProductData['cancellation_quantity'] =
                                (isset($cancellationProductDetail->quantity)) ?
                                    $cancellationProductDetail->quantity : 0;

                            return $kittingProductData;
                        });

                    $kittingData['kitting_products'] = $kittingProducts;

                    $kittingDataDetails = $cancellationProducts
                        ->where('sale_product_id', $kittingProducts[0]['product']['id'])
                        ->first();

                    $kittingData['cancellation_quantity'] = (isset($kittingDataDetails->kitting_quantity)) ?
                        $kittingDataDetails->kitting_quantity : 0;

                    $kittingData['available_quantity_snapshot'] = (isset($kittingDataDetails->available_kitting_quantity_snapshot)) ?
                        $kittingDataDetails->available_kitting_quantity_snapshot : 0;

                    return $kittingData;
                });

            $promotions = collect($invoiceDetails['sale']['promotions'])
                ->map(function($promotionData) use ($cancellationProducts){

                    $cancellationProductDetail = $cancellationProducts
                        ->where('sale_product_id', $promotionData['id'])
                        ->first();

                    $promotionData['available_quantity_snapshot'] =
                        (isset($cancellationProductDetail->available_quantity_snapshot)) ?
                            $cancellationProductDetail->available_quantity_snapshot : 0;

                    $promotionData['cancellation_quantity'] =
                        (isset($cancellationProductDetail->quantity)) ?
                            $cancellationProductDetail->quantity : 0;

                    return $promotionData;
                });

            $esacVouchers = collect($invoiceDetails['sale']['esac_vouchers'])
                ->map(function($esacVoucherData) use ($cancellationEsacVouchers){

                    $cancellationEsacVoucherDetail = $cancellationEsacVouchers
                        ->where('sale_esac_voucher_clone_id', $esacVoucherData['id'])
                        ->first();

                    $esacVoucherData['available_quantity_snapshot'] =
                        (isset($cancellationEsacVoucherDetail->available_quantity_snapshot)) ?
                            $cancellationEsacVoucherDetail->available_quantity_snapshot : 0;

                    $esacVoucherData['cancellation_quantity'] =
                        (isset($cancellationEsacVoucherDetail->quantity)) ?
                            $cancellationEsacVoucherDetail->quantity : 0;

                    return $esacVoucherData;
                });

            $invoiceDetails = $invoiceDetails->toArray();

            $invoiceDetails['sale']['products'] = $products;

            $invoiceDetails['sale']['kitting'] = $kitting;

            $invoiceDetails['sale']['promotions'] = $promotions;

            $invoiceDetails['sale']['esac_vouchers'] = $esacVouchers;

            $saleData = $invoiceDetails['sale'];

        } else {

            $legacyInvoice = $cancellationDetails->legacyInvoice()
                ->with('cw', 'country', 'transactionLocation')
                ->first();

            $legacyCancelProducts = $cancellationDetails->getLegacySaleCancelProducts();

            $legacyCancelKitting = $cancellationDetails->getLegacySaleCancellationKitting();

            $saleData = [
                'invoice' => $legacyInvoice,
                'products' => $legacyCancelProducts,
                'kitting' => $legacyCancelKitting
            ];
        }

        //get member details
        $member = $this->memberRepositoryObj
            ->find($cancellationDetails->user_id, ['status','user','country']);

        //get workflow
        $workflow = $this->workflowRepositoryObj->getTrackingWorkflowDetails($cancellationDetails->workflow_tracking_id);

        return collect([
            'member' => $member,
            'sale_cancellation' => $cancellationDetails,
            'sale' => $saleData,
            'workflow' => $workflow['workflow']
        ]);
    }

    /**
     * create new sales cancellation
     *
     * @param array $data
     * @return mixed
     */
    public function createSalesCancellation(array $data)
    {
        //Get Sales Details
        $salesDetails = $this->getSalesCancellationInvoiceDetails(
            'normal', $data['sale_cancellation']['user_id'], $data['sale_cancellation']['invoice_id'], 0);

        $buyBackPercentage = (strtolower($salesDetails['sale_cancellation_type']['title']) == 'buy back') ?
            $salesDetails['buy_back_percentage'] : 100;

        $saleCancellationProducts = $saleCancellationEsacVouchers = [];

        $totalKittingCancelCv = 0;

        //generate cancellation products data
        if(isset($data['sale']['products']) and !empty($data['sale']['products'])){

            collect($data['sale']['products'])->each(function ($product)
            use (&$saleCancellationProducts, $buyBackPercentage, $salesDetails){

                //get sales product details
                $saleProductsDetails = $this->saleProductsObj->find($product['id']);

                if(!empty($saleProductsDetails)){

                    //Get available quantity
                    $saleProducts = collect($salesDetails['sale']['products'])
                        ->where('id', $saleProductsDetails->id)
                        ->first();

                    $available_quantity_snapshot = $saleProducts['available_quantity_snapshot'];

                    //Form product cancellation data
                    $cancellationData = [
                        'sale_product_id' => $saleProductsDetails->id,
                        'kitting_quantity' => 0,
                        'available_quantity_snapshot' => $available_quantity_snapshot,
                        'quantity' => intval($product['cancellation_quantity']),
                        'product_cv' => intval($saleProductsDetails->eligible_cv) *
                            intval($product['cancellation_quantity']),
                        'price' =>
                            floatval($saleProductsDetails->gmp_price_gst) *
                            intval($product['cancellation_quantity']),
                        'is_kitting' => 0,
                    ];

                    $cancellationData['buy_back_price'] = floatval($buyBackPercentage) *
                        floatval($cancellationData['price']) / 100;

                    array_push($saleCancellationProducts, $cancellationData);
                }
            });
        }

        //generate cancellation kitting products data
        if(isset($data['sale']['kitting']) and !empty($data['sale']['kitting'])){

            collect($data['sale']['kitting'])->each(function ($kitting)
            use (&$saleCancellationProducts, &$totalKittingCancelCv, $buyBackPercentage, $salesDetails){

                //Get kitting available quantity
                $saleKittingDetail = collect($salesDetails['sale']['kitting'])
                    ->where('id', $kitting['id'])
                    ->first();

                //Sum Cancel Kitting CV
                $totalKittingCancelCv += intval($kitting['cancellation_quantity']) *
                    intval($saleKittingDetail['eligible_cv']);

                collect($kitting['kitting_products'])->each(function ($kittingProduct)
                use (&$saleCancellationProducts, $saleKittingDetail, $kitting, $buyBackPercentage, $salesDetails){

                    //get sales kitting product details
                    $saleKittingProductDetails = $this->saleProductsObj->find($kittingProduct['id']);

                    if(!empty($saleKittingProductDetails)){

                        $saleKittingProduct = collect($saleKittingDetail['kitting_products'])
                            ->where('id', $saleKittingProductDetails->id)
                            ->first();

                        $available_quantity_snapshot = $saleKittingProduct['available_quantity_snapshot'];

                        //Form product cancellation data
                        $cancellationData = [
                            'sale_product_id' => $saleKittingProductDetails->id,
                            'kitting_quantity' => intval($kitting['cancellation_quantity']),
                            'available_kitting_quantity_snapshot' => intval($saleKittingDetail['available_quantity_snapshot']),
                            'available_quantity_snapshot' => $available_quantity_snapshot,
                            'quantity' => intval($kittingProduct['cancellation_quantity']),
                            'product_cv' => intval($saleKittingDetail['eligible_cv']),
                            'price' =>
                                ($saleKittingProduct['quantity'] == $kittingProduct['cancellation_quantity']) ?
                                    floatval($saleKittingProductDetails->total) :
                                    floatval($saleKittingProductDetails->average_price_unit) *
                                    intval($kittingProduct['cancellation_quantity']),
                            'is_kitting' => 1,
                        ];

                        $cancellationData['buy_back_price'] = floatval($buyBackPercentage) *
                            floatval($cancellationData['price']) / 100;

                        array_push($saleCancellationProducts, $cancellationData);
                    }
                });
            });
        }

        //generate cancellation promotion products data
        if(isset($data['sale']['promotions']) and !empty($data['sale']['promotions'])){

            collect($data['sale']['promotions'])->each(function ($promotion)
            use (&$saleCancellationProducts, $buyBackPercentage, $salesDetails){

                //get sales promotion details
                $salePromotionProductDetails = $this->saleProductsObj->find($promotion['id']);

                if(!empty($salePromotionProductDetails)){

                    //Get available quantity
                    $salePromotions = collect($salesDetails['sale']['promotions'])
                        ->where('id', $salePromotionProductDetails->id)
                        ->first();

                    $available_quantity_snapshot = $salePromotions['available_quantity_snapshot'];

                    //Form product cancellation data
                    $cancellationData = [
                        'sale_product_id' => $salePromotionProductDetails->id,
                        'kitting_quantity' => 0,
                        'available_quantity_snapshot' => $available_quantity_snapshot,
                        'quantity' => intval($promotion['cancellation_quantity']),
                        'product_cv' => intval($salePromotionProductDetails->eligible_cv) *
                            intval($promotion['cancellation_quantity']),
                        'price' =>
                            floatval($salePromotionProductDetails->total) /
                                intval($salePromotionProductDetails->quantity) *
                                    intval($promotion['cancellation_quantity']),
                        'is_kitting' => 0,
                    ];

                    $cancellationData['buy_back_price'] = floatval($buyBackPercentage) *
                        floatval($cancellationData['price']) / 100;

                    array_push($saleCancellationProducts, $cancellationData);
                }
            });
        }

        //generate cancellation esac vouchers data
        if(isset($data['sale']['esac_vouchers']) and !empty($data['sale']['esac_vouchers'])){

            collect($data['sale']['esac_vouchers'])->each(function ($esacVoucher)
            use (&$saleCancellationEsacVouchers, $salesDetails){

                //get sales esac voucher details
                $saleEsacVoucherDetails = $this->saleEsacVouchersCloneObj->find($esacVoucher['id']);

                if(!empty($saleEsacVoucherDetails)){

                    //Get available quantity
                    $saleEsacVouchers = collect($salesDetails['sale']['esac_vouchers'])
                        ->where('id', $saleEsacVoucherDetails->id)
                        ->first();

                    //Form product cancellation data
                    $esacVoucherData = [
                        'sale_esac_voucher_clone_id' => $saleEsacVoucherDetails->id,
                        'available_quantity_snapshot' => $saleEsacVouchers['available_quantity_snapshot'],
                        'quantity' => intval($esacVoucher['cancellation_quantity']),
                        'voucher_value' => floatval($esacVoucher['cancellation_quantity']) *
                            floatval($saleEsacVoucherDetails->voucher_value),
                    ];

                    array_push($saleCancellationEsacVouchers, $esacVoucherData);
                }

            });
        }

        //Get Status Id
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_cancellation_status'));

        $statusValues = array_change_key_case(
            $settingsData['sale_cancellation_status']->pluck('id','title')->toArray()
        );

        //Calculate Total Cancellation Amount

        $totalSaleEsacVoucherValue = collect($saleCancellationEsacVouchers)->sum('voucher_value');

        $totalSaleEsacVoucherBuyBackValue = floatval($buyBackPercentage) *
            floatval($totalSaleEsacVoucherValue) / 100;

        $saleCancellationTotalBuyBackAmount = collect($saleCancellationProducts)->sum('buy_back_price');

        $saleCancellationTotalBuyBackAmount -= $totalSaleEsacVoucherBuyBackValue;

        $saleCancellationTotalActualAmount = $this->roundingAdjustment(
            $salesDetails['sale']['country_id'], $saleCancellationTotalBuyBackAmount);

        $saleCancellationRoundingAmount = floatval($saleCancellationTotalBuyBackAmount) - floatval($saleCancellationTotalActualAmount);

        $totalCancelProductCv = collect($saleCancellationProducts)
            ->where('is_kitting', 0)
            ->sum('product_cv');

        //Create Sale Cancellation
        $saleCancellationData = [
            'sale_id' => $salesDetails['sale']['id'],
            'invoice_id' => $salesDetails['sale']['invoice']['id'],
            'transaction_location_id' => $data['sale_cancellation']['transaction_location_id'],
            'stock_location_id' => $data['sale_cancellation']['stock_location_id'],
            'user_id' => $data['sale_cancellation']['user_id'],
            'cw_id' => $data['sale_cancellation']['cw_id'],
            'cancellation_type_id' => $data['sale_cancellation']['cancellation_type_id'],
            'cancellation_mode_id' => $data['sale_cancellation']['cancellation_mode_id'],
            'cancellation_reason_id' => $data['sale_cancellation']['cancellation_reason_id'],
            'cancellation_status_id' => $statusValues['pending approval'],
            'transaction_date' => date('Y-m-d'),
            'total_amount' => collect($saleCancellationProducts)->sum('price') -
                floatval($totalSaleEsacVoucherValue),
            'total_buy_back_amount' => $saleCancellationTotalBuyBackAmount,
            'rounding_adjustment' => $saleCancellationRoundingAmount,
            'total_product_cv' => $totalCancelProductCv + $totalKittingCancelCv,
            'remarks' => $data['sale_cancellation']['remarks'],
        ];

        $saleCancellation = Auth::user()->createdBy($this->saleCancellationObj)->create($saleCancellationData);

        //Create Workflow
        $workflowTrackingDetail = $this->workflowRepositoryObj->copyWorkflows(
            $saleCancellation->id, 'SaleCancellation',
            $salesDetails['workflow']['id'], $data['sale_cancellation']['user_id']
        );

        $workflowTrackingId = $workflowTrackingDetail['workflow']['workflow_tracking_id'];

        //Update Workflow ID
        $saleCancellationRecord = $this->saleCancellationObj->find($saleCancellation->id);

        $saleCancellationRecord->update(
            array(
                'workflow_tracking_id' => $workflowTrackingId,
                'updated_by' => Auth::id()
            )
        );

        //Insert Sales Cancellation Products
        collect($saleCancellationProducts)->each(function ($saleCancelProduct)
        use ($saleCancellation){
            unset($saleCancelProduct['is_kitting']);
            $saleCancelProduct['sale_cancellation_id'] = $saleCancellation->id;
            Auth::user()->createdBy($this->saleCancellationProductObj)->create($saleCancelProduct);
        });

        //Insert Sales Cancellation Esac Voucher
        collect($saleCancellationEsacVouchers)->each(function ($saleCancelEsacVoucher)
            use ($saleCancellation){
                $saleCancelEsacVoucher['sale_cancellation_id'] = $saleCancellation->id;
                $this->saleCancellationEsacVoucherObj->create($saleCancelEsacVoucher);
            });

        //Generate Credit Note if same day sale cancellation
        if(strtolower($salesDetails['sale_cancellation_type']['title']) == "same day"){

            //get workflow tracking step ID
            $workflowDetail = $this->workflowRepositoryObj->getTrackingWorkflowDetails($workflowTrackingId);

            $workflowTrackingStepId = $workflowDetail['workflow']['current_step']['id'];

            $workflowTrackingStepData = $workflowDetail['workflow']['current_step']['step_data'];

            $this->workflowRepositoryObj->updateWorkflowTrackingStep(
                $workflowTrackingStepId, $workflowTrackingStepData['actions']['fields'][0], NULL
            );
        }

        return $this->saleCancellationDetail($saleCancellation->id);
    }

    /**
     * create new legacy sales cancellation
     *
     * @param array $data
     * @return mixed
     */
    public function createLegacySalesCancellation(array $data)
    {
        $countryId = $data['sale']['legacy_invoice']['country_id'];

        //Get Sales Details
        $salesDetails = $this->getSalesCancellationInvoiceDetails(
            'legacy', $data['legacy_sale_cancellation']['user_id'], 0, $countryId);

        $buyBackPercentage = (strtolower($salesDetails['sale_cancellation_type']['title']) == 'buy back') ?
            $salesDetails['buy_back_percentage'] : 100;

        $saleCancellationProducts = $saleCancellationKitting = [];

        //Get Tax Detail
        $countryDetail = $this->countryObj->where('id', $countryId)->first();

        $countryTaxDetail = $countryDetail->taxes()->first();

        $countryTaxRate = (!empty($countryTaxDetail)) ? $countryTaxDetail->rate : 0;

        $locationArray[] = $data['legacy_sale_cancellation']['transaction_location_id'];

        //generate cancellation products data
        if(isset($data['sale']['products']) and !empty($data['sale']['products'])){

            collect($data['sale']['products'])->each(function ($product)
            use (&$saleCancellationProducts, $buyBackPercentage, $countryTaxRate){

                //get product details
                $productsDetails = $this->productObj->find($product['product_id']);

                if(!empty($productsDetails)){

                    $productGmpPriceTax = floatval($product['base_price']['gmp_price_tax']);

                    $productTotalPrice = floatval($product['base_price']['gmp_price_tax']) *
                        floatval($product['cancellation_quantity']);

                    //Form product cancellation data
                    $cancellationData = [
                        'available_quantity_snapshot' => intval($product['cancellation_quantity']),
                        'quantity'  => intval($product['cancellation_quantity']),
                        'product_cv' => intval($product['base_price']['base_cv']),
                        'gmp_price_gst' => $productGmpPriceTax,
                        'nmp_price' => $productGmpPriceTax / (100 + floatval($countryTaxRate)) * 100,
                        'average_price_unit' => 0,
                        'total' => $productTotalPrice,
                        'buy_back_price' => $productTotalPrice * floatval($buyBackPercentage) / 100,
                        'product_clone' => [
                            'product_id' => $productsDetails->id,
                            'name' => $productsDetails->name,
                            'sku' => $productsDetails->sku,
                            'uom' => $productsDetails->uom
                        ]
                    ];

                    array_push($saleCancellationProducts, $cancellationData);
                }
            });
        }

        //generate cancellation kitting products data
        if(isset($data['sale']['kitting']) and !empty($data['sale']['kitting'])){

            collect($data['sale']['kitting'])->each(function ($kitting)
            use (&$saleCancellationKitting, $buyBackPercentage, $countryId, $countryTaxRate, $locationArray){

                $kittingData = $this->kittingRepositoryObj
                    ->kittingDetails($countryId, $kitting['kitting_id']);

                $kittingGmpPriceTax = floatval($kitting['kitting_price']['gmp_price_tax']);

                $kittingTotalPrice = floatval($kitting['kitting_price']['gmp_price_tax']) *
                    floatval($kitting['cancellation_quantity']);

                $cancellationKittingData = [
                    'kitting_id' => $kitting['kitting_id'],
                    'code' => $kittingData['code'],
                    'name' => $kittingData['name'],
                    'available_quantity_snapshot' => $kitting['cancellation_quantity'],
                    'quantity' => $kitting['cancellation_quantity'],
                    'product_cv' => $kitting['kitting_price']['base_cv'],
                    'gmp_price_gst' => $kittingGmpPriceTax,
                    'nmp_price' => $kittingGmpPriceTax / (100 + floatval($countryTaxRate)) * 100,
                    'total' => $kittingTotalPrice,
                    'buy_back_price' => $kittingTotalPrice * floatval($buyBackPercentage) / 100,
                    'kitting_product' => []
                ];

                //calculate kitting total gmp for ratio calculation
                $totalGmpPrice = $this->kittingRepositoryObj->calculateKittingTotalGmp(
                    $countryId,
                    $kittingData,
                    $locationArray
                );

                collect($kittingData['kitting_products'])->each( function ($product)
                use (&$cancellationKittingData, $totalGmpPrice, $countryId,
                    $locationArray, $kittingGmpPriceTax, $buyBackPercentage){

                    //Get Product Pricing
                    $effectivePrice = optional($this->productRepositoryObj
                        ->productEffectivePricing(
                            $countryId,
                            $product['product']->id,
                            $locationArray
                        ))
                        ->toArray();

                    //fallback to active price
                    if ($effectivePrice == null){
                        $effectivePrice =  optional($this->productRepositoryObj
                            ->productEffectivePricing(
                                $countryId,
                                $product['product']->id
                            ))
                            ->toArray();
                    }

                    $totalPromoPriceGst = $averagePriceUnitGst = 0;

                    $productQuantity = (intval($product['quantity'])> 0) ? intval($product['quantity']) : intval($product['foc_qty']);

                    //calculate ratio for each product inside kitting
                    if ($effectivePrice['gmp_price_tax'] > 0) {

                        $ratio = number_format(((($effectivePrice['gmp_price_tax'] * $productQuantity) / $totalGmpPrice) * 100), 2);

                        $totalPromoPriceGst =  number_format((($kittingGmpPriceTax * $ratio) / 100),2, '.', '');

                        $averagePriceUnitGst = number_format(($totalPromoPriceGst / $productQuantity),2, '.', '');
                    }

                    //get product details
                    $productsDetails = $this->productObj->find($product['product']->id);

                    $cancelKittingProduct = [
                        'available_quantity_snapshot' => $productQuantity,
                        'quantity'  => $productQuantity,
                        'product_cv' => 0,
                        'gmp_price_gst' => 0,
                        'nmp_price' => 0,
                        'average_price_unit' => $averagePriceUnitGst,
                        'total' => $totalPromoPriceGst,
                        'buy_back_price' => $totalPromoPriceGst * floatval($buyBackPercentage) / 100,
                        'product_clone' => [
                            'product_id' => $productsDetails->id,
                            'name' => $productsDetails->name,
                            'sku' => $productsDetails->sku,
                            'uom' => $productsDetails->uom
                        ]
                    ];

                    array_push($cancellationKittingData['kitting_product'], $cancelKittingProduct);
                });

                array_push($saleCancellationKitting, $cancellationKittingData);

            });
        }

        //Get Status Id
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_cancellation_status', 'legacy_sale_cancellation_mode'));

        $statusValues = array_change_key_case(
            $settingsData['sale_cancellation_status']->pluck('id','title')->toArray()
        );

        $legacySaleCancellationModeValues = array_change_key_case(
            $settingsData['legacy_sale_cancellation_mode']->pluck('id','title')->toArray()
        );

        //Calculate Total Cancellation Amount
        $saleCancellationTotalAmount = collect($saleCancellationProducts)->sum('total');

        $saleCancellationTotalAmount += collect($saleCancellationKitting)->sum('total');

        $saleCancellationTotalBuyBackAmount = collect($saleCancellationProducts)->sum('buy_back_price');

        $saleCancellationTotalBuyBackAmount += collect($saleCancellationKitting)->sum('buy_back_price');

        $saleCancellationTotalActualAmount = $this->roundingAdjustment(
            $countryId, $saleCancellationTotalBuyBackAmount);

        $saleCancellationRoundingAmount = floatval($saleCancellationTotalBuyBackAmount) - floatval($saleCancellationTotalActualAmount);

        $totalCancelProductCv = collect($saleCancellationProducts)->sum('product_cv');

        $totalCancelProductCv += collect($saleCancellationKitting)->sum('product_cv');

        //Create Legacy Invoice
        $legacyInvoiceData = [
            'cw_id' => $data['sale']['legacy_invoice']['cw_id'],
            'country_id' => $countryId,
            'transaction_location_id' => $data['sale']['legacy_invoice']['transaction_location_id'],
            'invoice_number' => $data['sale']['legacy_invoice']['invoice_number'],
            'invoice_date' => $data['sale']['legacy_invoice']['invoice_date']
        ];

        $legacyInvoice = Auth::user()->createdBy($this->legacyInvoiceObj)->create($legacyInvoiceData);

        //Create Sale Cancellation
        $saleCancellationData = [
            'sale_id' => NULL,
            'invoice_id' => NULL,
            'transaction_location_id' => $data['legacy_sale_cancellation']['transaction_location_id'],
            'stock_location_id' => $data['legacy_sale_cancellation']['stock_location_id'],
            'user_id' => $data['legacy_sale_cancellation']['user_id'],
            'cw_id' => $data['legacy_sale_cancellation']['cw_id'],
            'cancellation_type_id' => $data['legacy_sale_cancellation']['cancellation_type_id'],
            'cancellation_mode_id' =>
                $legacySaleCancellationModeValues[$this->legacySaleCancellationModeConfigCodes['legacy']],
            'cancellation_reason_id' => $data['legacy_sale_cancellation']['cancellation_reason_id'],
            'cancellation_status_id' => $statusValues[$this->saleCancellationStatusConfigCodes['pending-approval']],
            'transaction_date' => date('Y-m-d'),
            'total_amount' => $saleCancellationTotalAmount,
            'total_buy_back_amount' => $saleCancellationTotalBuyBackAmount,
            'rounding_adjustment' => $saleCancellationRoundingAmount,
            'total_product_cv' => $totalCancelProductCv,
            'remarks' => $data['legacy_sale_cancellation']['remarks'],
            'is_legacy' => true,
            'legacy_invoice_id' => $legacyInvoice->id
        ];

        $saleCancellation = Auth::user()->createdBy($this->saleCancellationObj)->create($saleCancellationData);

        //Create Workflow
        $workflowTrackingDetail = $this->workflowRepositoryObj->copyWorkflows(
            $saleCancellation->id, 'SaleCancellation',
            $salesDetails['workflow']['id'], $data['legacy_sale_cancellation']['user_id']
        );

        $workflowTrackingId = $workflowTrackingDetail['workflow']['workflow_tracking_id'];

        //Update Workflow ID
        $saleCancellationRecord = $this->saleCancellationObj->find($saleCancellation->id);

        $saleCancellationRecord->update(
            array(
                'workflow_tracking_id' => $workflowTrackingId,
                'updated_by' => Auth::id()
            )
        );

        //Insert Sales Cancellation Products
        collect($saleCancellationProducts)->each(function ($saleCancelProductData)
        use ($saleCancellation){

            $productClone = $saleCancelProductData['product_clone'];

            unset($saleCancelProductData['product_clone']);

            $saleCancelProductData['sale_cancellation_id'] = $saleCancellation->id;

            $saleCancelProduct = $this->legacySaleCancellationProductObj->create($saleCancelProductData);

            //Create Product Clone
            $productClone['legacy_sale_cancellation_product_id'] = $saleCancelProduct->id;

            $this->legacySaleCancellationProductCloneObj->create($productClone);
        });

        //Insert Sales Cancellation Kitting
        collect($saleCancellationKitting)->each(function ($saleCancelKittingData)
        use ($saleCancellation){

            $kittingProducts = $saleCancelKittingData['kitting_product'];

            unset($saleCancelKittingData['kitting_product']);

            //Create Kitting Clone
            $saleCancelKittingData['sale_cancellation_id'] = $saleCancellation->id;

            $saleCancelKitting = $this->legacySaleCancellationKittingCloneObj->create($saleCancelKittingData);

            //Create Cancel Kitting Product
            collect($kittingProducts)->each(function ($kittingProductData)
            use ($saleCancellation, $saleCancelKitting){

                $productClone = $kittingProductData['product_clone'];

                unset($kittingProductData['product_clone']);

                $kittingProductData['sale_cancellation_id'] = $saleCancellation->id;

                $kittingProductData['legacy_sales_cancellations_kitting_clone_id'] = $saleCancelKitting->id;

                $saleCancelKittingProduct = $this->legacySaleCancellationProductObj->create($kittingProductData);

                //Create Product Clone
                $productClone['legacy_sale_cancellation_product_id'] = $saleCancelKittingProduct->id;

                $this->legacySaleCancellationProductCloneObj->create($productClone);
            });
        });

        return $this->saleCancellationDetail($saleCancellation->id);
    }

    /**
     * sales cancellation refund by batch
     *
     * @param array $saleCancellationIds
     * @param string $remark
     * @return mixed
     */
    public function salesCancellationBatchRefund(array $saleCancellationIds, string $remark = "")
    {
        $workFlowResult = 'Completed';

        collect($saleCancellationIds)->each(function($saleCancellationId)
        use (&$workFlowResult, $remark){

            //get sale cancellation detail
            $cancellationDetail = $this->saleCancellationObj
                ->find($saleCancellationId);

            //get workflow detail
            $workflow = $this->workflowRepositoryObj
                ->getTrackingWorkflowDetails($cancellationDetail->workflow_tracking_id);

            $workflowCompleteStatus = $workflow['workflow']['completion_status'];

            if(!$workflowCompleteStatus){

                $currentStepId = $workflow['workflow']['current_step']['id'];

                $trackingStepInput = [
                    "trigger" => "processrefund"
                ];

                $stepResult = $this->workflowRepositoryObj->updateWorkflowTrackingStep(
                    $currentStepId,
                    $trackingStepInput,
                    $remark
                );

                $workFlowStepResult = $stepResult->toArray();

                if(!$workFlowStepResult['step_status']){

                    $workFlowResult = 'Partially Completed';

                    return false;
                }

            } else {

                $workFlowResult = 'Partially Completed';

                return false;
            }
        });

        return [
            'result' => $workFlowResult
        ];
    }

    /**
     * To download pdf and export as content-stream header 'application/pdf'
     *
     * @param int $creditNoteId
     * @param string $section
     * @return Collection|mixed
     * @throws \Mpdf\MpdfException
     */
    public function downloadCreditNote(int $creditNoteId, string $section = 'sales_cancellation')
    {
        //TODO clean up the bellow code
        $creditNote = $this->creditNoteObj->find($creditNoteId);

        if ($section == 'sales_exchange' || $creditNote->mapping_model == 'sales_exchanges')
        {
            if ($creditNote->saleExchange->is_legacy)
            {
                $html = $this->legacySalesExchangeHtml($creditNote);
            }
            else
            {
                $html = $this->salesExchangeHtml($creditNote);
            }
        }
        else
        {
            $saleCancellation = $this->saleCancellationObj->find($creditNote->mapping_id);
            if ($saleCancellation->is_legacy)
            {
                $html = $this->legacySalesCancellationHtml($creditNote);
            }
            else
            {
                $html = $this->salesCancellationHtml($creditNote);
            }
        }

        $config = [
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 0,
            'margin_bottom' => 0
        ];

        $mpdf = new PdfCreator($config);
        $mpdf->WriteHTML($html);
        $total = $mpdf->getTotalPage();
        $config['margin_bottom'] = 20;
        $mpdf = new PdfCreator($config);
        $html = str_replace('{nb}', $total, $html);
        $mpdf->WriteHTML($html);
        $outputPath = Config::get('filesystems.subpath.credit_note.storage_path');
        $absoluteUrlPath = Config::get('filesystems.subpath.credit_note.absolute_url_path');
        $fileName = $this->uploader->getRandomFileName('credit_note' . $creditNoteId) . '.pdf';

        $fileUrl = $this->uploader->createS3File($absoluteUrlPath . $fileName, $mpdf->Output($fileName, "S"), true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * generate sales Exchange html
     *
     * @param $creditNote
     * @return mixed
     */
    private function salesExchangeHtml($creditNote)
    {
        $user = $creditNote->sale->user;
        $sale = $creditNote->sale;
        if($sale->tax_rate)
        {
            $taxRate = (round($sale->tax_rate) == $sale->tax_rate)? round($sale->tax_rate) : $sale->tax_rate;
        }
        else
        {
            $taxRate = 0;
        }
        $saleExchange = $creditNote->saleExchange;
        $member = $user->member;
        $sponsorUser = $user->member->tree->parent->user;
        $cw = $sale->cw;
        $products = $saleExchange->saleExchangeProducts;
        $view = 'invoices.credit_note.sales_exchange.'.strtolower($creditNote->sale->country->code_iso_2);
        $basic = [
            'no' => $creditNote->credit_note_number,
            'date' => $creditNote->credit_note_date,
            'memberID' => $user->old_member_id,
            'name' => $user->name,
            'orderType' => $sale->channel? $sale->channel->name: "",
            'transactionNo' => $sale->document_number,
            'address' => $member->address? $this->memberAddress->getCorrespondenceAddress($member->address->address_data): "",
            'tel' => $user->mobile,
            'location' => $saleExchange->transactionLocation->name,
            'salesDate' => $sale->transaction_date,
            'cycle' => $saleExchange->cw->cw_name,
            'sponsorID' => $sponsorUser->old_member_id,
            'sponsorName' => $sponsorUser->name,
            'invoiceNo' => $saleExchange->parentSale->invoices->invoice_number,
            'taxNo' => '',
            'createdBy' => $saleExchange->issuer? $saleExchange->issuer->name : "",
            'businessStyle' => ''
        ];
        //sales products lines
        $salesProducts = array();
        //populate summary product lines
        $productsSummary = array();
        $lineCount = 1;
        $totalProductQty = $totalProductCv = $totalExcTax = $totalTax = $totalIncTax = 0;
        $kittings = [];
        foreach($products as $product)
        {
            if(!$product->return_quantity)
            {
                continue;
            }
            // summary format : ['HED30' => 1, 'HES300P' => 4]
            $productsSummary[$product->product->product->sku] = $product->return_quantity;
            $total = 0;

            if ($product->getMappedModel instanceof SaleExchangeKitting &&
                $product->getMappedModel->return_quantity == $product->getMappedModel->kitting->quantity &&
                in_array($product->mapping_id, $kittings))
            {
                continue;
            }

            if($product->getMappedModel instanceof SaleExchangeKitting)
            {
                if ($product->getMappedModel->return_quantity == $product->getMappedModel->kitting->quantity)
                {
                    if (!in_array($product->mapping_id, $kittings))
                    {
                        $kitting = $product->getMappedModel->kitting;

                        $total = $kitting->gmp_price_gst * $kitting->quantity;

                        $excludingTaxGmp = $kitting->nmp_price * $kitting->quantity;

                        $salesProducts[] = array(
                            'no' => $lineCount,
                            'tos' => '',
                            'code' => $kitting->code,
                            'description' => $kitting->name,
                            'qty' => $kitting->quantity,
                            'cv' => $kitting->eligible_cv * $kitting->quantity,
                            'unitPrice' => $kitting->nmp_price,
                            'subTotal' => $excludingTaxGmp,
                            'discount' => 0.00,
                            'excTax' => $excludingTaxGmp,
                            'tax' => $total - $excludingTaxGmp,
                            'total' => $total
                        );
                        $totalProductQty += $kitting->quantity;
                        $totalProductCv += $kitting->eligible_cv * $kitting->quantity;
                        $totalExcTax += $excludingTaxGmp;
                        $totalTax += $total - $excludingTaxGmp;
                        $totalIncTax += $total;

                        $lineCount++;

                        array_push($kittings, $product->mapping_id);
                    }
                    continue;
                }
                else if ($product->snapshot_quantity == $product->return_quantity)
                { //full cancellation
                    $total = $product->product->total * $product->kitting_quantity;
                    $unitTax = 0.00;
                    $tax = 0.00;
                } else {
                    $total = $product->product->average_price_unit * $product->return_quantity;
                    $unitTax = $product->product->average_price_unit * $taxRate/(100+$taxRate);
                    $tax = $unitTax * $product->return_quantity;
                }
                $unitPrice = $product->average_price_unit;
                $subTotal = $total;
                $excludingTaxGmp = $total - $tax;
            }else{
                $total = $product->gmp_price_gst * $product->return_quantity;
                $unitPrice = $product->nmp_price;
                $unitTax = $product->gmp_price_gst - $product->nmp_price;
                $subTotal = $product->gmp_price_gst * $product->return_quantity;
                $excludingTaxGmp = $product->nmp_price * $product->return_quantity;
                $tax = $total - $excludingTaxGmp;
            }
            $salesProducts[] = array(
                'no' => $lineCount,
                'tos' => '',
                'code' => $product->product->product->sku,
                'description' => $product->product->product->name,
                'qty' => $product->return_quantity,
                'cv' => $product->product->base_cv * $product->return_quantity,
                'unitPrice' => $unitPrice,
                'subTotal' => $subTotal,
                'discount' => 0.00,
                'excTax' => $excludingTaxGmp,
                'tax' => $tax,
                'total' => $total
            );
            $totalProductQty += $product->return_quantity;
            $totalProductCv += $product->product->base_cv * $product->return_quantity;
            $totalExcTax += $excludingTaxGmp;
            $totalTax += $total - $excludingTaxGmp;
            $totalIncTax += $total;
            $lineCount++;
        }

        $reason = MasterData::find($saleExchange->reason_id);

        $addressArray = $this->memberAddress->toArray($member->address->address_data, "Correspondence");

        $summary = array(
            'items' => $productsSummary
        );
        //TODO: delivery, admin and other fee is currently unavailable
        $sales = array(
            'products' => $salesProducts,
            'subTotal' => [
                'qty' => $totalProductQty,
                'cv' =>  $totalProductCv,
                'excTax' => $totalExcTax,
                'tax' => $totalTax,
                'total' =>$totalIncTax,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ],
            'delivery' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'admin' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'other' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'total' => [
                'excTax' => $totalExcTax,
                'tax' => $totalTax,
                'total' => $totalIncTax,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ]
        );
        return \View::make($view)
            ->with('basic', $basic)
            ->with('remarks', $saleExchange->remarks)
            ->with('reason', $reason->title)
            ->with('summary', $summary)
            ->with('sales', $sales)
            ->with('taxRate', $taxRate)
            ->with('addressArray', $addressArray)
            ->render();
    }

    /**
     * generate legacy sales Exchange html
     *
     * @param $creditNote
     * @return mixed
     */
    private function legacySalesExchangeHtml($creditNote)
    {
        $saleExchange = $creditNote->saleExchange;
        $user = $saleExchange->user;
        $sale = $saleExchange->sale;
        if($sale->tax_rate)
        {
            $taxRate = (round($sale->tax_rate) == $sale->tax_rate)? round($sale->tax_rate) : $sale->tax_rate;
        }
        else
        {
            $taxRate = "0";
        }
        $member = $user->member;
        $sponsorUser = $user->member->tree->parent->user;
        $cw = $sale->cw;
        $products = $saleExchange->legacySaleExchangeReturnProduct;
        $view = 'invoices.credit_note.sales_exchange.'.strtolower($sale->country->code_iso_2);
        $basic = [
            'no' => $creditNote->credit_note_number,
            'date' => $creditNote->credit_note_date,
            'memberID' => $user->old_member_id,
            'name' => $user->name,
            'orderType' => $sale->channel? $sale->channel->name: "",
            'transactionNo' => $sale->document_number,
            'address' => $member->address? $this->memberAddress->getCorrespondenceAddress($member->address->address_data): "",
            'tel' => $user->mobile,
            'location' => $saleExchange->transactionLocation->name,
            'salesDate' => $sale->transaction_date,
            'cycle' => $saleExchange->cw->cw_name,
            'sponsorID' => $sponsorUser->old_member_id,
            'sponsorName' => $sponsorUser->name,
            'invoiceNo' => $saleExchange->legacyInvoice->invoice_number,
            'taxNo' => '',
            'createdBy' => $saleExchange->issuer? $saleExchange->issuer->name : "",
            'businessStyle' => ''
        ];
        //sales products lines
        $salesProducts = array();
        //populate summary product lines
        $productsSummary = array();
        $lineCount = 1;
        $totalProductQty = $totalProductCv = $totalExcTax = $totalTax = $totalIncTax = 0;
        foreach($products as $product)
        {
            if(!$product->return_quantity)
            {
                continue;
            }
            // summary format : ['HED30' => 1, 'HES300P' => 4]
            $productsSummary[$product->legacySaleExchangeProductClone->sku] = $product->return_quantity;
            $total = 0;

            if($product->legacy_sale_exchange_kitting_clone_id){
                $total = $product->return_total;
                $unitPrice = $product->average_price_unit;
                $unitTax = 0.00;
                $subTotal = $total;
                $excludingTaxGmp = $total;
                $tax = 0.00;
            }else{
                $total = $product->return_total;
                $unitPrice = $product->nmp_price;
                $unitTax = $product->gmp_price_gst - $product->nmp_price;
                $subTotal = $product->gmp_price_gst * $product->return_quantity;
                $excludingTaxGmp = $product->nmp_price * $product->return_quantity;
                $tax = $total - $excludingTaxGmp;
            }
            $salesProducts[] = array(
                'no' => $lineCount,
                'tos' => '',
                'code' => $product->legacySaleExchangeProductClone->sku,
                'description' => $product->legacySaleExchangeProductClone->name,
                'qty' => $product->return_quantity,
                'cv' => 0,
                'unitPrice' => $unitPrice,
                'subTotal' => $subTotal,
                'discount' => 0.00,
                'excTax' => $excludingTaxGmp,
                'tax' => $tax,
                'total' => $total
            );
            $totalProductQty += $product->return_quantity;
            $totalExcTax += $excludingTaxGmp;
            $totalTax += $total - $excludingTaxGmp;
            $totalIncTax += $total;
            $lineCount++;
        }

        $reason = MasterData::find($saleExchange->reason_id);

        $addressArray = $this->memberAddress->toArray($member->address->address_data, "Correspondence");

        $summary = array(
            'items' => $productsSummary
        );
        //TODO: delivery, admin and other fee is currently unavailable
        $sales = array(
            'products' => $salesProducts,
            'subTotal' => [
                'qty' => $totalProductQty,
                'cv' =>  $totalProductCv,
                'excTax' => $totalExcTax,
                'tax' => $totalTax,
                'total' =>$totalIncTax,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ],
            'delivery' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'admin' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'other' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'total' => [
                'excTax' => $totalExcTax,
                'tax' => $totalTax,
                'total' => $totalIncTax,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ]
        );
        return \View::make($view)
            ->with('basic', $basic)
            ->with('remarks', $saleExchange->remarks)
            ->with('reason', $reason->title)
            ->with('summary', $summary)
            ->with('sales', $sales)
            ->with('taxRate', $taxRate)
            ->with('addressArray', $addressArray)
            ->render();
    }

    /**
     * generate cancellation html
     *
     * @param $creditNote
     * @return mixed
     */
    private function salesCancellationHtml($creditNote)
    {
        $user = $creditNote->sale->user;
        $sale = $creditNote->sale;
        if($sale->tax_rate)
        {
            $taxRate = (round($sale->tax_rate) == $sale->tax_rate)? round($sale->tax_rate) : $sale->tax_rate;
        }
        else
        {
            $taxRate = "0";
        }
        $saleCancellation = $this->saleCancellationObj->find($creditNote->mapping_id);
        $member = $user->member;
        $sponsorUser = $user->member->tree->parent->user;
        $cw = $sale->cw;

        $products = $saleCancellation->saleCancelProducts;
        $view = 'invoices.credit_note.sales_cancellation.'.strtolower($creditNote->sale->country->code_iso_2);
        $basic = [
            'no' => $creditNote->credit_note_number,
            'date' => $creditNote->credit_note_date,
            'memberID' => $user->old_member_id,
            'name' => $user->name,
            'orderType' => $sale->channel? $sale->channel->name: "",
            'transactionNo' => $sale->document_number,
            'address' => $member->address? $this->memberAddress->getCorrespondenceAddress($member->address->address_data): "",
            'tel' => $user->mobile,
            'location' => $saleCancellation->transactionLocation->name,
            'salesDate' => $sale->transaction_date,
            'cycle' => $saleCancellation->cw->cw_name,
            'sponsorID' => $sponsorUser->old_member_id,
            'sponsorName' => $sponsorUser->name,
            'invoiceNo' => $saleCancellation->invoice->invoice_number,
            'taxNo' => '',
            'businessStyle' => '',
            'createdBy' => $saleCancellation->issuer? $saleCancellation->issuer->name : ""
        ];
        $sales = array();
        //sales products lines
        $salesProducts = array();
        //populate summary product lines
        $productsSummary = array();
        $lineCount = 1;
        $totalProductQty = $totalProductCv = $totalExcTax = $totalTax = $totalIncTax = 0;
        $kittings = array();

        foreach($products as $product){
            //if this product quantity is 0, continue to the next one because we are not showing those with 0
            if(!$product->quantity){
                continue;
            }
            // summary format : ['HED30' => 1, 'HES300P' => 4]
            $productsSummary[$product->saleProduct->product->sku] = isset($productsSummary[$product->saleProduct->product->sku])
                ? $product->quantity + $productsSummary[$product->saleProduct->product->sku]
                : $product->quantity;

            $total = 0;

            if ($product->kitting_quantity == $product->available_kitting_quantity_snapshot &&
                $product->saleProduct->getMappedModel instanceof SaleKittingClone &&
                in_array($product->saleProduct->mapping_id, $kittings))
            {
                continue;
            }

            if($product->saleProduct->getMappedModel instanceof SaleKittingClone)
            {
                if ($product->kitting_quantity == $product->available_kitting_quantity_snapshot)
                {
                    if (!in_array($product->saleProduct->mapping_id, $kittings))
                    {
                        $kitting = $product->saleProduct->getMappedModel;

                        $total = $kitting->gmp_price_gst * $kitting->quantity;

                        $excludingTaxGmp = $kitting->nmp_price * $kitting->quantity;

                        $salesProducts[] = array(
                            'no' => $lineCount,
                            'tos' => '',
                            'code' => $kitting->code,
                            'description' => $kitting->name,
                            'qty' => $kitting->quantity,
                            'cv' => $kitting->eligible_cv * $kitting->quantity,
                            'unitPrice' => $kitting->nmp_price,
                            'subTotal' => $excludingTaxGmp,
                            'discount' => 0.00,
                            'excTax' => $excludingTaxGmp,
                            'tax' => $total - $excludingTaxGmp,
                            'total' => $total
                        );
                        $totalProductQty += $kitting->quantity;
                        $totalProductCv += $kitting->eligible_cv * $kitting->quantity;
                        $totalExcTax += $excludingTaxGmp;
                        $totalTax += $total - $excludingTaxGmp;
                        $totalIncTax += $total;

                        $lineCount++;

                        array_push($kittings, $product->saleProduct->mapping_id);
                    }
                    continue;
                }
                else if ($product->available_quantity_snapshot == $product->quantity) //full cancellation
                {
                    $total = $product->saleProduct->total * $product->available_kitting_quantity_snapshot;
                } else {
                    $total = $product->saleProduct->average_price_unit * $product->quantity;
                }
            }
            else
            {
                $total = $product->saleProduct->gmp_price_gst * $product->quantity;
            }

            $excludingTaxGmp = $product->saleProduct->nmp_price * $product->quantity;

            $salesProducts[] = array(
                'no' => $lineCount,
                'tos' => '',
                'code' => $product->saleProduct->product->sku,
                'description' => $product->saleProduct->product->name,
                'qty' => $product->quantity,
                'cv' => $product->saleProduct->eligible_cv * $product->quantity,
                'unitPrice' => $product->saleProduct->nmp_price,
                'subTotal' => $product->saleProduct->nmp_price * $product->quantity,
                'discount' => 0.00,
                'excTax' => $excludingTaxGmp,
                'tax' => $total - $excludingTaxGmp,
                'total' => $total
            );
            $totalProductQty += $product->quantity;
            $totalProductCv += $product->eligible_cv * $product->quantity;
            $totalExcTax += $excludingTaxGmp;
            $totalTax += $total - $excludingTaxGmp;
            $totalIncTax += $total;
            $lineCount++;
        }

        $summary = array(
            'items' => $productsSummary
        );
        $reason = MasterData::find($saleCancellation->cancellation_reason_id);

        $addressArray = [];

        if (strtolower($creditNote->sale->country->code_iso_2) == "tw")
        {
            $addressArray = $this->memberAddress->toArray($member->address->address_data, "Correspondence");

            if (count($addressArray)<=8)
            {
                $addressArray = [];
            }
        }
        

        //TODO: delivery, admin and other fee is currently unavailable
        $sales = array(
            'products' => $salesProducts,
            'subTotal' => [
                'qty' => $totalProductQty,
                'cv' =>  $totalProductCv,
                'excTax' => $totalExcTax,
                'tax' => $totalTax,
                'total' =>$totalIncTax,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ],
            'delivery' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'admin' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'other' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'total' => [
                'excTax' => $totalExcTax,
                'tax' => $totalTax,
                'total' => $totalIncTax,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ]
        );
        return \View::make($view)
            ->with('basic', $basic)
            ->with('remarks', $saleCancellation->remarks)
            ->with('reason', $reason->title)
            ->with('summary', $summary)
            ->with('sales', $sales)
            ->with('taxRate', $taxRate)
            ->with('addressArray', $addressArray)
            ->render();
    }

    /**
     * generate legacy sales cancellation html
     *
     * @param $creditNote
     * @return mixed
     */
    private function legacySalesCancellationHtml($creditNote)
    {
        $saleCancellation = $this->saleCancellationObj->find($creditNote->mapping_id);
        $user = $saleCancellation->user;
        $member = $user->member;
        $sponsorUser = $user->member->tree->parent->user;
        $cw = $saleCancellation->legacyInvoice->cw;
        
        $view = 'invoices.credit_note.sales_cancellation.'.strtolower($saleCancellation->legacyInvoice->country->code_iso_2);
        $basic = [
            'no' => $creditNote->credit_note_number,
            'date' => $creditNote->credit_note_date,
            'memberID' => $user->old_member_id,
            'name' => $user->name,
            'orderType' => "",
            'transactionNo' => '',
            'address' => $member->address? $this->memberAddress->getCorrespondenceAddress($member->address->address_data): "",
            'tel' => $user->mobile,
            'location' => $saleCancellation->transactionLocation->name,
            'salesDate' => $saleCancellation->legacyInvoice->transaction_date,
            'cycle' => $cw->cw_name,
            'sponsorID' => $sponsorUser->old_member_id,
            'sponsorName' => $sponsorUser->name,
            'invoiceNo' => $saleCancellation->legacyInvoice->invoice_number,
            'taxNo' => '',
            'businessStyle' => '',
            'createdBy' => $saleCancellation->issuer? $saleCancellation->issuer->name : ""
        ];
        $sales = array();
        //sales products lines
        $salesProducts = array();
        //populate summary product lines
        $productsSummary = array();
        $lineCount = 1;
        $totalProductQty = $totalProductCv = $totalExcTax = $totalTax = $totalIncTax = 0;

        $products = $saleCancellation->legacySaleCancellationProduct;

        foreach($products as $product){
            //if this product quantity is 0, continue to the next one because we are not showing those with 0
            if(!$product->quantity){
                continue;
            }
            // summary format : ['HED30' => 1, 'HES300P' => 4]
            $productsSummary[$product->legacySaleCancellationProductClone->sku] = isset($productsSummary[$product->legacySaleCancellationProductClone->sku])
                ? $product->quantity + $productsSummary[$product->legacySaleCancellationProductClone->sku]
                : $product->quantity;
            $total = 0;
            $excludingTaxGmp = $product->nmp_price * $product->quantity;
            if($product->legacy_sales_cancellations_kitting_clone_id){
                if ($product->available_quantity_snapshot == $product->quantity){ //full cancellation
                    $total = $product->$total;
                } else {
                    $total = $product->average_price_unit * $product->quantity;
                }
            }else{
                $total = $product->gmp_price_gst * $product->quantity;
            }
            $salesProducts[] = array(
                'no' => $lineCount,
                'tos' => '',
                'code' => $product->legacySaleCancellationProductClone->sku,
                'description' => $product->legacySaleCancellationProductClone->name,
                'qty' => $product->quantity,
                'cv' => $product->product_cv,
                'unitPrice' => $product->nmp_price,
                'subTotal' => $product->nmp_price * $product->quantity,
                'discount' => 0.00,
                'excTax' => $excludingTaxGmp,
                'tax' => $total - $excludingTaxGmp,
                'total' => $total
            );
            $totalProductQty += $product->quantity;
            $totalProductCv += $product->product_cv;
            $totalExcTax += $excludingTaxGmp;
            $totalTax += $total - $excludingTaxGmp;
            $totalIncTax += $total;
            $lineCount++;
        }

        $kittings = $saleCancellation->legacySaleCancellationKitting;
        
        foreach($kittings as $kitting){
            //if this product quantity is 0, continue to the next one because we are not showing those with 0
            if(!$kitting->quantity){
                continue;
            }
            // summary format : ['HED30' => 1, 'HES300P' => 4]
            foreach ($kitting->products as $product) {
                $productsSummary[$product->legacySaleCancellationProductClone->sku] = isset($productsSummary[$product->legacySaleCancellationProductClone->sku])
                ? $product->quantity + $productsSummary[$product->legacySaleCancellationProductClone->sku]
                : $product->quantity;
            }
            
            $total = 0;
            $excludingTaxGmp = $kitting->nmp_price * $kitting->quantity;
            if ($kitting->available_quantity_snapshot == $kitting->quantity){ //full cancellation
                $total = $kitting->total;
            } else {
                $total = $kitting->total;//$kitting->average_price_unit * $kitting->quantity;
            }
            $salesProducts[] = array(
                'no' => $lineCount,
                'tos' => '',
                'code' => $kitting->code,
                'description' => $kitting->name,
                'qty' => $kitting->quantity,
                'cv' => $kitting->product_cv,
                'unitPrice' => $kitting->nmp_price,
                'subTotal' => $kitting->nmp_price * $kitting->quantity,
                'discount' => 0.00,
                'excTax' => $excludingTaxGmp,
                'tax' => $total - $excludingTaxGmp,
                'total' => $total
            );
            $totalProductQty += $kitting->quantity;
            $totalProductCv += $kitting->product_cv;
            $totalExcTax += $excludingTaxGmp;
            $totalTax += $total - $excludingTaxGmp;
            $totalIncTax += $total;
            $lineCount++;
        }

        $summary = array(
            'items' => $productsSummary
        );
        $reason = MasterData::find($saleCancellation->cancellation_reason_id);

        $addressArray = $this->memberAddress->toArray($member->address->address_data, "Correspondence");

        //TODO: delivery, admin and other fee is currently unavailable
        $sales = array(
            'products' => $salesProducts,
            'subTotal' => [
                'qty' => $totalProductQty,
                'cv' =>  $totalProductCv,
                'excTax' => $totalExcTax,
                'tax' => $totalTax,
                'total' =>$totalIncTax,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ],
            'delivery' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'admin' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'other' => [
                'excTax' => 0.00,
                'tax' => 0.00,
                'total' => 0.00
            ],
            'total' => [
                'excTax' => $totalExcTax,
                'tax' => $totalTax,
                'total' => $totalIncTax,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ]
        );
        
        return \View::make($view)
            ->with('basic', $basic)
            ->with('remarks', $saleCancellation->remarks)
            ->with('reason', $reason->title)
            ->with('summary', $summary)
            ->with('sales', $sales)
            ->with('addressArray', $addressArray)
            ->with('taxRate', "0")
            ->render();
    }

    /**
     * create new sales order (express edition)
     *
     * @param array $inputs
     * @return mixed
     */
    public function createSaleExpress(array $inputs)
    {
        $cwSchedule = $this->cwSchedulesRepositoryObj->getCwSchedulesList("current", [
            'limit' => 0,
            'sort' => 'id',
            'order' => 'asc'
        ])->get('data');

        $inputs['sales_data']['cw_id'] = $cwSchedule[0]->id;

        $products = $inputs['sales_data']['products'];

        foreach ($products as $key => $product)
        {
            $inputs['sales_data']['products'][$key]['product_id'] = $this->productObj->where("sku", $product['product_sku'])->first()->id;
        }

        $shipping = $inputs['sales_data']['selected']['shipping'];

        $masterData = $this->masterRepositoryObj->getMasterDataByKey(['sale_delivery_method']);

        $masterData['sale_delivery_method']->transform(function($item, $key){
            $item['title'] = strtolower($item['title']);
            return $item;
        });

        $inputs['sales_data']['selected']['shipping']['sale_delivery_method'] = $masterData['sale_delivery_method']->where( 'title', strtolower($shipping['sale_delivery_method']) )->pluck('id')[0];

        $inputs['sales_data'] = $this->eligibleSalesPromo(
            $inputs['sales_data']['downline_member_id'],
            $inputs['sales_data']['country_id'],
            $inputs['sales_data']['location_id'],
            $inputs['sales_data']['products'],
            [],
            $inputs['sales_data']
        );

        return $this->createSale($inputs, true);
    }

    /**
     * get non stockist sale payment
     *
     * @return mixed
     */
    public function getYonyouIntegrationNonStockistSalePayment()
    {
        $stockistLocationTypeCode = config('mappings.locations_types.stockist');

        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $data = $this->paymentObj
            ->join('sales', function ($join) {
                $join->on('sales.id', '=', 'payments.mapping_id')
                    ->where('payments.mapping_model', 'sales');
            })
            ->join('locations_types', function ($join) {
                $join->on('locations_types.id', '=', 'sales.channel_id');
            })
            ->where('payments.yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('locations_types.code', '!=', $stockistLocationTypeCode)
            ->whereIn('sales.country_id', $countryIdArray)
            ->where('payments.status', 1)
            ->select('payments.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get stockist sale payment (exclude payment that need verification)
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistSalePayment()
    {
        $stockistLocationTypeCode = config('mappings.locations_types.stockist');

        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $data = $this->paymentObj
            ->join('sales', function ($join) {
                $join->on('sales.id', '=', 'payments.mapping_id')
                    ->where('payments.mapping_model', 'sales');
            })
            ->join('locations_types', function ($join) {
                $join->on('locations_types.id', '=', 'sales.channel_id');
            })
            ->join('payments_modes_providers', function ($join) {
                $join->on('payments_modes_providers.id', '=', 'payments.payment_mode_provider_id');
            })
            ->where('payments.yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('locations_types.code', $stockistLocationTypeCode)
            ->where('payments_modes_providers.is_stockist_payment_verification', 0)
            ->whereIn('sales.country_id', $countryIdArray)
            ->where('payments.status', 1)
            ->select('payments.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get non stockist pre-order refund
     *
     * @return mixed
     */
    public function getYonyouIntegrationNonStockistPreOrderRefund()
    {
        $stockistLocationTypeCode = config('mappings.locations_types.stockist');

        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_order_status'
            )
        );

        $saleOrderStatus = array_change_key_case(
            $masterSettingsDatas['sale_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $voidStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['void']];

        $rejectedStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['rejected']];

        $data = $this->paymentObj
            ->join('sales', function ($join) {
                $join->on('sales.id', '=', 'payments.mapping_id')
                    ->where('payments.mapping_model', 'sales');
            })
            ->join('locations_types', function ($join) {
                $join->on('locations_types.id', '=', 'sales.channel_id');
            })
            ->leftJoin('invoices', function ($join) {
                $join->on('invoices.sale_id', '=', 'sales.id');
            })
            ->where('payments.yy_refund_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('locations_types.code', '!=', $stockistLocationTypeCode)
            ->whereIn('sales.country_id', $countryIdArray)
            ->where('payments.status', 1)
            ->whereIn('sales.order_status_id', [$voidStatusId, $rejectedStatusId])
            ->select('payments.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get stockist pre-order refund (exclude stokist sales payment that need verification)
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistPreOrderRefund()
    {
        $stockistLocationTypeCode = config('mappings.locations_types.stockist');

        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_order_status'
            )
        );

        $saleOrderStatus = array_change_key_case(
            $masterSettingsDatas['sale_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $voidStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['void']];

        $rejectedStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['rejected']];

        $data = $this->paymentObj
            ->join('sales', function ($join) {
                $join->on('sales.id', '=', 'payments.mapping_id')
                    ->where('payments.mapping_model', 'sales');
            })
            ->join('locations_types', function ($join) {
                $join->on('locations_types.id', '=', 'sales.channel_id');
            })
            ->join('payments_modes_providers', function ($join) {
                $join->on('payments_modes_providers.id', '=', 'payments.payment_mode_provider_id');
            })
            ->where('payments.yy_refund_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('locations_types.code', $stockistLocationTypeCode)
            ->where('payments_modes_providers.is_stockist_payment_verification', 0)
            ->whereIn('sales.country_id', $countryIdArray)
            ->where('payments.status', 1)
            ->whereIn('sales.order_status_id', [$voidStatusId, $rejectedStatusId])
            ->select('payments.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get non stockist sales for yonyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationNonStockistSales()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_order_status'
            )
        );

        $saleOrderStatus = array_change_key_case(
            $masterSettingsDatas['sale_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $completeStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];
        
        $cancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['cancelled']];
        
        $partiallyCancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['partially-cancelled']];

        $data = $this->modelObj
            ->join('invoices', function ($join) {
                $join->on('invoices.sale_id', '=', 'sales.id');
            })
            ->join('locations_types', function ($join) {
                $join->on('locations_types.id', '=', 'sales.channel_id');
            })
            ->where('sales.yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->whereIn('sales.order_status_id', [$completeStatusId, $cancelledStatusId, $partiallyCancelledStatusId])
            ->whereIn('sales.country_id', $countryIdArray)
            ->where('locations_types.code','<>', config('mappings.locations_types.stockist'))
            ->where('sales.is_product_exchange', 0)
            ->select('sales.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get stockist sales for yonyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistSales()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_order_status'
            )
        );

        $saleOrderStatus = array_change_key_case(
            $masterSettingsDatas['sale_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $completeStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];
        
        $cancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['cancelled']];
        
        $partiallyCancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['partially-cancelled']];

        $data = $this->modelObj
            ->join('locations_types', function ($join) {
                $join->on('locations_types.id', '=', 'sales.channel_id');
            })
            ->where('sales.yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->whereIn('sales.order_status_id', [$completeStatusId, $cancelledStatusId, $partiallyCancelledStatusId])
            ->whereIn('sales.country_id', $countryIdArray)
            ->where('locations_types.code', config('mappings.locations_types.stockist'))
            ->select('sales.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get stockist sales that ready to release stock
     *
     * @return mixed
     */
    public function getYonyouIntegrationSalesUpdate()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_order_status',
                'stockist_daily_transaction_release_status'
            )
        );

        $saleOrderStatus = array_change_key_case(
            $masterSettingsDatas['sale_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $transactionReleaseStatus = array_change_key_case(
            $masterSettingsDatas['stockist_daily_transaction_release_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $completeStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];
        
        $releasedStatusId = $transactionReleaseStatus[$this->stockistDailyTransactionReleaseStatusConfigCodes['released']];
            
        $data = $this->invoiceObj
            ->join('sales', function ($join) {
                $join->on('invoices.sale_id', '=', 'sales.id');
            })
            ->where('invoices.yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('invoices.stockist_daily_transaction_status_id', $releasedStatusId)
            ->where('sales.yy_integration_status', config('integrations.yonyou.yy_integration_status.success'))
            ->where('sales.is_rental_sale_order', 0) //exclude rental sales
            ->whereIn('sales.order_status_id', [$completeStatusId])
            ->whereIn('sales.country_id', $countryIdArray)
            ->select('invoices.id', 'invoices.sale_id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get rental sales that ready to release stock
     *
     * @return mixed
     */
    public function getYonyouIntegrationSalesRentalUpdate()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();
        
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_order_status'
            )
        );

        $saleOrderStatus = array_change_key_case(
            $masterSettingsDatas['sale_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $completeStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];
    
        $data = $this->modelObj
            ->where('yy_update_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('yy_integration_status', config('integrations.yonyou.yy_integration_status.success'))
            ->whereIn('order_status_id', [$completeStatusId])
            ->where('is_rental_sale_order', 1)
            ->where('rental_release', 1)
            ->whereIn('country_id', $countryIdArray)
            ->select('id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get non stockist sales payment for receipt integration
     *
     * @return Payment
     */
    public function getYonyouIntegrationNonStockistSaleReceipt()
    {
        $stockistLocationTypeCode = config('mappings.locations_types.stockist');

        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_order_status'
            )
        );

        $saleOrderStatus = array_change_key_case(
            $masterSettingsDatas['sale_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $completeStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];
        
        $cancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['cancelled']];
        
        $partiallyCancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['partially-cancelled']];
    
        $data = $this->paymentObj
            ->join('sales', function ($join) {
                $join->on('payments.mapping_id', '=', 'sales.id')
                    ->where('payments.mapping_model', 'sales');
            })
            ->join('locations_types', function ($join) {
                $join->on('locations_types.id', '=', 'sales.channel_id');
            })
            ->where('sales.yy_receipt_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('locations_types.code', '!=', $stockistLocationTypeCode)
            ->whereIn('sales.order_status_id', [$completeStatusId, $cancelledStatusId, $partiallyCancelledStatusId])
            ->whereIn('sales.country_id', $countryIdArray)
            ->where('payments.status', 1)
            ->select('sales.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get sales exchanges (integrate revised sales order)
     *
     * @return mixed
     */
    public function getYonyouIntegrationSaleExchangeInvoice()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_order_status'
            )
        );

        $saleOrderStatus = array_change_key_case(
            $masterSettingsDatas['sale_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $completeStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];
        
        $cancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['cancelled']];
        
        $partiallyCancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['partially-cancelled']];

        $data = $this->saleExchangeObj
            ->join('sales', function ($join) {
                $join->on('sales_exchanges.sale_id', '=', 'sales.id');
            })
            ->join('invoices', function ($join) {
                $join->on('invoices.sale_id', '=', 'sales.id');
            })
            ->where('sales.yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->whereIn('sales_exchanges.order_status_id', [$completeStatusId, $cancelledStatusId, $partiallyCancelledStatusId])
            ->whereIn('sales.order_status_id', [$completeStatusId, $cancelledStatusId, $partiallyCancelledStatusId])
            ->whereIn('sales.country_id', $countryIdArray)
            ->select('sales_exchanges.sale_id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get sales exchanges (cancel old sales order)
     *
     * @return mixed
     */
    public function getYonyouIntegrationSaleExchangeCreditNote()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_order_status'
            )
        );

        $saleOrderStatus = array_change_key_case(
            $masterSettingsDatas['sale_order_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $completeStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']];
        
        $cancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['cancelled']];
        
        $partiallyCancelledStatusId = $saleOrderStatus[$this->saleOrderStatusConfigCodes['partially-cancelled']];

        $data = $this->saleExchangeObj
            ->join('sales', function ($join) {
                $join->on('sales_exchanges.sale_id', '=', 'sales.id');
            })
            ->join('invoices', function ($join) {
                $join->on('invoices.sale_id', '=', 'sales.id');
            })
            ->where('sales_exchanges.yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->whereIn('sales_exchanges.order_status_id', [$completeStatusId, $cancelledStatusId, $partiallyCancelledStatusId])
            ->whereIn('sales.order_status_id', [$completeStatusId, $cancelledStatusId, $partiallyCancelledStatusId])
            ->whereIn('sales.country_id', $countryIdArray)
            ->select('sales_exchanges.id')
            ->distinct()
            ->get();
            
        return $data;
    }

    /**
     * get sales cancellation for yonyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationSalesCancellation()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_cancellation_status'
            )
        );

        $saleCancellationStatus = array_change_key_case(
            $masterSettingsDatas['sale_cancellation_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $completeStatusId = $saleCancellationStatus[$this->saleCancellationStatusConfigCodes['completed']];
    
        $data = $this->saleCancellationObj
            ->join('credit_notes', function ($join) {
                $join->on('sales_cancellations.id', '=', 'credit_notes.mapping_id')
                    ->where('credit_notes.mapping_model', 'sales_cancellations');
            })
            ->join('sales', function ($join) {
                $join->on('sales.id', '=', 'sales_cancellations.sale_id');
            })
            ->where('sales_cancellations.yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->whereIn('sales_cancellations.cancellation_status_id', [$completeStatusId])
            ->whereIn('sales.country_id', $countryIdArray)
            ->select('sales_cancellations.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get sales cancellation for refund
     *
     * @return mixed
     */
    public function getYonyouIntegrationSalesCancellationEWallet()
    {
        $countryIdArray = $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'sale_cancellation_status'
            )
        );

        $saleCancellationStatus = array_change_key_case(
            $masterSettingsDatas['sale_cancellation_status']
                ->pluck('id', 'title')
                ->toArray()
        );

        $completeStatusId = $saleCancellationStatus[$this->saleCancellationStatusConfigCodes['completed']];
    
        $data = $this->saleCancellationObj
            ->join('credit_notes', function ($join) {
                $join->on('sales_cancellations.id', '=', 'credit_notes.mapping_id')
                    ->where('credit_notes.mapping_model', 'sales_cancellations');
            })
            ->join('sales', function ($join) {
                $join->on('sales.id', '=', 'sales_cancellations.sale_id');
            })
            ->where('sales_cancellations.yy_receipt_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->whereIn('sales_cancellations.cancellation_status_id', [$completeStatusId])
            ->whereIn('sales.country_id', $countryIdArray)
            ->select('sales_cancellations.id')
            ->distinct()
            ->get();
            
        return $data;
    }

    /**
     * download sales daily transaction report by below parameter
     *
     * @param int $countryId
     * @param array $locations
     * @param string $dateFrom
     * @param string $dateTo
     * @param array $userIds
     * @return \Illuminate\Support\Collection|mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadSalesDailyTransactionReport(
        int $countryId = 0,
        array $locations = array(),
        $dateFrom = '',
        $dateTo = '',
        array $userIds = array()
    )
    {
        $paymentModeSetting = $this->paymentModeSettingObj
            ->leftJoin('payments_modes_providers', 'payments_modes_providers.id', '=', 'payments_modes_settings.payment_mode_provider_id')
            ->where('country_id', '=', $countryId)
            ->where('active', '=', 1)
            ->distinct()
            ->get(['payments_modes_providers.name']);

        $paymentMode = $paymentModeSetting->pluck('name')->all();

        $sales = $this->modelObj
            ->selectRaw("sales.*, invoices.invoice_number, master_data.title as 'release_status'")
            ->leftJoin('sales_exchanges', 'sales.id', '=', 'sales_exchanges.sale_id')
            ->leftJoin('invoices', 'sales.id', '=', 'invoices.sale_id')
            ->leftJoin('master_data', 'invoices.stockist_daily_transaction_status_id', '=', 'master_data.id')
            ->where('sales.country_id', $countryId)
            ->whereRaw('(sales.transaction_date between ? and ?
                        or sales_exchanges.transaction_date between ? and ?)', 
                        [$dateFrom, $dateTo, $dateFrom, $dateTo])
            ->whereRaw('(sales.transaction_date between ? and ?)', [$dateFrom, $dateTo])
            ->with(['transactionLocation', 'createdBy', 'salePayments']);

        $saleCancellations = $this->saleCancellationObj
            ->selectRaw("sales_cancellations.*")
            ->join('sales', 'sales.id', '=', 'sales_cancellations.sale_id')
            ->where('sales.country_id', $countryId)
            ->whereRaw('(sales_cancellations.transaction_date between ? and ?)', [$dateFrom, $dateTo]);

        if(!empty($locations)) {

            $sales = $sales->whereIn('sales.transaction_location_id', $locations);

            $saleCancellations = $saleCancellations->whereIn('sales_cancellations.transaction_location_id', $locations);
        }

        if(!empty($userIds)){

            $sales = $sales->whereIn('sales.created_by', $userIds);

            $saleCancellations = $saleCancellations->whereIn('sales_cancellations.created_by', $userIds);
        }

        $sales = $sales->get();

        $saleCancellations = $saleCancellations->get();

        $list = [];

        foreach ($sales as $sale){

            $row = [];
            $row['locationCode'] = $sale->transactionLocation->code;
            $row['name'] = $sale->createdBy ? $sale->createdBy->name : '';
            $row['transaction_date'] = $sale->transaction_date;
            $row['saleOrderNo'] = $sale->document_number;
            $row['invoice_number'] = $sale->invoice_number;
            $row['release_status'] = $sale->release_status;
            $row['oriTaxInv'] = "";
            $row['oriTaxInvDate'] = "";
            $row['iboId'] = $sale->user->old_member_id;
            $row['iboName'] = $sale->user->name;
            $row['creditNote'] = "";

            foreach ($paymentMode as $mode) {
                $row[$mode] = 0;
            }

            $total = 0;

            foreach ($sale->salePayments as $payment) {

                if ($payment->status != 1) {
                    continue;
                }

                if($payment->paymentModeProvider) {
                    $row[$payment->paymentModeProvider->name] += $payment->amount;
                }

                $total += $payment->amount;
            }

            $row['total'] = $total;

            array_push($list, $row);

            if ($sale->se_id) {

                $saleExchange = $this->saleExchangeObj->find($sale->se_id);

                if($saleExchange->creditNote()->first()){

                    $row = [];
                    $row['locationCode'] = $sale->transactionLocation->code;
                    $row['name'] = $sale->createdBy ? $sale->createdBy->name : '';
                    $row['transaction_date'] = $saleExchange->transaction_date;
                    $row['saleOrderNo'] = "";
                    $row['invoice_number'] = "";
                    $row['release_status'] = "";
                    $row['oriTaxInv'] = $saleExchange->is_legacy? $saleExchange->legacyInvoice->invoice_number : $saleExchange->parentSale->invoices->invoice_number;
                    $row['oriTaxInvDate'] = $saleExchange->is_legacy? $saleExchange->legacyInvoice->invoice_date :$saleExchange->parentSale->invoices->invoice_date;
                    $row['iboId'] = $sale->user->old_member_id;
                    $row['iboName'] = $sale->user->name;
                    $row['creditNote'] = $saleExchange->creditNote->credit_note_number;

                    foreach ($paymentMode as $mode){
                        $row[$mode] = 0;
                    }

                    $total = 0;

                    foreach ($sale->salePayments as $payment){

                        if ($payment->status != 1){
                            continue;
                        }

                        $amount = $payment->amount;

                        if($payment->paymentModeProvider) {
                            $row[$payment->paymentModeProvider->name] += $amount;
                        }

                        $total += $amount;
                    }

                    $row['total'] = $total;

                    array_push($list, $row);
                }
            }
        }

        foreach ($saleCancellations as $saleCancellation){

            if($saleCancellation->creditNote()->first()){

                $row = [];
                $row['locationCode'] = $saleCancellation->transactionLocation->code;
                $row['name'] = $saleCancellation->createdBy ? $saleCancellation->createdBy->name : '';
                $row['transaction_date'] = $saleCancellation->transaction_date;
                $row['saleOrderNo'] = "";
                $row['invoice_number'] = "";
                    $row['release_status'] = "";
                $row['oriTaxInv'] = $saleCancellation->is_legacy? $saleCancellation->legacyInvoice->invoice_number : $saleCancellation->invoice->invoice_number;
                $row['oriTaxInvDate'] = $saleCancellation->is_legacy? $saleCancellation->legacyInvoice->invoice_date : $saleCancellation->invoice->invoice_date;
                $row['iboId'] = $saleCancellation->user->old_member_id;
                $row['iboName'] = $saleCancellation->user->name;
                $row['creditNote'] = $saleCancellation->creditNote->credit_note_number;

                foreach ($paymentMode as $mode) {
                    $row[$mode] = 0;
                }

                $total = $saleCancellation->total_amount * -1;

                $row['total'] = $total;

                array_push($list, $row);
            }
        }

        $spreadsheet = new Spreadsheet();

        //inserting header into spreadsheet
        $header = ['Location Code', 'User Name','Transaction Date', 'Sale Order No.', 'Invoice No.', 'Release Status', 'Original Tax Invoice No.', 'Original Tax Invoice Date', 'IBO ID', 'IBO Name', 'Credit Note No.'];

        $header = array_merge($header, $paymentMode);

        array_push($header, "Total");

        $col = "A";

        foreach ($header as $value) {

            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            if ($col < "L") {
                $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
            }

            $col++;
        }

        //row 2 start inserting data into spreadsheet
        $row = 2;

        foreach ($list as $data) {

            $col = "A";

            $total = 0;

            foreach($data as $attribute){

                $cell = $col.$row;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $attribute);

                if ($col >= "L"){

                    $spreadsheet->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');

                    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth(13);
                }

                $col++;
            }

            $row++;
        }

        $styleArray = [
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $cell = "K".$row;

        $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, "Total");

        $spreadsheet->getActiveSheet()->getStyle($cell)->applyFromArray($styleArray);

        $col = "L";

        for($i = 0; $i <= count($paymentMode); $i++){

            $cell = $col.$row;

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, "=SUM(".$col."2:".$col."".($row-1).")");

            $spreadsheet->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');

            $spreadsheet->getActiveSheet()->getStyle($cell)->applyFromArray($styleArray);

            $col++;
        }
        
        // Output excel file
        $outputPath = Config::get('filesystems.subpath.sales.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.sales.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('sale_daily_transaction_report') . '.xlsx';

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($outputPath . $fileName);

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect([['download_link' => $fileUrl]]);
    }

    /**
     * download sales daily receipt report by below parameter
     *
     * @param int $countryId
     * @param array $locations
     * @param string $dateFrom
     * @param string $dateTo
     * @param array $userIds
     * @return \Illuminate\Support\Collection|mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadSaleDailyReceiptReport(
        int $countryId = 0,
        array $locations = array(),
        $dateFrom = '',
        $dateTo = '',
        array $userIds = array()
    )
    {
        //Get Sale Cancellation Status and Cancellation Mode
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_cancellation_status', 'sale_cancellation_type'));

        //Sale Cancellation Status
        $cancellationStatusValues = array_change_key_case(
            $settingsData['sale_cancellation_status']->pluck('id','title')->toArray()
        );

        $completeCancellationStatusId = $cancellationStatusValues[
            $this->saleCancellationStatusConfigCodes['completed']];

        //Sale Cancellation Mode
        $cancellationTypeValues = array_change_key_case(
            $settingsData['sale_cancellation_type']->pluck('id','title')->toArray()
        );

        $sameDayCancellationId = $cancellationTypeValues[
            $this->saleCancellationTypeConfigCodes['same day']];

        //Get Payment Mode
        $paymentModeSetting = $this->paymentModeSettingObj
            ->leftJoin('payments_modes_providers', 'payments_modes_providers.id', '=', 'payments_modes_settings.payment_mode_provider_id')
            ->where('country_id', '=', $countryId)
            ->where('active', '=', 1)
            ->distinct()
            ->get(['payments_modes_providers.name']);

        $paymentMode = $paymentModeSetting->pluck('name')->all();

        //Get Payment Record
        $paymentRecords = $this->paymentObj
            ->with(["createdBy"])
            ->join('sales', function ($join) {
                $join->on('payments.mapping_id', '=', 'sales.id')
                    ->where(function ($query) {
                        $query
                            ->where('payments.mapping_model', 'sales');

                        if ($this->isUser('stockist')){
                            $locationId = $this->getStockistUser()->stockistLocation()->first()->id;

                            $query->where('sales.transaction_location_id', $locationId);

                        }elseif($this->isUser('stockist_staff')){
                            $locationId = $this->getStockistParentLocation();

                            $query->where('sales.transaction_location_id', $locationId[0]);
                        }
                    });
            })
            ->where(function ($saleSubQuery) use ($locations, $userIds){

                if(!empty($locations)){
                    $saleSubQuery->whereIn('sales.transaction_location_id', $locations);
                }

                if(!empty($userIds)){
                    $saleSubQuery->whereIn('sales.created_by', $userIds);
                }
            })
            ->where('sales.country_id','=', $countryId)
            ->where('payments.created_at','>=', date('Y-m-d  H:i:s',strtotime($dateFrom.'00:00:00')))
            ->where('payments.created_at','<=', date('Y-m-d  H:i:s',strtotime($dateTo.'23:59:59')))
            ->where('payments.status', 1)
            ->select("payments.*")
            ->get();

        //Get Cancellation Record
        $creditNotes = $this->creditNoteObj
            ->with(['sale', 'sale.member', 'sale.user'])
            ->join('sales_cancellations', function ($join){
                $join->on('credit_notes.mapping_id', '=', 'sales_cancellations.id')
                    ->where('credit_notes.mapping_model', 'sales_cancellations');
            })
            ->join('sales', function ($join) {
                $join->on('credit_notes.sale_id', '=', 'sales.id');
            })
            ->where(function ($saleSubQuery) use ($locations, $userIds){

                if(!empty($locations)){
                    $saleSubQuery->whereIn('sales.transaction_location_id', $locations);
                }

                if(!empty($locations)){
                    $saleSubQuery->whereIn('sales.user_id', $userIds);
                }
            })
            ->where('sales.country_id','=', $countryId)
            ->where('credit_notes.credit_note_date','>=', $dateFrom)
            ->where('credit_notes.credit_note_date','<=', $dateTo)
            ->where("sales_cancellations.cancellation_status_id", $completeCancellationStatusId)
            ->where("sales_cancellations.cancellation_type_id", $sameDayCancellationId)
            ->select('credit_notes.*')
            ->get();

        $list = [];

        foreach ($paymentRecords as $paymentRecord){

            $sale = $this->modelObj->find($paymentRecord->mapping_id);

            if($sale->is_product_exchange){

                $saleExchange = $this->saleExchangeObj->where('sale_id', $sale->id)->first();

                $parentInvoice = ($saleExchange->is_legacy) ?
                    $saleExchange->legacyInvoice :
                        $saleExchange->parentSale->invoices;
            }

            $row = [];
            $row['locationCode'] = $sale->transactionLocation->code;
            $row['name'] = $paymentRecord->createdBy ? $paymentRecord->createdBy->name : '';
            $row['transaction_date'] = $paymentRecord->created_at;
            $row['saleOrderNo'] = $sale->document_number;
            $row['invoice_number'] = optional($sale->invoices()->first())->invoice_number;
            $row['oriTaxInv'] = ($sale->is_product_exchange) ? $parentInvoice->invoice_number : "";
            $row['oriTaxInvDate'] = ($sale->is_product_exchange) ? $parentInvoice->invoice_date : "";
            $row['iboId'] = $sale->user->old_member_id;
            $row['iboName'] = $sale->user->name;
            $row['creditNote'] = "";

            foreach ($paymentMode as $mode){
                $row[$mode] = 0;
            }

            $row[$paymentRecord->paymentModeProvider->name] = $paymentRecord->amount;

            $row['total'] = $paymentRecord->amount;

            array_push($list, $row);

        }

        foreach ($creditNotes as $creditNote){

            $sale = $this->modelObj->find($creditNote->sale_id);

            $saleCancellation = $this->saleCancellationObj->find($creditNote->mapping_id);

            $salePayments = $sale->salePayments->where('status', 1);

            foreach ($salePayments as $payment){

                $row = [];
                $row['locationCode'] = $saleCancellation->transactionLocation->code;
                $row['name'] = $payment->createdBy ? $payment->createdBy->name : '';
                $row['transaction_date'] = $payment->created_at;
                $row['saleOrderNo'] = $sale->document_number;
                $row['invoice_number'] = optional($sale->invoices()->first())->invoice_number;
                $row['oriTaxInv'] = "";
                $row['oriTaxInvDate'] = "";
                $row['iboId'] = $sale->user->old_member_id;
                $row['iboName'] = $sale->user->name;
                $row['creditNote'] = $creditNote->credit_note_number;

                foreach ($paymentMode as $mode){
                    $row[$mode] = 0;
                }

                $payAmount = $payment->amount * -1;

                $row[$payment->paymentModeProvider->name] = $payAmount;

                $row['total'] = $payAmount;

                array_push($list, $row);
            }
        }

        $spreadsheet = new Spreadsheet();

        //inserting header into spreadsheet
        $header = [
            'Location Code',
            'User Name',
            'Transaction Date',
            'Sale Order No.',
            'Invoice No.',
            'Original Tax Invoice No.',
            'Original Tax Invoice Date',
            'IBO ID',
            'IBO Name',
            'Credit Note No.'
        ];

        $header = array_merge($header, $paymentMode);

        array_push($header, "Total");

        $col = "A";

        foreach ($header as $value){

            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            if ($col < "K"){
                $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
            }

            $col++;
        }

        //row 2 start inserting data into spreadsheet
        $row = 2;

        foreach ($list as $data){

            $col = "A";

            $total = 0;

            foreach($data as $attribute){

                $cell = $col.$row;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $attribute);

                if ($col >= "K"){
                    $spreadsheet->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');

                    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setWidth(13);
                }

                $col++;
            }
            $row++;
        }

        //Draw Summary Row
        $styleArray = [
            'borders' => [
                'top' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $cell = "J".$row;

        $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, "Total");

        $spreadsheet->getActiveSheet()->getStyle($cell)->applyFromArray($styleArray);

        $col = "K";

        for($i = 0; $i <= count($paymentMode); $i++){

            $cell = $col.$row;

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, "=SUM(".$col."2:".$col."".($row-1).")");

            $spreadsheet->getActiveSheet()->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');

            $spreadsheet->getActiveSheet()->getStyle($cell)->applyFromArray($styleArray);

            $col++;
        }
        
        // Output excel file
        $outputPath = Config::get('filesystems.subpath.sales.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.sales.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('sale_daily_receipt_report') . '.xlsx';

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($outputPath . $fileName);

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect([['download_link' => $fileUrl]]);
    }

    /**
     * download sales MPOS report by below parameter
     *
     * @param int $countryId
     * @param array $locations
     * @param string $dateFrom
     * @param string $dateTo
     * @return \Illuminate\Support\Collection|mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadSaleMposReport(
        int $countryId = 0,
        array $locations = array(),
        $dateFrom = '',
        $dateTo = ''
    )
    {
        //Get MPOS Payment Mode ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $mposMasterDataId = $paymentMode[$this->paymentModeConfigCodes['mpos']];

        $mposDetails = $this->paymentObj
            ->join('payments_modes_providers', function ($join) use ($mposMasterDataId){
                $join->on('payments.payment_mode_provider_id', '=', 'payments_modes_providers.id')
                    ->where(function ($paymentProvidersQuery) use ($mposMasterDataId) {
                        $paymentProvidersQuery->where(
                            'payments_modes_providers.master_data_id', $mposMasterDataId);
                    });
            })
            ->join('sales', function ($join) {
                $join->on('payments.mapping_id', '=', 'sales.id')
                    ->where(function ($query) {
                        $query
                            ->where('payments.mapping_model', 'sales');
                    });
            })
            ->where(function ($saleSubQuery) use ($locations){
                if(!empty($locations)){
                    $saleSubQuery->whereIn('sales.transaction_location_id', $locations);
                }
            })
            ->where('sales.country_id','=', $countryId)
            ->where('payments.created_at','>=', date('Y-m-d  H:i:s',strtotime($dateFrom.'00:00:00')))
            ->where('payments.created_at','<=', date('Y-m-d  H:i:s',strtotime($dateTo.'23:59:59')))
            ->select("payments.*")
            ->get();

        $list = [];

        foreach ($mposDetails as $mposDetail){

            $sale = $this->modelObj->find($mposDetail->mapping_id);

            $paymentDetail = json_decode($mposDetail->payment_detail, true);

            $row = [];
            $row['locationCode'] = $sale->transactionLocation->code;
            $row['locationName'] = $sale->transactionLocation->name;
            $row['tidNo'] = (isset($paymentDetail['terminal_id'])) ? $paymentDetail['terminal_id'] : '';
            $row['midNo'] = (isset($paymentDetail['merchant_id'])) ? $paymentDetail['merchant_id'] : '';
            $row['settlementDate'] = (isset($paymentDetail['settlement_date'])) ? $paymentDetail['settlement_date'] : '';
            $row['claimDate'] = $mposDetail->created_at;
            $row['claimStatus'] = ($mposDetail->status == 1) ? 'P' : (($mposDetail->status == 2) ? 'N' : 'V');
            $row['approvalCode'] = (isset($paymentDetail['approval_code'])) ? $paymentDetail['approval_code'] : '';
            $row['approvalAmount'] = $mposDetail->amount;
            $row['invoiceDate'] = optional($sale->invoices()->first())->invoice_date;
            $row['invoiceNo'] = optional($sale->invoices()->first())->invoice_number;
            $row['ibo_id'] = $sale->user->old_member_id;
            $row['ibo_name'] = $sale->user->name;
            $row['staff_name'] = $mposDetail->createdBy ? $mposDetail->createdBy->name : '';
            $row['remarks'] = '';

            array_push($list, $row);

        }

        $spreadsheet = new Spreadsheet();

        //inserting header into spreadsheet
        $header = [
            'Location Code',
            'Location Name',
            'TID#',
            'MID#',
            'Settlement Date',
            'Claim Date',
            'Claim Status',
            'Approval Code',
            'Approval Amount',
            'Invoice Date',
            'Invoice No',
            'IBO ID',
            'IBO Name',
            'Staff Name',
            'Remarks'
        ];

        $col = "A";

        foreach ($header as $value)
        {
            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }

        //row 2 start inserting data into spreadsheet
        $row = 2;

        foreach ($list as $data){

            $col = "A";

            $total = 0;

            foreach($data as $attribute){

                $cell = $col.$row;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $attribute);

                $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

                $col++;
            }
            $row++;
        }

        // Output excel file
        $outputPath = Config::get('filesystems.subpath.sales.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.sales.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('sale_mpos_report') . '.xlsx';

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($outputPath . $fileName);

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect([['download_link' => $fileUrl]]);
    }

    /**
     * download pre-order note
     *
     * @param int $saleId
     * @return Collection|mixed
     * @throws \Mpdf\MpdfException
     */
    public function downloadPreOrderNote(int $saleId)
    {
        $sale = $this->modelObj->find($saleId);

        if (empty($sale))
        {
            return;
        }

        $user = $sale->user;
        $payments = $sale->salePayments;
        $cw = $sale->cw;
        $products = $sale->saleProducts;

        //populate the general info
        $info = array(
            'memberID' => $user->old_member_id,
            'name' => $user->name,
            'address' => $user->member->address? $this->memberAddress->getCorrespondenceAddress($user->member->address->address_data): "",
            'tel' => $user->mobile,
            'location' => $sale->transactionLocation->name,
            'branch' => $sale->stockLocation->name,
            'cycle' => $cw->cw_name,
            'date' => $sale->transaction_date,
            'saleDocNo' => $sale->document_number,
            'issuer' => $sale->createdBy->stockist? $sale->createdBy->stockist->stockist_number:'',
            'currency' => $sale->country->currency->code
        );

        //sales products lines
        $salesProducts = array();

        //populate summary product lines
        $productsSummary = array();

        $lineCount = 1;
        $totalProductQty = $totalProductCv = $totalExcTax = $totalTax = $totalIncTax = 0;
        $addedNonLooseProduct = $productsSummaryName = array();

        foreach($products as $product){
            // summary format : ['HED30' => 1, 'HES300P' => 4]
            //if this is loose item, it will have sku, else for foc or kitting, code is used instead
            $uniqueCode = isset($product->product->sku) ?
                $product->product->sku : $product->product->code;

            $productsSummary[$uniqueCode] = isset($productsSummary[$uniqueCode])
                ? $product->quantity + $product->foc_qty + $productsSummary[$uniqueCode]
                : $product->quantity + $product->foc_qty;

            $productsSummaryName[$uniqueCode] = isset($productsSummaryName[$uniqueCode])
                ? $productsSummaryName[$uniqueCode] : $product->product->name;

            if($product->mapping_id && $product->mapping_model)
            {
                if(!isset($addedNonLooseProduct[$product->mapping_model][$product->mapping_id]))
                {
                    $code = '';
                    $productQty = $productCv = $unitPrice = $subTotal = $tax = $total = 0;

                    //this can be a kitting or FOC or PWP. Will get the info from the respective table
                    if($product->getMappedModel instanceof SalePromotionFreeItemClone)
                    {
                        // salepromotionfreeitem is 1:1, so always refer back to sales product
                        $masterData = $this->masterDataObj->find($product->getMappedModel->promo_type_id);
                        $code = $masterData? $masterData->title : '';
                        $unitPrice = $product->nmp_price;
                        $gmpPrice = $product->gmp_price_gst;
                        $productQty = $product->quantity;
                        $productCv = 0;
                        $tax = ($gmpPrice - $unitPrice) * $productQty;

                        if(strtolower($code) == $this->promotionFreeItemsPromoTypesConfigCodes['pwp(f)'])
                        {
                            $subTotal = $unitPrice;
                            $total = $gmpPrice;
                        } else {
                            $subTotal = $unitPrice * $productQty;
                            $total = $gmpPrice * $productQty;
                        }
                    }
                    elseif($product->getMappedModel instanceof SaleKittingClone)
                    {
                        $code = $product->getMappedModel->code;
                        $unitPrice = $product->getMappedModel->nmp_price;
                        $gmpPrice = $product->getMappedModel->gmp_price_gst;
                        $productQty = $product->getMappedModel->quantity;
                        $productCv = $product->getMappedModel->eligible_cv;
                        $tax = ($gmpPrice - $unitPrice) * $productQty;
                        $subTotal = $unitPrice * $productQty;
                        $total = $gmpPrice * $productQty;
                    }

                    $salesProducts[] = array(
                        'no' => $lineCount,
                        'tos' => '',
                        'code' => $code,
                        'description' => $product->getMappedModel->name,
                        'qty' => $productQty,
                        'uom' => $product->product->product->uom,
                        'cv' => $productCv * $productQty,
                        'unitPrice' => $unitPrice,
                        'subTotal' => $subTotal,
                        'discount' => 0.00,
                        'excTax' => $unitPrice * $productQty,
                        'tax' => $tax,
                        'total' => $total
                    );

                    $addedNonLooseProduct[$product->mapping_model][$product->mapping_id] = $lineCount;

                    $totalProductQty += $productQty;
                    $totalProductCv += $productCv * $productQty;
                    $lineCount++;

                    //to indicates that this is already entered into the sale detail
                }
                else if($product->getMappedModel instanceof SalePromotionFreeItemClone)
                {
                    $lineCount = $addedNonLooseProduct[$product->mapping_model][$product->mapping_id];
                    $lineItem = $salesProducts[$lineCount-1];

                    $lineItem['qty'] +=  $product->quantity;

                    $masterData = $this->masterDataObj->find($product->getMappedModel->promo_type_id);
                    $code = $masterData? $masterData->title : '';
                    $unitPrice = $product->nmp_price;
                    $gmpPrice = $product->gmp_price_gst;

                    if(strtolower($code) != $this->promotionFreeItemsPromoTypesConfigCodes['pwp(f)'])
                    {
                        $lineItem['subTotal'] = $unitPrice * $lineItem['qty'];
                        $lineItem['total'] = $gmpPrice * $lineItem['qty'];
                    }

                    $salesProducts[$lineCount-1] = $lineItem;
                }
            }
            else
            {
                //this must be a loose product
                $salesProducts[] = array(
                    'no' => $lineCount,
                    'tos' => '',
                    'code' => $product->product->sku,
                    'description' => $product->product->name,
                    'qty' => $product->quantity,
                    'uom' => $product->product->uom,
                    'cv' => $product->eligible_cv * $product->quantity,
                    'unitPrice' => $product->nmp_price,
                    'subTotal' => $product->nmp_price * $product->quantity,
                    'discount' => 0.00,
                    'excTax' => $product->nmp_price * $product->quantity,
                    'tax' => ($product->gmp_price_gst - $product->nmp_price) * $product->quantity,
                    'total' => $product->gmp_price_gst * $product->quantity
                );
                $totalProductQty += $product->quantity;
                $totalProductCv += $product->eligible_cv * $product->quantity;
                $lineCount++;
            }
        }

        //discount
        $esacVouchers = $this->saleEsacVouchersCloneObj->where('sale_id', $sale->id)->get();

        //payments
        $paid = 0;
        $paymentsSummary = array();
        $payments->each(function($payment) use(&$paymentsSummary, &$paid){
            if($payment->status == 1){ // only get success one
                $paymentsSummary[] = [
                    'method'=>$payment->paymentModeProvider->name,
                    'total' => $payment->amount
                ];
                $paid += $payment->amount;
            }
        });

        $balanceDue = $sale->total_gmp > $paid ? $sale->total_gmp - $paid : 0;

        $summary = array(
            'items' => $productsSummary,
            'names' => $productsSummaryName,
            'payments' => $paymentsSummary,
            'paid' => $paid,
            'balanceDue' => $balanceDue
        );

        // @TODO: check total amount is including admin fee, delivery fee, and other fees
        $items = array(
            'products' => $salesProducts,
            'subTotal' => [
                'qty' => $totalProductQty, 
                'cv' =>  $totalProductCv, 
                'excTax' => $sale->total_amount - $sale->other_fees - $sale->admin_fees - $sale->delivery_fees, 
                'tax' => $sale->tax_amount, 
                'total' => $sale->total_gmp - $sale->other_fees - $sale->admin_fees - $sale->delivery_fees,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ],
            'delivery' => ['excTax' => $sale->delivery_fees, 'tax' => 0.00, 'total' => $sale->delivery_fees],
            'admin' => ['excTax' => $sale->admin_fees, 'tax' => 0.00, 'total' => $sale->admin_fees],
            'other' => ['excTax' => $sale->other_fees, 'tax' => 0.00, 'total' => $sale->other_fees],
            'total' => ['excTax' => $sale->total_amount, 'tax' => $sale->tax_amount, 'total' => $sale->total_gmp, 'exempt' => 0.00, 'zeroRated' => 0.00]
        );

        $view = 'invoices.preorder_note';
        $html = \View::make($view)
            ->with('basic', $info)
            ->with('summary', $summary)
            ->with('items', $items)
            ->with('esacVouchers', $esacVouchers)
            ->render();

        $config = ['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 0];
        $mpdf = new PdfCreator($config);
        //$mpdf->setFooter("Invoice No: INV1712000001632<br/>Page No: {PAGENO} / {nb}");
        $mpdf->WriteHTML($html);
        $total = $mpdf->getTotalPage();

        $config['margin_bottom'] = 20;

        $mpdf = new PdfCreator($config);
        $html = str_replace('{nb}', $total, $html);
        $mpdf->WriteHTML($html);
        $outputPath = Config::get('filesystems.subpath.invoice.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.invoice.absolute_url_path');
        
        $fileName = $this->uploader->getRandomFileName('pre_order_note_' . $saleId) . '.pdf';
        
        $fileUrl = $this->uploader->createS3File($absoluteUrlPath . $fileName, $mpdf->Output($fileName, "S"), true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * download itemised sales report by below parameter
     *
     * @param array $countryIds
     * @param array $locationIds
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $fromCw
     * @param int $toCw
     * @param array $broadCategories
     * @param array $subCategories
     * @param array $minorCategories
     * @return Collection|mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadSaleProductReport(
        array $countryIds = array(),
        array $locationIds = array(),
        $dateFrom = '',
        $dateTo = '',
        $fromCw = 0,
        $toCw = 0,
        array $broadCategories = array(),
        array $subCategories = array(),
        array $minorCategories = array()
    )
    {

        $products = $this->saleProductsObj
                        ->join('sales', 'sales.id', '=', 'sales_products.sale_id')
                        ->join('sales_products_clone', 'sales_products.product_id', '=', 'sales_products_clone.id')
                        ->join('products', 'sales_products_clone.product_id', '=', 'products.id')
                        ->whereBetween("sales.transaction_date", [$dateFrom, $dateTo])
                        ->with(['sale', 'product', 'product.product', 'sale.country', 'product.product.category']);
  
        if ($fromCw && $toCw)
        {
            $products = $products->whereBetween("sales.cw_id", [$fromCw, $toCw]);
        }

        if (count($countryIds)>0)
        {
            $products = $products->whereIn('sales.country_id', $countryIds);
        }

        if (count($locationIds)>0)
        {
            $products = $products->whereIn('sales.stock_location_id', $locationIds);
        }

        if (count($minorCategories) > 0 || count($subCategories) > 0 || count($broadCategories) > 0)
        {
            $categories = array_merge($minorCategories, $subCategories, $broadCategories);

            $products->leftJoin('product_categories as c1', 'products.category_id', '=', 'c1.id')
                    ->leftJoin('product_categories as c2', 'c1.parent_id', '=', 'c2.id')
                    ->leftJoin('product_categories as c3', 'c2.parent_id', '=', 'c3.id')
                    ->leftJoin('product_categories as c4', 'c3.parent_id', '=', 'c4.id')
                    ->where(function ($query) use ($categories){
                        $query->whereIn('c4.id', $categories)
                              ->orWhereIn('c3.id', $categories)
                              ->orWhereIn('c2.id', $categories)
                              ->orWhereIn('c1.id', $categories);
                    });
        }
        $products = $products->get(['sales_products.*']);

        $spreadsheet = new Spreadsheet();

        //inserting header into spreadsheet
        $header = ["Country Code", "Broad Category", "Sub-Category", "Minor Category", "Base SKU Code", "Kit Code", "Product Name", "Qty Sold", "Total NMP (excl. Tax)", "Total GMP (incl. Tax)", "Total Avg Price Ratio", "FOC Qty"];

        $col = "A";

        foreach ($header as $value)
        {
            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }


        $styleArray = [
            'font' => [
                'bold' => true,
            ]
        ];

        $list = [];

        foreach ($products as $product)
        {
            $row = [];
            $countryId = $product->sale->country_id;
            $uniqueCode = $product->product? $product->product->sku: $product->product_id;


            if (array_key_exists($countryId, $list) && isset($list[$countryId][$uniqueCode]))
            {
                $list[$countryId][$uniqueCode]['qty'] += $product->quantity;
                $list[$countryId][$uniqueCode]['nmp'] += $product->nmp_price * $product->quantity;
                $list[$countryId][$uniqueCode]['total'] += $product->total;
                $list[$countryId][$uniqueCode]['foc_qty'] += $product->foc_qty;
            }
            else
            {
                $categoryCode = $product->product->product->category->hierarchy;

                $row["countryCode"] = $product->sale->country->code_iso_2;
                $row["broadCat"] = $categoryCode[0];
                $row["subCat"] = $categoryCode[1];
                $row["minorCat"] = $categoryCode[2];
                $row["sku"] = $product->product? $product->product->sku: "-";
                if ($product->mapping_model == 'sales_kitting_clone')
                {
                    $kitting = $product->getMappedModel;
                    $row["kitCode"] = $kitting->code;
                    $row["kitName"] = $kitting->name;
                    $row["avgPrice"] = $product->average_price_unit;
                }
                else
                {
                    $row["kitCode"] = "-";
                    $row["kitName"] = "-";
                    $row["avgPrice"] = 0.00;
                }
                
                $row['qty'] = $product->quantity;
                $row['nmp'] = $product->nmp_price * $product->quantity;
                $row['total'] = $product->total;
                $row['foc_qty'] = $product->foc_qty;

                $list[$countryId][$uniqueCode] = $row;
            }
            
        }

        $row = 2;
        foreach($list as $countryId => $uniqueCode)
        {
            $startRow = $row;

            foreach ($uniqueCode as $product)
            {
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("A".$row, $product['countryCode']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("B".$row, $product['broadCat']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("C".$row, $product['subCat']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("D".$row, $product['minorCat']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("E".$row, $product['sku']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("F".$row, $product['kitCode']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("G".$row, $product['kitName']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("H".$row, $product['qty']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("I".$row, $product['nmp']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("J".$row, $product['total']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("K".$row, $product['avgPrice']);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("L".$row, $product['foc_qty']);

                $row++;
            }

            $spreadsheet->setActiveSheetIndex(0)->setCellValue("G".$row, "TOTAL");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("I".$row, "=SUM(I".$startRow.":I".($row-1).")");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("J".$row, "=SUM(J".$startRow.":J".($row-1).")");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("K".$row, "=SUM(K".$startRow.":K".($row-1).")");
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("L".$row, "=SUM(L".$startRow.":L".($row-1).")");
            $spreadsheet->getActiveSheet()->getStyle("G".$row.":L".$row)->applyFromArray($styleArray);

            $row +=2;
        }
        $spreadsheet->getActiveSheet()->getStyle("I2:K".$row)->getNumberFormat()->setFormatCode('#,##0.00');

        $styleArray = [
            'alignment' => [
                'horizontal' => 'right',
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle("K2:K".$row)->applyFromArray($styleArray);

        // Output excel file

        $outputPath = Config::get('filesystems.subpath.sales.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.sales.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('itemised_sales_report') . '.xlsx';
        
        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Xlsx($spreadsheet);

        $writer->save($outputPath . $fileName);

        $fileUrl = $this->uploader->moveLocalFileToS3($outputPath . $fileName, $absoluteUrlPath . $fileName, true);

        return collect([['download_link' => $fileUrl]]);
    }
}