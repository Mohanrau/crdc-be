<?php
namespace App\Models\Bonus;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\User;

class BonusMentorBonusDetails extends Model
{
    protected $table = 'bonus_mentor_bonus_details';

    protected $fillable = [
        'bonuses_summary_id',
        'sponsor_child_user_id',
        'sponsor_generation_level',
        'team_bonus',
        'mentor_bonus_percentage',
        'mentor_bonus'
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
    public function sponsorChild()
    {
        return $this->belongsTo(User::class, 'sponsor_child_user_id', 'id');
    }
}
