<?php
namespace App\Models\Locations;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasAudit;

    protected $table = 'locations';

    protected $fillable = [
        'entity_id',
        'name',
        'code',
        'location_types_id',
        'active'
    ];

    /**
     * get entity info for a given locationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entity()
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * get the location type details for a given locationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function locationType()
    {
        return $this->belongsTo(LocationTypes::class, 'location_types_id');
    }

    /**
     * get location address info for a given locationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function locationAddress()
    {
        return $this->hasOne(LocationAddresses::class, 'location_id');
    }

    /**
     * pivot relation between location and stockLocation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stockLocations()
    {
        return $this->belongsToMany(StockLocation::class, 'location_stock_location')->withPivot(['is_default']);
    }
}
