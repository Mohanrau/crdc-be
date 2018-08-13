<?php
namespace App\Repositories\Invoices;

use App\{
    Helpers\Traits\AccessControl,
    Interfaces\Masters\MasterInterface,
    Interfaces\General\CwSchedulesInterface,
    Interfaces\Invoices\InvoiceInterface,
    Interfaces\Payments\PaymentInterface,
    Interfaces\Products\ProductInterface,
    Interfaces\Settings\SettingsInterface,
    Interfaces\Stockists\StockistInterface,
    Models\Invoices\Invoice,
    Models\Invoices\LegacyInvoice,
    Models\Masters\Master,
    Models\Masters\MasterData,
    Models\Payments\Payment,
    Models\Payments\PaymentModeProvider,
    Models\Sales\CreditNote,
    Models\Sales\Sale,
    Models\Sales\SaleProduct,
    Models\Sales\SaleCancellation,
    Models\Sales\SaleExchange,
    Models\Sales\SaleKittingClone,
    Models\Sales\SalePromotionFreeItemClone,
    Models\Sales\SaleEsacVouchersClone,
    Models\Stockists\Stockist,
    Models\Stockists\StockistDepositSetting,
    Models\Stockists\StockistSalePayment,
    Models\Locations\Country,
    Repositories\BaseRepository,
    Helpers\Classes\PdfCreator,
    Helpers\Classes\MemberAddress,
    Helpers\Classes\Uploader
};
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use \PhpOffice\PhpSpreadsheet\{
    Spreadsheet,
    Writer\Xlsx
};

class InvoiceRepository extends BaseRepository implements InvoiceInterface
{
    use AccessControl;

    private $saleObj,
        $masterRepositoryObj,
        $cwSchedulesRepositoryObj,
        $productRepositoryObj,
        $settingsRepositoryObj,
        $stockistRepositoryObj,
        $masterDataObj,
        $masterObj,
        $paymentObj,
        $creditNoteObj,
        $saleCancellationObj,
        $saleExchangeObj,
        $stockistObj,
        $stockistDepositSettingObj,
        $saleEsacVouchersCloneObj,
        $memberAddress,
        $uploader,
        $stockistSalePaymentObj,
        $stockistTransactionReleaseStatusConfigCodes,
        $saleCancellationStatusConfigCodes,
        $saleCancellationModeConfigCodes,
        $saleCancellationTypeConfigCodes,
        $promotionFreeItemsPromoTypesConfigCodes,
        $aeonPaymentStockReleaseStatusConfigCodes,
        $paymentModeConfigCodes,
        $saleOrderStatusConfigCodes,
        $legacyInvoiceObj,
        $countryObj,
        $transactionTypeConfigCodes,
        $locationTypeCodeConfigCodes,
        $saleDeliveryMethodConfigCodes,
        $saleProductObj,
        $paymentModeProviderObj;

    /**
     * InvoiceRepository constructor.
     *
     * @param Invoice $model
     * @param MasterData $masterData
     * @param Master $master
     * @param Payment $payment
     * @param CreditNote $creditNote
     * @param Sale $sale
     * @param SaleCancellation $saleCancellation
     * @param Stockist $stockist
     * @param StockistDepositSetting $stockistDepositSetting
     * @param SaleEsacVouchersClone $saleEsacVouchersClone
     * @param MasterInterface $masterInterface
     * @param SettingsInterface $settingsInterface
     * @param StockistInterface $stockistInterface
     * @param ProductInterface $productInterface
     * @param CwSchedulesInterface $cwSchedulesInterface
     * @param MemberAddress $memberAddress
     * @param Uploader $uploader
     * @param StockistSalePayment $stockistSalePayment
     * @param LegacyInvoice $legacyInvoice
     * @param SaleExchange $saleExchange
     * @param Country $country
     * @param SaleProduct $saleProduct
     * @param PaymentModeProvider $paymentModeProvider
     */
    public function __construct(
        Invoice $model,
        MasterData $masterData,
        Master $master,
        Payment $payment,
        CreditNote $creditNote,
        Sale $sale,
        SaleCancellation $saleCancellation,
        Stockist $stockist,
        StockistDepositSetting $stockistDepositSetting,
        SaleEsacVouchersClone $saleEsacVouchersClone,
        MasterInterface $masterInterface,
        SettingsInterface $settingsInterface,
        StockistInterface $stockistInterface,
        ProductInterface $productInterface,
        CwSchedulesInterface $cwSchedulesInterface,
        MemberAddress $memberAddress,
        Uploader $uploader,
        StockistSalePayment $stockistSalePayment,
        LegacyInvoice $legacyInvoice,
        SaleExchange $saleExchange,
        Country $country,
        SaleProduct $saleProduct,
        PaymentModeProvider $paymentModeProvider
    )
    {
        parent::__construct($model);

        $this->masterRepositoryObj = $masterInterface;

        $this->settingsRepositoryObj = $settingsInterface;

        $this->stockistRepositoryObj = $stockistInterface;

        $this->productRepositoryObj = $productInterface;

        $this->cwSchedulesRepositoryObj = $cwSchedulesInterface;

        $this->masterDataObj = $masterData;

        $this->masterObj = $master;

        $this->paymentObj = $payment;

        $this->creditNoteObj = $creditNote;

        $this->saleObj = $sale;

        $this->saleCancellationObj = $saleCancellation;

        $this->stockistObj = $stockist;

        $this->stockistDepositSettingObj = $stockistDepositSetting;

        $this->saleEsacVouchersCloneObj = $saleEsacVouchersClone;

        $this->memberAddress = $memberAddress;

        $this->uploader = $uploader;

        $this->stockistSalePaymentObj = $stockistSalePayment;

        $this->legacyInvoiceObj = $legacyInvoice;

        $this->saleExchangeObj = $saleExchange;

        $this->countryObj = $country;

        $this->saleProductObj = $saleProduct;

        $this->paymentModeProviderObj = $paymentModeProvider;

        $this->stockistTransactionReleaseStatusConfigCodes =
            config('mappings.stockist_daily_transaction_release_status');

        $this->saleCancellationStatusConfigCodes =
            config('mappings.sale_cancellation_status');

        $this->saleCancellationModeConfigCodes =
            config('mappings.sale_cancellation_mode');

        $this->saleCancellationTypeConfigCodes =
            config('mappings.sale_cancellation_type');

        $this->promotionFreeItemsPromoTypesConfigCodes =
            config('mappings.promotion_free_items_promo_types');

        $this->paymentModeConfigCodes =
            config('mappings.payment_mode');

        $this->aeonPaymentStockReleaseStatusConfigCodes =
            config('mappings.aeon_payment_stock_release_status');

        $this->saleOrderStatusConfigCodes =
            config('mappings.sale_order_status');

        $this->transactionTypeConfigCodes =
            config('mappings.sale_types');

        $this->locationTypeCodeConfigCodes =
            config('mappings.location_type_code');

        $this->saleDeliveryMethodConfigCodes =
            config('mappings.sale_delivery_method');
    }

