<?php
namespace App\Rules\Campaign;

use App\Models\Campaigns\CampaignRule;
use App\Models\Campaigns\EsacVoucherType;
use App\Models\Campaigns\EsacVoucherSubType;
use App\Models\Campaigns\EsacPromotion;
use Illuminate\Contracts\Validation\Rule;

class EsacVoucherTypeEditDeleteCheck implements Rule
{
    private $isEdit,
        $campaignRuleObj, 
        $esacVoucherTypeObj, 
        $esacVoucherSubTypeObj, 
        $esacPromotionObj, 
        $esacVoucherTypeName;

    /**
     * EsacVoucherTypeEditDeleteCheck constructor
     * 
     * @param bool $isEdit
     * @param CampaignRule $campaignRule
     * @param EsacVoucherType $esacVoucherType
     * @param EsacVoucherSubType $esacVoucherSubType
     * @param EsacPromotion $esacPromotion
     */
    public function __construct(
        bool $isEdit,
        CampaignRule $campaignRule,
        EsacVoucherType $esacVoucherType,
        EsacVoucherSubType $esacVoucherSubType,
        EsacPromotion $esacPromotion) 
    {
        $this->isEdit = $isEdit;
        
        $this->campaignRuleObj = $campaignRule;

        $this->esacVoucherTypeObj = $esacVoucherType;

        $this->esacVoucherSubTypeObj = $esacVoucherSubType;

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
            $esacVoucherType = $this->esacVoucherTypeObj->find($value);

            if ($esacVoucherType !== null) {
                $this->esacVoucherTypeName = $esacVoucherType->name;
            }

            $campaignRuleCount = $this->campaignRuleObj
            ->where('voucher_type_id', '=', $value)
            ->count();

            $esacVoucherSubTypeCount = $this->esacVoucherSubTypeObj
            ->where('voucher_type_id', '=', $value)
            ->count();

            $esacPromotionCount = $this->esacPromotionObj
                ->where('voucher_type_id', '=', $value)
                ->count();

            return $campaignRuleCount == 0 && $esacVoucherSubTypeCount == 0 && $esacPromotionCount == 0;
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
            'master' => 'eSac Voucher Type', 
            'name' => $this->esacVoucherTypeName
        ];

        return __($translateKey, $translateParam);
    }
} 
