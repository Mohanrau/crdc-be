<?php
namespace App\Repositories\Sales;

use App\Interfaces\{
    Sales\SaleExchangeInterface,
    Sales\SaleInterface,
    Invoices\InvoiceInterface,
    Kitting\KittingInterface,
    Products\ProductInterface,
    Masters\MasterInterface,
    Settings\SettingsInterface
};
use App\{
    Helpers\Traits\AccessControl, 
    Models\Invoices\LegacyInvoice, 
    Models\Locations\Country, 
    Models\Locations\Location, 
    Models\Masters\MasterData, 
    Models\Sales\LegacySaleExchangeKittingClone, 
    Models\Sales\LegacySaleExchangeProduct, 
    Models\Sales\LegacySaleExchangeProductClone, 
    Models\Sales\SaleExchange, 
    Models\Sales\SaleExchangeKitting, 
    Models\Sales\SaleExchangeProduct, 
    Models\Sales\SaleExchangeBill, 
    Models\Sales\SaleKittingClone, 
    Models\Sales\SalePromotionFreeItemClone, 
    Models\Sales\SaleProduct, 
    Models\Sales\CreditNote, 
    Models\Users\User, 
    Models\Products\Product, 
    Repositories\BaseRepository, 
    Helpers\Classes\PdfCreator, 
    Helpers\Classes\MemberAddress,
    Helpers\Classes\Uploader
};
use Illuminate\Support\Facades\{
    Storage,
    Config
};
use Auth;

class SaleExchangeRepository extends BaseRepository implements SaleExchangeInterface
{
    use AccessControl;

    private
        $saleExchangeProductObj,
        $saleExchangeKittingObj,
        $saleExchangeBillObj,
        $saleRepositoryObj,
        $saleProductObj,
        $saleKittingObj,
        $salePromotionObj,
        $creditNoteObj,
        $masterRepositoryObj,
        $masterDataObj,
        $invoiceRepositoryObj,
        $kittingRepositoryObj,
        $productRepositoryObj,
        $countryObj,
        $locationObj,
        $legacyInvoiceObj,
        $legacySaleExchangeKittingCloneObj,
        $legacySaleExchangeProductObj,
        $legacySaleExchangeProductCloneObj,
        $settingRepositoryObj,
        $userObj,
        $productObj,
        $memberAddress,
        $uploader,
        $saleOrderStatusConfigCodes
    ;

    /**
     * SaleExchangeRepository constructor.
     *
     * @param SaleExchange $model
     * @param SaleExchangeProduct $saleExchangeProduct
     * @param SaleExchangeKitting $saleExchangeKitting
     * @param SaleExchangeBill $saleExchangeBill
     * @param SaleInterface $saleInterface
     * @param SaleProduct $saleProduct
     * @param SaleKittingClone $saleKittingClone
     * @param SalePromotionFreeItemClone $salePromotionFreeItemClone
     * @param CreditNote $creditNote
     * @param MasterInterface $masterInterface
     * @param MasterData $masterData
     * @param InvoiceInterface $invoiceInterface
     * @param KittingInterface $kittingInterface
     * @param ProductInterface $productInterface
     * @param Country $country
     * @param Location $location
     * @param LegacyInvoice $legacyInvoice
     * @param LegacySaleExchangeKittingClone $legacySaleExchangeKittingClone
     * @param LegacySaleExchangeProduct $legacySaleExchangeProduct
     * @param LegacySaleExchangeProductClone $legacySaleExchangeProductClone
     * @param SettingsInterface $settingsInterface
     * @param User $user
     * @param Product $product
     * @param MemberAddress $memberAddress
     * @param Uploader $uploader
     */
    public function __construct(
        SaleExchange $model,
        SaleExchangeProduct $saleExchangeProduct,
        SaleExchangeKitting $saleExchangeKitting,
        SaleExchangeBill $saleExchangeBill,
        SaleInterface $saleInterface,
        SaleProduct $saleProduct,
        SaleKittingClone $saleKittingClone,
        SalePromotionFreeItemClone $salePromotionFreeItemClone,
        CreditNote $creditNote,
        MasterInterface $masterInterface,
        MasterData $masterData,
        InvoiceInterface $invoiceInterface,
        KittingInterface $kittingInterface,
        ProductInterface $productInterface,
        Country $country,
        Location $location,
        LegacyInvoice $legacyInvoice,
        LegacySaleExchangeKittingClone $legacySaleExchangeKittingClone,
        LegacySaleExchangeProduct $legacySaleExchangeProduct,
        LegacySaleExchangeProductClone $legacySaleExchangeProductClone,
        SettingsInterface $settingsInterface,
        User $user,
        Product $product,
        MemberAddress $memberAddress,
        Uploader $uploader
    )
    {
        parent::__construct($model);

        $this->saleExchangeProductObj = $saleExchangeProduct;

        $this->saleExchangeKittingObj = $saleExchangeKitting;

        $this->saleExchangeBillObj = $saleExchangeBill;

        $this->saleRepositoryObj = $saleInterface;

        $this->saleProductObj = $saleProduct;

        $this->saleKittingObj = $saleKittingClone;

        $this->salePromotionObj = $salePromotionFreeItemClone;

        $this->creditNoteObj = $creditNote;

        $this->masterRepositoryObj = $masterInterface;

        $this->masterDataObj = $masterData;

        $this->invoiceRepositoryObj = $invoiceInterface;

        $this->kittingRepositoryObj = $kittingInterface;

        $this->productRepositoryObj = $productInterface;

        $this->countryObj = $country;

        $this->locationObj = $location;

        $this->legacyInvoiceObj = $legacyInvoice;

        $this->legacySaleExchangeKittingCloneObj = $legacySaleExchangeKittingClone;

        $this->legacySaleExchangeProductObj = $legacySaleExchangeProduct;

        $this->legacySaleExchangeProductCloneObj = $legacySaleExchangeProductClone;

        $this->settingRepositoryObj = $settingsInterface;

        $this->userObj = $user;

        $this->productObj = $product;

        $this->memberAddress = $memberAddress;

        $this->uploader = $uploader;

        $this->saleOrderStatusConfigCodes = config('mappings.sale_order_status');
    }

