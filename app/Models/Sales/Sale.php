<?php
namespace App\Models\Sales;

use App\Models\{
    Enrollments\EnrollmentTemp,
    General\CWSchedule,
    Locations\StockLocation,
    Locations\LocationAddresses,
    Payments\Payment,
    Invoices\Invoice,
    Locations\Country,
    Locations\Location,
    Locations\LocationTypes,
    Masters\MasterData,
    Members\Member,
    Users\User,
    Campaigns\EsacVoucher,
    Sales\SaleCorporateSale
};
use Illuminate\Database\Eloquent\{
    Model,
    SoftDeletes
};
use App\Helpers\Traits\HasAudit;

class Sale extends Model
{
    use HasAudit, SoftDeletes;

    protected $table = 'sales';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id',
        'country_id',
        'document_number',
        'self_pick_up_number',
        'sponsor_id',
        'transaction_location_id', //was trn_loc_id
        'stock_location_id',
        'transaction_date',

        'cw_id',
        'workflow_tracking_id',
        'tax_rate',
        'total_amount', //was total_amt
        'rounding_adjustment',
        'tax_amount',
        'total_gmp',

        'total_cv',
        'total_qualified_cv',

        'admin_fees',
        'delivery_fees',
        'other_fees',

        'delivery_method_id',
        'self_collection_point_id',
        'delivery_status_id',
        'channel_id',
        'order_status_id',

        'remarks',

