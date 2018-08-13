<?php
namespace App\Models\Products;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Country;
use App\Models\Currency\Currency;
use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    use HasAudit;

    protected $table = 'product_prices';

    protected $fillable = [
        'yy_id',
        'country_id',
        'entity_id',
        'product_id',
        'currency_id',
        'gmp_price_gst',
        'rp_price',
        'rp_price_gst',
        'nmp_price',
        'effective_date',
        'expiry_date',

        'base_cv',
        'wp_cv',
        'cv1',
        'cv2',
        'cv3',
        'cv4',
        'cv5',
        'cv6',
        'welcome_bonus_l1',
        'welcome_bonus_l2',
        'welcome_bonus_l3',
        'welcome_bonus_l4',
        'welcome_bonus_l5',

        'active',
        'promo'
    ];

    /**
     * get productPrices where price is promo
     *
     * @param $query
     * @return mixed
     */
    public function scopePromo($query)
    {
        return $query->where('promo', 1);
    }

    /**
     * get country details for a given productPriceObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * get currency details for a given product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * get product promo locations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productPromoLocations()
    {
        return $this->belongsToMany(ProductPromoLocation::class, 'product_promo_locations','promo_id','location_id')
            ->withTimestamps();
    }

    /**
     * get promo locations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function promoLocations()
    {
        return $this->hasMany(ProductPromoLocation::class,'promo_id');
    }

    /**
     * change the representation of array
     *
     * @return array
     */
    public function toArray()
    {
        $data =  [
            'id' => $this->id,
            'gmp_price_tax' => $this->gmp_price_gst,
            'nmp_price' => $this->nmp_price,
            'rp_price' => $this->rp_price,
            'rp_price_tax' => $this->rp_price_gst,
            'effective_date' => $this->effective_date,
            'expiry_date' => $this->expiry_date,
            'base_cv' => $this->base_cv,
            'wp_cv' => $this->wp_cv,
            'cv_1' => $this->cv1,
            'cv_2' => $this->cv2,
            'cv_3' => $this->cv3,
            'cv_4' => $this->cv4,
            'cv_5' => $this->cv5,
            'cv_6' => $this->cv6,
            'cv1' => $this->cv1,
            'cv2' => $this->cv2,
            'cv3' => $this->cv3,
            'cv4' => $this->cv4,
            'cv5' => $this->cv5,
            'cv6' => $this->cv6,
            'bonuses' => [
                'welcome_bonus_1' => $this->welcome_bonus_l1,
                'welcome_bonus_2' => $this->welcome_bonus_l2,
                'welcome_bonus_3'=> $this->welcome_bonus_l3,
                'welcome_bonus_4' => $this->welcome_bonus_l4,
                'welcome_bonus_5' => $this->welcome_bonus_l5,
            ]
        ];

        if ($this->promo == 1)
        {
            $promoLocations = ['location_ids' => $this->promoLocations()->pluck('location_id')];

            $data = array_merge($data, $promoLocations);
        }

        return $data;
    }
}