    /**
     * filter sales exchange based on given param's
     *
     * @param int $countryId
     * @param string $text
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getSalesExchangeByFilters(
        int $countryId,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->with([
                'member' => function($query){
                    $query->with(['country', 'user']);
                },
                'parentSale.invoices',
                'sale.invoices',
                'legacyInvoice.cw',
                'legacyInvoice.country',
                'legacyInvoice.transactionLocation',
                'country',
                'transactionLocation',
                'stockLocation',
                'saleExchangeBill',
                'creditNote',
                'createdBy'
            ])
            ->where($this->modelObj->getTable() .'.country_id', $countryId);
        ;

        //check the granted location give for the user if he back_office,stockist or member
        $this->applyLocationQuery($data, $countryId, 'transaction_location_id');

        //search on the invoices, member ....etc
        if ($text != '') {
            //check if text match invoice number
            $invoice = $this->invoiceRepositoryObj
                ->getInvoicesByFilters($countryId, $text)['data']
                ->count();

            //check if text match legacy invoice number
            $legacyInvoice = $this->legacyInvoiceObj
                ->where('invoice_number', 'like', '%' . $text . '%')
                ->count();

            // check if text match old member id
            $user = $this->userObj
                ->where('old_member_id', 'like', '%' . $text . '%')
                ->count();

            //check sale doc number
            $exchangeBillNumber = $this->saleExchangeBillObj
                ->where('exchange_bill_number', $text)
                ->first();

            if ($invoice > 0){
                $data = $data
                    ->join('invoices', function ($join) use ($text) {
                        $join->on('invoices.sale_id', '=', 'sales_exchanges.sale_id')
                            ->where(function ($query) use ($text) {
                                $query
                                    ->where('invoices.invoice_number', $text);
                            });
                    });
            } elseif ($legacyInvoice > 0) {
                $data = $data
                    ->join('legacies_invoices', function ($join) use ($text) {
                        $join->on('legacies_invoices.id', '=', 'sales_exchanges.legacy_invoice_id')
                            ->where(function ($query) use ($text) {
                                $query
                                    ->where('legacies_invoices.invoice_number', $text);
                            });
                    });
            } elseif ($user > 0) {
                $data = $data
                    ->join('users', function ($join) use ($text) {
                        $join->on('users.id', '=', 'sales_exchanges.user_id')
                            ->where(function ($query) use ($text) {
                                $query
                                    ->where('users.old_member_id', 'like', '%' . $text . '%');
                            });
                    });
            } elseif ($exchangeBillNumber != null){
                $data = $data
                    ->join('sales_exchange_bills', function ($join) use ($text){
                        $join->on('sales_exchange_bills.sale_exchange_id', '=', 'sales_exchanges.id')
                            ->where(function ($query) use ($text) {
                                $query
                                    ->where('sales_exchange_bills.exchange_bill_number', $text);
                            });
                    });
            } else {
                $data = $data
                    ->join('members', function ($join) use ($text){
                        $join->on('members.user_id', '=', 'sales_exchanges.user_id')
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

        $totalRecords = collect(['total' => $data->count()]);

        $data = $data->orderBy($orderBy, $orderMethod);

        $data->select($this->modelObj->getTable().'.*');

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get sales exchanges for a given id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get sales exchange details for a given id
     *
     * @param int $saleExchangeId
     * @return array|mixed
     */
    public function saleExchangeDetails(int $saleExchangeId)
    {
        $salesExchange = $this->find($saleExchangeId);

        $isLegacy = $salesExchange->is_legacy;

        //get normal sale exchange return detail---------------------------------------------------------------------
        $returnedProducts = ($isLegacy) ? [] : $salesExchange->getSaleExchangeReturnedProducts();

        $returnKitting = ($isLegacy) ? [] : $salesExchange->getSaleExchangeReturnedKitting();

        $returnPromotions = ($isLegacy) ? [] : $salesExchange->getSaleExchangeReturnedPromotions();

        //get legacy sale exchange return detail----------------------------------------------------------------------
        $legacyInvoice = $salesExchange->legacyInvoice()->with(['cw', 'country', 'transactionLocation'])->first();

        $returnedLegacyProducts = ($isLegacy) ? $salesExchange->getLegacySaleExchangeReturnProducts() : [];

        $returnLegacyKitting = ($isLegacy) ? $salesExchange->getLegacySaleExchangeReturnKitting() : [];

        //get parent sales and new sales---------------------------------------------
        $parentSale = ($isLegacy) ? [] : $this->saleRepositoryObj->saleDetails($salesExchange->parent_sale_id);

        $newSale = $this->saleRepositoryObj->saleDetails($salesExchange->sale_id);

        $shipping = $newSale['sales_data']['selected']['shipping'];

        $salePayments = $newSale['sales_data']['selected']['payments'];

        //credit note---------------------------------------------------------------------------------------------------
        $creditNote = $salesExchange->creditNote()->first();

        //sales exchange bill ------------------------------------------------------------------------------------------
        $exchangeBill = $salesExchange->saleExchangeBill()->first();

        $orderStatus = $this->masterDataObj
            ->where('id', $salesExchange->order_status_id)
            ->first();

        return [
            'sales_exchange_data' => [
                'id' => $salesExchange->id,
                'user_id' => $salesExchange->user_id,
                'country_id' => $salesExchange->country_id,
                'sale_id' => $salesExchange->sale_id,
                'parent_sale_id' => $salesExchange->parent_sale_id,
                'legacy_invoice_id' => $salesExchange->legacy_invoice_id,
                'location_id' => $salesExchange->location_id,
                'transaction_location_id' => $salesExchange->transaction_location_id,
                'stock_location_id' => $salesExchange->stock_location_id,
                'cw_id' => $salesExchange->cw_id,
                'cw' => $salesExchange->cw,
                'reason_id' => $salesExchange->reason_id,
                'fms_number' => $salesExchange->fms_number,
                'transaction_date' => $salesExchange->transaction_date,
                'delivery_fees' => $salesExchange->delivery_fees,
                'balance' => $salesExchange->balance,
                'exchange_amount_total' => $salesExchange->exchange_amount_total,
                'return_amount_total' => $salesExchange->return_amount_total,
                'remarks' => $salesExchange->remarks,
                'order_status_id' => $salesExchange->order_status_id,
                'order_status' => $orderStatus->title,
                'is_legacy' => $salesExchange->is_legacy,

                'legacy_invoice' => $legacyInvoice,
                'sale' => ($isLegacy) ? [] : $parentSale['sales_data'],
                'new_sale' => $newSale['sales_data'],

                'legacy_return_products' => $returnedLegacyProducts,
                'legacy_return_kitting' => $returnLegacyKitting,

                'return_products' => $returnedProducts,
                'return_kitting' => $returnKitting,
                'return_promotions' => $returnPromotions,

                'exchange_products' => $newSale['sales_data']['products'],
                'exchange_kitting' => $newSale['sales_data']['kittings'],

                'selected' =>
                [
                    'shipping' => $shipping,
                    'payments' => $salePayments
                ],

                'credit_note' => $creditNote,
                'exchange_bill' => $exchangeBill
            ]
        ];
    }

