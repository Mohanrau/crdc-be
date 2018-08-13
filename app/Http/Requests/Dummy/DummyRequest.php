<?php
namespace App\Http\Requests\Dummy;

use App\{
    Models\Products\Product,
    Models\Dummy\Dummy,
    Rules\Product\ProductAvailableInCountry,
    Rules\Dummy\CheckDuplicateDummyProduct
};
use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class DummyRequest extends FormRequest
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
     * @param Dummy $dummy
     * @return array
     */
    public function rules(Product $product, Dummy $dummy)
    {
        $id = ($this->input('dummy_id') != null) ? $this->input('dummy_id') : '';

        return [
            'country_id' => 'required|integer|exists:countries,id',
            'dummy_id' => 'nullable|integer|exists:dummies,id',
            'dmy_code' => ['required','min:3','max:255', Rule::unique('dummies', 'dmy_code')->ignore($id)],
            'dmy_name' => 'nullable|min:3|max:191',
            'is_lingerie' => 'required|boolean',
            'active' => 'nullable|boolean',
            'dummy_products.product_ids.*' =>  [
                'required', 'integer', 'exists:products,id',
                new ProductAvailableInCountry(
                    $product,
                    $this->input('country_id')
                ),
                new CheckDuplicateDummyProduct(
                    $product,
                    $dummy,
                    $this->input('country_id'),
                    ($this->has('dummy_id') ?
                        ((($this->input('dummy_id') == null) ? 0 : $this->input('dummy_id'))):
                        0)
                )
            ],
        ];
    }
}
