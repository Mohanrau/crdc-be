<?php
namespace App\Models\Bonus;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\User;

class BonusTeamBonusDetails extends Model
{
    protected $table = 'bonus_team_bonus_details';

    protected $fillable = [
        'bonuses_summary_id',
        'placement_child_user_id',
        'gcv',
        'optimising_personal_sales',
        'gcv_calculation',
        'gcv_bring_forward',
        'gcv_bring_forward_position',
        'gcv_leg_group',
        'gcv_flush',
        'gcv_bring_over',
        'team_bonus_percentage',
        'team_bonus'
    ];

    protected $bonusLegGroup = [
        0 => 'null',
        1 => 'power',
        2 => 'pay'
    ];

    /**
     * get bonuses details for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bonuses()
    {
        return $this->belongsTo(BonusSummary::class, 'bonuses_summary_id');
    }

    /**
     * get user for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function placementChild()
    {
        return $this->belongsTo(User::class, 'placement_child_user_id', 'id');
    }

    /**
     * get the bonus leg group array or value if $leg is set
     *
     * @param int|null $leg
     * @return array|false|int|string
     */
    public function getBonusLegGroup(int $leg = null)
    {
        return ($leg == null)? $this->bonusLegGroup :  $this->bonusLegGroup[$leg];
    }
}
