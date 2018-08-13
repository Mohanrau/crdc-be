<?php
namespace App\Models\Locations;

use App\{Helpers\Traits\HasAudit,
    Models\Authorizations\Role,
    Models\Currency\Currency,
    Models\Enrollments\EnrollmentTypes,
    Models\Kitting\Kitting,
    Models\Languages\Language,
    Models\Masters\Master,
    Models\Locations\Zone,
    Models\Settings\Tax};
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasAudit;

    protected $table = 'countries';

    protected $fillable = [
        'name',
        'code',
        'code_iso_2',
        'call_code',
        'tax_desc',
        'default_currency_id',
        'active'
    ];

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', '=', 1);
    }

    /**
     * get the roles for a give country
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'country_roles');
    }

    /**
     * get all taxes for a given country
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    /**
     * get entity for a given countryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function entity()
    {
        return $this->hasOne(Entity::class);
    }

    /**
     * get kitting's for a given countryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function kitting()
    {
        return $this->hasMany(Kitting::class);
    }

    /**
     * get currency info for a given countryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }

    /**
     * get states for  a given CountryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function states()
    {
        return $this->hasMany(State::class);
    }

    /**
     * get banks for a given countryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function banks()
    {
        return $this->hasMany(CountryBank::class);
    }

    /**
     * get stock location for  a given CountryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function stockLocation()
    {
        return $this->hasMany(StockLocation::class);
    }

    /**
     * get countries enrollments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function enrollmentTypes()
    {
        return $this->belongsToMany(EnrollmentTypes::class, 'country_enrollments')
            ->withTimestamps()
            ->with('enrollmentRanks');
    }

    /**
     * get country default tax for a given countryId
     *
     * @param int $countryId
     * @return mixed
     */
    public function countryTax(int $countryId)
    {
        return $this
            ->where('id', $countryId)
            ->first()
            ->taxes()
            ->active()
            ->default()
            ->first();
    }

    /**
     * country rules based on master data
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function countryRules()
    {
        return $this->belongsToMany(Master::class, 'country_master_data');
    }

    /**
     * country languages based on country
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function countryLanguages()
    {
        return $this->belongsToMany(Language::class, 'country_languages')->withPivot('order')->orderBy('country_languages.order');
    }

    /**
     * get zones for a given CountryObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function countryZones()
    {
        return $this
            ->belongsToMany(Zone::class, 'zones_countries', 'country_id', 'zone_id')
            ->withTimestamps();
    }
}
