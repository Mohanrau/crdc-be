<?php
namespace App\Models\Locations;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class LocationTypes extends Model
{
    use HasAudit;

    protected $table = 'locations_types';

    protected $fillable = [
        'code',
        'name'
    ];

    /**
     * get location for a given locationTypesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
