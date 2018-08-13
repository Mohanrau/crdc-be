<?php
namespace App\Models\Enrollments;

use App\Models\{
    Bonus\EnrollmentRank,
    Locations\Country
};
use Illuminate\Database\Eloquent\Model;

class EnrollmentTypes extends Model
{
    protected $table = 'enrollments_types';

    protected $fillable = [
        'title',
        'info',
        'step_data',
        'sale_types',
        'active'
    ];

    /**
     * check if tax active
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * get countries enrollments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function countryEnrollments()
    {
        return $this->belongsToMany(Country::class, 'country_enrollments');
    }

    /**
     * get enrollmentRanks data for the given enrollmentTypeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function enrollmentRanks()
    {
        return $this->hasMany(EnrollmentRank::class, 'enrollment_type_id');
    }
}
