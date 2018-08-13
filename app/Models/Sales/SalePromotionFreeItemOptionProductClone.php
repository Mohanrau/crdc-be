<?php
namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;

class SalePromotionFreeItemOptionProductClone extends Model
{
    protected $table = 'sales_promotion_free_items_options_products_clone';

    protected $fillable = [
        'sale_id',
        'promo_id',
        'option_id',
        'product_id',
        'product_clone_id',
        'quantity'
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
     * get product details for a given obj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(SaleProductClone::class, 'product_clone_id');
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
