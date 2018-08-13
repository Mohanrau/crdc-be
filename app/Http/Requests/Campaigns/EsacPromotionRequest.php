<?php
namespace App\Http\Requests\Campaigns;

use App\Models\Campaigns\EsacPromotion;
use App\Models\Campaigns\EsacVoucher;
use App\Rules\Campaign\EsacPromotionEditDeleteCheck;
use Illuminate\Foundation\Http\FormRequest;

class EsacPromotionRequest extends FormRequest
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
     * @param EsacPromotion $esacPromotion
     * @param EsacVoucher $esacVoucher
     * @return array
     */
    public function rules(
        EsacPromotion $esacPromotion,
        EsacVoucher $esacVoucher
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
            //TODO: temporarily disable checking for parallel run, replace when go live
            // 'id' => [
            //     'bail', 'sometimes', 'nullable', 'integer', 'exists:esac_promotions,id',
            //     new EsacPromotionEditDeleteCheck(
            //         true,
            //         $esacPromotion, 
            //         $esacVoucher
            //     )
            // ],
            'id' => 'bail|sometimes|nullable|integer|exists:esac_promotions,id',
            'country_id' => 'required|integer|exists:countries,id',
            'campaign_id' => 'required|integer|exists:campaigns,id',
            'taxable' => 'required|boolean',
            'voucher_type_id' => 'required|integer|exists:esac_voucher_types,id|unique:esac_promotions,voucher_type_id,' . $ignoredId . ',id,country_id,' . $countryId,
            'entitled_by' => 'required|string|in:C,P',
            'max_purchase_qty' => 'required_if:entitled_by,P|integer|min:0',
            'esac_promotion_product_categories.*.product_category_id' => 'sometimes|nullable|integer|exists:product_categories,id',
            'esac_promotion_exception_products.*.product_id' => 'sometimes|nullable|integer|exists:products,id',
            'esac_promotion_exception_products.*.kitting_id' => 'sometimes|nullable|integer|exists:kitting,id',
            'esac_promotion_products.*.product_id' => 'sometimes|nullable|integer|exists:products,id',
            'esac_promotion_products.*.kitting_id' => 'sometimes|nullable|integer|exists:kitting,id',
            'esac_promotion_voucher_sub_types.*.sub_type_id' => 'sometimes|nullable|integer|exists:esac_voucher_sub_types,id'
        ];
    }
}