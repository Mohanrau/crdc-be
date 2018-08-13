<?php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SaleExchangeProduct extends Model
{
    protected $table = 'sales_exchange_products';

    protected $fillable = [
        'sale_exchange_id',
        'sale_product_id',
        'mapping_id',
        'mapping_model',
        'snapshot_quantity',
        'kitting_quantity',
        'return_quantity',
        'return_amount',
        'gmp_price_gst',
        'rp_price',
        'rp_price_gst',
        'nmp_price',
        'average_price_unit',
        'total'
    ];

    /**
     * get sales exchange info for a given saleExchangeProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleExchange()
    {
        return $this->belongsTo(SaleExchange::class, 'sale_exchange_id');
    }

    /**
     * get product details for a given salesExchangeProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(SaleProduct::class, 'sale_product_id');
    }

    /**
     * get kitting for a given salesExchangeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitting()
    {
        return $this->belongsTo(SaleKittingClone::class, 'mapping_id');
    }

    /**
     * get the mapped model of this product sale - to find out if this is a loose product, pwp or kitting
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getMappedModel()
    {
        switch($this->mapping_model){
            case 'sales_exchange_kitting' :
                return $this->belongsTo(SaleExchangeKitting::class, 'mapping_id');
                break;
            default :
                return $this->product();
        }
    }
}
