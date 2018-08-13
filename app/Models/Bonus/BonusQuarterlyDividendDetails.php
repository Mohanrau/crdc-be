<?php
namespace App\Models\Bonus;

use Illuminate\Database\Eloquent\Model;
use App\Models\General\CWSchedule;

class BonusQuarterlyDividendDetails extends Model
{
    protected $table = 'bonus_quarterly_dividend_details';

    protected $fillable = [
        'bonuses_summary_id',
        'cw_id',
        'shares'
    ];

    /**
     * get bonuses details for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bonuses()
    {
        return $this->belongsTo(BonusSummary::class, 'bonuses_summary_id');
    }

    /**
     * return commission week for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }
}
