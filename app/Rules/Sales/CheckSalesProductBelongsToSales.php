<?php
namespace App\Rules\Sales;

use App\Models\Sales\Sale;
use App\Models\Sales\SaleProduct;
use Illuminate\Contracts\Validation\Rule;

class CheckSalesProductBelongsToSales implements Rule
{
    protected $saleProductObj, $saleObj, $saleNumber;

    /**
     * CheckSalesProductBelongsToSales constructor.
     *
     * @param SaleProduct $saleProduct
     * @param Sale $sale
     * @param string $saleNumber
     */
    public function __construct(SaleProduct $saleProduct, Sale $sale, string $saleNumber)
    {
        $this->saleProductObj = $saleProduct;

        $this->saleObj = $sale;

        $this->saleNumber = $saleNumber;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $saleProduct = $this->saleProductObj->find($value);

        $sale = $this->saleObj->where('document_number', $this->saleNumber)->first();

        if(isset($saleProduct) && isset($sale) && $saleProduct->sale_id == $sale->id)
        {
            return $value;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.sale_product_does_not_belongs_to_sale');
    }
}
