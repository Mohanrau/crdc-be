<?php
namespace App\Models\Languages;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Country;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasAudit;

    protected $table = 'languages';

    protected $fillable = [
        'key',
        'name',
        'active'
    ];

    /**
     * country languages based on country
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function countryLanguages()
    {
        return $this->belongsToMany(Country::class, 'country_languages')->withPivot('order');
    }
}
