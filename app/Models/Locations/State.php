<?php
namespace App\Models\Locations;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Zone;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasAudit, Cachable;

    protected $table = 'states';

    protected $fillable = [
        'country_id',
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
     * get cities for a given stateObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cities()
    {
        return $this->hasMany(City::class);
    }

    /**
     * get zones for a given StateObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function stateZones()
    {
        return $this
            ->belongsToMany(Zone::class, 'zones_states', 'state_id', 'zone_id')
            ->withTimestamps();
    }
}
