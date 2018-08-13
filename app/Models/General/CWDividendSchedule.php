<?php
namespace App\Models\General;

use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class CWDividendSchedule extends Model
{
    use HasAudit;

    protected $table = 'cw_dividend_schedules';

    protected $fillable = [
        'cw_name',
        'from_cw_id',
        'to_cw_id'
    ];

    /**
     * get the cwSchedules for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwDividendStart()
    {
        return $this->belongsTo(CWSchedule::class,'from_cw_id');
    }

    /**
     * get the cwSchedules for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwDividendEnd()
    {
        return $this->belongsTo(CWSchedule::class,'to_cw_id');
    }
}
