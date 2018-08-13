<?php
namespace App\Models\Members;

use Illuminate\Database\Eloquent\Model;
use App\{
    Helpers\Traits\HasAudit,
    Models\Bonus\EnrollmentRank,
    Models\General\CWSchedule,
    Models\Bonus\TeamBonusRank,
    Models\Members\Member,
    Models\Locations\Country
};

class MemberRankTransaction extends Model
{
    use HasAudit;

    protected $table = 'member_rank_transactions';

    protected $fillable = [
        'user_id',
        'cw_id',
        'enrollment_rank_id',
        'previous_enrollment_rank_id',
        'highest_rank_id',
        'previous_highest_rank_id',
        'case_reference_number'
    ];

    /**
     * get cw details for a given memberRanksTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwSchedule()
    {
        return $this->belongsTo(CWSchedule::class, 'cw_id');
    }

    /**
     * get enrollment details for a given memberRanksTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function enrollmentRank()
    {
        return $this->belongsTo(EnrollmentRank::class, 'enrollment_rank_id');
    }

    /**
     * get previous enrollment details for a given memberRanksTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function previousEnrollmentRank()
    {
        return $this->belongsTo(EnrollmentRank::class, 'previous_enrollment_rank_id');
    }

    /**
     * get highest rank details for a given memberRanksTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function highestRank()
    {
        return $this->belongsTo(TeamBonusRank::class, 'highest_rank_id');
    }

    /**
     * get previous highest rank for a given memberRanksTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function previousHighestRank()
    {
        return $this->belongsTo(TeamBonusRank::class, 'previous_highest_rank_id');
    }

    /**
     * get member details for a given memberRanksTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id','user_id')
            ->with('user');
    }
}
