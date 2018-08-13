<?php
namespace App\Http\Requests\Promotions;

use Illuminate\{
    Foundation\Http\FormRequest,
    Http\Request
};

class PromotionRequest extends FormRequest
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
     * @param Request $request
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'country_id' => 'required|integer|exists:countries,id',
            'promo_id' => 'nullable|integer|exists:promotion_free_items,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:'.$request->input('start_date'),
            'active' => 'nullable|boolean',

            //Todo build validation for this part for request -JALALA
            //validation the product categories ids--------------------------------------------------------------------
            //'product_category_ids.ids' => 'array|integer|exists:product_categories,id',

            //validation for kitting id's-------------------------------------------------------------------------------
            //'kit_ids.ids' => 'array|integer|exists:kitting,id',

            'conditions.options' => 'array|min:1',
        ];
    }
}
