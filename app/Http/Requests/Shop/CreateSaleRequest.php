<?php
namespace App\Http\Requests\Shop;

use App\Interfaces\Masters\MasterInterface;
use Illuminate\Foundation\Http\FormRequest;
use App\Rules\General\MasterDataIdExists;
use Auth;

class CreateSaleRequest extends FormRequest
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
     * @param MasterInterface $masterRepository
     * @return array
     */
    public function rules(
        MasterInterface $masterRepository
    )
    {
        $masterKeys = $masterRepository->getMasterDataByKey(['sale_delivery_method']);

        $selfCollectionMethodMasterIds = $masterKeys['sale_delivery_method']
            ->where('title','ilike', 'Self Pick-up')
            ->implode('id',',');

        $deliveryMethodMasterIds = $masterKeys['sale_delivery_method']
            ->where('title','ilike','Delivery')
            ->implode('id',',');

        $validations = [
            'country_id' => 'required|integer|exists:countries,id',
            'location_id' => 'required|integer|exists:locations,id',
            'stock_location_id' => 'required|integer|exists:stock_locations,id',
            'order_for_user_id' => 'sometimes|nullable|integer|exists:members,user_id',
            'selected.shipping.sale_delivery_method' => [
                'required',
                new MasterDataIdExists($masterRepository, 'sale_delivery_method')
            ],
            'selected.shipping.self_collection_point_id' => [
                'required_if:selected.shipping.sale_delivery_method_id,'.$selfCollectionMethodMasterIds,
                'integer',
                'exists:locations_addresses_data,id'
            ],
            'selected.shipping.recipient_name' => [
                'required_if:selected.shipping.sale_delivery_method_id,'.$deliveryMethodMasterIds,
                'string'
            ],
            'selected.shipping.recipient_mobile_phone_number' => [
                'required_if:selected.shipping.sale_delivery_method_id,'.$deliveryMethodMasterIds,
                'string'
            ],
            'selected.shipping.recipient_mobile_country_code_id' => [
                'required_if:selected.shipping.sale_delivery_method_id,'.$deliveryMethodMasterIds,
                'integer'
            ],
            'selected.shipping.recipient_addresses' => [
                'required_if:selected.shipping.sale_delivery_method_id,'.$deliveryMethodMasterIds,
                'array',
                'min:1'
            ],
            'selected.shipping.recipient_selected_shipping_index' => [
                'required_if:selected.shipping.sale_delivery_method_id,'.$deliveryMethodMasterIds,
                'integer'
            ]
        ];

        // Require sponsor id if user is guest and referrer dose not exist
        if (Auth::user()->isGuest() && is_null(Auth::user()->identifier()->referrer)) {
            $validations['sponsor_member_id'] = 'required|integer|exists:users,old_member_id';
        }

        return $validations;
    }
}
