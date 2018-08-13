<?php
namespace App\Models\Locations;

use App\Helpers\Traits\AccessControl;
use App\Helpers\Traits\HasAudit;
use App\Models\Products\Product;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    use HasAudit, AccessControl;

    protected $table = 'entities';

    protected $fillable = [
        'country_id',
        'name',
        'active'
    ];

    /**
     * get country details for a given EntityObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get locations for a given entityObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    /**
     * get user locations for a given entityObj - RNP
     *
     * @param int $countryId
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locationsRnp(int $countryId = null)
    {
        $data =  $this->hasMany(Location::class);

        if ($this->isUser('back_office') or $this->isUser('stockist') or $this->isUser('stockist_staff')){
            $data = $data
                ->whereIn('id', $this->getUserLocations($countryId))
                ->active();
        }

        return $data;
    }

    /**
     * get products for a given entity id
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_entities');
    }
}
