<?php
namespace App\Rules\Sales;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Sales\SaleCancellation;
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Config
};

class SalesCancellationsInvoiceCheck implements Rule
{
    private $masterRepositoryObj, $saleCancellationObj, $saleCancellationStatusConfigCodes;

    /**
     * SalesCancellationsInvoiceCheck constructor.
     *
     * @param MasterInterface $masterInterface
     * @param SaleCancellation $saleCancellation
     */
    public function __construct(MasterInterface $masterInterface, SaleCancellation $saleCancellation)
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->saleCancellationObj = $saleCancellation;

        $this->saleCancellationStatusConfigCodes = Config::get('mappings.sale_cancellation_status');
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
            $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(array('sale_cancellation_status'));

            $saleCancellationStatusTitles = array_change_key_case($masterSettingsDatas['sale_cancellation_status']->pluck('id','title')->toArray());

            $completeRejectStatusId = [
                $saleCancellationStatusTitles[$this->saleCancellationStatusConfigCodes['completed']],
                $saleCancellationStatusTitles[$this->saleCancellationStatusConfigCodes['rejected']]
            ];

            $salesCancellationRecord = $this->saleCancellationObj
                ->where('invoice_id', $value)
                ->whereNotIn('cancellation_status_id', $completeRejectStatusId)
                ->first();

            $result = (empty($salesCancellationRecord)) ? true : false;
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
        return trans('message.sales-cancellation.invoice-in-sale-cancellation-process');
    }
}