    /**
     * create new sales exchange
     *
     * @param array $data
     * @return array|mixed
     */
    public function createSaleExchange(array $data)
    {
        $exchange = $data['sales_exchange_data'];

        //get the parent sales-------------------------------
        $parentSales = ($exchange['is_legacy']) ? null :
            $this->saleRepositoryObj->find($exchange['sale']['id']);

        //get the pre-order status id---------------------------
        $orderStatusId = MasterData::where('title', $this->saleOrderStatusConfigCodes['pre-order'])
            ->whereHas('master', function($masterQuery){
                $masterQuery->where('key', 'sale_order_status');
            })->get()->first()->id;

        //get channel id based on transaction location----------
        $location = $this->locationObj->find($exchange['location_id']);

        //extract some var for obj-----------------------------
        $countryId =  $exchange['country_id'];

        //create new sales record---------------------------------------------------------------------------------------
        $saleExchangeData = [
            'user_id' => $exchange['user_id'],
            'country_id' => $countryId,
            'parent_sale_id' => ($exchange['is_legacy']) ? null : $parentSales->id,
            'sale_id' => null,
            'cw_id' => $exchange['cw_id'],
            'transaction_location_id' => $exchange['location_id'],
            'stock_location_id' => $exchange['stock_location_id'],
            'reason_id' => $exchange['reason_id'],
            'fms_number' => $exchange['fms_number'],
            'transaction_date' => date('Y-m-d'),
            'delivery_fees' => $exchange['delivery_fees'],
            'balance' => $exchange['balance'],
            'exchange_amount_total' => isset($exchange['exchange_amount_total']) ? $exchange['exchange_amount_total'] : '0.00',
            'return_amount_total' => isset($exchange['return_amount_total']) ? $exchange['return_amount_total'] : '0.00',
            'remarks' => $exchange['remarks'],
            'is_legacy' => $exchange['is_legacy'],
            'order_status_id' => $orderStatusId
        ];

        //create new sales exchange
        $salesExchange = Auth::user()->createdBy($this->modelObj)->create($saleExchangeData);

        if($exchange['is_legacy']) //Legacy Sale Exchange
        {
            //Create Legacy Invoice
            $legacyInvoiceData = [
                'cw_id' => $exchange['legacy_invoice']['cw_id'],
                'country_id' => $exchange['legacy_invoice']['country_id'],
                'transaction_location_id' => $exchange['legacy_invoice']['transaction_location_id'],
                'invoice_number' => $exchange['legacy_invoice']['invoice_number'],
                'invoice_date' => $exchange['legacy_invoice']['invoice_date']
            ];

            $legacyInvoice = Auth::user()->createdBy($this->legacyInvoiceObj)->create($legacyInvoiceData);

            //update legacy invoice id to sales exchange
            $salesExchange->update([
                'legacy_invoice_id' => $legacyInvoice->id
            ]);

            //Get Tax Detail
            $countryDetail = $this->countryObj->where('id', $countryId)->first();

            $countryTaxDetail = $countryDetail->taxes()->first();

            $countryTaxRate = (!empty($countryTaxDetail)) ? $countryTaxDetail->rate : 0;

            $locationArray[] = $exchange['location_id'];

            $saleExchangeProducts = $saleExchangeKitting = [];

            //generate legacy return products data
            if(isset($exchange['legacy_return_products']) and !empty($exchange['legacy_return_products'])){

                collect($exchange['legacy_return_products'])->each(function ($product)
                    use (&$saleExchangeProducts, $countryTaxRate){

                        //get product details
                        $productsDetails = $this->productObj->find($product['product_id']);

                        if(!empty($productsDetails)){

                            $productGmpPriceTax = floatval($product['base_price']['gmp_price_tax']);

                            $productTotalPrice = floatval($product['base_price']['gmp_price_tax']) *
                                floatval($product['return_quantity']);

                            //Form product cancellation data
                            $exchangeData = [
                                'available_quantity_snapshot' => intval($product['return_quantity']),
                                'return_quantity'  => intval($product['return_quantity']),
                                'gmp_price_gst' => $productGmpPriceTax,
                                'nmp_price' => $productGmpPriceTax / (100 + floatval($countryTaxRate)) * 100,
                                'average_price_unit' => 0,
                                'return_total' => $productTotalPrice,
                                'product_clone' => [
                                    'product_id' => $productsDetails->id,
                                    'name' => $productsDetails->name,
                                    'sku' => $productsDetails->sku,
                                    'uom' => $productsDetails->uom
                                ]
                            ];

                            array_push($saleExchangeProducts, $exchangeData);
                        }
                    });
            }

            //generate legacy return kitting products data
            if(isset($exchange['legacy_return_kitting']) and !empty($exchange['legacy_return_kitting'])){

                collect($exchange['legacy_return_kitting'])->each(function ($kitting)
                    use (&$saleExchangeKitting, $countryId, $countryTaxRate, $locationArray){

                        $kittingData = $this->kittingRepositoryObj
                            ->kittingDetails($countryId, $kitting['kitting_id']);

                        $kittingGmpPriceTax = floatval($kitting['kitting_price']['gmp_price_tax']);

                        $kittingTotalPrice = floatval($kitting['kitting_price']['gmp_price_tax']) *
                           floatval($kitting['return_quantity']);

                        $exchangeKittingData = [
                            'kitting_id' => $kitting['kitting_id'],
                            'code' => $kittingData['code'],
                            'name' => $kittingData['name'],
                            'available_quantity_snapshot' => $kitting['return_quantity'],
                            'return_quantity' => $kitting['return_quantity'],
                            'gmp_price_gst' => $kittingGmpPriceTax,
                            'nmp_price' => $kittingGmpPriceTax / (100 + floatval($countryTaxRate)) * 100,
                            'return_total' => $kittingTotalPrice,
                            'kitting_product' => []
                        ];

                        //calculate kitting total gmp for ratio calculation
                        $totalGmpPrice = $this->kittingRepositoryObj->calculateKittingTotalGmp(
                            $countryId,
                            $kittingData,
                            $locationArray
                        );

                        collect($kittingData['kitting_products'])->each( function ($product)
                            use (&$exchangeKittingData, $totalGmpPrice, $countryId,
                                $locationArray, $kittingGmpPriceTax){

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

                                    $exchangeKittingProduct = [
                                        'available_quantity_snapshot' => $productQuantity,
                                        'return_quantity'  => $productQuantity,
                                        'gmp_price_gst' => 0,
                                        'nmp_price' => 0,
                                        'average_price_unit' => $averagePriceUnitGst,
                                        'return_total' => $totalPromoPriceGst,
                                        'product_clone' => [
                                            'product_id' => $productsDetails->id,
                                            'name' => $productsDetails->name,
                                            'sku' => $productsDetails->sku,
                                            'uom' => $productsDetails->uom
                                        ]
                                    ];

                                    array_push($exchangeKittingData['kitting_product'], $exchangeKittingProduct);
                            });

                        array_push($saleExchangeKitting, $exchangeKittingData);

                    });
            }

            //Insert Sales Cancellation Products
            collect($saleExchangeProducts)->each(function ($saleExchangeProductData)
                use ($salesExchange){

                    $productClone = $saleExchangeProductData['product_clone'];

                    unset($saleExchangeProductData['product_clone']);

                    $saleExchangeProductData['sale_exchange_id'] = $salesExchange->id;

                    $saleExchangeProduct = $this->legacySaleExchangeProductObj->create($saleExchangeProductData);

                    //Create Product Clone
                    $productClone['legacy_sale_exchange_product_id'] = $saleExchangeProduct->id;

                    $this->legacySaleExchangeProductCloneObj->create($productClone);
                });

            //Insert Sales Cancellation Kitting
            collect($saleExchangeKitting)->each(function ($saleExchangeKittingData)
                use ($salesExchange){

                    $kittingProducts = $saleExchangeKittingData['kitting_product'];

                    unset($saleExchangeKittingData['kitting_product']);

                    //Create Kitting Clone
                    $saleExchangeKittingData['sale_exchange_id'] = $salesExchange->id;

                    $saleExchangedKitting = $this->legacySaleExchangeKittingCloneObj->create($saleExchangeKittingData);

                    //Create Cancel Kitting Product
                    collect($kittingProducts)->each(function ($kittingProductData)
                        use ($salesExchange, $saleExchangedKitting){

                            $productClone = $kittingProductData['product_clone'];

                            unset($kittingProductData['product_clone']);

                            $kittingProductData['sale_exchange_id'] = $salesExchange->id;

                            $kittingProductData['legacy_sale_exchange_kitting_clone_id'] = $saleExchangedKitting->id;

                            $saleExchangeKittingProduct = $this->legacySaleExchangeProductObj->create($kittingProductData);

                            //Create Product Clone
                            $productClone['legacy_sale_exchange_product_id'] = $saleExchangeKittingProduct->id;

                            $this->legacySaleExchangeProductCloneObj->create($productClone);
                        });
                });

        }
        else //Normal Sale Exchange
        {
            //return products-----------------------------------------------------------------------------------------------
            if (isset($exchange['return_products']) and !empty($exchange['return_products']))
            {
                collect($exchange['return_products'])
                    ->where('return_quantity', '>', 0)
                    ->each(function ($product) use($exchange, $salesExchange){

                    $productInfo = $this->saleProductObj->find($product['id']);

                    $salesExchangeProduct = [
                        'sale_exchange_id' => $salesExchange->id,
                        'sale_product_id' => $product['id'],
                        'return_quantity' => $product['return_quantity'],
                        'return_amount' => $product['return_amount'],
                        'gmp_price_gst' => $productInfo->gmp_price_gst,
                        'rp_price' => $productInfo->rp_price,
                        'rp_price_gst' => $productInfo->rp_price_gst,
                        'nmp_price' => $productInfo->nmp_price,
                        'total' => $productInfo->gmp_price_gst * $product['return_quantity']
                    ];

                    //create sales exchange product record
                    $this->saleExchangeProductObj->create($salesExchangeProduct);

                    //get the sale product to change availability
//                    $saleProduct = $this->saleProductObj->find($product['id']);
//
//                    $saleProduct->update([
//                        'available_quantity' => $saleProduct->available_quantity -  $product['return_quantity']
//                    ]);
                });
            }

            //return kitting------------------------------------------------------------------------------------------------
            if (isset($exchange['return_kitting']) and !empty($exchange['return_kitting']))
            {
                collect($exchange['return_kitting'])->each(function ($kitting) use ($salesExchange) {

                    //get kitting data by id
                    $kittingData = $this->saleKittingObj->find($kitting['id']);

                    $kittingProductsData  = $kittingData->products()->get();

                    $kittingProductCollection  = collect($kitting['kitting_products']);

                    $kittingProductSum = $kittingProductCollection->sum('return_quantity');

                    $kittingOriginalQty = $kittingProductsData->sum('quantity') / $kittingData->quantity;
//
//                    $kittingAvailableQty =
//                        ($kittingProductSum > $kittingOriginalQty)
//                            ?  $kittingData->available_quantity -  $kitting['return_quantity']
//                            : 0
//                    ;

                    //update sale kitting clone
//                    $kittingData->update([
//                        'available_quantity' => $kittingAvailableQty
//                    ]);

                    $salesExchangeKitting = [
                        'sale_exchange_id' => $salesExchange->id,
                        'sale_kitting_id' => $kittingData->id,
                        'return_quantity' => $kitting['return_quantity'],
                        'return_amount' => $kitting['return_amount'],
                    ];

                    //create sales exchange product record
                    $salesExchangeKitting = $this->saleExchangeKittingObj->create($salesExchangeKitting);

                    collect($kitting['kitting_products'])
                        ->where('return_quantity', '>', 0)
                        ->each(
                        function ($product) use($salesExchange, $salesExchangeKitting) {

                            $productInfo = $this->saleProductObj->find($product['id']);

                            $salesExchangeProduct = [
                                'sale_exchange_id' => $salesExchange->id,
                                'sale_product_id' => $product['id'],
                                'mapping_id' => $salesExchangeKitting->id,
                                'mapping_model' => 'sales_exchange_kitting',
                                'return_quantity' => $product['return_quantity'],
                                'return_amount' => $product['return_amount'],
                                'gmp_price_gst' => $productInfo->gmp_price_gst,
                                'rp_price' => $productInfo->gmp_price_gst,
                                'rp_price_gst' => $productInfo->gmp_price_gst,
                                'nmp_price' => $productInfo->gmp_price_gst,
                                'average_price_unit' => $productInfo->average_price_unit, // for kitting
                                'total' => $productInfo->average_price_unit * $product['return_quantity']
                            ];

                            //create sales exchange product record
                            $this->saleExchangeProductObj->create($salesExchangeProduct);

                            //get the sale product to change availability
//                            $saleProduct = $this->saleProductObj->find($product['id']);
//
//                            $saleProduct->update([
//                                'available_quantity' => $saleProduct->available_quantity -  $product['return_quantity']
//                            ]);

                        });
                });
            }

            //return Promotion Products-------------------------------------------------------------------------------------
            if (isset($exchange['return_promotions']) and !empty($exchange['return_promotions']))
            {
                collect($exchange['return_promotions'])
                    ->where('return_quantity', '>', 0)
                    ->each(function ($promotion) use ($salesExchange) {

                    //get promotion data by id
                    $promotionProductData = $this->saleProductObj->find($promotion['id']);

                    $salesReturnPromotionProduct = [
                        'sale_exchange_id' => $salesExchange->id,
                        'sale_product_id' => $promotionProductData->id,
                        'return_quantity' => $promotion['return_quantity'],
                        'return_amount' => $promotion['return_amount'],
                        'gmp_price_gst' => $promotionProductData->gmp_price_gst,
                        'rp_price' => $promotionProductData->gmp_price_gst,
                        'rp_price_gst' => $promotionProductData->gmp_price_gst,
                        'nmp_price' => $promotionProductData->gmp_price_gst,
                        'total' => $promotionProductData->gmp_price_gst * $promotion['return_quantity'],
                        'mapping_id' => $promotionProductData->mapping_id,
                        'mapping_model' => $promotionProductData->mapping_model,
                    ];

                    //create sales exchange product record
                    $this->saleExchangeProductObj->create($salesReturnPromotionProduct);
//
//                    $promotionProductData->update([
//                        'available_quantity' => $promotionProductData->available_quantity -  $promotion['return_quantity']
//                    ]);
                });
            }
        }

        //create new sales for the exchange products and kitting -------------------------------------------------------
        if (isset($exchange['exchange_products']) or isset($exchange['exchange_kitting']))
        {
            $taxAmount =  $this->calculateTaxAmount($exchange['country_id'], $exchange['exchange_amount_total']);

            $totalGmp = $exchange['exchange_amount_total'];

            $salesArray =
            [
                'sales_data' =>
                [
                    'country_id' => $exchange['country_id'],
                    'location_id' => $exchange['location_id'],
                    'stock_location_id' => $exchange['stock_location_id'],
                    'cw_id' => $exchange['cw_id'],
                    'sponsor_id' => (isset($exchange['sale']['sponsor_id'])) ? $exchange['sale']['sponsor_id'] : NULL,
                    'downline_member_id' => $exchange['user_id'],
                    'remarks' => $exchange['remarks'],
                    'cvs' => $exchange['cvs'],

                    'is_product_exchange' => 1,

                    'order_fees' => [
                        'total_nmp' => number_format($exchange['exchange_amount_total'] - $taxAmount, 2),
                        'tax_amount' => $taxAmount,
                        'total_gmp' => $totalGmp,
                        'rounding_adjustment' => 0,
                        'total_esac_voucher_value' => 0
                    ],

                    'products' => isset($exchange['exchange_products']) ? $exchange['exchange_products'] : [],
                    'kittings' => isset($exchange['exchange_kitting']) ? $exchange['exchange_kitting'] : [],
                    'selected' => [
                        'shipping' => $exchange['selected']['shipping']
                    ],

                    'status' => 'save' //preorder status
                ]
            ];

            $exchangeAmount = (isset($exchange['exchange_amount_total'])) ? floatval($exchange['exchange_amount_total']) : 0;

            $returnAmount = (isset($exchange['return_amount_total'])) ? floatval($exchange['return_amount_total']) : 0;

            //generate invoice if $returnAmount >= $exchangeAmount
            if ($returnAmount >= $exchangeAmount){
                //create sales
                $newSales = $this->saleRepositoryObj->createSale($salesArray, $complete = true);

                //generate invoice
                $this->invoiceRepositoryObj->generateInvoice(
                    $this->saleRepositoryObj->find($newSales['sales_data']['sale_id'])
                );

                //generate sales exchange bill and credit note
                $this->generateExchangeBillAndCreditNote($salesExchange);

                //change sales exchange status to complete
                $orderStatusId = MasterData::where('title', $this->saleOrderStatusConfigCodes['completed'])
                    ->whereHas('master', function($masterQuery){
                        $masterQuery->where('key', 'sale_order_status');
                    })->get()->first()->id;

                $salesExchange->order_status_id = $orderStatusId;

                $salesExchange->save();

                //deduct product returned qty
                $this->deductReturnedProductQty($salesExchange);

            }else{
                $newSales = $this->saleRepositoryObj->createSale($salesArray);
            }

            //update sales exchange to get the new sales id for the exchanged products and kitting
            $salesExchange->update([
                'sale_id' => $newSales['sales_data']['sale_id']
            ]);
        }

        return $this->saleExchangeDetails($salesExchange->id);
    }

