<?php
namespace App\Models\Bonus;

use Illuminate\Database\Eloquent\Model;
use App\Models\
{
    Users\User
};

class BonusWelcomeBonusDetails extends Model
{
    protected $table = 'bonus_welcome_bonus_details';

    protected $fillable = [
        'bonuses_summary_id',
        'sponsor_child_user_id',
        'sponsor_child_depth_level',
        'join_date',
        'total_local_amount',
        'total_local_amount_currency',
        'total_amount',
        'total_amount_currency',
        'total_usd_amount',
        'nett_usd_amount'
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
