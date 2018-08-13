<?php
namespace App\Models\Promotions;

use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;

class PromotionFreeItemOptionProducts extends Model
{
    protected $table = 'promotion_free_items_options_products';

    protected $fillable = [
        'promo_id',
        'option_id',
        'product_id',
        'quantity'
    ];

    public $timestamps = false;

    /**
     * get product details for a given promotionFreeItemProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
