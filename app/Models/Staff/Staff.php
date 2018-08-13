<?php
namespace App\Models\Staff;

use App\Models\{
    Users\User,
    Locations\Country,
    Locations\City
};
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Traits\HasAudit;

class Staff extends Model
{
    use HasAudit;
    
    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'stockist_user_id',
        'country_id',
        'position'
    ];

    /**
     * get user data for a given staffObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * get country details for a given staffObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get city details for a given staffObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
