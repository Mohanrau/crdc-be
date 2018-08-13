<?php
namespace App\Models\Campaigns;

use App\Models\{
    Locations\Country,
    Campaigns\Campaign,
    Campaigns\EsacVoucherType,
    Campaigns\EsacVoucherSubType,
    Campaigns\EsacPromotionVoucherSubType,
    Products\ProductCategory,
    Products\Product,
    Kitting\Kitting
};
use App\Helpers\Traits\LastModified;
use Illuminate\Database\Eloquent\Model;

class EsacPromotion extends Model
{
    use LastModified;

    protected $table = 'esac_promotions';

    protected $appends = ['last_modified_by', 'last_modified_at'];

    protected $fillable = [
        'country_id',
        'campaign_id',
        'taxable',
        'voucher_type_id',
        'entitled_by',
        'max_purchase_qty',
        'active',
        'updated_by'
    ];

    /**
     * get country for a given EsacPromotionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * get campaign for a given EsacPromotionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    /**
     * get voucher type for a given EsacPromotionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function esacVoucherType()
    {
        return $this->belongsTo(EsacVoucherType::class, 'voucher_type_id', 'id');
    }

    /**
     * get promotion product category for a given EsacPromotionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function esacPromotionProductCategories()
    {
        return $this
            ->belongsToMany(ProductCategory::class, 'esac_promotion_product_categories', 'promotion_id', 'product_category_id')
            ->withTimestamps();
    }

    /**
     * get promotion exception product for a given EsacPromotionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function esacPromotionExceptionProducts()
    {
        return $this
            ->belongsToMany(Product::class, 'esac_promotion_exception_products', 'promotion_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * get promotion exception kitting for a given EsacPromotionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function esacPromotionExceptionKittings()
    {
        return $this
            ->belongsToMany(Kitting::class, 'esac_promotion_exception_kittings', 'promotion_id', 'kitting_id')
            ->withTimestamps();
    }

    /**
     * get promotion product for a given EsacPromotionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function esacPromotionProducts()
    {
        return $this
            ->belongsToMany(Product::class, 'esac_promotion_products', 'promotion_id', 'product_id')
            ->withTimestamps();
    }

    /**
     * get promotion kitting for a given EsacPromotionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function esacPromotionKittings()
    {
        return $this
            ->belongsToMany(Kitting::class, 'esac_promotion_kittings', 'promotion_id', 'kitting_id')
            ->withTimestamps();
    }

    /**
     * get promotion voucher sub type for a given EsacPromotionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function esacPromotionVoucherSubTypes()
    {
        return $this
            ->hasMany(EsacPromotionVoucherSubType::class, 'promotion_id', 'id')
            ->with(['voucherSubType', 'voucherPeriod']);
    }
}