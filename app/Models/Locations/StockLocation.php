<?php
namespace App\Models\Locations;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class StockLocation extends Model
{
    use HasAudit;

    protected $table = 'stock_locations';

    protected $fillable = [
        'country_id',
        'name',
        'code',
        'auto_release',
        'active'
    ];

    /**
     * get country details for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * pivot relation between location and stockLocation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function location()
    {
        return $this->belongsToMany(Location::class, 'location_stock_locations')->withPivot(['is_default']);
    }

    /**
     * pivot relation between city and stockLocation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function city()
    {
        return $this->belongsToMany(City::class, 'stock_location_cities');
    }
}
