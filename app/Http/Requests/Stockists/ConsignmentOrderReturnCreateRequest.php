<?php
namespace App\Http\Requests\Stockists;

use App\{
    Interfaces\Stockists\StockistInterface,
    Rules\Stockists\ConsignmentReturnPendingStatusValidate,
    Rules\Stockists\ConsignmentReturnProductValidate,
    Rules\Stockists\ConsignmentOrderTotalGmp
};
use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class ConsignmentOrderReturnCreateRequest extends FormRequest
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
     * @param StockistInterface $stockistInterface
     * @return array
     */
    public function rules(StockistInterface $stockistInterface)
    {
        return [
            'consignment_order_return.type' => 'required|string|in:order,return',
            'consignment_order_return.stockist_id' => 'required|integer|exists:stockists,id',
            'consignment_order_return.stockist_user_id' => [
                'required',
                'integer',
                'exists:stockists,stockist_user_id',
                new ConsignmentReturnPendingStatusValidate(
                    $stockistInterface,
                    $this->input('consignment_order_return.type')
                )
            ],
            'consignment_order_return.products' => 'required|array',
            'consignment_order_return.products.*' => [
                new ConsignmentReturnProductValidate(
                    $stockistInterface,
                    $this->input('consignment_order_return.stockist_user_id'),
                    $this->input('consignment_order_return.type')
                )
            ],
            'consignment_order_return.products.*.product_id' => 'required|integer|exists:products,id',
            'consignment_order_return.products.*.quantity' => 'required|integer|min:1',
            'consignment_order_return.total_gmp' => [
                'required',
                'numeric',
                'regex:/^\d*(\.\d{1,2})?$/',
                new ConsignmentOrderTotalGmp(
                    $stockistInterface,
                    $this->input('consignment_order_return.stockist_user_id'),
                    $this->input('consignment_order_return.type')
                )
            ]
        ];
    }
}
