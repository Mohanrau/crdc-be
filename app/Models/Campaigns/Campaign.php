<?php
namespace App\Models\Campaigns;

use App\Models\{
    Locations\Country,
    General\CWSchedule,
    Campaigns\CampaignRule
};
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'campaigns';

    protected $fillable = [
        'country_id',
        'name',
        'report_group',
        'from_cw_schedule_id',
        'to_cw_schedule_id',
        'custom_script',
        'active',
        'updated_by'
    ];

    /**
     * get country for a given CampaignObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    /**
     * get cw schedule from for a given CampaignObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fromCwSchedule()
    {
        return $this->belongsTo(CWSchedule::class, 'from_cw_schedule_id', 'id');
    }

    /**
     * get cw schedule to for a given CampaignObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function toCwSchedule()
    {
        return $this->belongsTo(CWSchedule::class, 'to_cw_schedule_id', 'id');
    }

    /**
     * get rule for a given CampaignObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function campaignRules()
    {
        return $this
            ->hasMany(CampaignRule::class, 'campaign_id', 'id');
    }
}