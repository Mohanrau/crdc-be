<?php
namespace App\Rules\Campaign;

use App\Models\Campaigns\Campaign;
use App\Models\Campaigns\CampaignPayoutPoint;
use App\Models\Campaigns\EsacPromotion;
use Illuminate\Contracts\Validation\Rule;

class CampaignEditDeleteCheck implements Rule
{
    private $isEdit,
        $campaignObj, 
        $campaignPayoutPointObj,
        $esacPromotionObj, 
        $campaignName;

    /**
     * CampaignEditDeleteCheck constructor
     * 
     * @param bool $isEdit
     * @param Campaign $campaign
     * @param CampaignPayoutPoint $campaignPayoutPoint
     * @param EsacPromotion $esacPromotion
     */
    public function __construct(
        bool $isEdit,
        Campaign $campaign,
        CampaignPayoutPoint $campaignPayoutPoint,
        EsacPromotion $esacPromotion) 
    {
        $this->isEdit = $isEdit;
        
        $this->campaignObj = $campaign;

        $this->campaignPayoutPointObj = $campaignPayoutPoint;

        $this->esacPromotionObj = $esacPromotion;
    }

    /**
     * Determine if the validation rule passes
     * 
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->isEdit && !isset($value)) {
            return true;
        }
        else {
            $campaign = $this->campaignObj->find($value);

            if ($campaign !== null) {
                $this->campaignName = $campaign->name;
            }

            if ($this->isEdit) {
                $esacPromotionCount = 0;
            }
            else {
                $esacPromotionCount = $this->esacPromotionObj
                    ->where('campaign_id', '=', $value)
                    ->count();
            }

            $campaignPayoutPointCount = $this->campaignPayoutPointObj
                ->where('campaign_id', '=', $value)
                ->count();
            
            return $esacPromotionCount == 0 && $campaignPayoutPointCount == 0;
        }
    }

    /**
     * Get the validation error message.
     * 
     * @return string
     */
    public function message()
    {
        $translateKey = ($this->isEdit) ? 
            'message.campaign.cannot-edit-used-master': 
            'message.campaign.cannot-delete-used-master';
        
        $translateParam = [
            'master' => 'Campaign', 
            'name' => $this->campaignName
        ];

        return __($translateKey, $translateParam);
    }
} 
