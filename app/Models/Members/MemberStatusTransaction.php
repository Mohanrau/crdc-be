<?php
namespace App\Models\Members;

use Illuminate\Database\Eloquent\Model;
use App\{
    Helpers\Traits\HasAudit,
    Models\General\CWSchedule,
    Models\Members\Member,
    Models\Masters\MasterData
};

class MemberStatusTransaction extends Model
{
    use HasAudit;

    protected $table = 'members_status_transactions';

    protected $fillable = [
        "user_id",
        "status_id",
        "previous_status_id",
        "effective_date",
        "bonus_payout_deferment",
        "cw_id",
        "reason_id",
        "case_reference_number"
    ];

    /**
     * get member details for a given memberStatusTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id','user_id')
            ->with('user');
    }

    /**
     * get cw details for a given memberStatusTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwSchedule()
    {
        return $this->belongsTo(CWSchedule::class, 'cw_id');
    }

    /**
     * get status for a given memberStatusTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status()
    {
        return $this->belongsTo(MasterData::class,'status_id');
    }

    /**
     * get previous status for a given memberStatusTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function previousStatus()
    {
        return $this->belongsTo(MasterData::class,'previous_status_id');
    }

    /**
     * get reason for a given memberStatusTransactionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reason()
    {
        return $this->belongsTo(MasterData::class,'reason_id');
    }
}
