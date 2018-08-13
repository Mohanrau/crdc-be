<?php
namespace App\Models\EWallets;

use App\Helpers\Traits\HasAudit;
use App\Models\General\CWSchedule;
use App\Models\Locations\Country;
use Illuminate\Database\Eloquent\Model;

class EWalletGIROBankPayment extends Model
{
    use HasAudit;

    protected $table = 'user_ewallet_giro_bank_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "batch_id",
        "cw_id",
        "registered_country_id",
        "giro_type",
        "total_amount",
        "data"
    ];

    /**
     * Generate Batch ID
     *
     * @return string
     */
    public function generateBatchId()
    {
        $batch_id = config('ewallet.giro_bank_payment_batch_start');
        if($this->count())
        {
            $batch_id = $this->orderBy('id', 'desc')->first()->batch_id + 1;
        }

        return str_pad($batch_id, 9, "0", STR_PAD_LEFT);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwSchedule()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'registered_country_id');
    }
}
