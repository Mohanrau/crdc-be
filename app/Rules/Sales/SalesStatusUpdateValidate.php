<?php
namespace App\Rules\Sales;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Sales\Sale;
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Config
};

class SalesStatusUpdateValidate implements Rule
{
    private $masterRepositoryObj,
        $saleObj,
        $saleId,
        $saleOrderStatusConfigCodes;

    /**
     * SalesStatusUpdateValidate constructor.
     *
     * @param MasterInterface $masterInterface
     * @param Sale $sale
     * @param int $saleId
     */
    public function __construct(
        MasterInterface $masterInterface,
        Sale $sale,
        int $saleId
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->saleObj = $sale;

        $this->saleId = $saleId;

        $this->saleOrderStatusConfigCodes = Config::get('mappings.sale_order_status');
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
        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(array('sale_order_status'));

        $saleOrderStatusTitles = array_change_key_case(
            $masterSettingsDatas['sale_order_status']->pluck('id','title')->toArray());

        $rejectUpdateStatusId = [
            $saleOrderStatusTitles[$this->saleOrderStatusConfigCodes['completed']],
            $saleOrderStatusTitles[$this->saleOrderStatusConfigCodes['cancelled']],
            $saleOrderStatusTitles[$this->saleOrderStatusConfigCodes['partially-cancelled']]
        ];

        $saleRecord = $this->saleObj
            ->where('id', $this->saleId)
            ->whereNotIn('order_status_id', $rejectUpdateStatusId)
            ->first();

        return (empty($saleRecord)) ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.sales.sales-is-not-in-pre-order-status');
    }
}
