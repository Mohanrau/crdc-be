<?php
namespace App\Models\Bonus;

use Illuminate\Database\Eloquent\Model;
use App\Models\
{
    Locations\Country,
    Currency\Currency,
    Users\User,
    General\CWSchedule
};

class BonusSummary extends Model
{
    protected $table = 'bonuses_summary';

    protected $fillable = [
        'country_id',
        'cw_id',
        'user_id',
        'statement_date',
        'tax_company_name',
        'tax_no',
        'tax_type',
        'tax_rate',
        'highest_rank_id',
        'effective_rank_id',
        'enrollment_rank_id',
        'address_data',
        'welcome_bonus',
        'team_bonus',
        'team_bonus_diluted',
        'mentor_bonus',
        'mentor_bonus_diluted',
        'quarterly_dividend',
        'incentive',
        'total_gross_bonus',
        'default_currency_id',
        'currency_rate',
        'total_gross_bonus_local_amount',
        'total_net_bonus_payable',
        'total_tax_amount',
        'diluted_percentage'
    ];

    /**
     * get country details for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * get currency info for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'default_currency_id');
    }

    /**
     * get user for a given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

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
     * get member's enrollment rank for given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function enrollmentRank()
    {
        return $this->belongsTo(EnrollmentRank::class, 'enrollment_rank_id');
    }

    /**
     * get member's effective rank for given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function effectiveRank()
    {
        return $this->belongsTo(TeamBonusRank::class, 'effective_rank_id');
    }

    /**
     * get member's highest rank for given modelObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     *
     */
    public function highestRank()
    {
        return $this->belongsTo(TeamBonusRank::class, 'highest_rank_id');
    }
}
