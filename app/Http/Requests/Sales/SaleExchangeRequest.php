<?php
namespace App\Http\Requests\Sales;

use App\Interfaces\Masters\MasterInterface;
use App\Models\{
    Masters\MasterData,
    Products\Product,
    Sales\Sale,
    Sales\SaleKittingClone,
    Sales\SaleProduct
};
use App\Rules\{ForeignBelongTo,
    General\MasterDataIdExists,
    Product\ProductAvailableInCountry,
    Sales\SalesCheckSaleIsCompleted};
use Illuminate\Foundation\Http\FormRequest;

class SaleExchangeRequest extends FormRequest
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
     * @param Sale $sale
     * @param SaleProduct $saleProduct
     * @param SaleKittingClone $saleKittingClone
     * @param MasterInterface $masterInterface
     * @param MasterData $masterData
     * @param Product $product
     * @return array
     */
    public function rules(
        Sale $sale,
        SaleProduct $saleProduct,
        SaleKittingClone $saleKittingClone,
        MasterInterface $masterInterface,
        MasterData $masterData,
        Product $product
    )
    {
        return [
            'sales_exchange_data' => 'required|array',
            'sales_exchange_data.country_id' => 'required|integer|exists:countries,id',
            'sales_exchange_data.user_id' => [
                'required',
                'integer',
                'exists:members,user_id',
                new ForeignBelongTo(
                    $sale,
                    'user_id',
                    $this->input('sales_exchange_data.user_id'),
                    $this->has('sales_exchange_data.sale.id') ? $this->input('sales_exchange_data.sale.id') : 0,
                    false
                )
            ],
            'sales_exchange_data.stock_location_id' => 'required|integer|exists:stock_locations,id',
            'sales_exchange_data.location_id' => 'required|integer|exists:locations,id',
            'sales_exchange_data.reason_id' =>[
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'product_exchange_reason')
            ] ,
            'sales_exchange_data.fms_number' => 'required|string|unique:sales_exchanges,fms_number',
            'sales_exchange_data.delivery_fees' => 'required',
            'sales_exchange_data.balance' => 'required',
            'sales_exchange_data.cw_id' => 'required',
            'sales_exchange_data.exchange_amount_total' => 'required',
            'sales_exchange_data.return_amount_total' => 'required',
            'sales_exchange_data.is_legacy' => 'required|boolean',

            //validating the parent sale id=
            'sales_exchange_data.sale.id' => [
                'required_if:sales_exchange_data.is_legacy,0',
                'integer',
                'exists:sales,id',
                new SalesCheckSaleIsCompleted($sale, $masterData)
            ],

            //validating returning products, kitting and promotions items ----------------------------------------------
            'sales_exchange_data.return_products.*.id'=> [
                'sometimes',
                'required',
                'integer',
                'exists:sales_products,id',
                new ForeignBelongTo(
                    $saleProduct,
                    'sale_id',
                    $this->has('sales_exchange_data.sale.id') ? $this->input('sales_exchange_data.sale.id') : 0
                )
            ],
            'sales_exchange_data.return_products.*.return_quantity' => [
                'required_with:sales_exchange_data.return_products.*.id', //TODO validate qty
                'numeric',
                'min:0'
            ],

            'sales_exchange_data.return_kitting.*.id'=> [
                'sometimes',
                'required',
                'integer',
                'exists:sales_kitting_clone,id',
                new ForeignBelongTo(
                    $saleKittingClone,
                    'sale_id',
                    $this->has('sales_exchange_data.sale.id') ? $this->input('sales_exchange_data.sale.id') : 0
                )
            ],
            'sales_exchange_data.return_kitting.*.kitting_products.*.id' => [
                'required_with:sales_exchange_data.return_kitting.*.kitting_products.*.id',
                'numeric',
                'min:0',
                new ForeignBelongTo(
                    $saleProduct,
                    'sale_id',
                    $this->has('sales_exchange_data.sale.id') ? $this->input('sales_exchange_data.sale.id') : 0
                )
            ],
            'sales_exchange_data.return_kitting.*.kitting_products.*.return_quantity' => [
                'required_with:sales_exchange_data.return_kitting.*.kitting_products.*.id',
                'numeric',
                'min:0'],

            'sales_exchange_data.return_promotions.*.id'=> 'sometimes|required|integer|exists:sales_products,id',
            'sales_exchange_data.return_promotions.*.return_quantity' => [
                'required_with:sales_exchange_data.return_promotions.*.id',
                'numeric',
                'min:0'],

            //validating exchanged products ----------------------------------------------------------------------------
            'sales_exchange_data.exchange_products.*.id'=> [
                'sometimes',
                'required',
                'integer',
                'exists:products,id',
                new ProductAvailableInCountry($product, $this->input('sales_exchange_data.country_id'))
            ],
            'sales_exchange_data.exchange_products.*.quantity' =>
                'required_with:sales_exchange_data.exchange_products.*.product_id|numeric|min:1',

            //validating exchanged kitting-------------------------------------------------------------------------------
            'sales_exchange_data.exchange_kitting.*.kitting_id' => 'sometimes|required|integer|exists:kitting,id',
            'sales_exchange_data.exchange_kitting.*.quantity' =>
                'required_with:sales_exchange_data.exchange_kitting.*.kitting|numeric|min:1',

            //validating returning legacy invoice, legacy products and kitting----------------------------------------
            'sales_exchange_data.legacy_invoice.invoice_number' => 'required_if:sales_exchange_data.is_legacy,1|string',
            'sales_exchange_data.legacy_invoice.invoice_date' => 'required_if:sales_exchange_data.is_legacy,1|date|before_or_equal:today',
            'sales_exchange_data.legacy_invoice.cw_id' => 'required_if:sales_exchange_data.is_legacy,1|integer|exists:cw_schedules,id',
            'sales_exchange_data.legacy_invoice.country_id' => 'required_if:sales_exchange_data.is_legacy,1|integer|exists:countries,id',
            'sales_exchange_data.legacy_invoice.transaction_location_id' => 'required_if:sales_exchange_data.is_legacy,1|integer|exists:locations,id',

            'sales_exchange_data.legacy_return_products' => 'sometimes|array',
            'sales_exchange_data.legacy_return_products.*.product_id' => 'sometimes|required|integer|exists:products,id',
            'sales_exchange_data.legacy_return_products.*.return_quantity' => 'sometimes|required|integer',
            'sales_exchange_data.legacy_return_products.*.gmp_price_tax' => 'sometimes|required|regex:/^\d*(\.\d{1,2})?$/', // for 2 decimals or without decimals,

            'sales_exchange_data.legacy_return_kitting' => 'sometimes|array',
            'sales_exchange_data.legacy_return_kitting.*.kitting_id' => 'sometimes|required|integer|exists:kitting,id',
            'sales_exchange_data.legacy_return_kitting.*.return_quantity' => 'sometimes|required|integer',
            'sales_exchange_data.legacy_return_kitting.*.gmp_price_tax' => 'sometimes|required|regex:/^\d*(\.\d{1,2})?$/', // for 2 decimals or without decimals,
        ];
    }
}
