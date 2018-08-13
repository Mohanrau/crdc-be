<?php
namespace App\Models\Campaigns;

use App\Models\Campaigns\Campaign;
use App\Models\General\CWSchedule;
use App\Models\Users\User;
use App\Models\Campaigns\CampaignRule;
use Illuminate\Database\Eloquent\Model;

class CampaignPayoutPoint extends Model
{
    protected $table = 'campaign_payout_points';

    protected $fillable = [
        'campaign_id',
        'user_id',
        'cw_id',
        'campaign_rule_id',
        'payout_points',
        'active'
    ];

    /**
     * get campaign for a given CampaignPayoutPointObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    /**
     * get user for a given CampaignPayoutPointObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * get cw schedule for a given CampaignPayoutPointObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cwSchedule()
    {
        return $this->belongsTo(CWSchedule::class, 'cw_id', 'id');
    }

    /**
     * get campaign rule for a given CampaignPayoutPointObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaignRule()
    {
        return $this->belongsTo(CampaignRule::class, 'campaign_rule_id', 'id');
    }
}
