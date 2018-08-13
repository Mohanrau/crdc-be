<?php
namespace App\Rules\Sales;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Sales\SaleCancellation;
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Config
};

class SaleCancellationBathRefund implements Rule
{
    private $masterRepositoryObj, $saleCancellationObj, $saleCancellationStatusConfigCodes;

    /**
     * SaleCancellationBathRefund constructor.
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
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(array('sale_cancellation_status'));

        $saleCancellationStatusTitles = array_change_key_case($masterSettingsDatas['sale_cancellation_status']->pluck('id','title')->toArray());

        $pendingRefundStatusId =
            $saleCancellationStatusTitles[$this->saleCancellationStatusConfigCodes['pending-refund']];

        $cancellationPendingRefundRecord = $this->saleCancellationObj
            ->where('id', $value)
            ->where('cancellation_status_id', $pendingRefundStatusId)
            ->first();

        return (empty($cancellationPendingRefundRecord)) ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.sales-cancellation.un-available-sale-cancellation-refund');
    }
}
