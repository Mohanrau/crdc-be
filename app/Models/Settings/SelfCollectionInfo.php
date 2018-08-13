<?php
namespace App\Models\Settings;

use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Stockists\Stockist;
use Illuminate\Database\Eloquent\Model;

class SelfCollectionInfo extends Model
{
    protected $table = 'self_collection_info';

    protected $fillable = [
        'country_id',
        'state_id',
        'area',
        'stockist_id',
        'name',
        'address',
        'contact_no'
    ];

    /**
     * get country of current Obj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * get state of current Obj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo(State::class);
    }

    /**
     * get stockist of current Obj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockist()
    {
        return $this->belongsTo(Stockist::class);
    }
}
