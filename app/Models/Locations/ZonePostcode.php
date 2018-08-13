<?php
namespace App\Models\Locations;

use App\Models\Locations\Zone;
use Illuminate\Database\Eloquent\Model;

class ZonePostcode extends Model
{
    protected $table = 'zones_postcodes';

    protected $fillable = [
        'zone_id',
        'postcode'
    ];

    /**
     * get zone info for a given zonePostcodeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

  }