<?php
namespace App\Models\Products;

use App\Helpers\Traits\HasAudit;
use App\Models\Dummy\Dummy;
use App\Models\Locations\Entity;
use App\Models\Masters\Master;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasAudit;

    protected $table = 'products';

    protected $fillable = [
        'yy_product_id',
        'category_id',
        'name',
        'sku',
        'uom',
        'is_dummy_code',
        'inventorize'
    ];

    /**
     * get category info for a given ProductObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    /**
     * sync the relationships between the products and entities
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function entity()
    {
        return $this->belongsToMany(Entity::class, 'product_entities');
    }

    /**
     * get the product size groups for a given productObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function sizeGroups()
    {
        return $this->belongsToMany(Master::class, 'product_size_groups')
            ->with('masterData');
    }

    /**
     * get the active status by countryId
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function productActiveByCountry(int $countryId)
    {
        return $this->hasOne(ProductActive::class)
            ->where('country_id', $countryId);
    }

    /**
     * check if the given product is available in the given country
     *
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productAvailableInCountry(int $countryId)
    {
        return $this->belongsToMany(Entity::class, 'product_entities')
            ->where('country_id', $countryId);
    }

    /**
     * get productPrices for a given product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class)
            ->with('currency');
    }

    /**
     * get latest active product price
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productPricesLatest()
    {
        return $this->hasMany(ProductPrice::class)
            ->with('currency');
    }

    /**
     * get productPricePromos for a given product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productPricePromos()
    {
        return $this->hasMany(ProductPrice::class)
            ->with(['promoLocations' => function($query){
                $query->pluck('location_id');
            }])
            ->where('promo', 1);
    }

    /**
     * get product locations for a given product
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productLocations(int $countryId)
    {
        return $this
            ->hasMany(ProductLocation::class)
            ->where('country_id', $countryId);
    }

    /**
     * get product rental plan for a given product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productRentalPlan()
    {
        return $this->hasMany(ProductRentalPlan::class, 'product_id')
            ->with('productRentalCvAllocation');
    }

    /**
     * get product description for a given productIbj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productDescriptions()
    {
        return $this->hasMany(ProductDescription::class)
            ->with('language');
    }

    /**
     * get product images for a given productObj
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productImages(int $countryId = 0)
    {
        $data = $this->hasMany(ProductImage::class);

        if ($countryId > 0){
            $data = $data->where('country_id', $countryId);
        }

        return $data;
    }

    /**
     * get dumy codes for a given product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function dummy()
    {
        return $this->belongsToMany(Dummy::class, 'dummies_products');
    }

    /**
     * get virtual product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function virtualProducts()
    {
        return $this->belongsToMany(Product::class, 'virtual_products', 'product_id', 'virtual_product_id')
                    ->withPivot(['country_id', 'master_data_id']);
    }

    /**
     * get product general settings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productGeneralSetting()
    {
        return $this->hasMany(ProductGeneralSetting::class)
            ->with(['masterData.master' => function($query){
                $query->groupBy('title');
            }]);
    }

    /**
     * get product base price for a given country and ProductObj
     *
     * @param int $countryId
     * @return Model|null|static
     */
    public function getProductPriceByCountry(int $countryId)
    {
        return $this
            ->productPrices()
            ->where('country_id',$countryId)
            ->where('promo', 0)
            ->where('product_prices.expiry_date','>=', Carbon::now())
            ->where('effective_date','<=', Carbon::now())
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * check if we have promo price for a given productObj
     *
     * @param int $countryId
     * @param array $locationId
     * @param null $startDate
     * @return Model|null|static
     */
    public function getEffectivePromoProductPrice(int $countryId, array $locationId = [], $startDate = null)
    {
        $data = $this
            ->productPrices()
            ->where('product_prices.country_id',$countryId)
            ->where('product_prices.expiry_date','>=', Carbon::now());

        if ($startDate != null) {
            $data
                ->where('effective_date', '<=', $startDate);
        } else{
            $data
                ->where('effective_date','<=', Carbon::now());
        }

        if (count($locationId) > 0)
        {
            $data->join('product_promo_locations', function ($join) use ($locationId)
            {
                $join
                    ->on('product_promo_locations.promo_id', '=', 'product_prices.id')
                    ->where('product_prices.promo', 1)
                    ->whereIn('product_promo_locations.location_id', $locationId);
            });
        }

        return $data
            ->orderBy('id','desc')
            ->first();
    }

    /**
     * check if we have base price for a given productObj
     *
     * @param int $countryId
     * @param array $locationId
     * @param null $startDate
     * @return Model|null|static
     */
    public function getEffectiveBaseProductPrice(int $countryId, array $locationId = [], $startDate = null)
    {
        $data = $this
            ->productPrices()
            ->where('product_prices.country_id',$countryId)
            ->where('product_prices.expiry_date','>=', Carbon::now())
        ;

        if ($startDate != null) {
            $data
                ->where('effective_date', '<=', $startDate);
        } else{
            $data
                ->where('effective_date','<=', Carbon::now());
        }

        if (count($locationId) > 0)
        {
            $data->join('product_locations', function ($join) use ($locationId) {
                $join
                    ->on('product_locations.product_id', '=', 'product_prices.product_id')
                    ->where('product_prices.promo', 0)
                    ->whereIn('product_locations.location_id', $locationId);
            });
        }

        return $data
            ->orderBy('id','desc')
            ->first();

    }

    /**
     * get product promo price for a given country and ProductObj
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductPromoPriceByCountry(int $countryId)
    {
        return $this
            ->productPricePromos()
            ->where('country_id',$countryId)
            ->where('promo', 1)
            ->get();
    }

    /**
     * check product promo by promo date
     *
     * @param int $countryId
     * @param int $promoId
     * @param $effectiveDate
     * @return int
     */
    public function checkProductPromoDateRange(int $countryId, int $promoId = 0, $effectiveDate)
    {
        //todo compair promo date range to product effective dates - Jalala
        $data =  $this
            ->productPricePromos()
            ->where('country_id',$countryId)
            ->where('expiry_date', '>=', $effectiveDate);

        if ($promoId > 0)
            $data->where('id', '<>', $promoId);

        return $data->count();
    }

    /**
     * get product name for a given productObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productNames()
    {
        return $this->hasMany(ProductName::class);
    }

    /**
     * get product name by country id
     *
     * @param int $countryId
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|null|object
     */
    public function getProductName(int $countryId)
    {
        return $this
            ->productNames()
            ->where('country_id', $countryId)
            ->first();
    }
}
