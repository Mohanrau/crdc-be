<?php
namespace App\Models\Kitting;

use App\{
    Helpers\Traits\HasAudit,
    Models\Locations\Country,
    Models\Locations\Location
};
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Kitting extends Model
{
    use HasAudit;

    protected $table = 'kitting';

    protected $fillable = [
        'country_id',
        'code',
        'name',
        'is_esac',
        'active'
    ];

    /**
     * get country details for a given kittingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get all kitting products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(KittingProduct::class);
    }

    /**
     * get product details with productPrices for a given countryId
     *
     * @param $countryId
     * @param $with
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kittingProducts($countryId, $with = [])
    {
        return $this->hasMany(KittingProduct::class)
            ->with(['product.productGeneralSetting'])
            ->with($with);

    }

    /**
     * get kittingDescriptions for a given kittingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kittingDescriptions()
    {
        return $this->hasMany(KittingDescription::class)
            ->with('language');
    }

    /**
     * get kittingLocations for a given kittingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function kittingLocations()
    {
        return $this->belongsToMany(Location::class, 'kitting_locations');
    }

    /**
     * get kittingPrice(one) for a given kittingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function kittingPrice()
    {
        return $this->hasOne(KittingPrice::class);
    }

    /**
     * get kittingImages for a given kittingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kittingImages()
    {
        return $this->hasMany(KittingImage::class);
    }

    /**
     * get product general settings
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kittingGeneralSetting()
    {
        return $this->hasMany(KittingGeneralSetting::class)
                    ->with(['masterData.master' => function($query){
                        $query->groupBy('title');
                    }]);
    }
}
