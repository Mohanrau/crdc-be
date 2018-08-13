<?php
namespace App\Rules\Campaign;

use App\Models\Campaigns\EsacPromotion;
use App\Models\Campaigns\EsacVoucher;
use Illuminate\Contracts\Validation\Rule;

class EsacPromotionEditDeleteCheck implements Rule
{
    private $isEdit,
        $esacPromotionObj,
        $esacVoucherObj, 
        $esacPromotionId;

    /**
     * EsacVoucherEditDeleteCheck constructor
     * 
     * @param bool $isEdit
     * @param EsacPromotion $esacPromotion
     * @param EsacVoucher $esacVoucher
     */
    public function __construct(
        bool $isEdit,
        EsacPromotion $esacPromotion,
        EsacVoucher $esacVoucher) 
    {
        $this->isEdit = $isEdit;

        $this->esacPromotionObj = $esacPromotion;

        $this->esacVoucherObj = $esacVoucher;
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
            $esacPromotion = $this->esacPromotionObj->find($value);

            if ($esacPromotion !== null) {
                $this->esacPromotionId = $esacPromotion->id;
            }

            $esacVoucherCount = $this->esacVoucherObj
                ->where('promotion_id', '=', $value)
                ->count();

            return $esacVoucherCount == 0;
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
            'master' => 'eSac Promotion', 
            'name' => $this->esacPromotionId
        ];

        return __($translateKey, $translateParam);
    }
} 
