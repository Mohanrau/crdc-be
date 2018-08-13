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

class MemberEnrollmentRankUpgradeLog extends Model
{
    use HasAudit;

    protected $table = 'members_enrollments_ranks_upgrades_logs';

    protected $fillable = [
        'user_id',
        'cw_id',
        'previous_cw_enrollment_rank_id',
        'from_enrollment_rank_id',
        'to_enrollment_rank_id',
    ];

    /**
     * get member details for a given memberEnrollmentRankUpgradeLogObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id','user_id')
            ->with('user');
    }

    /**
     * get cw details for a given memberEnrollmentRankUpgradeLogObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwSchedule()
    {
        return $this->belongsTo(CWSchedule::class, 'cw_id');
    }

    /**
     * get previous cw enrollment details for a given memberEnrollmentRankUpgradeLogObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function previousCwEnrollmentRank()
    {
        return $this->belongsTo(EnrollmentRank::class, 'previous_cw_enrollment_rank_id');
    }

    /**
     * get from enrollment details for a given memberEnrollmentRankUpgradeLogObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fromEnrollmentRank()
    {
        return $this->belongsTo(EnrollmentRank::class, 'from_enrollment_rank_id');
    }

    /**
     * get to enrollment details for a given memberEnrollmentRankUpgradeLogObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function toEnrollmentRank()
    {
        return $this->belongsTo(EnrollmentRank::class, 'to_enrollment_rank_id');
    }

}
