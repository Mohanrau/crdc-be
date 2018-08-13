<?php
namespace App\Rules\Sales;

use App\Interfaces\Sales\SaleInterface;
use Illuminate\Contracts\Validation\Rule;

class SalesCancellationsInvoiceProductValidation implements Rule
{
    private $saleRepositoryObj,
        $userId,
        $saleCancellationMethod;

    /**
     * CancellationProductQuantity constructor.
     *
     * @param SaleInterface $saleInterface
     * @param int $userId
     * @param string $saleCancellationMethod
     */
    public function __construct(
        SaleInterface $saleInterface,
        int $userId,
        string $saleCancellationMethod
    )
    {
        $this->saleRepositoryObj = $saleInterface;

        $this->userId = $userId;

        $this->saleCancellationMethod = $saleCancellationMethod;
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

        if($this->saleCancellationMethod == "normal"){

            $saleInvoiceDetail = $this->saleRepositoryObj
                ->getSalesCancellationInvoiceDetails("normal", $this->userId, $value, 0);

            $availableQty = collect($saleInvoiceDetail['sale']['products'])
                ->sum('available_quantity_snapshot');

            collect($saleInvoiceDetail['sale']['kitting'])->each(function ($kitting)
                use (&$availableQty){
                    $availableQty += collect($kitting['kitting_products'])
                        ->sum('available_quantity_snapshot');
                });

            $availableQty += collect($saleInvoiceDetail['sale']['promotions'])
                ->sum('available_quantity_snapshot');

            $result = ($availableQty > 0) ? true : false;
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
        return __('message.sales-cancellation.un-available-product-cancel');
    }
}
