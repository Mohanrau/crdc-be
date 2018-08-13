<?php
namespace App\Models\Sales;

use App\Models\Masters\MasterData;
use App\Models\Promotions\PromotionFreeItem;
use Illuminate\Database\Eloquent\Model;

class SalePromotionFreeItemClone extends Model
{
    protected $table = 'sales_promotion_free_items_clone';

    protected $fillable = [
        'sale_id',
        'promotion_free_items_id',
        'name',
        'start_date',
        'end_date',
        'promo_type_id',
        'from_cv_range',
        'to_cv_range',
        'pwp_value',
        'min_purchase_qty',
        'options_relation'
    ];

    /**
     * get promotion free item details for a given salePromotionFreeItemObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function promotionFreeItem()
    {
        return $this->belongsTo(PromotionFreeItem::class);
    }

    /**
     * get sale promotion free items products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(SaleProduct::class, 'mapping_id', 'id')
            ->where('mapping_model', 'sales_promotion_free_items_clone');
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
     * get products for a given salePromotionFreeItemObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salePromotionFreeItemOptionProductClone()
    {
        return $this->hasMany(SalePromotionFreeItemOptionProductClone::class,'promo_id');
    }

    /**
     * get salePromotionFreeItemsOptions for a given obj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function salePromotionOptionsClone()
    {
        return $this->hasMany(SalePromotionFreeItemOptionClone::class,'promo_id');
    }
}
