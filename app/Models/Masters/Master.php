<?php
namespace App\Models\Masters;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Country;
use Illuminate\Database\Eloquent\Model;

class Master extends Model
{
    use HasAudit;

    protected $table = 'masters';

    protected $fillable = [
        'title',
        'key',
        'active'
    ];

    /**
     * Cast Name to Capitalize First Letter of every word
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        return ucwords( strtolower($this->attributes['title']) );
    }

    /**
     * get masterData for a given masterObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function masterData()
    {
        return $this->hasMany(MasterData::class);
    }

    /**
     * country rules based on master data
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function countryRules()
    {
        return $this->belongsToMany(Country::class, 'country_master_data');
    }
}
