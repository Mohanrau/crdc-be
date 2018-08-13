<?php
namespace App\Listeners\Sales;

use App\Interfaces\{
    Masters\MasterInterface,
    Payments\PaymentInterface,
    Sales\SaleInterface,
    Settings\SettingsInterface
};
use App\Models\{
    Sales\Sale
};
use Illuminate\{
    Queue\InteractsWithQueue,
    Contracts\Queue\ShouldQueue,
    Support\Facades\Auth,
    Support\Facades\Config
};
use App\Events\Sales\RentalSaleOrderEvents;

class RentalSaleOrderListeners
{
    private $masterRepositoryObj,
        $paymentRepositoryObj,
        $saleRepositoryObj,
        $settingRepositoryObj,
        $saleObj,
        $saleOrderStatusConfigCodes;

    /**
     * Create the event listener.
     *
     * @param MasterInterface $masterInterface
     * @param PaymentInterface $paymentInterface
     * @param SaleInterface $saleInterface
     * @param SettingsInterface $settingsInterface
     * @param Sale $sale
     * @return void
     */
    public function __construct(
        MasterInterface $masterInterface,
        PaymentInterface $paymentInterface,
        SaleInterface $saleInterface,
        SettingsInterface $settingsInterface,
        Sale $sale
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->paymentRepositoryObj = $paymentInterface;

        $this->saleRepositoryObj = $saleInterface;

        $this->settingRepositoryObj = $settingsInterface;

        $this->saleObj = $sale;

        //Get config Code Mappings
        $this->saleOrderStatusConfigCodes = Config::get('mappings.sale_order_status');
    }

    /**
     * Handle the event.
     *
     * @param RentalSaleOrderEvents $event
     * @return mixed
     */
    public function handle(RentalSaleOrderEvents $event)
    {
        $saleId = $event->saleId;

        $action = $event->stepInput['trigger'];

        //Get Status ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_order_status'));

        $saleOrderStatus = array_change_key_case(
            $settingsData['sale_order_status']->pluck('id','title')->toArray()
        );

        $saleDetail = $this->saleObj
            ->where('id', $saleId)
            ->whereNotNull('workflow_tracking_id')
            ->where('is_rental_sale_order', 1)
            ->where('rental_release', 0)
            ->where('order_status_id', $saleOrderStatus[$this->saleOrderStatusConfigCodes['completed']])
            ->first();

        switch (strtolower($action)) {
            case "processrelease":
                $invalidAction = false;
                $rentalRelease = 1;

                break;

            default :
                $invalidAction = true;
                $rentalRelease = 0;

                break;
        };

        if($saleDetail && !$invalidAction){

            $saleData = [
                'rental_release' => $rentalRelease,
                'updated_by' => Auth::id()
            ];

            $saleDetail->update($saleData);

        }

        return $this->saleRepositoryObj->saleDetails($saleId);
    }
}