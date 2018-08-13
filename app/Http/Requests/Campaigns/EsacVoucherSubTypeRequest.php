<?php
namespace App\Http\Requests\Campaigns;

use App\Models\Campaigns\CampaignRule;
use App\Models\Campaigns\EsacVoucherSubType;
use App\Models\Campaigns\EsacPromotionVoucherSubType;
use App\Rules\Campaign\EsacVoucherSubTypeEditDeleteCheck;
use Illuminate\Foundation\Http\FormRequest;

class EsacVoucherSubTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param CampaignRule $campaignRule,
     * @param EsacVoucherSubType $esacVoucherSubType,
     * @param EsacPromotionVoucherSubType $esacPromotionVoucherSubType
     * @return array
     */
    public function rules(
        CampaignRule $campaignRule,
        EsacVoucherSubType $esacVoucherSubType,
        EsacPromotionVoucherSubType $esacPromotionVoucherSubType
    )
    {
        if ($this->has('id')) {
            $ignoredId = $this->input('id');
        }
        else {
            $ignoredId = 'NULL';
        }

        if ($this->has('voucher_type_id')) {
            $voucherTypeId = $this->input('voucher_type_id');
        }
        else {
            $voucherTypeId = 'NULL';
        }

        return [
            'id' => [
                'bail', 'sometimes', 'integer', 'nullable', 'exists:esac_voucher_sub_types,id',
                new EsacVoucherSubTypeEditDeleteCheck(
                    true,
                    $campaignRule,
                    $esacVoucherSubType,
                    $esacPromotionVoucherSubType
                )
            ],
            'voucher_type_id' => 'required|integer|exists:esac_voucher_types,id',
            'name' => 'required|string|min:3|max:50|unique:esac_voucher_sub_types,name,' . $ignoredId . ',id,voucher_type_id,' . $voucherTypeId,
            'description' => 'required|string|min:3|max:500'
        ];
    }
}