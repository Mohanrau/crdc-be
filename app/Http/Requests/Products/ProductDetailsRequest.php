<?php
namespace App\Http\Requests\Products;

use App\Models\Products\Product;
use App\Rules\Product\ProductAvailableInCountry;
use Illuminate\Foundation\Http\FormRequest;

class ProductDetailsRequest extends FormRequest
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
     * @param Product $product
     * @return array
     */
    public function rules(Product $product)
    {
        return [
            'country_id' => 'required|integer|exists:countries,id',
            'product_id' => [
                'required', 'integer', 'exists:products,id',
                new ProductAvailableInCountry($product, $this->input('country_id'))
            ]
        ];
    }
}
