<?php
namespace App\Models\Bonus;

use App\Models\General\CWSchedule;
use Illuminate\Database\Eloquent\Model;

class TeamBonusAdjustment extends Model
{
    protected $table = 'team_bonus_adjustment';

    protected $fillable = [
        'cw_id',
        'user_id',
        'pass_up_by_user_id',
        'adjustment_gcv',
        'created_by',
        'updated_by'
    ];

    /**
     * get user data for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Member::class,'user_id');
    }

    /**
     * get commission week data for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }
}
