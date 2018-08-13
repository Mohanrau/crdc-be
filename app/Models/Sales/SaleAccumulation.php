<?php
namespace App\Models\Sales;

use App\Models\{
    General\CWSchedule,
    Members\Member,
    Users\User
};
use Illuminate\Database\Eloquent\Model;

class SaleAccumulation extends Model
{
    protected $table = 'sales_accumulations';

    protected $fillable = [
        'user_id',
        'cw_id',
        'base_cv',
        'amp_cv',
        'enrollment_cv'
    ];

    /**
     * get member details for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class,'user_id','user_id')
            ->with('user');
    }

    /**
     * get user for given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * get commission week for given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cw()
    {
        return $this->belongsTo(CWSchedule::class, 'cw_id');
    }
}