    /**
     * generate exchange bill and credit note for the given saleExchangeObj
     *
     * @param SaleExchange $saleExchange
     * @return mixed|void
     */
    public function generateExchangeBillAndCreditNote(SaleExchange $saleExchange)
    {
        //generate credit note------------------------------------------------------------------------------------------
        $this->creditNoteObj->create([
            'sale_id' => ($saleExchange->is_legacy) ? null : $saleExchange->parent_sale_id,
            'mapping_id' => $saleExchange->id,
            'mapping_model' => 'sales_exchanges',
            'credit_note_number' => $this->settingRepositoryObj->getRunningNumber(
                'credit_note', $saleExchange->country_id, $saleExchange->transaction_location_id
            ),
            'credit_note_date' => date('Y-m-d'),
        ]);

        //general exchange bill-----------------------------------------------------------------------------------------
        $saleExchange->saleExchangeBill()->create([
            'exchange_bill_number' => $this->settingRepositoryObj->getRunningNumber(
                'exchange_bill', $saleExchange->country_id, $saleExchange->transaction_location_id
            ),
            'exchange_reference_number' => '',
            'exchange_bill_date' => date('Y-m-d')
        ]);

        return;
    }

    /**
     * deduct products qty for all different types kitting, pwp
     *
     * @param SaleExchange $saleExchange
     * @return mixed|void
     */
    public function deductReturnedProductQty(SaleExchange $saleExchange)
    {
        //loose products section---------------------------------------------------------
        //-------------------------------------------------------------------------------
        $saleReturnedProducts = $saleExchange
            ->saleExchangeProducts()
            ->whereNull('mapping_model')
            ->get();
        
        $saleReturnedProducts->each(function ($returnedProduct){
            //get the sale product to change availability
            $saleProduct = $this->saleProductObj->find($returnedProduct->sale_product_id);

            $saleProduct->update([
                'available_quantity' => $saleProduct->available_quantity -  $returnedProduct->return_quantity
            ]);
        });

        //kitting section----------------------------------------------------------------
        //-------------------------------------------------------------------------------
        $saleReturnedKitting = $saleExchange->saleExchangeKitting()->get();

        $saleReturnedKitting->each(function ($returnedKit)
        {
            //get kitting data by id
            $kittingData = $this->saleKittingObj->find($returnedKit->sale_kitting_id);

            $kittingProductsData  = $kittingData
                ->products()
                ->where('mapping_model', 'sales_exchange_kitting')
                ->where('mapping_id', $returnedKit->sale_kitting_id)
                ->get();

            //get the returned products
            $returnedProduct = $returnedKit->products()->get();

            $kittingReturnedProductSum = $returnedProduct->sum('return_quantity');

            $kittingOriginalQty = $kittingProductsData->sum('quantity') / $kittingData->quantity;

            $kittingAvailableQty =
                ($kittingReturnedProductSum > $kittingOriginalQty)
                    ?  $kittingData->available_quantity -  $returnedKit->return_quantity
                    : 0;

            //update sale kitting clone
            $kittingData->update([
                'available_quantity' => $kittingAvailableQty
            ]);

            $returnedProduct->each(function ($returnedProduct){
                //get the sale product to change availability
                $saleProduct = $this->saleProductObj->find($returnedProduct->sale_product_id);

                $saleProduct->update([
                    'available_quantity' => $saleProduct->available_quantity -  $returnedProduct->return_quantity
                ]);
            });

        });

        //pwp section--------------------------------------------------------------------
        //-------------------------------------------------------------------------------
        $saleReturnedPWPProducts = $saleExchange
            ->saleExchangeProducts()
            ->where('mapping_model', 'sales_promotion_free_items_clone')
            ->get();

        $saleReturnedPWPProducts->each(function ($returnedPWPProduct){
            //get the sale pwp product to change availability
            $salePWPProduct = $this->saleProductObj->find($returnedPWPProduct->sale_product_id);

            $salePWPProduct->update([
                'available_quantity' => $salePWPProduct->available_quantity -  $returnedPWPProduct->return_quantity
            ]);
        });
    }