    /**
     * get invoice filtered by the following parameters
     *
     * @param int $countryId
     * @param string $text
     * @param $dateFrom
     * @param $dateTo
     * @param int $userId
     * @param bool $isSaleCancellation
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getInvoicesByFilters(
        int $countryId,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $userId = NULL,
        bool $isSaleCancellation = false,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->with(['createdBy','sale.member', 'sale.country',
                'sale.orderStatus', 'sale.channel', 'sale.deliveryMethod',
                'sale.deliveryStatus', 'sale.createdBy', 'sale.cw',
                'sale.transactionLocation', 'sale.stockLocation']
            )
            ->join('sales', function ($join)
                use ($countryId, $userId){
                    $join->on('invoices.sale_id', '=', 'sales.id')
                         ->where(function ($saleQuery) use ($countryId, $userId) {
                             $saleQuery->where('sales.country_id', $countryId);

                             //check the granted location give for the user if he back_office,stockist or member
                             if (
                                 $this->isUser('back_office') or
                                 $this->isUser('stockist') or
                                 $this->isUser('stockist_staff')
                             )
                             {
                                 $this->applyLocationQuery($saleQuery, $countryId, 'transaction_location_id');
                             }

                             if($userId > 0){
                                 $saleQuery->where('sales.user_id', $userId);
                             }
                         });
                });

        if (
            ($this->isUser('stockist')  or $this->isUser('stockist_staff'))
            &&
            ($isSaleCancellation))
        {
            $data = $data
                ->where('invoice_date','=', Carbon::now()->format('Y-m-d'));
        }

        if ($text != '') {
            $data = $data
                ->join('users', function ($join)
                    use ($text) {
                        $join->on('users.id', '=', 'sales.user_id')
                            ->where(function ($userQuery) use ($text) {
                                $userQuery->OrWhere('users.old_member_id', 'like','%' . $text . '%');
                                $userQuery->Orwhere('invoices.invoice_number', 'like','%' . $text. '%');
                            });
                    });
        }

        //check the dates if given
        if ($dateFrom != '' and $dateTo != ''){
            $data = $data
                ->where('invoice_date','>=', $dateFrom)
                ->where('invoice_date','<=', $dateTo);
        }

        $totalRecords = collect(['total' => $data->count()]);

        $data = $data->orderBy($orderBy, $orderMethod);

        $data->select('invoices.*');

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get invoice details for a given id
     *
     * @param int $invoiceId
     * @return mixed
     */
    public function find(int $invoiceId)
    {
        return $this->modelObj->findOrFail($invoiceId);
    }

    /**
     * get invoice details for a given invoiceId
     *
     * @param int $invoiceId
     * @return mixed
     */
    public function invoiceDetails(int $invoiceId)
    {
        $invoices = $this->modelObj->with('cw')->find($invoiceId);

        $saleDetail = $this->saleObj
            ->with([
                'member','createdBy', 'country', 'stockLocation',
                'orderStatus', 'channel', 'deliveryMethod',
                'deliveryStatus', 'cw', 'transactionLocation',
                'saleCancellation.saleCancelProducts'
            ])
            ->find($invoices->sale_id);

        $products = $saleDetail->getSaleProducts($this->productRepositoryObj, $saleDetail->country_id);

        $kitting = $saleDetail->getSaleKitting($this->productRepositoryObj, $saleDetail->country_id);

        $promotions = $saleDetail->getSaleSelectedPromotions($this->productRepositoryObj, $saleDetail->country_id);

        $esacVouchers = $saleDetail->getSaleEsacs();

        $salePayments = $saleDetail->salePayments()->get();

        $selectedPromotions = [];

        collect($promotions)->each(function($promotion)
            use(&$selectedPromotions){
                collect($promotion)->each(function($selected)
                    use (&$selectedPromotions){
                        array_push($selectedPromotions, $selected);
                });
        });

        return [
            'sale' => array_merge($saleDetail->toArray(),
                array(
                    'invoice' => $invoices->toArray(),
                    'products' => $products,
                    'kitting' => $kitting,
                    'promotions' => $selectedPromotions,
                    'esac_vouchers' => $esacVouchers,
                    'sale_payments' => $salePayments
                ))
        ];
    }

    /**
     * To generate invoice out from a sale
     *
     * @param $sale
     * @return mixed
     */
    public function generateInvoice(Sale $sale)
    {
        //Get Current Back Date CW
        $currenctBackCw = $this->cwSchedulesRepositoryObj
            ->getCwSchedulesList(
                'current_back_date',
                [
                    'sort' => 'cw_name',
                    'order' => 'desc',
                    'limit' => 2,
                    'offset' => 0
                ]
            );

        $previousCw = $this->cwSchedulesRepositoryObj
            ->getCwSchedulesList(
                'past',
                [
                    'sort' => 'cw_name',
                    'order' => 'desc',
                    'limit' => 1,
                    'offset' => 0
                ]
            );

        $currentBackCwIds = collect($currenctBackCw['data'])->pluck('id');

        if($currentBackCwIds->contains($sale->cw_id)){
            $invoiceCwId = $sale->cw_id;
        } else {
            if($sale->cw_id == $previousCw['data'][0]->id){
                $invoiceCwId = $currenctBackCw['data'][0]->id;
            } else {
                return false;
            }
        }

        //Get Sale Payment Record
        $salePayments = $sale->salePayments()->where('status', 1)->get();

        //Get Status ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode', 'stockist_daily_transaction_release_status', 'aeon_payment_stock_release_status'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $stockistReleaseStatusValues = array_change_key_case(
            $settingsData['stockist_daily_transaction_release_status']->pluck('id','title')->toArray()
        );

        $aeonReleaseStatusValues = array_change_key_case(
            $settingsData['aeon_payment_stock_release_status']->pluck('id','title')->toArray()
        );

        $aeonPaymentId = $paymentMode[$this->paymentModeConfigCodes['aeon']];

        $pendingStockistReleaseStatusId = $stockistReleaseStatusValues[
            $this->stockistTransactionReleaseStatusConfigCodes['pending']];

        $releaseStockistReleaseStatusId = $stockistReleaseStatusValues[
            $this->stockistTransactionReleaseStatusConfigCodes['released']];

        $pendingAeonReleaseStatusId = $aeonReleaseStatusValues[
            $this->aeonPaymentStockReleaseStatusConfigCodes['pending']];

        $releasedAeonReleaseStatusId = $aeonReleaseStatusValues[
            $this->aeonPaymentStockReleaseStatusConfigCodes['released']];

        //Get Aeon Payment Provider
        $aeonPaymentProviderId = $this->paymentModeProviderObj
            ->where('master_data_id', $aeonPaymentId)
            ->pluck("id");

        $aeonPaymentCount = collect($salePayments)
            ->whereIn("payment_mode_provider_id", $aeonPaymentProviderId)
            ->count();

        if($aeonPaymentCount > 0){

            $aeonReleaseStatusId = $pendingAeonReleaseStatusId;

            $stockistDailyTransactionStatusId = $pendingStockistReleaseStatusId;

            $aeonReleaseDate = $stockistReleaseDate = NULL;
            
        } else {

            $saleChannelCode = $sale->channel->code;

            $aeonReleaseStatusId = $releasedAeonReleaseStatusId;

            $aeonReleaseDate = date('Y-m-d');

            $stockistDailyTransactionStatusId = ($saleChannelCode == $this->locationTypeCodeConfigCodes['stockist']) ?
                $pendingStockistReleaseStatusId : $releaseStockistReleaseStatusId;
            
            $stockistReleaseDate = ($saleChannelCode == $this->locationTypeCodeConfigCodes['stockist']) ?
                NULL : date('Y-m-d');
        }

        $invoiceRunningNumber = $this->settingsRepositoryObj->getRunningNumber(
            'tax_invoice',
            $sale->country->id,
            $sale->transactionLocation->id);

        //insert into invoice table
        $invoiceData = array(
            'sale_id' => $sale->id,
            'invoice_number' => $invoiceRunningNumber,
            'cw_id' => $invoiceCwId,
            'aeon_payment_stock_release_status_id' => $aeonReleaseStatusId,
            'aeon_release_date' => $aeonReleaseDate,
            'stockist_daily_transaction_status_id' => $stockistDailyTransactionStatusId,
            'released_date' => $stockistReleaseDate,
            'document_number' => '',
            'invoice_date' => Carbon::now(),
            'reference_number' => '',
            'self_collection_code' => ''
        );

