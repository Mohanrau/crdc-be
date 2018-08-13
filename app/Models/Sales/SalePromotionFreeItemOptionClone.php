<?php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SalePromotionFreeItemOptionClone extends Model
{
    protected $table = 'sales_promotion_free_items_options_clone';

    protected $fillable = [
        'sale_id',
        'promo_id',
        'option_id',
        'option_products'
    ];

    public $timestamps = false;

    /**
     * get sales info for a given salePromotionFreeItemSettingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * get promotionFreeItem info for a given salePromotionFreeItemSettingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function promotionFreeItem()
    {
        return $this->belongsTo(SalePromotionFreeItemClone::class, 'promo_id', 'id');
    }
}
