<?php
namespace App\Models\Sales;

use App\{
    Helpers\Traits\HasAudit,
    Models\General\CWSchedule,
    Models\Invoices\LegacyInvoice,
    Models\Locations\Country,
    Models\Locations\Location,
    Models\Locations\StockLocation,
    Models\Masters\MasterData,
    Models\Members\Member,
    Models\Users\User
};
use Illuminate\Database\Eloquent\Model;

class SaleExchange extends Model
{
    use HasAudit;

    protected $table = 'sales_exchanges';

    protected $fillable = [
        'user_id',
        'country_id',
        'sale_id',
        'parent_sale_id',
        'legacy_invoice_id',
        'transaction_location_id',
        'stock_location_id',
        'cw_id',
        'reason_id',
        'fms_number',
        'transaction_date',
        'delivery_fees',
        'balance',
        'exchange_amount_total',
        'return_amount_total',
        'remarks',
        'is_legacy',
        'order_status_id'
    ];

    /**
     * get member data for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id', 'user_id');
    }

    /**
     * get user data for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * get issuer data for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function issuer()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * get country details for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get sales info for a given saleExchangeObj data
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * get the parent sales data for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parentSale()
    {
        return $this->belongsTo(Sale::class, 'parent_sale_id');
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
     * get legacy invoice details for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legacyInvoice()
    {
        return $this->belongsTo(LegacyInvoice::class, 'legacy_invoice_id');
    }

    /**
     * get transaction location details for a given salesExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionLocation()
    {
        return $this->belongsTo(Location::class, 'transaction_location_id');
    }

    /**
     * get stock location details for a given salesExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockLocation()
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }

    /**
     * get the cw for a given cw saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class, 'cw_id');
    }

    /**
     * get saleExchange product for a given salesExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleExchangeProducts()
    {
        return $this->hasMany(SaleExchangeProduct::class);
    }

    /**
     * get saleExchangeKitting for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleExchangeKitting()
    {
        return $this->hasMany(SaleExchangeKitting::class);
    }

    /**
     * get the credit note for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function creditNote()
    {
       return $this->hasOne(CreditNote::class, 'mapping_id')
           ->where('mapping_model', $this->table);
    }

    /**
     * get saleExchangeBill info for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function saleExchangeBill()
    {
        return $this->hasOne(SaleExchangeBill::class);
    }

    /**
     * get salesExchangeReturned Products
     *
     * @return array
     */
    public function getSaleExchangeReturnedProducts()
    {
        return $this
            ->saleExchangeProducts()
            ->with('product.product')
            ->whereNull('mapping_model')
            ->get()
            ->map(function ($item){
                return [
                    'id' => $item->id,
                    'sale_exchange_id' => $item->sale_exchange_id,
                    'sale_product_id' => $item->sale_product_id,
                    'quantity' => $item->product->quantity,
                    'foc_qty' => $item->product->foc_qty,
                    'available_quantity' => $item->product->available_quantity,
                    'return_quantity' => $item->return_quantity,
                    'return_amount' => $item->return_amount,
                    'name' => $item->product->product->name,
                    'sku' => $item->product->product->sku,
                    'uom' => $item->product->product->uom,
                    'transaction_type_id' => $item->product->transaction_type_id,
                    'base_price' => [
                        'gmp_price_tax' => $item->product->gmp_price_gst,
                        'nmp_price' => $item->product->nmp_price,
                        'base_cv' => $item->product->base_cv,
                        'cv1' => $item->product->cv1,
                        'wp_cv' => $item->product->wp_cv,
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * get salesExchangeReturnedKitting for a given SaleExchangeObj
     *
     * @return array
     */
    public function getSaleExchangeReturnedKitting()
    {
        return $this
            ->saleExchangeKitting()
            ->with('products')
            ->get()
            ->map(function ($item){
                return [
                    'id' => $item->id,
                    'sale_exchange_id' => $item->sale_exchange_id,
                    'sale_kitting_id' => $item->sale_kitting_id,
                    'name' => $item->kitting->name,
                    'code' => $item->kitting->code,
                    'quantity' => $item->kitting->quantity,
                    'available_quantity' => $item->kitting->available_quantity,
                    'transaction_type_id' => $item->kitting->transaction_type_id,
                    'eligible_cv' => $item->kitting->eligible_cv,
                    'return_quantity' => $item->return_quantity,
                    'return_amount' => $item->return_amount,
                    'kitting_price' => [
                        'gmp_price_tax' => $item->kitting->gmp_price_gst,
                        'nmp_price' => $item->kitting->nmp_price,
                        'base_cv' => $item->kitting->base_cv,
                        'cv1' => $item->kitting->cv1,
                        'wp_cv' => $item->kitting->wp_cv,
                    ],
                    'kitting_products' => collect($item->products)->map(function ($productItem) use ($item){
                        return
                            [
                                'id' => $productItem->id,
                                'sale_exchange_id' => $productItem->sale_exchange_id,
                                'sale_product_id' => $productItem->sale_product_id,
                                'quantity' => $productItem->product->quantity,
                                'foc_qty' => $productItem->product->foc_qty,
                                'available_quantity' => $productItem->product->available_quantity,
                                'return_quantity' => $productItem->return_quantity,
                                'return_amount' => $productItem->return_amount,
                                'product' => [
                                    'id' => $productItem->id,
                                    'product_id' => $productItem->id,
                                    'name' => $productItem->product->product->name,
                                    'sku' => $productItem->product->product->sku,
                                    'transaction_type_id' => $productItem->product->transaction_type_id,
                                    'base_price' => [
                                        'average_price_unit' => $productItem->product->average_price_unit,
                                        'gmp_price_tax' => $productItem->product->gmp_price_gst,
                                        'total' => $productItem->product->total,
                                        'base_cv' => $productItem->product->base_cv,
                                        'wp_cv' => $productItem->product->wp_cv,
                                    ]
                                ]
                            ];
                    })
                ];
            })
            ->toArray();
    }

    /**
     * get salesExchangeReturned Products
     *
     * @return array
     */
    public function getSaleExchangeReturnedPromotions()
    {
        return $this
            ->saleExchangeProducts()
            ->with('product.product')
            ->where('mapping_model', 'sales_promotion_free_items_clone')
            ->get()
            ->map(function ($item){
                return [
                    'id' => $item->id,
                    'sale_exchange_id' => $item->sale_exchange_id,
                    'sale_product_id' => $item->sale_product_id,
                    'quantity' => $item->product->quantity,
                    'foc_qty' => $item->product->foc_qty,
                    'available_quantity' => $item->product->available_quantity,
                    'return_quantity' => $item->return_quantity,
                    'return_amount' => $item->return_amount,
                    'name' => $item->product->product->name,
                    'sku' => $item->product->product->sku,
                    'uom' => $item->product->product->uom,
                    'transaction_type_id' => $item->product->transaction_type_id,
                    'base_price' => [
                        'gmp_price_tax' => $item->product->gmp_price_gst,
                        'nmp_price' => $item->product->nmp_price,
                        'base_cv' => $item->product->base_cv,
                        'cv1' => $item->product->cv1,
                        'wp_cv' => $item->product->wp_cv,
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * get legacy sale exchange return products for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function legacySaleExchangeReturnProduct()
    {
        return $this->hasMany(LegacySaleExchangeProduct::class, 'sale_exchange_id')
            ->whereNull('legacy_sale_exchange_kitting_clone_id');
    }

    /**
     * get legacy sale exchange return kitting for a given saleExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function legacySaleExchangeReturnKitting()
    {
        return $this->hasMany(LegacySaleExchangeKittingClone::class, 'sale_exchange_id');
    }

    /**
     * get return legacy product in sales exchange
     *
     * @return array
     */
    public function getLegacySaleExchangeReturnProducts()
    {
        return $this
            ->legacySaleExchangeReturnProduct()
            ->with('legacySaleExchangeProductClone')
            ->get()
            ->map(function ($item){
                return  [
                    'product_id' => $item['legacySaleExchangeProductClone']->product_id,
                    'name' => $item['legacySaleExchangeProductClone']->name,
                    'sku' => $item['legacySaleExchangeProductClone']->sku,
                    'quantity' => $item->return_quantity,
                    'base_price' => [
                        'gmp_price_tax' => $item->gmp_price_gst,
                        'nmp_price' => $item->nmp_price,
                        'return_total' => $item->return_total
                    ],
                    'available_quantity' => $item->available_quantity_snapshot,
                    'return_quantity' => $item->return_quantity
                ];
            });
    }

    /**
     * get return legacy kitting in sales exchange
     *
     * @return array
     */
    public function getLegacySaleExchangeReturnKitting()
    {
        return $this
            ->legacySaleExchangeReturnKitting()
            ->with('products.legacySaleExchangeProductClone')
            ->get()
            ->map(function ($item){
                return [
                    'kitting_id' => $item->kitting_id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'quantity' => $item->return_quantity,
                    'available_quantity' => $item->available_quantity_snapshot,
                    'kitting_price' => [
                        'gmp_price_tax' => $item->gmp_price_gst,
                        'nmp_price' => $item->nmp_price,
                        'total' => $item->total
                    ],
                    'return_quantity' => $item->return__quantity,
                    'kitting_products' => collect($item->products)->map(function ($productItem){
                        return [
                            'product_id' => $productItem['legacySaleExchangeProductClone']->product_id,
                            'name' => $productItem['legacySaleExchangeProductClone']->name,
                            'sku' => $productItem['legacySaleExchangeProductClone']->sku,
                            'quantity' => $productItem->return_quantity,
                            'base_price' => [
                                'gmp_price_tax' => $productItem->gmp_price_gst,
                                'nmp_price' => $productItem->nmp_price,
                                'average_price_unit' => $productItem->average_price_unit,
                                'total' => $productItem->total
                            ],
                            'available_quantity' => $productItem->available_quantity_snapshot,
                            'return_quantity' => $productItem->return_quantity
                        ];
                    })
                ];
            });
    }

}
