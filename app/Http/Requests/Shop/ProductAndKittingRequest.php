<?php
namespace App\Http\Requests\Shop;

use App\Models\Shop\ProductAndKitting;
use App\Rules\Shop\ProductAndKittingAvailable;
use Illuminate\Foundation\Http\FormRequest;

class ProductAndKittingRequest extends FormRequest
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
     * Validates if a product or kitting is available
     *
     * @param ProductAndKitting $productAndKitting
     * @return array
     */
    public function rules(ProductAndKitting $productAndKitting)
    {
        return [
            'country_id' => 'required|integer|exists:countries,id',
            'location_id' => 'required|integer|exists:locations,id',
            'product_id' => [
                'required_without:kitting_id', 'integer', 'exists:products,id',
                new ProductAndKittingAvailable(
                    $productAndKitting
                    , $this->input('country_id')
                    , $this->input('location_id')
                    , $this->input('product_id')
                    , $this->input('kitting_id')
                )
            ],
            'kitting_id' => [
                'required_without:product_id', 'integer', 'exists:kitting,id',
                new ProductAndKittingAvailable(
                    $productAndKitting
                    , $this->input('country_id')
                    , $this->input('location_id')
                    , $this->input('product_id')
                    , $this->input('kitting_id')
                )
            ]
        ];
    }
}
