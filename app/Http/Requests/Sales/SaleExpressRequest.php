<?php
namespace App\Http\Requests\Sales;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Products\Product;
use App\Rules\General\MasterDataTitleExists;
use App\Rules\Product\ProductAvailableInCountry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaleExpressRequest extends FormRequest
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
     * @param MasterInterface $masterRepository
     * @return array
     */
    public function rules(Product $product, MasterInterface $masterRepository)
    {
        return [
            'sales_data' => 'required|array',
            'sales_data.country_id' => 'required|integer|exists:countries,id',
            'sales_data.downline_member_id' => 'required|integer|exists:members,user_id',
            'sales_data.location_id' => 'required|integer|exists:locations,id',
            'sales_data.stock_location_id' => 'required|integer|exists:stock_locations,id',
            'sales_data.selected.shipping.sale_delivery_method' => [
                'required',
                Rule::in(['delivery', 'self pick-up']),
                'exists:master_data,title',
                new MasterDataTitleExists($masterRepository, 'sale_delivery_method')
            ],

            //validating products --------------------------------------------------------------------------------------
            'sales_data.products.*.product_sku'=>
                [
                    'required',
                    'exists:products,sku',
                    new ProductAvailableInCountry($product, $this->input('sales_data.country_id'), true)
                ],
            'sales_data.products.*.quantity' => 'required_with:sales_data.products.*.product_sku|integer',
            'sales_data.products.*.transaction_type' => 'required_with:sales_data.products.*.product_sku|integer|exists:master_data,id',
        ];
    }
}
