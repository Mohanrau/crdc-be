<?php
namespace App\Http\Requests\Sales;

use App\{
    Interfaces\General\CwSchedulesInterface,
    Interfaces\Masters\MasterInterface,
    Interfaces\Sales\SaleInterface,
    Models\Invoices\Invoice,
    Models\Sales\SaleCancellation,
    Rules\General\MasterDataIdExists,
    Rules\CW\CwCheck,
    Rules\Invoices\InvoiceUserCheck,
    Rules\Sales\SalesCancellationsInvoiceCheck,
    Rules\Sales\SalesCancellationProductQuantityCheck
};
use Illuminate\Foundation\Http\FormRequest;

class LegacySaleCancellationRequest extends FormRequest
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
     * @param CwSchedulesInterface $cwSchedulesInterface
     * @param Invoice $invoice
     * @return array
     */
    public function rules(
        CwSchedulesInterface $cwSchedulesInterface,
        SaleInterface $saleInterface,
        Invoice $invoice,
        MasterInterface $masterInterface,
        SaleCancellation $saleCancellation
    )
    {
        return [
            'legacy_sale_cancellation.user_id' => 'required|integer|exists:members,user_id',
            'legacy_sale_cancellation.cw_id' => ['required', new CwCheck($cwSchedulesInterface)],
            'legacy_sale_cancellation.cancellation_type_id' => [
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'sale_cancellation_type')
            ],
            'legacy_sale_cancellation.cancellation_reason_id' => [
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'sale_cancellation_reason')
            ],
            'legacy_sale_cancellation.transaction_location_id' => 'required|integer|exists:locations,id',
            'legacy_sale_cancellation.stock_location_id' => 'integer|exists:stock_locations,id',
            'sale.legacy_invoice.invoice_number' => 'required|string',
            'sale.legacy_invoice.invoice_date' => 'required|date|before_or_equal:today',
            'sale.legacy_invoice.cw_id' => 'required|integer|exists:cw_schedules,id',
            'sale.legacy_invoice.transaction_location_id' => 'required|integer|exists:locations,id',
            'sale.legacy_invoice.country_id' => 'required|integer|exists:countries,id',
            'sale.products' => 'required_without:sale.kitting|array',
            'sale.products.*.product_id' => 'sometimes|required|integer|exists:products,id',
            'sale.products.*.cancellation_quantity' => 'sometimes|required|integer',
            'sale.products.*.gmp_price_tax' => 'sometimes|required|regex:/^\d*(\.\d{1,2})?$/', // for 2 decimals or without decimals,
            'sale.kitting' => 'required_without:sale.products|array',
            'sale.kitting.*.kitting_id' => 'sometimes|required|integer|exists:kitting,id',
            'sale.kitting.*.cancellation_quantity' => 'sometimes|required|integer',
            'sale.kitting.*.gmp_price_tax' => 'sometimes|required|regex:/^\d*(\.\d{1,2})?$/', // for 2 decimals or without decimals,
        ];
    }
}
