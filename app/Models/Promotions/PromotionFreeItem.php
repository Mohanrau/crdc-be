<?php
namespace App\Models\Promotions;

use App\{
    Helpers\Traits\HasAudit,
    Models\Kitting\Kitting,
    Models\Masters\MasterData,
    Models\Products\Product,
    Models\Products\ProductCategory
};
use Illuminate\Database\Eloquent\Model;

class PromotionFreeItem extends Model
{
    use HasAudit;

    protected $table = 'promotion_free_items';

    protected $fillable = [
        'country_id',
        'name',
        'start_date',
        'end_date',
        'promo_type_id',
        'from_cv_range',
        'to_cv_range',
        'pwp_value',
        'min_purchase_qty',
        'options_relation',
        'active'
    ];

    /**
     * get products for a given promotionFreeItemObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class,'promotion_free_items_products','promo_id');
    }

    /**
     * get kitting's for a given promotionFreeItemObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function kitting()
    {
        return $this->belongsToMany(Kitting::class,'promotion_free_items_kitting','promo_id');
    }

    /**
     * get products for a given promotionFreeItemObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function promotionFreeItemOptionProducts()
    {
        return $this->hasMany(PromotionFreeItemOptionProducts::class,'promo_id')
            ->with('product');
    }

    /**
     * get category details with a given obj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(ProductCategory::class,'promotion_free_items_categories','promo_id');
    }

    /**
     * get promotion free item type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function promotionType()
    {
        return $this->belongsTo(MasterData::class,'promo_type_id');
    }

    /**
     * get promotionFreeItemsOptions for a given obj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function promotionOptions()
    {
        return $this->hasMany(PromotionFreeItemOption::class,'promo_id');
    }
}
