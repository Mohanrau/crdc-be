<?php
namespace App\Http\Requests\Campaigns;

use Illuminate\{
    Foundation\Http\FormRequest,
    Support\Facades\Auth
};
use App\{
    Rules\General\MasterDataIdExists,
    Rules\Campaign\CampaignRuleTo,
    Rules\Campaign\CampaignEditDeleteCheck,
    Rules\Product\ProductAvailableInCountry,
    Interfaces\Masters\MasterInterface,
    Models\Products\Product,
    Models\Campaigns\Campaign,
    Models\Campaigns\CampaignPayoutPoint,
    Models\Campaigns\EsacPromotion
};

class CampaignRequest extends FormRequest
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
     * @param MasterInterface $masterRepository
     * @param Product $product
     * @param Campaign $campaign
     * @param CampaignPayoutPoint $campaignPayoutPoint
     * @param EsacPromotion $esacPromotion
     * @return array
     */
    public function rules(
        MasterInterface $masterRepository,
        Product $product,
        Campaign $campaign,
        CampaignPayoutPoint $campaignPayoutPoint,
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
                'bail', 'sometimes', 'nullable', 'integer', 'exists:campaigns,id',
                new CampaignEditDeleteCheck(
                    true,
                    $campaign,
                    $campaignPayoutPoint,
                    $esacPromotion
                )
            ],
            'country_id' => 'required|integer|exists:countries,id',
            'name' => 'required|string|min:3|max:50|unique:campaigns,name,' . $ignoredId . ',id,country_id,' . $countryId,
            'report_group' => 'required|string|min:3|max:50',
            'from_cw_schedule_id' => 'required|integer|exists:cw_schedules,id',
            'to_cw_schedule_id' => 'required|integer|exists:cw_schedules,id',
            'campaign_rules' => 'required|array|min:1',
            'campaign_rules.*' => [ new CampaignRuleTo() ],
            'campaign_rules.*.id' => 'required|integer', // we need this for parent-child mapping, cannot check exists
            'campaign_rules.*.parent_id' => 'sometimes|integer|nullable', // we need this for parent-child mapping, cannot check exists
            'campaign_rules.*.name' => 'required|string|min:3|max:50',
            'campaign_rules.*.report_title' => 'required|string|min:2|max:50',
            'campaign_rules.*.qualify_member_status' => [
                'required', 'integer',
                new MasterDataIdExists($masterRepository, 'member_status'),
            ],
            'campaign_rules.*.qualify_team_bonus_ranks' => 'required|array|min:1',
            'campaign_rules.*.qualify_team_bonus_ranks.*' => 'required|integer|exists:team_bonus_ranks,id',
            'campaign_rules.*.qualify_enrollment_ranks' => 'required|array|min:1',
            'campaign_rules.*.qualify_enrollment_ranks.*' => 'required|integer|exists:enrollment_ranks,id',
            'campaign_rules.*.sale_item_quantity' => 'required|integer|min:0',
            'campaign_rules.*.team_bonus_rank_quantity' => 'required|integer|min:0',
            'campaign_rules.*.enrollment_rank_quantity' => 'required|integer|min:0',
            'campaign_rules.*.from_sale_item_level' => 'required|integer|min:0',
            'campaign_rules.*.to_sale_item_level' => 'required|integer|min:0',
            'campaign_rules.*.from_team_bonus_rank_level' => 'required|integer|min:0',
            'campaign_rules.*.to_team_bonus_rank_level' => 'required|integer|min:0',
            'campaign_rules.*.from_enrollment_rank_level' => 'required|integer|min:0',
            'campaign_rules.*.to_enrollment_rank_level' => 'required|integer|min:0',
            'campaign_rules.*.from_cv' => 'required|integer|min:0',
            'campaign_rules.*.to_cv' => 'required|integer|min:0',
            'campaign_rules.*.point' => 'required|boolean',
            'campaign_rules.*.point_value' => 'required_if:point,true|nullable|numeric|min:0',
            'campaign_rules.*.point_value_multiplier' => [
                'required_if:point,true', 'nullable', 'integer',
                new MasterDataIdExists($masterRepository, 'campaign_reward_value_multiplier')
            ],
            'campaign_rules.*.min_point_value' => 'required_if:point,true|nullable|numeric|min:0',
            'campaign_rules.*.max_point_value' => 'required_if:point,true|nullable|numeric|min:0',
            'campaign_rules.*.voucher' => 'required|boolean',
            'campaign_rules.*.voucher_type_id' => 'required_if:voucher,true|nullable|integer|exists:esac_voucher_types,id',
            'campaign_rules.*.voucher_sub_type_id' => 'required_if:voucher,true|nullable|integer|exists:esac_voucher_sub_types,id',
            'campaign_rules.*.voucher_value' => 'required_if:voucher,true|nullable|numeric|min:0',
            'campaign_rules.*.voucher_value_multiplier' => [
                'required_if:voucher,true', 'nullable', 'integer',
                new MasterDataIdExists($masterRepository, 'campaign_reward_value_multiplier')
            ],
            'campaign_rules.*.min_voucher_value' => 'required_if:voucher,true|nullable|numeric|min:0',
            'campaign_rules.*.max_voucher_value' => 'required_if:voucher,true|nullable|numeric|min:0',
            'campaign_rules.*.ewallet_money' => 'required|boolean',
            'campaign_rules.*.ewallet_money_value' => 'required_if:ewallet_money,true|nullable|numeric|min:0',
            'campaign_rules.*.ewallet_money_value_multiplier' => [
                'required_if:poewallet_moneyint,true', 'nullable', 'integer',
                new MasterDataIdExists($masterRepository, 'campaign_reward_value_multiplier'),
            ],
            'campaign_rules.*.min_ewallet_money_value' => 'required_if:ewallet_money,true|nullable|numeric|min:0',
            'campaign_rules.*.max_ewallet_money_value' => 'required_if:ewallet_money,true|nullable|numeric|min:0',
            'campaign_rules.*.locations' => 'present|array',
            'campaign_rules.*.locations.*' => 'sometimes|nullable|integer|exists:locations,id',
            'campaign_rules.*.product_categories' => 'present|array',
            'campaign_rules.*.product_categories.*' => 'sometimes|nullable|integer|exists:product_categories,id',
            'campaign_rules.*.products' => 'present|array',
            'campaign_rules.*.products.*' => [
                'sometimes', 'nullable', 'integer', 'exists:products,id',
                new ProductAvailableInCountry($product, $this->input('country_id'))
            ],
            'campaign_rules.*.kittings' => 'present|array',
            'campaign_rules.*.kittings.*' => 'sometimes|nullable|integer|exists:kitting,id',
            'campaign_rules.*.sale_types' => 'present|array',
            'campaign_rules.*.sale_types.*' => [
                'sometimes', 'nullable', 'integer',
                new MasterDataIdExists($masterRepository, 'sale_types'),
            ],
            'campaign_rules.*.team_bonus_ranks' => 'present|array',
            'campaign_rules.*.team_bonus_ranks.*' => 'sometimes|nullable|integer|exists:team_bonus_ranks,id',
            'campaign_rules.*.enrollment_ranks' => 'present|array',
            'campaign_rules.*.enrollment_ranks.*' => 'sometimes|nullable|integer|exists:enrollment_ranks,id'
        ];
    }
}