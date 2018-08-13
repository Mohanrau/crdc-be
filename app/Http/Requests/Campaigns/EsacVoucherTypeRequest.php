<?php
namespace App\Http\Requests\Campaigns;

use App\Models\Campaigns\CampaignRule;
use App\Models\Campaigns\EsacVoucherType;
use App\Models\Campaigns\EsacVoucherSubType;
use App\Models\Campaigns\EsacPromotion;
use App\Rules\Campaign\EsacVoucherTypeEditDeleteCheck;
use Illuminate\Foundation\Http\FormRequest;

class EsacVoucherTypeRequest extends FormRequest
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
     * @param CampaignRule $campaignRule
     * @param EsacVoucherType $esacVoucherType
     * @param EsacVoucherSubType $esacVoucherSubType
     * @param EsacPromotion $esacPromotion
     * @return array
     */
    public function rules(
        CampaignRule $campaignRule,
        EsacVoucherType $esacVoucherType,
        EsacVoucherSubType $esacVoucherSubType,
        EsacPromotion $esacPromotion
    )
    {
        if ($this->has('id')) {
            $ignoredId = $this->input('id');
        }
        else {
            $ignoredId = 'NULL';
        }

        if ($this->has('country_id')) {
            $countryId = $this->input('country_id');
        }
        else {
            $countryId = 'NULL';
        }

        return [
            'id' => [
                'bail', 'sometimes', 'integer', 'nullable', 'exists:esac_voucher_types,id',
                new EsacVoucherTypeEditDeleteCheck(
                    true,
                    $campaignRule,
                    $esacVoucherType,
                    $esacVoucherSubType,
                    $esacPromotion
                )
            ],
            'country_id' => 'required|integer|exists:countries,id',
            'name' => 'required|string|min:3|max:50|unique:esac_voucher_types,name,' . $ignoredId . ',id,country_id,' . $countryId,
            'description' => 'required|string|min:3|max:500'
        ];
    }
}