        $invoiceResult = $this->modelObj->create($invoiceData);

        if($invoiceResult->id){

            $selfPickupMasterDataId = $this->masterDataObj->getIdByTitle($this->saleDeliveryMethodConfigCodes['self pick-up'], 'sale_delivery_method');

            $completeStatusMasterDataId = $this->masterDataObj->getIdByTitle($this->saleOrderStatusConfigCodes['completed'], 'sale_order_status');

            $orderStatusId = ($completeStatusMasterDataId) ? $completeStatusMasterDataId : 0 ;

            if($selfPickupMasterDataId == $sale->delivery_method_id){
                //Generate Self Pick up Document
                $sale->self_pick_up_number = $this->settingsRepositoryObj->getRunningNumber(
                    'self_pick_up',
                    $sale->country->id,
                    $sale->transactionLocation->id
                );
            }

            //update the sales to be completed
            $sale->order_status_id = $orderStatusId;

            $sale->save();

            if ($sale->is_product_exchange){

                //Retrieve Sale Exchange Table
                $saleExchange = $this->saleExchangeObj
                    ->where('sale_id', $sale->id)
                    ->first();

                //Update Sale Exchange Order Status Id Column
                if($saleExchange){
                    $saleExchange->update([
                        'order_status_id' => $orderStatusId
                    ]);
                }
            }

            return true;

        } else {
            return false;
        }
    }

    /**
     * Download pdf of invoice
     *
     * @param int $invoiceId
     * @param boolean $isLegacy
     * @return \Illuminate\Support\Collection
     * @throws \Mpdf\MpdfException
     */
    public function downloadPDF(int $invoiceId, bool $isLegacy = false)
    {
        if ($isLegacy)
        {
            $invoice = $this->legacyInvoiceObj->find($invoiceId);
            $saleCancellation = $this->saleCancellationObj->where('legacy_invoice_id', '=', $invoiceId)->first();
            $saleExchange = $this->saleExchangeObj->where('legacy_invoice_id', '=', $invoiceId)->first();

            if($saleExchange)
            {
                $sale = $saleExchange->sale;
            }
            else if ($saleCancellation)
            {
                $sale = $saleCancellation->sale;
            }
        }
        else
        {
            $invoice = $this->modelObj->find($invoiceId);
            $sale = $invoice->sale;
        }

        if (empty($sale))
        {
            return;
        }

        $user = $sale->user;
        $payments = $sale->salePayments;
        if($sale->tax_rate)
        {
            $taxRate = (round($sale->tax_rate) == $sale->tax_rate)? round($sale->tax_rate) : $sale->tax_rate;
        }
        else
        {
            $taxRate = "0";
        }
        $sponsorUser = $user->member->tree->parent->user;
        $cw = $sale->cw;

        $products = $sale->saleProducts;

        //populate the general info
        $info = array(
            'memberID' => $user->old_member_id,
            'name' => $user->name,
            'collection' => '', //self collection code
            'orderType' => isset($sale->channel) ? $sale->channel->name : '',
            'address' => $user->member->address? $this->memberAddress->getCorrespondenceAddress($user->member->address->address_data): "",
            'tel' => $user->mobile,
            'location' => $sale->transactionLocation->name,
            'transaction_date' => $invoice->transaction_date,
            'cycle' => $cw->cw_name,
            'date' => Carbon::now(),
            'created_at' => $invoice->created_at,
            'salesDate' => $invoice->invoice_date,
            'sponsorID' => $sponsorUser->old_member_id,
            'sponsorName' => $sponsorUser->name,
            'no' => $invoice->invoice_number,
            'taxNo' => '',
            'delivery' => '',
            'businessStyle' => '',
            'saleDocNo' => $sale->document_number,
            'issuer' => $sale->createdBy->name
        );

        //sales products lines
        $salesProducts = array();

        //populate summary product lines
        $productsSummary = array();

        $lineCount = 1;
        $totalProductQty = $totalProductCv = 0;

        $addedNonLooseProduct = array();

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

                    //to indicates that this is already entered into the sale detail
                    $addedNonLooseProduct[$product->mapping_model][$product->mapping_id] = $lineCount;

                    $totalProductQty += $productQty;
                    $totalProductCv += $productCv * $productQty;
                    $lineCount++;

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
        $paymentsSummary = array();
        $payments->each(function($payment) use(&$paymentsSummary){
            if($payment->status == 1){ // only get success one
                $paymentsSummary[] = [
                    'method'=>$payment->paymentModeProvider->name,
                    'total' => $payment->amount
                ];
            }
        });

        $summary = array(
            'items' => $productsSummary,
            'names' => $productsSummaryName,
            'payments' => $paymentsSummary
        );

        // @TODO: check total amount is including admin fee, delivery fee, and other fees
        $sales = array(
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

        $shippingDetails = $sale->SaleShippingAddress;
        $shipping = [];
        
        if($shippingDetails)
        {
            $contact = '';
            
            if ($shippingDetails->country_id != null) {
                $country = $this->countryObj->find($shippingDetails->country_id);
                if ($country != null) {
                    if ($country->code_iso_2 != null) {
                        $contact = $contact . $country->code_iso_2;
                    }
                    if ($country->call_code != null) {
                        $contact = $contact . '+' . $country->call_code;
                    }
                }
            }

            if ($shippingDetails->mobile != null) {
                $contact = $contact . $shippingDetails->mobile;
            }

            $shipping = [
                'name' => $shippingDetails->recipient_name,
                'address' => $this->memberAddress->getAddress($shippingDetails->address, ""), 
                'contact' => $contact
            ];
        }

        $remarks = $sale->remarks;

        //additional/special information needed for different countries
        switch($sale->country->code_iso_2){
            case 'MY' :

                break;
            case 'SG' :

                break;
        }

        $view = 'invoices.tax_invoice.'.strtolower($sale->country->code_iso_2);
        $html = \View::make($view)
            ->with('basic', $info)
            ->with('summary', $summary)
            ->with('sales', $sales)
            ->with('remarks', $remarks)
            ->with('shipping', $shipping)
            ->with('esacVouchers', $esacVouchers)
            ->with('taxRate', $taxRate)
            ->render();

        $config = ['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 0, 'margin_right' => 0, 'margin_top' => 0, 'margin_bottom' => 0];
        $mpdf = new PdfCreator($config);
        //$mpdf->setFooter("Invoice No: INV1712000001632<br/>Page No: {PAGENO} / {nb}");
        $mpdf->WriteHTML($html);
        $total = $mpdf->getTotalPage();

        $config['margin_bottom'] = 20;

        $mpdf = new PdfCreator($config);
        $html = str_replace('{nb}', $total, $html);
        $mpdf->WriteHTML($html);

        $absoluteUrlPath = Config::get('filesystems.subpath.invoice.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('invoice' . $invoiceId) . '.pdf';

        $fileUrl = $this->uploader->createS3File($absoluteUrlPath . $fileName, $mpdf->Output($fileName, "S"), true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * get stockist daily invoice transcation list by below parameter
     *
     * @param PaymentInterface $paymentRepositoryObj
     * @param int $stockistNumber
     * @param $filterDate
     * @param int $stockistDailyTransactionStatusId
     * @return mixed
     */
    public function getStockistDailyInvoiceTransactionList
    (
        PaymentInterface $paymentRepositoryObj,
        int $stockistNumber = 0,
        $filterDate = '',
        int $stockistDailyTransactionStatusId = 0
    )
    {
        //Get Sale Cancellation Status and Cancellation Mode
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_cancellation_status', 'sale_cancellation_type', 'sale_order_status'));

        $cancellationStatusValues = array_change_key_case(
            $settingsData['sale_cancellation_status']->pluck('id','title')->toArray()
        );

        $completeCancellationStatusId = $cancellationStatusValues[
            $this->saleCancellationStatusConfigCodes['completed']];

        $cancellationTypeValues = array_change_key_case(
            $settingsData['sale_cancellation_type']->pluck('id','title')->toArray()
        );

        $sameDayCancellationId = $cancellationTypeValues[
            $this->saleCancellationTypeConfigCodes['same day']];

        $saleOrderStatusValues = array_change_key_case(
            $settingsData['sale_order_status']->pluck('id','title')->toArray()
        );

        $salePreOrderStatusId = $saleOrderStatusValues[
            $this->saleOrderStatusConfigCodes['pre-order']];

        //Get Stockist Details
        $stockist = $this->stockistObj
            ->where('stockist_number', $stockistNumber)
            ->first();

        $stockistDetail = $this->stockistRepositoryObj
            ->stockistDetails($stockist->stockist_user_id);

        //Get Stockist country and Location
        $stockistLocationId = $stockist->stockistLocation->id;

        $stockistCountryId = $stockist->country_id;

        //Get Providers Details
        $paymentModeProviders = [];

        $paymentModes = $paymentRepositoryObj
            ->getSupportedPayments($stockistCountryId, $stockistLocationId);

        collect($paymentModes)->each(function($paymentMode)
            use(&$paymentModeProviders){
                collect($paymentMode['payment_mode_provider'])->each(function($provider)
                    use(&$paymentModeProviders){
                        array_push($paymentModeProviders, $provider);
                });
            });

        //Retrieve Invoice Records
        $invoices = $this->modelObj
            ->with(['sale', 'sale.member', 'sale.user', 'stockistDailyTransactionStatus'])
            ->where("invoice_date", $filterDate);

        if ($stockistDailyTransactionStatusId > 0) {
            $invoices = $invoices
                ->where('stockist_daily_transaction_status_id', $stockistDailyTransactionStatusId);
        }

        $invoices = $invoices->get();

        //Get Stockist Invoice
        $stockistInvoices = $stockistPreOrderSales = $stockistCreditNotes = [];

        collect($invoices)->each(function($invoice)
            use(&$stockistInvoices, $stockistLocationId, $paymentModeProviders){

                //Get Stockist Location ID
                $saleLocationId = $invoice->sale->transaction_location_id;

                if($saleLocationId == $stockistLocationId){

                    $invoicePaymentModes = $approvalCodes = [];

                    $totalInvoiceAmount = 0;

                    collect($paymentModeProviders)->each(function($invoicePaymentModeProvider)
                        use(&$invoicePaymentModes, &$totalInvoiceAmount, &$approvalCodes, $invoice){

                            //Get Sale Payment Json Detail
                            $salePayments = $invoice->sale
                                ->salePayments()
                                ->where('status', 1)
                                ->where('payment_mode_provider_id', $invoicePaymentModeProvider->id)
                                ->get();

                            //Get all related agreement no approval code
                            collect($salePayments)->each(function($salePayment)
                                use(&$approvalCodes){

                                    $paymentDetails = json_decode($salePayment->payment_detail, true);

                                    if(isset($paymentDetails['payment_response'])){

                                        if(isset($paymentDetails['payment_response']['agreement_no'])){

                                            array_push($approvalCodes,
                                                $paymentDetails['payment_response']['agreement_no']);

                                        } else if(isset($paymentDetails['payment_response']['approval_code'])){

                                            array_push($approvalCodes,
                                                $paymentDetails['payment_response']['approval_code']);
                                        }
                                    }
                            });

                            //To prevent make multiple pay in same payment mode
                            $totalAmount = $invoice->sale
                                ->salePayments()
                                ->where('status', 1)
                                ->where('payment_mode_provider_id', $invoicePaymentModeProvider->id)
                                ->sum('amount');

                            $totalInvoiceAmount += $totalAmount;

                            $paymentMode = [
                                'id' => $invoicePaymentModeProvider->id,
                                'master_data_id' => $invoicePaymentModeProvider->master_data_id,
                                'code' => $invoicePaymentModeProvider->code,
                                'name' => $invoicePaymentModeProvider->name,
                                'payment_mode_setting' => $invoicePaymentModeProvider->payment_mode_setting,
                                'total_amount' => $totalAmount
                            ];

                            $invoicePaymentModes[$invoicePaymentModeProvider->code] = $paymentMode;
                    });

                    $invoice->paymentMode = $invoicePaymentModes;

                    $invoice->total_amount = $totalInvoiceAmount;

                    $invoice->approval_code = implode(" / ", $approvalCodes);

                    $invoice->account_payable = 0;

                    $invoice->account_receive = 0;

                    $invoice->credit_note_number = '';

                    array_push($stockistInvoices, $invoice);
                }
            });

        //Retrieve Pre-order Sale Records
        $saleRecords = $this->saleObj
            ->where('transaction_location_id', $stockistLocationId)
            ->where('order_status_id', $salePreOrderStatusId)
            ->where("transaction_date", $filterDate)
            ->get();

        collect($saleRecords)->each(function($sale)
            use(&$stockistPreOrderSales, $paymentModeProviders){

                $preOrderSalePaymentModes = $approvalCodes = [];

                $totalPreOrderSaleAmount = 0;

                collect($paymentModeProviders)->each(function($preOrderSalePaymentModeProvider)
                    use(&$preOrderSalePaymentModes, &$totalPreOrderSaleAmount, &$approvalCodes, $sale){

                        //Get Sale Payment Json Detail
                        $salePayments = $sale->salePayments()
                            ->where('status', 1)
                            ->where('payment_mode_provider_id', $preOrderSalePaymentModeProvider->id)
                            ->get();

                        //Get all related agreement no approval code
                        collect($salePayments)->each(function($salePayment)
                            use(&$approvalCodes){

                                $paymentDetails = json_decode($salePayment->payment_detail, true);

                                if(isset($paymentDetails['payment_response'])){

                                    if(isset($paymentDetails['payment_response']['agreement_no'])){

                                        array_push($approvalCodes,
                                            $paymentDetails['payment_response']['agreement_no']);

                                    } else if(isset($paymentDetails['payment_response']['approval_code'])){

                                        array_push($approvalCodes,
                                            $paymentDetails['payment_response']['approval_code']);
                                    }
                                }
                        });

                        $totalAmount = $sale->salePayments()
                            ->where('status', 1)
                            ->where('payment_mode_provider_id', $preOrderSalePaymentModeProvider->id)
                            ->sum('amount');

                        $totalPreOrderSaleAmount += $totalAmount;

                        $paymentMode = [
                            'id' => $preOrderSalePaymentModeProvider->id,
                            'master_data_id' => $preOrderSalePaymentModeProvider->master_data_id,
                            'code' => $preOrderSalePaymentModeProvider->code,
                            'name' => $preOrderSalePaymentModeProvider->name,
                            'payment_mode_setting' => $preOrderSalePaymentModeProvider->payment_mode_setting,
                            'total_amount' => $totalAmount
                        ];

                        $preOrderSalePaymentModes[$preOrderSalePaymentModeProvider->code] = $paymentMode;
                });

                //Map with stockist invoice Obj
                $preOrderSaleDetail = [
                    "id" => $sale->id,
                    "sale_id" => $sale->id,
                    "cw_id" => $sale->cw_id,
                    "stockist_daily_transaction_status_id" => "",
                    "released_date" => "",
                    "invoice_number" => "",
                    "document_number" => "",
                    "invoice_date" => $sale->transaction_date,
                    "reference_number" => "",
                    "self_collection_code" => "",
                    "created_by" => $sale->created_by,
                    "updated_by" => $sale->updated_by,
                    "created_at" => $sale->created_at->toDateTimeString(),
                    "updated_at" => $sale->updated_at->toDateTimeString(),
                    "paymentMode" => $preOrderSalePaymentModes,
                    "total_amount" => $totalPreOrderSaleAmount,
                    "approval_code" => implode(" / ", $approvalCodes),
                    "account_payable" => 0,
                    "account_receive" => 0,
                    "credit_note_number" => '',
                    "sale" => $sale,
                    "stockist_daily_transaction_status" => []
                ];

                array_push($stockistPreOrderSales, $preOrderSaleDetail);

            });

        //Retrieve Sale Cancellation Records
        $creditNotes = $this->creditNoteObj
            ->with(['sale', 'sale.member', 'sale.user'])
            ->join('sales_cancellations', function ($join){
                $join->on('credit_notes.mapping_id', '=', 'sales_cancellations.id')
                    ->where('credit_notes.mapping_model', 'sales_cancellations');
            })
            ->where("credit_notes.credit_note_date", $filterDate)
            ->where("sales_cancellations.cancellation_status_id", $completeCancellationStatusId)
            ->whereNotNull("credit_notes.sale_id")
            ->select(
                'credit_notes.*',
                'sales_cancellations.cw_id',
                'sales_cancellations.cancellation_type_id',
                'sales_cancellations.invoice_id',
                'sales_cancellations.total_buy_back_amount'
            )
            ->get();

        collect($creditNotes)->each(function($creditNote)
            use(&$stockistCreditNotes, $sameDayCancellationId,
                $stockistLocationId, $stockistInvoices, $paymentModeProviders){

                     //Get Stockist Location ID
                    $saleLocationId = $creditNote->sale->transaction_location_id;

                    if($saleLocationId == $stockistLocationId){

                        $creditNotesPaymentModes = [];

                        if($creditNote->cancellation_type_id == $sameDayCancellationId){

                            $creditNoteInvoice = collect($stockistInvoices)
                                ->where('id', $creditNote->invoice_id)->first();

                            $creditNoteInvoicePaymentModes = $creditNoteInvoice->paymentMode;

                            collect($creditNoteInvoicePaymentModes)->each(function($creditNoteInvoicePaymentModeProvider)
                                use(&$creditNotesPaymentModes){

                                    $paymentMode = [
                                        'id' => $creditNoteInvoicePaymentModeProvider['id'],
                                        'master_data_id' => $creditNoteInvoicePaymentModeProvider['master_data_id'],
                                        'code' => $creditNoteInvoicePaymentModeProvider['code'],
                                        'name' => $creditNoteInvoicePaymentModeProvider['name'],
                                        'payment_mode_setting' => $creditNoteInvoicePaymentModeProvider['payment_mode_setting'],
                                        'total_amount' => floatval($creditNoteInvoicePaymentModeProvider['total_amount']) * -1
                                    ];

                                    $creditNotesPaymentModes[$creditNoteInvoicePaymentModeProvider['code']] = $paymentMode;
                                });

                            $totalCreditNoteAmount = floatval($creditNote->total_buy_back_amount) * -1;

                            $accountPayable = 0;

                        } else {

                            collect($paymentModeProviders)->each(function($paymentModeProvider)
                                use(&$creditNotesPaymentModes){
                                    $paymentMode = [
                                        'id' => $paymentModeProvider->id,
                                        'master_data_id' => $paymentModeProvider->master_data_id,
                                        'code' => $paymentModeProvider->code,
                                        'name' => $paymentModeProvider->name,
                                        'payment_mode_setting' => $paymentModeProvider->payment_mode_setting,
                                        'total_amount' => 0
                                    ];

                                    $creditNotesPaymentModes[$paymentModeProvider->code] = $paymentMode;
                                });

                            $totalCreditNoteAmount = 0;

                            $accountPayable = $creditNote->total_buy_back_amount;

                        }

                        //Map with stockist invoice Obj
                        $creditNoteDetail = [
                            "id" => $creditNote->id,
                            "sale_id" => $creditNote->sale_id,
                            "cw_id" => $creditNote->cw_id,
                            "stockist_daily_transaction_status_id" => "",
                            "released_date" => "",
                            "invoice_number" => "",
                            "document_number" => "",
                            "invoice_date" => $creditNote->credit_note_date,
                            "reference_number" => "",
                            "self_collection_code" => "",
                            "created_by" => $creditNote->created_by,
                            "updated_by" => $creditNote->updated_by,
                            "created_at" => $creditNote->created_at->toDateTimeString(),
                            "updated_at" => $creditNote->updated_at->toDateTimeString(),
                            "paymentMode" => $creditNotesPaymentModes,
                            "total_amount" => $totalCreditNoteAmount,
                            "approval_code" => "",
                            "account_payable" => $accountPayable,
                            "account_receive" => 0,
                            "credit_note_number" => $creditNote->credit_note_number,
                            "sale" => $creditNote->sale,
                            "stockist_daily_transaction_status" => []
                        ];

                        array_push($stockistCreditNotes, $creditNoteDetail);
                    }
                });

        return [
            'stockist_data' => $stockistDetail['stockist_data'],
            'payment_mode' => $paymentModeProviders,
            'stockist_invoice' => $stockistInvoices,
            'stockist_pre_order_sale' => $stockistPreOrderSales,
            'stockist_credit_note' => $stockistCreditNotes
        ];
    }

    /**
     * batch release stockist invoice transaction
     *
     * @param array $stockistInvoices
     * @return mixed
     */
    public function batchReleaseStockistDailyInvoiceTransaction(array $stockistInvoices)
    {
        //Get Status ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('stockist_daily_transaction_release_status'));

        $releaseStatusValues = array_change_key_case(
            $settingsData['stockist_daily_transaction_release_status']->pluck('id','title')->toArray()
        );

        $transactionReleaseStatus = $this->stockistTransactionReleaseStatusConfigCodes['released'];

        collect($stockistInvoices)->each(function($stockistInvoiceId)
            use($releaseStatusValues, $transactionReleaseStatus){

            //Retrieve Stockist Sale Payment Details
            $invoiceRecord = $this->modelObj->find($stockistInvoiceId);

            $invoiceRecord->update([
                'stockist_daily_transaction_status_id'
                    => $releaseStatusValues[$transactionReleaseStatus],
                'released_date' => Carbon::now()
            ]);
        });

        return [
            "result" => true
        ];
    }

    /**
     * To get sales with pending integration
     *
     * @param bool $isIntegrated
     * @return mixed
     */
    public function getIntegrationReleaseStockistSales(bool $isIntegrated)
    {
        $data = $this->modelObj
            ->where('invoices.integration_flag', $isIntegrated)
            ->whereNotNull('invoices.stockist_daily_transaction_status_id')
            ->whereNotNull('invoices.released_date')
            ->get();
        return $data;
    }
    
    /**
     * To download pdf and export as content-stream header 'application/pdf'
     *
     * @param int $invoiceId
     * @param boolean $isLegacy
     * @return mixed
     */
    public function downloadAutoMaintenanceInvoice(int $invoiceId, bool $isLegacy = false)
    {
        if ($isLegacy)
        {
            $invoice = $this->legacyInvoiceObj->find($invoiceId);
            $saleCancellation = $this->saleCancellationObj->where('legacy_invoice_id', '=', $invoiceId)->first();
            $saleExchange = $this->saleExchangeObj->where('legacy_invoice_id', '=', $invoiceId)->first();

            if($saleExchange)
            {
                $sale = $saleExchange->sale;
            }
            else if ($saleCancellation)
            {
                $sale = $saleCancellation->sale;
            }
        }
        else
        {
            $invoice = $this->modelObj->find($invoiceId);
            $sale = $invoice->sale;
        }

        if (empty($sale))
        {
            return;
        }
        $user = $sale->user;
        
        $sponsorUser = $user->member->tree->parent->user;

        //populate the general info
        $info = array(
            'memberID' => $user->old_member_id,
            'name' => $user->name,
            'collection' => '', //self collection code
            'orderType' => isset($sale->channel->title) ? $sale->channel->title : '',
            'address' => $user->member->address? $this->memberAddress->getCorrespondenceAddress($user->member->address->address_data): "",
            'tel' => $user->mobile,
            'location' => $sale->transactionLocation->name,
            'transaction_date' => $invoice->transaction_date,
            'cycle' => $sale->cw->cw_name,
            'created_at' => $invoice->created_at,
            'salesDate' => $invoice->invoice_date,
            'sponsorID' => $sponsorUser->old_member_id,
            'sponsorName' => $sponsorUser->name,
            'no' => $invoice->invoice_number,
            'taxNo' => '',
            'delivery' => '',
            'businessStyle' => '',
            'issuer' => $sale->createdBy->name
        );

        $products = $sale->saleProducts;

        $salesProducts = array();

        $totalProductQty = $totalProductCv = 0;

        $settingsData = $this->masterRepositoryObj->getMasterDataByKey([
            'sale_types'
        ]);

        //Get Sale Type
        $saleType = array_change_key_case($settingsData['sale_types']
            ->pluck('id','title')->toArray());

        $autoMaintenanceCode = $this->transactionTypeConfigCodes['auto-maintenance'];

        $autoMaintenanceId = $saleType[$autoMaintenanceCode];

        $formationCode = $this->transactionTypeConfigCodes['formation'];

        $formationId = $saleType[$formationCode];

        foreach($products as $product)
        {
            if ($product->transaction_type_id == $autoMaintenanceId)
            {
                $totalProductQty += $product->quantity;

                $totalProductCv += $product->quantity * $product->virtual_invoice_cv;

            } else if($product->transaction_type_id == $formationId){

                $totalProductQty += $product->quantity;

                $totalProductCv += $product->quantity * $product->virtual_invoice_cv;
            }
        }

        $salesProducts[] = array(
            'no' => 1,
            'tos' => '',
            'code' => "AMPVIRTUAL",
            'description' => "AMP VIRTUAL",
            'qty' => $totalProductQty,
            'cv' => $totalProductCv,
            'unitPrice' => 0.00,
            'subTotal' => 0.00,
            'discount' => 0.00,
            'excTax' => 0.00,
            'tax' => 0.00,
            'total' => 0.00
        );

        $payments = $sale->salePayments;

        //payments
        $paymentsSummary = array();
        $payments->each(function($payment) use(&$paymentsSummary){
            if($payment->status == 1){ // only get success one
                $paymentsSummary[] = [
                    'method'=>$payment->paymentModeProvider->name,
                    'total' => $payment->amount
                ];
            }
        });

        if($sale->tax_rate)
        {
            $taxRate = (round($sale->tax_rate) == $sale->tax_rate)? round($sale->tax_rate) : $sale->tax_rate;
        }
        else
        {
            $taxRate = "0";
        }

        $summary = array(
            'items' => [],
            'payments' => $paymentsSummary
        );

        $sales = array(
            'products' => $salesProducts,
            'subTotal' => [
                'qty' => $totalProductQty, 
                'cv' =>  $totalProductCv, 
                'excTax' => 0.00, 
                'tax' => 0.00, 
                'total' => 0.00,
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ],
            'delivery' => ['excTax' => 0.00, 'tax' => 0.00, 'total' => 0.00],
            'admin' => ['excTax' => 0.00, 'tax' => 0.00, 'total' => 0.00],
            'other' => ['excTax' => 0.00, 'tax' => 0.00, 'total' => 0.00],
            'total' => ['excTax' => 0.00, 'tax' => 0.00, 'total' => 0.00, 'exempt' => 0.00, 'zeroRated' => 0.00]
        );

        $shippingDetails = $sale->SaleShippingAddress;

        if($shippingDetails)
        {
            $contact = '';
            
            if ($shippingDetails->country_id != null) {
                $country = $this->countryObj->find($shippingDetails->country_id);
                if ($country != null) {
                    if ($country->code_iso_2 != null) {
                        $contact = $contact . $country->code_iso_2;
                    }
                    if ($country->call_code != null) {
                        $contact = $contact . '+' . $country->call_code;
                    }
                }
            }

            if ($shippingDetails->mobile != null) {
                $contact = $contact . $shippingDetails->mobile;
            }

            $shipping = [
                'name' => $shippingDetails->recipient_name,
                'address' => $this->memberAddress->getAddress($shippingDetails->address, ""), 
                'contact' => $contact
            ];
        }

        $remarks = $sale->remarks;

        $view = 'invoices.auto_maintenance.'.strtolower($sale->country->code_iso_2);
        $html = \View::make($view)
            ->with('basic', $info)
            ->with('summary', $summary)
            ->with('sales', $sales)
            ->with('remarks', $remarks)
            ->with('shipping', $shipping)
            ->with('taxRate', $taxRate)
            ->render();

        $config = ['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 0, 'margin_bottom' => 0];
        $mpdf = new PdfCreator($config);
        //$mpdf->setFooter("Invoice No: INV1712000001632<br/>Page No: {PAGENO} / {nb}");
        $mpdf->WriteHTML($html);
        $total = $mpdf->getTotalPage();

        $config['margin_bottom'] = 20;

        $mpdf = new PdfCreator($config);
        $html = str_replace('{nb}', $total, $html);
        $mpdf->WriteHTML($html);
        
        $absoluteUrlPath = Config::get('filesystems.subpath.invoice.absolute_url_path');
        
        $fileName = $this->uploader->getRandomFileName('auto_maintenance_' . $invoiceId) . '.pdf';
        
        $fileUrl = $this->uploader->createS3File($absoluteUrlPath . $fileName, $mpdf->Output($fileName, "S"), true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * To download tax invoice summary report in excel format
     *
     * @param int $countryId
     * @param array $locationIds
     * @param string $fromDate
     * @param string $toDate
     * @param int $fromCw
     * @param int $toCw
     * @param array $iboIds
     * @param int $status
     * @return mixed
     */
    public function downloadTaxInvoiceSummaryReport
    (
        $countryId = 0,
        array $locationIds,
        $fromDate = "",
        $toDate = "",
        $fromCw = 0,
        $toCw = 0,
        array $iboIds,
        $status = 0
    )
    {
        $invoices = $this->modelObj
                        ->join('sales', 'sales.id', '=', 'invoices.sale_id')
                        ->where('sales.country_id', $countryId)
                        ->whereBetween("invoice_date", [$fromDate, $toDate])
                        ->whereBetween("invoices.cw_id", [$fromCw, $toCw]);

        if (count($locationIds)>0)
        {
            $invoices = $invoices->whereIn('sales.transaction_location_id', $locationIds);
        }

        if (count($iboIds)>0)
        {
            $invoices = $invoices->whereIn('sales.user_id', $iboIds);
        }
        
        if ($status > 0)
        {
            $invoices = $invoices->where('sales.order_status_id', '=', $status);
        }

        $invoices = $invoices->get();

        $countries = $invoices->groupBy('country_id');

        $spreadsheet = new Spreadsheet();

        //inserting header into spreadsheet
        $header = ["Country Code", "CW", "Sales Channel", "Location Code", "Location Name", "Inv. Date & Time", "Inv. No", "IBO ID", "IBO Name", "Status", "Pick-up Location", "Total NMP (Excl. Tax)", "Admin Fee", "Delivery Fee", "Other Fee", "Tax Amt", "Total GMP (Incl. Tax)", "Total CV", "CV (WP)", "CV (Others)", "GMP (WP)", "GMP (Others)", "Sponsor ID", "Sponsor Code", "Sponsor Country", "Remarks"];

        $col = "A";

        foreach ($header as $value)
        {
            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }

        $row = 2;

        //Get WP type transaction ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey([
            'sale_types', 'amp_cv_allocation_types'
        ]);

        $saleType = array_change_key_case($settingsData['sale_types']
            ->pluck('id','title')->toArray());

        $registrationCode = $this->transactionTypeConfigCodes['registration'];
        $upgradeCode = $this->transactionTypeConfigCodes['ba-upgrade'];

        $wpIds = array($saleType[$registrationCode], $saleType[$upgradeCode]);

        foreach($countries as $countryId => $invoices)
        {
            $totalAmount = $adminFees = $deliveryFees = $otherFees = $taxAmount = $totalGmp = $totalCv = $wpCv = $otherCv = $wpGmp = $otherGmp = 0;

            foreach ($invoices as $invoice)
            {

                $products = $invoice->sale->saleProducts;
                $wpProducts = $products->whereIn('transaction_type_id', $wpIds);
                $otherProducts = $products->whereNotIn('transaction_type_id', $wpIds);
                $sale = $invoice->sale;
                $user = $sale->user;
                $tree = $user->member->tree;
                $sponsorUser = (!empty($tree) && !empty($tree->parent))? $tree->parent->user: null;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue("A".$row, $sale->country->code_iso_2);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("B".$row, $invoice->cw->cw_name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("C".$row, $sale->channel->name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("D".$row, $sale->transactionLocation->code);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("E".$row, $sale->transactionLocation->name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("F".$row, $invoice->created_at);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("G".$row, $invoice->invoice_number);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("H".$row, $user->old_member_id);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("I".$row, $user->name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("J".$row, $sale->orderStatus->title);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("K".$row, $sale->selfCollectionPoint?$invoice->sale->selfCollectionPoint->location->code: "");
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("L".$row, $sale->total_amount);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("M".$row, $sale->admin_fees);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("N".$row, $sale->delivery_fees);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("O".$row, $sale->other_fees);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("P".$row, $sale->tax_amount);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("Q".$row, $sale->total_gmp);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("R".$row, $sale->total_cv);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("S".$row, $wpProducts->sum('eligible_cv'));
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("T".$row, $otherProducts->sum('eligible_cv'));
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("U".$row, $wpProducts->sum('gmp_price_gst'));
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("V".$row, $otherProducts->sum('gmp_price_gst'));
                if (!empty($sponsorUser))
                {
                    $spreadsheet->setActiveSheetIndex(0)->setCellValue("W".$row, $sponsorUser->old_member_id);
                    $spreadsheet->setActiveSheetIndex(0)->setCellValue("X".$row, $sponsorUser->name);
                    $spreadsheet->setActiveSheetIndex(0)->setCellValue("Y".$row, $sponsorUser->member->country->code_iso_2);
                }
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("Z".$row, $sale->remarks);

                $spreadsheet->getActiveSheet()->getStyle("L".$row.":Q".$row)
                            ->getNumberFormat()->setFormatCode('#,##0.00');
                $spreadsheet->getActiveSheet()->getStyle("U".$row)->getNumberFormat()->setFormatCode('#,##0.00');
                $spreadsheet->getActiveSheet()->getStyle("V".$row)->getNumberFormat()->setFormatCode('#,##0.00');

                $totalAmount += $sale->total_amount;
                $adminFees += $sale->admin_fees;
                $deliveryFees += $sale->delivery_fees;
                $otherFees += $sale->other_fees;
                $taxAmount += $sale->tax_amount;
                $totalGmp += $sale->total_gmp;
                $totalCv += $sale->total_cv;
                $wpCv += $wpProducts->sum('eligible_cv');
                $otherCv += $otherProducts->sum('eligible_cv');
                $wpGmp += $wpProducts->sum('gmp_price_gst');
                $otherGmp += $otherProducts->sum('gmp_price_gst');

                $row++;
            }
            $styleArray = [
                'font' => [
                    'bold' => true,
                ]
            ];

            $spreadsheet->setActiveSheetIndex(0)->setCellValue("A".$row, "Total By Country");
            $spreadsheet->getActiveSheet()->getStyle("A".$row)->applyFromArray($styleArray);

            $spreadsheet->setActiveSheetIndex(0)->setCellValue("L".$row, $totalAmount);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("M".$row, $adminFees);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("N".$row, $deliveryFees);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("O".$row, $otherFees);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("P".$row, $taxAmount);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("Q".$row, $totalGmp);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("R".$row, $totalCv);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("S".$row, $wpCv);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("T".$row, $otherCv);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("U".$row, $wpGmp);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("V".$row, $otherGmp);

            $styleArray = [
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                    ],
                ],
            ];

            $spreadsheet->getActiveSheet()->getStyle("L".$row.":V".$row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle("L".$row.":Q".$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $spreadsheet->getActiveSheet()->getStyle("U".$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $spreadsheet->getActiveSheet()->getStyle("V".$row)->getNumberFormat()->setFormatCode('#,##0.00');
                
            $row = $row+2;
        }
        

        // Output excel file
        $outputPath = Config::get('filesystems.subpath.invoice.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.invoice.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('tax_invoice_summary') . '.xlsx';
        
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
     * To download tax invoice product details report in excel format
     *
     * @param int $countryId
     * @param array $locationIds
     * @param string $fromDate
     * @param string $toDate
     * @param int $fromCw
     * @param int $toCw
     * @param array $iboIds
     * @return mixed
     */
    public function downloadTaxInvoiceDetailsReport
    (
        $countryId = 0,
        array $locationIds,
        $fromDate = "",
        $toDate = "",
        $fromCw = 0,
        $toCw = 0,
        array $iboIds
    )
    {
        $saleProducts = $this->saleProductObj
                        ->join('sales', 'sales.id', '=', 'sales_products.sale_id')
                        ->join('invoices', 'sales.id', '=', 'invoices.sale_id')
                        ->where('sales.country_id', $countryId)
                        ->whereBetween("invoice_date", [$fromDate, $toDate])
                        ->whereBetween("invoices.cw_id", [$fromCw, $toCw]);

        if (count($locationIds)>0)
        {
            $saleProducts = $saleProducts->whereIn('sales.transaction_location_id', $locationIds);
        }

        if (count($iboIds)>0)
        {
            $saleProducts = $saleProducts->whereIn('sales.user_id', $iboIds);
        }
        
        $saleProducts = $saleProducts->get();

        $countries = $saleProducts->groupBy('country_id');

        $spreadsheet = new Spreadsheet();

        //inserting header into spreadsheet
        $header = ["Country Code", "CW", "Sales Channel", "Location Code", "Location Name", "Location Type", "Inv. Date", "Inv. No", "Transaction Type", "IBO's Registered Country", "IBO ID", "IBO Name", "D/O No.", "Doc Date", "Doc. No.", "CN Amt", "Broad Category", "Kit", "Kit/SKU Code", "Kit/SKU Description", "Qty", "Price Code", "Total NMP (Excl. Tax)", "Tax Amt", "Total GMP (Incl. Tax)", "Total CV", "CV (WP)", "CV (Others)", "GMP (WP)", "GMP (Others)", "Sponsor ID", "Sponsor Name", "Sponsor Country", "L2 Sponsor ID", "L2 Sponsor Name", "L2 Sponsor Country", "Remarks"];

        $col = "A";

        foreach ($header as $value)
        {
            $cell = $col."1";

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }

        $row = 2;

        //Get WP type transaction ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey([
            'sale_types', 'amp_cv_allocation_types'
        ]);

        $saleType = array_change_key_case($settingsData['sale_types']
            ->pluck('id','title')->toArray());

        $registrationCode = $this->transactionTypeConfigCodes['registration'];
        $upgradeCode = $this->transactionTypeConfigCodes['ba-upgrade'];

        $wpIds = array($saleType[$registrationCode], $saleType[$upgradeCode]);

        foreach($countries as $countryId => $saleProducts)
        {
            $totalAmount = $adminFees = $deliveryFees = $otherFees = $taxAmount = $totalGmp = $totalCv = $wpCv = $otherCv = $wpGmp = $otherGmp = 0;

            foreach ($saleProducts as $product)
            {
                $sale = $product->sale;
                $invoice = $sale->invoices;
                $user = $sale->user;
                if (in_array($product->transaction_type_id, $wpIds))
                {
                    $cv_wp = $product->eligible_cv;
                    $cv_other = 0;
                    $gmp_wp = $product->total_gmp;
                    $gmp_other = 0;
                }
                else
                {
                    $cv_wp = 0;
                    $cv_other = $product->eligible_cv;
                    $gmp_wp = 0;
                    $gmp_other = $product->total_gmp;
                }
                $sponsorUser = $user->member->tree->parent->user;
                $l2SponsorUser = $sponsorUser->member->tree->parent?$sponsorUser->member->tree->parent->user: null;
                $deliveryOrders = $sale->deliveryOrder->pluck('delivery_order_number');
                if (count($deliveryOrders)>0)
                {
                    $deliveryOrders = $deliveryOrders->implode(', ');
                }
                else
                {
                    $deliveryOrders = "";
                }

                $docNo = NULL;

                $cnAmt = 0;

                if ($sale->is_product_exchange)
                {
                    $docNo = $sale->saleExchange->saleExchangeBill->exchange_bill_number;
                    $cnAmt = $sale->saleExchange->saleExchangeProducts->sum('total');
                }
                else
                {
                    $saleCancellation = $this->saleCancellationObj->where('sale_id', $sale->id)->first();

                    if ($saleCancellation && !empty($saleCancellation->creditNote))
                    {
                        $docNo = optional($saleCancellation->creditNote()->first())->credit_note_number;
                        $cnAmt = $saleCancellation->total_amount;
                    }
                }

                $kit = "N";
                switch($product->mapping_model){
                    case 'sales_kitting_clone' :
                        $kit = "Y";
                        break;
                    case 'sales_promotion_free_items_clone' :
                        $kit = $product->getMappedModel->promotionType->title;
                        break;
                    default :
                        $kit = "N";
                }
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("A".$row, $sale->country->code_iso_2);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("B".$row, $sale->cw->cw_name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("C".$row, $sale->channel->name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("D".$row, $sale->transactionLocation->code);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("E".$row, $sale->transactionLocation->name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("F".$row, $sale->transactionLocation->locationType->code);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("G".$row, $invoice->invoice_date);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("H".$row, $invoice->invoice_number);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("I".$row, optional($product->transactionType()->first())->title);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("J".$row, $user->member->country->code_iso_2);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("K".$row, $user->old_member_id);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("L".$row, $user->name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("M".$row, $deliveryOrders);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("N".$row, $sale->transaction_date);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("O".$row, $docNo);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("P".$row, $cnAmt);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("Q".$row, ""); //Broad Category
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("R".$row, $kit);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("S".$row, $product->product->sku);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("T".$row, $product->product->name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("U".$row, $product->quantity);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("V".$row, ""); //Price Code
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("W".$row, $invoice->sale->total_amount);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("X".$row, $invoice->sale->tax_amount);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("Y".$row, $invoice->sale->total_gmp);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("Z".$row, $invoice->sale->total_cv);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AA".$row, $cv_wp);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AB".$row, $cv_other);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AC".$row, $gmp_wp);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AD".$row, $gmp_other);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AE".$row, $sponsorUser->old_member_id);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AF".$row, $sponsorUser->name);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AG".$row, $sponsorUser->member->country->code_iso_2);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AH".$row, $l2SponsorUser? $l2SponsorUser->old_member_id: "");
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AI".$row, $l2SponsorUser? $l2SponsorUser->name:"");
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AJ".$row, $l2SponsorUser? $l2SponsorUser->member->country->code_iso_2: "");
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("AK".$row, $sale->remarks);

                $spreadsheet->getActiveSheet()->getStyle("P".$row)->getNumberFormat()->setFormatCode('#,##0.00');
                $spreadsheet->getActiveSheet()->getStyle("W".$row.":Y".$row)
                            ->getNumberFormat()->setFormatCode('#,##0.00');
                $spreadsheet->getActiveSheet()->getStyle("AC".$row)->getNumberFormat()->setFormatCode('#,##0.00');
                $spreadsheet->getActiveSheet()->getStyle("AD".$row)->getNumberFormat()->setFormatCode('#,##0.00');

                $totalAmount += $invoice->sale->total_amount;
                $taxAmount += $invoice->sale->tax_amount;
                $totalGmp += $invoice->sale->total_gmp;
                $totalCv += $invoice->sale->total_cv;
                $wpCv += $cv_wp;
                $otherCv += $cv_other;
                $wpGmp += $gmp_wp;
                $otherGmp += $gmp_other;

                $row++;
            }
            $styleArray = [
                'font' => [
                    'bold' => true,
                ]
            ];

            $spreadsheet->setActiveSheetIndex(0)->setCellValue("A".$row, "Total By Country");
            $spreadsheet->getActiveSheet()->getStyle("A".$row)->applyFromArray($styleArray);

            $spreadsheet->setActiveSheetIndex(0)->setCellValue("W".$row, $totalAmount);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("X".$row, $taxAmount);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("Y".$row, $totalGmp);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("Z".$row, $totalCv);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("AA".$row, $wpCv);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("AB".$row, $otherCv);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("AC".$row, $wpGmp);
            $spreadsheet->setActiveSheetIndex(0)->setCellValue("AD".$row, $otherGmp);

            $styleArray = [
                'borders' => [
                    'top' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    ],
                    'bottom' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                    ],
                ],
            ];

            $spreadsheet->getActiveSheet()->getStyle("W".$row.":AD".$row)->applyFromArray($styleArray);
            $spreadsheet->getActiveSheet()->getStyle("W".$row.":Y".$row)
                        ->getNumberFormat()->setFormatCode('#,##0.00');
            $spreadsheet->getActiveSheet()->getStyle("AC".$row)->getNumberFormat()->setFormatCode('#,##0.00');
            $spreadsheet->getActiveSheet()->getStyle("AD".$row)->getNumberFormat()->setFormatCode('#,##0.00');
                
            $row = $row+2;
        }
        
        // Output excel file
        $outputPath = Config::get('filesystems.subpath.invoice.storage_path');

        $absoluteUrlPath = Config::get('filesystems.subpath.invoice.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('tax_invoice_details') . '.xlsx';
        
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