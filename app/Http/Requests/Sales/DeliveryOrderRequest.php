<?php
namespace App\Http\Requests\Sales;

use App\{
    Models\Sales\Sale,
    Models\Sales\SaleProduct,
    Rules\Sales\CheckSalesProductBelongsToSales
};
use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class DeliveryOrderRequest extends FormRequest
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
            'sale_number' => 'required|exists:sales,document_number',
            'sales_product_id' => [
                'required',
                'integer',
                'exists:sales_products,id',
                new CheckSalesProductBelongsToSales(new SaleProduct(), new Sale(), $this->input('sale_number'))
            ],
            'provider' => Rule::in([
                'ABXEX',
                'CITYLINK',
                'FEDEX',
                'FMGLOBAL',
                'FMMULTI',
                'GDEX',
                'JONE',
                'NATIONWIDE',
                'SPCCSERV',
                'SWIFTLOG',
                'TASCO',
                'TNTT',
                'UTSLOG'
            ]),
            'delivered_quantity' => 'required|integer',
            'delivery_order_number' => 'required',
            'consignment_order_number' => 'required',
            'status_code' => 'required|integer|in:0,1,2,3,4,5',
            'status' => 'required'
        ];
    }
}