    /**
     * To download exchange bill in pdf and export as content-stream header 'application/pdf'
     *
     * @param int $salesExchangeBillId
     * @return \App\Interfaces\Sales\Collection|\Illuminate\Support\Collection|mixed
     * @throws \Mpdf\MpdfException
     */
    public function downloadExchangeBill(int $salesExchangeBillId)
    {
        //TODO clean up the bellow code
        $salesExchangeBill = $this->saleExchangeBillObj->find($salesExchangeBillId);

        $salesExchange = $salesExchangeBill->salesExchange; 

        $user = $salesExchange->user;

        $exchangeProducts = $salesExchange->sale->saleProducts;

        $returnProducts = $salesExchange->saleExchangeProducts;

        $legacyReturnProducts = $salesExchange->legacySaleExchangeReturnProduct;

        if (count($exchangeProducts) > 0)
        {
            $taxInvoiceNo = isset($exchangeProducts[0]->sale->invoices)? $exchangeProducts[0]->sale->invoices->invoice_number: '';
        }

        $member = $user->member;

        $view = 'invoices.exchange.'.strtolower($salesExchange->country->code_iso_2);

        $basic = ['no' => $salesExchangeBill->exchange_bill_number,
                'memberID' => $user->old_member_id,
                'refNo' => $salesExchangeBill->exchange_reference_number,
                'name' => $user->name,
                'address' => $member->address? $this->memberAddress->getCorrespondenceAddress($member->address->address_data): "",
                'location' => $salesExchange->transactionLocation->code." ".$salesExchange->transactionLocation->name,
                'date' => $salesExchange->transaction_date,
                'taxInvNo' => $taxInvoiceNo,
                'issuer' => $salesExchange->issuer->name,
            ];

        //sales products lines
        $salesProducts = array();

        //populate summary product lines
        $productsSummary = array();

        $lineCount = 1;
        $totalProductQty = $totalProductCv = $totalExcTax = $totalTax = $totalIncTax = 0;

        //exchange product
        foreach($exchangeProducts as $product)
        {
            if(!$product->quantity){
                continue;
            }
            $productsSummary[$product->product->sku] = $product->quantity;

            $total = 0;

            if($product->getMappedModel instanceof SaleKittingClone)
            {
                $unitPrice = $product->average_price_unit;
                $unitTax = 0.00;
                $total = $product->total;
                $subTotal = $total;
                $excludingTaxGmp = $total;
                $tax = 0.00;
            }
            else
            {
                $unitPrice = $product->gmp_price_gst;
                $total = $product->total;
                $unitTax = $product->gmp_price_gst - $product->nmp_price;
                $subTotal = $product->nmp_price * $product->quantity;
                $excludingTaxGmp = $product->nmp_price * $product->quantity;
                $tax = $total - $excludingTaxGmp;
            }

            $salesProducts[] = array(
                'no' => $lineCount,
                'code' => $product->product->sku,
                'description' => $product->product->name,
                'priceCode' => "", //@TODO: where is price code?
                'qty' => $product->quantity,
                'unitPrice' => $unitPrice,
                'unitTax' => $unitTax,
                'subTotal' => $subTotal,
                'excTax' => $excludingTaxGmp,
                'tax' => $tax,
                'total' => $total
            );

            $totalProductQty += $product->quantity;
            $totalExcTax += $excludingTaxGmp;
            $totalTax += $tax;
            $totalIncTax += $total;

            $lineCount++;
        }

        //return product
        foreach($returnProducts as $product)
        {
            if(!$product->return_quantity)
            {
                continue;
            }

            $qty = 0 - $product->return_quantity;
            $productsSummary[$product->product->product->sku] = $qty;
            $total = 0;

            if($product->getMappedModel instanceof SaleExchangeKitting)
            {
                $total = 0 - $product->return_amount;
                $unitPrice = $product->average_price_unit;
                $unitTax = 0.00;
                $subTotal = $total;
                $excludingTaxGmp = $total;
                $tax = 0.00;
            }
            else
            {
                $total = 0 - $product->return_amount;
                $unitPrice = $product->gmp_price_gst;
                $unitTax = $product->gmp_price_gst - $product->nmp_price;
                $subTotal = $product->nmp_price * $qty;
                $excludingTaxGmp = $product->nmp_price * $qty;
                $tax = $total - $excludingTaxGmp;
            }

            $salesProducts[] = array(
                'no' => $lineCount,
                'code' => $product->product->product->sku,
                'description' => $product->product->product->name,
                'priceCode' => "", //@TODO: where is price code?
                'qty' => $qty,
                'unitPrice' => $unitPrice,
                'unitTax' => $unitTax,
                'subTotal' => $subTotal,
                'excTax' => $excludingTaxGmp,
                'tax' => $tax,
                'total' => $total
            );

            $totalProductQty += $qty;
            $totalExcTax += $excludingTaxGmp;
            $totalTax += $tax;
            $totalIncTax += $total;
            $lineCount++;
        }

        //legacy return products
        foreach($legacyReturnProducts as $product)
        {
            if(!$product->return_quantity)
            {
                continue;
            }

            $qty = 0 - $product->return_quantity;
            $productsSummary[$product->legacySaleExchangeProductClone->sku] = $qty;
            $total = 0;

            if($product->legacy_sale_exchange_kitting_clone_id)
            {
                $total = 0 - $product->return_total;
                $unitPrice = $product->average_price_unit;
                $unitTax = 0.00;
                $subTotal = $total;
                $excludingTaxGmp = $total;
                $tax = 0.00;
            }
            else
            {
                $total = 0 - $product->return_total;
                $unitPrice = $product->gmp_price_gst;
                $unitTax = $product->gmp_price_gst - $product->nmp_price;
                $subTotal = $product->nmp_price * $qty;
                $excludingTaxGmp = $product->nmp_price * $qty;
                $tax = $total - $excludingTaxGmp;
            }

            $salesProducts[] = array(
                'no' => $lineCount,
                'code' => $product->legacySaleExchangeProductClone->sku,
                'description' => $product->legacySaleExchangeProductClone->name,
                'priceCode' => "", //@TODO: where is price code?
                'qty' => $qty,
                'unitPrice' => $unitPrice,
                'unitTax' => $unitTax,
                'subTotal' => $subTotal,
                'excTax' => $excludingTaxGmp,
                'tax' => $tax,
                'total' => $total
            );

            $totalProductQty += $qty;
            $totalExcTax += $excludingTaxGmp;
            $totalTax += $tax;
            $totalIncTax += $total;
            $lineCount++;
        }

        //@TODO
        $remarks = [];//;

        //TODO: delivery, admin and other fee is currently unavailable
        $items = array(
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
            'total' => [
                'excTax' => ($totalExcTax > 0? $totalExcTax : 0.00),
                'tax' => ($totalTax > 0? $totalTax : 0.00),
                'total' => ($totalIncTax > 0? $totalIncTax : 0.00),
                'exempt' => 0.00,
                'zeroRated' => 0.00
            ]
        );

        $html = \View::make($view)
            ->with('basic', $basic)
            ->with('remarks', $salesExchange->remarks)
            ->with('items', $items)
            ->render();

        

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

        $absoluteUrlPath = Config::get('filesystems.subpath.exchange_bill.absolute_url_path');

        $fileName = $this->uploader->getRandomFileName('exchange_bill' . $salesExchangeBillId) . '.pdf';

        $fileUrl = $this->uploader->createS3File($absoluteUrlPath . $fileName, $mpdf->Output($fileName, "S"), true);

        return collect(['download_link' => $fileUrl]);
    }

    /**
     * calculate tax based on country and total
     *
     * @param int $countryId
     * @param $totalGmp
     * @return float|int
     */
    private function calculateTaxAmount(int $countryId, $totalGmp)
    {
        $tax = $this->countryObj->countryTax($countryId);

        if ($tax != null){
            return $tax->rate * $totalGmp / (100 + $tax->rate);
        }

        return 0;
    }

}