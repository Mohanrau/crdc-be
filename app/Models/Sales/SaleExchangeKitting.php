<?php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SaleExchangeKitting extends Model
{
    protected $table = 'sales_exchange_kitting';

    protected $fillable = [
        'sale_exchange_id',
        'sale_kitting_id',
        'return_quantity',
        'return_amount'
    ];

    /**
     * get saleExchange info for a given saleExchangeKittingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleExchange()
    {
        return $this->belongsTo(SaleExchange::class);
    }

    /**
     * get kitting info for a given saleExchangeKittingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function kitting()
    {
        return $this->belongsTo(SaleKittingClone::class, 'sale_kitting_id');
    }

    /**
     * get saleExchangeKitting Products for a given saleExchangeKittingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(SaleExchangeProduct::class, 'mapping_id', 'id')
            ->where('mapping_model', 'sales_exchange_kitting');
    }
}
