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

class SaleCancellationRequest extends FormRequest
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
     * @param SaleInterface $saleInterface
     * @param Invoice $invoice
     * @param MasterInterface $masterInterface
     * @param SaleCancellation $saleCancellation
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
            'sale_cancellation.invoice_id' => [
                'required', 'integer', 'exists:invoices,id',
                new InvoiceUserCheck($invoice, $this->input('sale_cancellation.user_id')),
                new SalesCancellationsInvoiceCheck($masterInterface, $saleCancellation)
            ],
            'sale_cancellation.user_id' => 'required|integer|exists:members,user_id',
            'sale_cancellation.cw_id' => ['required', new CwCheck($cwSchedulesInterface)],
            'sale_cancellation.cancellation_type_id' => [
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'sale_cancellation_type')
            ],
            'sale_cancellation.cancellation_mode_id' => [
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'sale_cancellation_mode')
            ],
            'sale_cancellation.cancellation_reason_id' => [
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'sale_cancellation_reason')
            ],
            'sale_cancellation.transaction_location_id' => 'required|integer|exists:locations,id',
            'sale_cancellation.stock_location_id' => 'integer|exists:stock_locations,id',
            'sale.products.*.id' => 'sometimes|required|integer|exists:sales_products,id',
            'sale.products.*.cancellation_quantity' => [
                'sometimes','required','integer',
                new SalesCancellationProductQuantityCheck(
                    $saleInterface,
                    $this->input('sale_cancellation.invoice_id'),
                    $this->input('sale_cancellation.user_id'),
                    $this->input('sale.products'),
                    'products'
                )
            ],
            'sale.kitting.*.id' => 'sometimes|required|integer|exists:sales_kitting_clone,id',
            'sale.kitting.*.kitting_products.*.id' =>
                'sometimes|required|integer|exists:sales_products,id',
            'sale.kitting.*.kitting_products.*.cancellation_quantity' => [
                'sometimes','required','integer',
                new SalesCancellationProductQuantityCheck(
                    $saleInterface,
                    $this->input('sale_cancellation.invoice_id'),
                    $this->input('sale_cancellation.user_id'),
                    $this->input('sale.kitting'),
                    'kitting'
                )
            ],
            'sale.promotions.*.id' =>'sometimes|required|integer|exists:sales_products,id',
            'sale.promotions.*.cancellation_quantity' => [
                'sometimes','required','integer',
                new SalesCancellationProductQuantityCheck(
                    $saleInterface,
                    $this->input('sale_cancellation.invoice_id'),
                    $this->input('sale_cancellation.user_id'),
                    $this->input('sale.promotions'),
                    'promotions'
                )
            ],
            'sale.esac_vouchers.*.id' =>'sometimes|required|integer|exists:sales_esac_vouchers_clone,id',
            'sale.esac_vouchers.*.cancellation_quantity' => [
                'sometimes','required','integer',
                new SalesCancellationProductQuantityCheck(
                    $saleInterface,
                    $this->input('sale_cancellation.invoice_id'),
                    $this->input('sale_cancellation.user_id'),
                    $this->input('sale.esac_vouchers'),
                    'esac_vouchers'
                )
            ]
        ];
    }
}
