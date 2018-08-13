<?php
namespace App\Rules\Stockists;

use Illuminate\Contracts\Validation\Rule;

class StockistOutstandingAdjustmentAmount implements Rule
{
    /**
     * StockistOutstandingAdjustmentAmount constructor.
     *
     */
    public function __construct()
    {

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
        $totalAdjustmentAmount = collect($value)->sum('adjustment_amount');

        return ($totalAdjustmentAmount == 0) ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.stockist-payment-validation-message.zero-adjustment-amount');
    }
}
