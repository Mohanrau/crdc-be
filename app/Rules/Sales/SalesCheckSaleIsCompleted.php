<?php
namespace App\Rules\Sales;

use App\Models\{
    Masters\MasterData,
    Sales\Sale
};
use Illuminate\Contracts\Validation\Rule;

class SalesCheckSaleIsCompleted implements Rule
{
    private
        $saleObj,
        $saleStatus,
        $masterDataObj,
        $saleStatusCodes
    ;

    /**
     * SalesCheckSaleIsCompleted constructor.
     *
     * @param Sale $sale
     * @param MasterData $masterData
     */
    public function __construct(Sale $sale, MasterData $masterData)
    {
        $this->saleObj = $sale;

        $this->masterDataObj = $masterData;

        $this->saleStatusCodes = config('mappings.sale_order_status');
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
        // check if sales status is completed so can proceed to do be.
        $sale = $this->saleObj->find($value);

        if (!is_null($sale)){
            $this->saleStatus = $this->masterDataObj->findOrFail($sale->order_status_id);

            if ($this->saleStatus->title == ucwords($this->saleStatusCodes['completed'])){
                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.sales.not-completed',[
            'status' => $this->saleStatus->title
        ]);
    }
}
