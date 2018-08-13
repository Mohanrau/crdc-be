<?php
namespace App\Models\Members;

use App\Models\General\CWSchedule;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class MemberSnapshot extends Model
{
    protected $table = 'member_snapshots';

    protected $fillable = [
        'cw_id',
        'user_id',
        'data',
    ];

    /**
     * get related cwScheduleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwSchedule()
    {
        return $this->belongsTo(CWSchedule::class, 'cw_id');
    }

    /**
     * get related userObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
