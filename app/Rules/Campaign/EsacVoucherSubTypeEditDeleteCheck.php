<?php
namespace App\Rules\Campaign;

use App\Models\Campaigns\CampaignRule;
use App\Models\Campaigns\EsacVoucherSubType;
use App\Models\Campaigns\EsacPromotionVoucherSubType;
use Illuminate\Contracts\Validation\Rule;

class EsacVoucherSubTypeEditDeleteCheck implements Rule
{
    private $isEdit,
        $campaignRuleObj, 
        $esacVoucherSubTypeObj, 
        $esacPromotionVoucherSubTypeObj, 
        $esacVoucherSubTypeName;

    /**
     * EsacVoucherSubTypeEditDeleteCheck constructor
     * 
     * @param bool $isEdit
     * @param CampaignRule $campaignRule
     * @param EsacVoucherSubType $esacVoucherSubType
     * @param EsacPromotionVoucherSubType $esacPromotionVoucherSubTypeObj
     */
    public function __construct(
        bool $isEdit,
        CampaignRule $campaignRule,
        EsacVoucherSubType $esacVoucherSubType,
        EsacPromotionVoucherSubType $esacPromotionVoucherSubType) 
    {
        $this->isEdit = $isEdit;

        $this->campaignRuleObj = $campaignRule;

        $this->esacVoucherSubTypeObj = $esacVoucherSubType;

        $this->esacPromotionVoucherSubTypeObj = $esacPromotionVoucherSubType;
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
            $esacVoucherSubType = $this->esacVoucherSubTypeObj->find($value);

            if ($esacVoucherSubType !== null) {
                $this->esacVoucherSubTypeName = $esacVoucherSubType->name;
            }

            $campaignRuleCount = $this->campaignRuleObj
                ->where('voucher_sub_type_id', '=', $value)
                ->count();

            $esacPromotionVoucherSubTypeCount = $this->esacPromotionVoucherSubTypeObj
                ->where('voucher_sub_type_id', '=', $value)
                ->count();

            return $campaignRuleCount == 0 && $esacPromotionVoucherSubTypeCount == 0;
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
            'master' => 'eSac Voucher Sub Type', 
            'name' => $this->esacVoucherSubTypeName
        ];

        return __($translateKey, $translateParam);
    }
} 
