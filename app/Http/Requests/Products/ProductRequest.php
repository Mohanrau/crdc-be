<?php
namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'country_id' => 'required|integer|exists:countries,id',
            'product_id' => 'required|integer|exists:products,id',
            'entity_id' => 'required|integer|exists:entities,id',

            'base_price.id' => 'required|integer|exists:product_prices,id',
            'base_price.gmp_price_tax' => 'required',
            'base_price.rp_price_tax' => 'required',

            'base_price.effective_date' => 'required|date',
            'base_price.expiry_date' => 'required|date',

            'base_price.base_cv' => 'required|integer',
            'base_price.wp_cv' => 'required|integer',

            'virtual_product' => 'sometimes|array',
            'virtual_product.virtual_product_id' => 'required_with:virtual_product|integer|exists:products,id',
            'virtual_product.country_id' => 'required_with:virtual_product|integer|exists:countries,id',
            'virtual_product.master_data_id' => 'required_with:virtual_product|integer|exists:master_data,id',

            'description.*.language_id' => 'sometimes|distinct|required|integer|exists:languages,id',
            'description.*.marketing_description' => 'sometimes|required|min:3',
            'description.*.benefits' => 'sometimes|required|min:3',

            'images.list.*.image_path' => 'sometimes|distinct|required',
            'images.list.*.default' => 'sometimes|required|boolean',

            'rental_plan' => 'array',
            'rental_plan.*.initial_payment' => 'required_with:rental_plan|regex:/^\d*(\.\d{1,2})?$/',
            'rental_plan.*.monthly_repayment' => 'required_with:rental_plan|regex:/^\d*(\.\d{1,2})?$/',
            'rental_plan.*.tenure' => 'required_with:rental_plan|integer',
            'rental_plan.*.product_rental_cv_allocation' => 'required_with:rental_plan|array',
            'rental_plan.*.product_rental_cv_allocation.*.cw_number' => 'required_with:rental_plan|integer',
            'rental_plan.*.product_rental_cv_allocation.*.allocate_cv' => 'required_with:rental_plan|integer'
        ];
    }
}
