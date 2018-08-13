<?php
namespace App\Models\Invoices;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\Traits\HasAudit;
use App\Models\General\CWSchedule;
use App\Models\Locations\Country;
use App\Models\Locations\Location;

class LegacyInvoice extends Model
{
    use HasAudit;

    protected $table = 'legacies_invoices';

    protected $fillable = [
        'cw_id',
        'country_id',
        'transaction_location_id',
        'invoice_number',
        'invoice_date'
    ];

    /**
     * get the cwSchedules for a given legacyInvoicesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }

    /**
     * get country details for a given legacyInvoicesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * get transaction location details for a given legacyInvoicesObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transactionLocation()
    {
        return $this->belongsTo(Location::class, 'transaction_location_id');
    }
}
