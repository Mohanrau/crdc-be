<?php
namespace App\Models\Sales;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class LegacySaleExchangeProduct extends Model
{
    use HasAudit;

    protected $table = 'legacies_sales_exchanges_products';

    protected $fillable = [
        'sale_exchange_id',
        'legacy_sale_exchange_kitting_clone_id',
        'available_quantity_snapshot',
        'return_quantity',
        'gmp_price_gst',
        'nmp_price',
        'average_price_unit',
        'return_total',
    ];

    /**
     * get sale exchange detail for a given legacySaleExchangeProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleExchange()
    {
        return $this->belongsTo(SaleExchange::class,'sale_exchange_id');
    }

    /**
     * get legacy sale exchange product clone for a given legacySaleExchangeProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function legacySaleExchangeProductClone()
    {
        return $this->hasOne(LegacySaleExchangeProductClone::class, 'legacy_sale_exchange_product_id');
    }

    /**
     * get kitting clone detail for a given legacySaleExchangeProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function legacySaleExchangeKittingClone()
    {
        return $this->belongsTo(LegacySaleExchangeKittingClone::class,'legacy_sale_exchange_kitting_clone_id');
    }

}
