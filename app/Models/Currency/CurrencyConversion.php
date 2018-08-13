<?php
namespace App\Models\Currency;

use App\Helpers\Traits\HasAudit;
use App\Models\General\CWSchedule;
use Illuminate\Database\Eloquent\Model;

class CurrencyConversion extends Model
{
    use HasAudit;

    protected $table = 'currencies_conversions';

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'cw_id'
    ];

    /**
     * get from currency details for a given currenyConversionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    /**
     * get to currency details for a given currenyConversionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    /**
     * get the cwSchedules for a given currenyConversionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }
}