        'is_product_exchange', //this col for PE to capture is this sales created by PE
        'is_rental_sale_order',
        'rental_release',
        'skip_downline',
        'is_esac_redemption',
        'is_corporate_sales',
        'total_esac_voucher_value',
        'updated_by'
    ];

    /**
     * get country details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get transaction location details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionLocation()
    {
        return $this->belongsTo(Location::class, 'transaction_location_id');
    }

    /**
     * get stockLocation data for given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockLocation()
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }

    /**
     * get sale products for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleProducts()
    {
        return $this->hasMany(SaleProduct::class);
    }

    /**
     * get user details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * get member details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id','user_id')
            ->with('user');
    }

    /**
     * get creator user details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by');
    }

    /**
     * get the cwSchedules for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }

    /**
     * get orderStatus Details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderStatus()
    {
        return $this->belongsTo(MasterData::class,'order_status_id');
    }

    /**
     * get channel details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo(LocationTypes::class,'channel_id');
    }

    /**
     * get channel details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deliveryOrder()
    {
        return $this->hasMany(DeliveryOrder::class,'sale_id');
    }

    /**
     * get deliveryMethod details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deliveryMethod()
    {
        return $this->belongsTo(MasterData::class,'delivery_method_id');
    }

    /**
     * get deliveryStatus details for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deliveryStatus()
    {
        return $this->belongsTo(MasterData::class, 'delivery_status_id');
    }

    //TODO remove the bellow function cwDetails
    /**
     * get commission week details
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwDetails()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }

    /**
     * get the invoices for a given salesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function invoices()
    {
        return $this->hasOne(Invoice::class, 'sale_id')
            ->with('cw');
    }

    /**
     * get sale shipping address for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function saleShippingAddress()
    {
        return $this->hasOne(SaleShippingAddress::class);
    }

    /**
     * get the sales payments for a given SalesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salePayments()
    {
        return $this->hasMany(Payment::class, 'mapping_id')
            ->where('mapping_model', 'sales')
            ->with('paymentModeProvider');
    }

    /**
     * get sale cancellation detail for a given SalesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleCancellation()
    {
        return $this->hasMany(SaleCancellation::class);
    }

    /**
     * get sales type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleTypeDetails()
    {
        return $this->belongsTo(MasterData::class,'sales_type_id');
    }

    /**
     * get sale product clone for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleProductClone()
    {
        return $this->hasMany(SaleProductClone::class);
    }

    /**
     * get sale kitting clone for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleKittingClone()
    {
        return $this->hasMany(SaleKittingClone::class);
    }

    /**
     * get sale promotion free item clone for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salePromotionFreeItemClone()
    {
        return $this->hasMany(SalePromotionFreeItemClone::class);
    }

    /**
     * get salesExchange info for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleExchange()
    {
        return $this->belongsTo(SaleExchange::class, 'id','sale_id');
    }

    /**
     * get esac vouchers for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function esacVouchers()
    {
        return $this
            ->belongsToMany(EsacVoucher::class, 'sales_esac_vouchers', 'sale_id', 'voucher_id')
            ->withTimestamps();
    }

    /**
     * get sale esac vouchers clone for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleEsacVouchersClone()
    {
        return $this->hasMany(SaleEsacVouchersClone::class, 'sale_id');
    }

    /**
     * get esac in sales
     *
     * @return array
     */
    public function getSaleEsacs()
    {
        return $this
            ->saleEsacVouchersClone()
            ->with(['esacVoucher.esacVoucherType'])
            ->get()
            ->map(function ($item){
                return  [
                    'id' => $item->id,
                    'product_id' => $item->voucher_id,
                    'name' => $item['esacVoucher']['esacVoucherType']['description'],
                    'sku' => $item['esacVoucher']['esacVoucherType']['name'],
                    'quantity' => 1,
                    'transaction_type_id' => 0,
                    'eligible_cv' => 0,
                    'base_price' => [
                        'gmp_price_tax' => $item->voucher_value,
                        'nmp_price' => $item->voucher_value,
                        'base_cv' => 0,
                        'cv1' => 0,
                        'wp_cv' => 0,
                        'total' => $item->voucher_value,
                    ],
                    'general' => [],
                    'size_groups' => [],
                    'available_quantity' => 1,
                    'cancellation_quantity' => 0,
                    'return_quantity' => 0,
                    'return_amount' => 0
                ];
            })
            ->toArray();
    }

    /**
     * get loose product in sales
     *
     * @param $productRepositoryObj
     * @param $countryId
     * @return array
     */
    public function getSaleProducts($productRepositoryObj, $countryId)
    {
        return $this
            ->saleProducts()
            ->with(['product','deliveryStage' => function($delivery){
                $delivery
                    ->with('status')
                    ->orderBy('id', 'desc')
                    ->first();
            }])
            ->whereNull('mapping_model')
            ->get()
            ->map(function ($item) use ($productRepositoryObj, $countryId)
            {
                $masterProductDetail = $productRepositoryObj->find($item->product->product_id);

                $masterProductDummyCode = $masterProductDetail->dummy()
                    ->with('dummyProducts')
                    ->where('country_id', $countryId)
                    ->first();

                return  [
                    'id' => $item->id,
                    'product_id' => $item->id,
                    'master_product_id' => $item->product->product_id,
                    'name' => $item->product->name,
                    'sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'transaction_type_id' => $item->transaction_type_id,
                    'eligible_cv' => $item->eligible_cv,
                    'base_price' => [
                        'gmp_price_tax' => $item->gmp_price_gst,
                        'nmp_price' => $item->nmp_price,
                        'base_cv' => $item->base_cv,
                        'cv1' => $item->cv1,
                        'wp_cv' => $item->wp_cv,
                        'total' => $item->total,
                    ],
                    'general' => $productRepositoryObj->productDetails($countryId, $item->product->product_id)['general'], //TODO clean this part clone general settings for products sales
                    'size_groups' => [],
                    'available_quantity' => $item->available_quantity,
                    'cancellation_quantity' => 0,
                    'return_quantity' => 0,
                    'return_amount' => 0,
                    'dummy_code' => (!empty($masterProductDummyCode)) ? $masterProductDummyCode : NULL,
                    'delivery_stage' => $item->deliveryStage
                ];
            })
            ->toArray();
    }

    /**
     * get loose product eligibleCvs in sales
     *
     * @return array
     */
    public function getSaleProductsEligibleCVs()
    {
        return $this
            ->saleProducts()
            ->whereNull('mapping_model')
            ->get()
            ->map(function ($item)
            {
                return  [
                    'product_id' => $item->id,
                    'unit_cv' => $item->eligible_cv
                ];
            })
            ->toArray();
    }

    /**
     * get sales kitting
     *
     * @param $productRepositoryObj
     * @param $countryId
     * @return array
     */
    public function getSaleKitting($productRepositoryObj, $countryId)
    {
        return $this
            ->saleKittingClone()
            ->with(['products'])
            ->get()
            ->map(function ($item) use ($productRepositoryObj, $countryId)
            {
                return  [
                    'id' => $item->id,
                    'sale_id' => $item->sale_id,
                    'kitting_id' => $item->kitting_id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'quantity' => $item->quantity,
                    'available_quantity' => $item->available_quantity,
                    'transaction_type_id' => $item->transaction_type_id,
                    'eligible_cv' => $item->eligible_cv,
                    'kitting_price' => [
                        'gmp_price_tax' => $item->gmp_price_gst,
                        'nmp_price' => $item->nmp_price,
                        'base_cv' => $item->base_cv,
                        'cv1' => $item->cv1,
                        'wp_cv' => $item->wp_cv,
                    ],
                    'cancellation_quantity' => 0,
                    'return_quantity' => 0,
                    'return_amount' => 0,
                    'kitting_products' => collect($item->products)->map(function ($productItem) use ($item,$productRepositoryObj, $countryId){

                        $masterProductDetail = $productRepositoryObj->find($productItem->product->product_id);

                        $masterProductDummyCode = $masterProductDetail->dummy()
                            ->with('dummyProducts')
                            ->where('country_id', $countryId)
                            ->first();

                        return
                        [
                            'id' => $productItem->id,
                            'product_id' => $productItem->id,
                            'kitting_id' => $item->id,
                            'quantity' => $productItem->quantity,
                            'foc_qty' => $productItem->foc_qty,
                            'available_quantity' => $productItem->available_quantity,
                            'cancellation_quantity' => 0,
                            'return_quantity' => 0,
                            'return_amount' => 0,
                            'product' => [
                                'id' => $productItem->id,
                                'product_id' => $productItem->id,
                                'name' => $productItem->product->name,
                                'sku' => $productItem->product->sku,
                                'transaction_type_id' => $productItem->transaction_type_id,
                                'base_price' => [
                                    'average_price_unit' => $productItem->average_price_unit,
                                    'gmp_price_tax' => $productItem->gmp_price_gst,
                                    'total' => $productItem->total,
                                    'base_cv' => $productItem->base_cv,
                                    'wp_cv' => $productItem->wp_cv,
                                ],
                                'general' => $productRepositoryObj->productDetails($countryId, $productItem->product->product_id)['general'],
                                'size_groups' => [],
                                'dummy_code' => (!empty($masterProductDummyCode)) ? $masterProductDummyCode : NULL
                            ]
                        ];
                    })
                ];
            })
            ->toArray();
    }

    /**
     * get sales kitting eligible Cvs
     *
     * @return array
     */
    public function getSaleKittingEligibleCVs()
    {
        return $this
            ->saleKittingClone()
            ->get()
            ->map(function ($item)
            {
                return  [
                    'kitting_id' => $item->kitting_id,
                    'unit_cv' => $item->eligible_cv
                ];
            })
            ->toArray();
    }

    /**
     * get sale promotions
     *
     * @return array
     */
    public function getSalePromotions()
    {
        return $this
            ->salePromotionFreeItemClone()
          //  ->with('deliveryStage')
            ->get()
            ->map(function ($item)
            {
                return  [
                    'id' => $item->id,
                    'promo_id' => $item->id,
                    'name' => $item->name,
                    'promo_type_id' => $item->promo_type_id,
                    'min_purchase_qty' => $item->min_purchase_qty,
                    'pwp_value' => $item->pwp_value,
                     'conditions' => [
                         'operator' => $item->options_relation,
                         'options' => $this->getSalePromotionFreeItemsOptions($item)
                     ]
                ];
            })
            ->toArray();
    }

    /**
     * get promotion selected items
     *
     * @param $productRepositoryObj
     * @param $countryId
     * @return array
     */
    public function getSaleSelectedPromotions($productRepositoryObj, $countryId)
    {
        return $this
            ->salePromotionFreeItemClone()
            ->with('products')
            ->get()
            ->map(function ($promo) use ($productRepositoryObj, $countryId)
            {
                return collect($promo->products)->map(function ($item)  use ($promo, $productRepositoryObj, $countryId) {

                    $masterProductDetail = $productRepositoryObj->find($item->product->product_id);

                    $masterProductDummyCode = $masterProductDetail->dummy()
                        ->with('dummyProducts')
                        ->where('country_id', $countryId)
                        ->first();

                    return  [
                        'id' => $item->id,
                        'product_id' => $item->id,
                        'promo_id' => $promo->id,
                        'option_id' => $item->option_id,
                        'operator' => $item->operator,
                        'set_id' => $item->set_id,
                        'set_key' => $item->set_key,
                        'name' => $item->product->name,
                        'sku' => $item->product->sku,
                        'selected_quantity' => $item->quantity,
                        'quantity' => $item->quantity,
                        'available_quantity' => $item->available_quantity,
                        'return_quantity' => 0,
                        'return_amount' => 0,
                        'base_price' => [
                            'average_price_unit' => $item->average_price_unit,
                            'gmp_price_tax' => $item->gmp_price_gst,
                            'total' => $item->total,
                            'base_cv' => $item->base_cv,
                        ],
                        'cancellation_quantity' => 0,
                        'dummy_code' => (!empty($masterProductDummyCode)) ? $masterProductDummyCode : NULL
                    ];
                });
            })
            ->all();
    }

    /**
     * get salePromotionFreeItems conditions for a given promotionObj
     *
     * @param SalePromotionFreeItemClone $promo
     * @return array
     */
    private function getSalePromotionFreeItemsOptions(SalePromotionFreeItemClone $promo)
    {
        $salePromoOptionProductObj = new SalePromotionFreeItemOptionProductClone();

        $promoProducts = $promo
            ->salePromotionFreeItemOptionProductClone()
            ->get();

        $promoOptions = $promo
            ->salePromotionOptionsClone()
            ->get();

        $productDataResponse = []; $andProducts = []; $orProducts = [];

        $j = 1;
        foreach ($promoOptions as $option)
        {
            $productArray = explode(',', $option->option_products);

            $i = 1;
            foreach ($productArray as $product)
            {
                if ($product == '') continue;

                if ($product != 'or')
                {
                    $productRecord = $salePromoOptionProductObj
                        ->with('product')
                        ->where('product_clone_id',$product)
                        ->where('promo_id', $promo->id)
                        ->where('option_id', $option->option_id)
                        ->first();

                    $productRecord['product_id'] = $productRecord['product_clone_id'];

                    if ($i !=1){
                        $orProducts['option_'.$j]['set_'.$i][] = $productRecord;
                    }else{
                        $andProducts['option_'.$j]['set_'.$i][] = $productRecord;
                    }
                }
                else{
                    $i++;
                }
            }

            $optionID['option_'.$j] = ['option_id' => $option->option_id];

            $productDataResponse = array_merge_recursive($andProducts,$orProducts);

            $productDataResponse = array_merge_recursive($optionID, $productDataResponse);

            $j++;
        }

        $resultArray = [];

        if(count($productDataResponse) > 0) {

            $opt = 1;
            foreach ($productDataResponse as $option)
            {
                $resultArray[]['option_'.$opt] = $option;

                $opt++;
            }
        }

        return $resultArray;
    }

    /**
     * get sale shipping address for a given salesObj
     *
     * @return array
     */
    public function getSaleShippingAddress()
    {
        return $this
            ->saleShippingAddress()
            ->get()
            ->map(function ($item)
            {
                return  [
                    'sale_delivery_method' => $item->delivery_method_id,
                    'recipient_name' => $item->recipient_name,
                    'recipient_mobile_country_code_id' => $item->country_id,
                    'recipient_mobile_phone_number' => $item->mobile,
                    'recipient_addresses' => ($item->address != '"[]"') ? $item->address : [],
                    'recipient_selected_shipping_index' => $item->shipping_index
                ];
            })
            ->first();
    }

    /**
     * get sale member address for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function memberAddress()
    {
        return $this->belongsTo(MemberAddress::class,'user_id','user_id');
    }

    /**
     * get sale member contact for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function memberContactInfo()
    {
        return $this->belongsTo(MemberContactInfo::class,'user_id','user_id');
    }

    /**
     * get self Collection point for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function selfCollectionPoint()
    {
        return $this->belongsTo(LocationAddresses::class,'self_collection_point_id');
    }

    /**
     * get enrollment temp details if this sale is enrollment sale
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function enrollmentSale()
    {
        return $this->hasOne(EnrollmentTemp::class);
    }

    /**
     * get sales amp cvs
     *
     * @return array
     */
    public function getSaleAmpCVs()
    {
        $kittingAmpCv = $this
            ->saleKittingClone()
            ->get()
            ->sum(function ($item){
                return $item->virtual_invoice_cv * $item->quantity;
            });

        $productAmpCv = $this
            ->saleProducts()
            ->whereNull('mapping_model')
            ->get()
            ->sum(function ($item){
                return $item->virtual_invoice_cv * $item->quantity;
            });

        return $kittingAmpCv + $productAmpCv;
    }

    /**
     * get sales base cvs
     *
     * @return array
     */
    public function getSaleBaseCVs($masterRepositoryObj, $transactionTypeConfigCodes)
    {
        $settingsData = $masterRepositoryObj->getMasterDataByKey(array('sale_types'));

        $saleType = array_change_key_case($settingsData['sale_types']->pluck('id', 'title')->toArray());

        $baseCvType = [
            $saleType[$transactionTypeConfigCodes['repurchase']],
            $saleType[$transactionTypeConfigCodes['auto-ship']],
            $saleType[$transactionTypeConfigCodes['rental']]
        ];

        $kittingBaseCv = $this
            ->saleKittingClone()
            ->where('transaction_type_id', $baseCvType)
            ->get()
            ->sum(function ($item){
                return $item->eligible_cv * $item->quantity;
            });

        $productBaseCv = $this
            ->saleProducts()
            ->whereIn('transaction_type_id', $baseCvType)
            ->whereNull('mapping_model')
            ->get()
            ->sum(function ($item){
                return $item->eligible_cv * $item->quantity;
            });

        return $kittingBaseCv + $productBaseCv;
    }

    /**
     * get sales wp cvs
     *
     * @return array
     */
    public function getSaleWpCVs($masterRepositoryObj, $transactionTypeConfigCodes)
    {
        $settingsData = $masterRepositoryObj->getMasterDataByKey(array('sale_types'));

        $saleType = array_change_key_case($settingsData['sale_types']->pluck('id', 'title')->toArray());

        $wpCvType = [
            $saleType[$transactionTypeConfigCodes['registration']],
            $saleType[$transactionTypeConfigCodes['member-upgrade']],
            $saleType[$transactionTypeConfigCodes['ba-upgrade']],
            $saleType[$transactionTypeConfigCodes['formation']]
        ];

        $kittingWpCv = $this
            ->saleKittingClone()
            ->whereIn('transaction_type_id', $wpCvType)
            ->get()
            ->sum(function ($item){
                return $item->eligible_cv * $item->quantity;
            });

        $productWpCv = $this
            ->saleProducts()
            ->whereIn('transaction_type_id', $wpCvType)
            ->whereNull('mapping_model')
            ->get()
            ->sum(function ($item){
                return $item->eligible_cv * $item->quantity;
            });

        return $kittingWpCv + $productWpCv;
    }

    /**
     * get sales enrollement cvs
     *
     * @return array
     */
    public function getSaleEnrollementCVs($masterRepositoryObj, $transactionTypeConfigCodes)
    {
        $settingsData = $masterRepositoryObj->getMasterDataByKey(array('sale_types'));

        $saleType = array_change_key_case($settingsData['sale_types']->pluck('id', 'title')->toArray());

        $enrollementCvType = [
            $saleType[$transactionTypeConfigCodes['registration']],
            $saleType[$transactionTypeConfigCodes['member-upgrade']],
            $saleType[$transactionTypeConfigCodes['ba-upgrade']],
            $saleType[$transactionTypeConfigCodes['formation']]
        ];

        $kittingEnrollementpCv = $this
            ->saleKittingClone()
            ->whereIn('transaction_type_id', $enrollementCvType)
            ->get()
            ->sum(function ($item){
                return $item->cv4 * $item->quantity;
            });

        $productEnrollementCv = $this
            ->saleProducts()
            ->whereIn('transaction_type_id', $enrollementCvType)
            ->whereNull('mapping_model')
            ->get()
            ->sum(function ($item){
                return $item->cv4 * $item->quantity;
            });

        return $kittingEnrollementpCv + $productEnrollementCv;
    }


    /**
     * get sale corporate sale details for a given saleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function saleCorporateSale()
    {
        return $this->hasOne(SaleCorporateSale::class, 'sale_id');
    }
}
