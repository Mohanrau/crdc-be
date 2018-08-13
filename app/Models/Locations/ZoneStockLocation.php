<?php
namespace App\Models\Locations;

use App\Models\Locations\Zone;
use App\Models\Locations\Location;
use Illuminate\Database\Eloquent\Model;

class ZoneStockLocation extends Model
{
    protected $table = 'zones_stock_locations';

    protected $fillable = [
        'zone_id',
        'effective_date',
        'expiry_date',
        'stock_location_id'
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

    /**
     * get stock location info for a given zonePostcodeObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stockLocation()
    {
        return $this->belongsTo(Location::class);
    }

  }