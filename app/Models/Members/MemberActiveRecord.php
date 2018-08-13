<?php
namespace App\Models\Members;

use Illuminate\Database\Eloquent\Model;
use App\{
    Helpers\Traits\HasAudit,
    Models\General\CWSchedule,
    Models\Members\Member
};

class MemberActiveRecord extends Model
{
    use HasAudit;

    protected $table = 'members_active_records';

    protected $fillable = [
        'user_id',
        'cw_id',
        'is_active'
    ];

    /**
     * get member details for a given memberActiveRecordObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id','user_id')
            ->with('user');
    }

    /**
     * get cw details for a given memberActiveRecordObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwSchedule()
    {
        return $this->belongsTo(CWSchedule::class, 'cw_id');
    }
}
