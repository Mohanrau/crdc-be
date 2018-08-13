<?php
namespace App\Rules\Invoices;

use App\Models\Invoices\Invoice;
use Illuminate\Contracts\Validation\Rule;

class InvoiceUserCheck implements Rule
{
    private $invoiceObj, $userId;

    /**
     * InvoiceUserCheck constructor.
     *
     * @param Invoice $invoice
     * @param int $userId
     */
    public function __construct(Invoice $invoice, int $userId)
    {
        $this->invoiceObj = $invoice;

        $this->userId = $userId;
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
        $result = true;

        if(!empty($value)){
            $invoiceDetail = $this->invoiceObj
                ->where('invoices.id', $value)
                ->join('sales', function ($join){
                    $join->on('invoices.sale_id', '=', 'sales.id')
                        ->where(function ($saleQuery){
                            $saleQuery->where('sales.user_id', $this->userId);
                        });
                })
                ->first();

            $result = (!empty($invoiceDetail)) ? true : false;
        }

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.sales-cancellation.invalid-member-invoice');
    }
}
