<?php
namespace App\Models\Sales;

use App\Models\Kitting\Kitting;
use Illuminate\Database\Eloquent\Model;

class LegacySaleExchangeKittingClone extends Model
{
    protected $table = 'legacies_sales_exchanges_kitting_clone';

    protected $fillable = [
        'sale_exchange_id',
        'kitting_id',
        'code',
        'name',
        'available_quantity_snapshot',
        'return_quantity',
        'gmp_price_gst',
        'nmp_price',
        'return_total'
    ];

    /**
     * get legacy sale exchange detail for a given legacySaleExchangeKittingCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleExchange()
    {
        return $this->belongsTo(SaleExchange::class,'sale_exchange_id');
    }

    /**
     * get kitting details for a given legacySaleExchangeKittingCloneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitting()
    {
        return $this->belongsTo(Kitting::class, 'kitting_id');
    }

    /**
     * get legacy sale cancellation kitting products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(LegacySaleExchangeProduct::class, 'legacy_sale_exchange_kitting_clone_id', 'id');
    }
}
