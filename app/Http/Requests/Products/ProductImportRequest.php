<?php
namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class ProductImportRequest extends FormRequest
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
            //validating productCategories------------------------------------------------------------------------------
            'product_category.*.id' => 'sometimes|required',
            'product_category.*.name' => 'sometimes|required|min:2|max:255',

            'products.*.name' => 'sometimes|distinct|required|min:2|max:255',
            'products.*.sku' => 'sometimes|distinct|required|min:2|max:100',
            'products.*.is_dummy_code' => 'sometimes|required|boolean',
            'products.*.yy_active' => 'sometimes|required|boolean',
            //'products.*.entities.entity_code' => 'sometimes|exists:entities,name',

            'pricelist.*.productid' => 'sometimes|required|min:2|max:100',
            'pricelist.*.currency_code' => 'sometimes|required|min:2|max:5',
            'pricelist.*.effective_date' => 'sometimes|required|date',
            'pricelist.*.expiry_date' => 'sometimes|required|date|after:effective_date',
        ];
    }
}
