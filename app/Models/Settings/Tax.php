<?php
namespace App\Models\Settings;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Country;
use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    use HasAudit;

    protected $table = 'taxes';

    protected $fillable = [
        'country_id',
        'code',
        'rate',
        'default',
        'active'
    ];

    /**
     * get country info for a given taxObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * check if default for a given query
     *
     * @param $query
     * @return mixed
     */
    public function scopeDefault($query)
    {
        return $query->where('default', 1);
    }
}
