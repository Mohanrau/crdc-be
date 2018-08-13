<?php
namespace App\Models\Locations;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Zone;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasAudit, Cachable;

    protected $table = 'cities';

    protected $fillable = [
        'country_id',
        'state_id',
        'name',
        'active'
    ];

    /**
     * Cast Name to Capitalize First Letter of every word
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return ucwords( strtolower($this->attributes['name']) );
    }

    /**
     * pivot relation between location and stockLocation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stockLocation()
    {
        return $this->belongsToMany(StockLocation::class, 'stock_location_cities');
    }

     /**
     * get zones for a given CityObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cityZones()
    {
        return $this
            ->belongsToMany(Zone::class, 'zones_cities', 'city_id', 'zone_id')
            ->withTimestamps();
    }
}
