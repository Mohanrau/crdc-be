<?php
namespace App\Http\Requests\Kitting;

use App\{
    Interfaces\Users\UserInterface,
    Models\Products\Product,
    Rules\Product\ProductAvailableInCountry,
    Rules\Users\UserLocationAccess
};
use Illuminate\{
    Foundation\Http\FormRequest,
    Support\Facades\Auth,
    Validation\Rule
};

class KittingRequest extends FormRequest
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
     * @param UserInterface $userInterface
     * @return array
     */
    public function rules(
        Product $product,
        UserInterface $userInterface
    )
    {
        $id = ($this->input('kitting_id') != null) ? $this->input('kitting_id') : '';

        return [
            'country_id' => 'required|integer|exists:countries,id',
            'kitting_id' => 'sometimes|nullable|integer|exists:kitting,id',
            'code' => ['required','min:3','max:255'],
            'is_esac' => 'sometimes|boolean',

            'kitting_price.kitting_id' => 'nullable|same:kitting_id',
            'kitting_price.gmp_price_tax' => 'required|numeric',
            'kitting_price.effective_date' => 'required|date',
            'kitting_price.expiry_date' => 'required|date',
            'kitting_price.base_cv' => 'required|integer',
            'kitting_price.wp_cv' => 'required|integer',

            'kitting_products' => 'array|min:1',
            'kitting_products.*.kitting_id' => 'nullable|same:kitting_id',
            'kitting_products.*.product_id' => [
                'required', 'integer', 'exists:products,id',
                new ProductAvailableInCountry($product, $this->input('country_id'))
            ],

            'description.*.language_id' => 'sometimes|distinct|required|integer|exists:languages,id',
            'description.*.marketing_description' => 'sometimes|required|min:3',

            'images.list.*.image_path' => 'sometimes|distinct|required',
            'images.list.*.default' => 'sometimes|required|boolean',

            //validating location ids
            'location.selected.*' => ['integer','exists:locations,id',
                new UserLocationAccess($userInterface, Auth::id())
            ]
        ];
    }
}
