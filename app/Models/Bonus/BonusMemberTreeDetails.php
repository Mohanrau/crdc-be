<?php
namespace App\Models\Bonus;

use App\Helpers\Traits\Bonus;
use App\Helpers\Traits\Member;
use Illuminate\Database\Eloquent\Model;
use App\Models\
{
    General\CWSchedule,
    Users\User
};

class BonusMemberTreeDetails extends Model
{
    use Member, Bonus;

    protected $table = 'bonus_member_tree_details';

    protected $fillable = [
        'user_id',
        'cw_id',
        'sponsor_parent_user_id',
        'placement_parent_user_id',
        'sponsor_depth_level',
        'placement_depth_level',
        'placement_position',
        'personal_sales_cv',
        'member_sales_cv',
        'is_active_brand_ambassador',
        'is_tri_formation',
        'is_new_ba',
        'total_ba_left',
        'total_ba_right',
        'total_unique_line_left',
        'total_unique_line_right',
        'total_active_ba_left',
        'total_active_ba_right',
        'total_new_ba_left',
        'total_new_ba',
        'total_new_ba_right',
        'total_downline',
        'total_direct_downline',
        'total_direct_downline_active_ba',
        'total_sponsor_unique_line_1BA',
        'total_sponsor_unique_line_1SD',
        'total_sponsor_unique_line_2SD',
        'total_sponsor_unique_line_1RD',
        'total_sponsor_unique_line_2RD',
        'total_sponsor_unique_line_1ED',
        'total_sponsor_unique_line_2ED',
        'left_gcv',
        'right_gcv'
    ];

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
     * return user for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * get user for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sponsorParent()
    {
        return $this->belongsTo(User::class, 'sponsor_parent_user_id', 'id');
    }

    /**
     * get user for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function placementParent()
    {
        return $this->belongsTo(User::class, 'placement_parent_user_id', 'id');
    }
}
