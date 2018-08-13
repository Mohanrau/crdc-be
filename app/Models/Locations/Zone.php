<?php
namespace App\Models\Locations;

use App\{
    Models\Locations\Country,
    Models\Locations\State,
    Models\Locations\City,
    Models\Locations\ZonePostcode,
    Models\Locations\ZoneStockLocation,
    Models\Users\User
};
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $table = 'zones';

    protected $fillable = [
        'code',
        'name',
        'is_all_countries',
        'is_all_states',
        'is_all_cities',
        'is_all_postcodes',
        'updated_by'
    ];

    /**
     * get country for a given ZoneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function zoneCountries()
    {
        return $this
            ->belongsToMany(Country::class, 'zones_countries', 'zone_id', 'country_id')
            ->withTimestamps();
    }

    /**
     * get state for a given ZoneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function zoneStates()
    {
        return $this
            ->belongsToMany(State::class, 'zones_states', 'zone_id', 'state_id')
            ->withTimestamps();
    }

    /**
     * get city for a given ZoneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function zoneCities()
    {
        return $this
            ->belongsToMany(City::class, 'zones_cities', 'zone_id', 'city_id')
            ->withTimestamps();
    }

    /**
     * get postcode for a given ZoneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zonePostcodes()
    {
        return $this->hasMany(ZonePostcode::class);
    }

    /**
     * get stock location for a given ZoneObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function zoneStockLocations()
    {
        return $this->hasMany(ZoneStockLocation::class);
    }

    /**
     * return created by - user details for a given roleGroupObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * return update by - user info for a given roleGroupObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }
}
