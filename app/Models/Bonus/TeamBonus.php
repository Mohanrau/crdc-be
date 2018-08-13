<?php
namespace App\Models\Bonus;

use App\Models\{
    Members\Member,
    General\CWSchedule
};
use Illuminate\Database\Eloquent\Model;

class TeamBonus extends Model
{
    protected $table = 'team_bonuses';

    protected $fillable = [
        'user_id',
        'effective_rank_id',
        'highest_rank_id',
        'cw_id',
        'gcv',
        'gcv_bf',
        'gcv_bf_by',
        'gcv_bf_position',
        'ops',
        'gcv_flush',
        'gcv_bring_over',
        'is_active_ba',
        'payout'
    ];

    /**
     * return member details for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Member::class,'user_id','user_id');
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

    /**
     * return effective rank details for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function effectiveRank()
    {
        return $this->belongsTo(TeamBonusRank::class,'effective_rank_id');
    }

    /**
     * return highest rank details for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function highestRank()
    {
        return $this->belongsTo(TeamBonusRank::class,'highest_rank_id');
    }

    /**
     * return gcv_bf_by member details for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gcv_bf_by()
    {
        return $this->belongsTo(Member::class, 'gcv_bf_by');
    }
}
