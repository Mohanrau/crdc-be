<?php
namespace App\Models\Bonus;

use App\Models\{
    Sales\Sale,
    General\CWSchedule,
    Members\Member,
    Users\User
};
use App\Helpers\Traits\HasAudit;
use Illuminate\Database\Eloquent\Model;

class AmpCvAllocation extends Model
{
    use HasAudit;

    protected $table = 'amp_cv_allocations';

    protected $fillable = [
        'type_id',
        'sale_id',
        'user_id',
        'cw_id',
        'cv',
        'active'
    ];

    /**
     * get sale details for a given ampCvAllocationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    /**
     * get user details for a given ampCvAllocationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * get member details for a given ampCvAllocationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id','user_id')
            ->with('user');
    }

    /**
     * get the cwSchedules for a given ampCvAllocationObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class,'cw_id');
    }
}
