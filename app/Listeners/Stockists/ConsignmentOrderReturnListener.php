<?php
namespace App\Listeners\Stockists;

use App\{
    Events\Stockists\ConsignmentOrderReturnEvents,
    Interfaces\Masters\MasterInterface,
    Interfaces\Settings\SettingsInterface,
    Interfaces\Stockists\StockistInterface,
    Models\Stockists\Stockist,
    Models\Stockists\StockistDepositSetting,
    Models\Stockists\ConsignmentOrderReturn
};
use Illuminate\{
    Queue\InteractsWithQueue,
    Contracts\Queue\ShouldQueue,
    Support\Facades\Auth,
    Support\Facades\Config
};

class ConsignmentOrderReturnListener
{
    private
        $masterRepositoryObj,
        $settingRepositoryObj,
        $stockistRepositoryObj,
        $stockistObj,
        $stockistDepositSettingObj,
        $consignmentOrderReturnObj,
        $consignmentOrderReturnTypeConfigCodes,
        $consignmentOrderStatusConfigCodes,
        $consignmentReturnStatusConfigCodes;

    /**
     * Create the event listener.
     *
     * @param StockistInterface $stockistInterface
     * @param MasterInterface $masterInterface
     * @param SettingsInterface $settingsInterface
     * @param Stockist $stockist
     * @param StockistDepositSetting $stockistDepositSetting
     * @param ConsignmentOrderReturn $consignmentOrderReturn
     * @return void
     */
    public function __construct(
        StockistInterface $stockistInterface,
        MasterInterface $masterInterface,
        SettingsInterface $settingsInterface,
        Stockist $stockist,
        StockistDepositSetting $stockistDepositSetting,
        ConsignmentOrderReturn $consignmentOrderReturn
    )
    {
        $this->stockistRepositoryObj = $stockistInterface;

        $this->masterRepositoryObj = $masterInterface;

        $this->settingRepositoryObj = $settingsInterface;

        $this->stockistObj = $stockist;

        $this->stockistDepositSettingObj = $stockistDepositSetting;

        $this->consignmentOrderReturnObj = $consignmentOrderReturn;

        $this->consignmentOrderReturnTypeConfigCodes =
            Config::get('mappings.consignment_order_and_return_type');

        $this->consignmentOrderStatusConfigCodes =
            Config::get('mappings.consignment_order_status');

        $this->consignmentReturnStatusConfigCodes =
            Config::get('mappings.consignment_return_status');
    }

    /**
     * Handle the event.
     *
     * @param ConsignmentOrderReturnEvents $event
     * @return mixed
     */
    public function handle(ConsignmentOrderReturnEvents $event)
    {
        $consignmentOrderReturnId = $event->consignmentOrderReturnId;

        $action = strtolower($event->stepInput['trigger']);

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_order_and_return_type',
                'consignment_order_status',
                'consignment_return_status'
            )
        );

        $orderReturnTypeIds = array_change_key_case(
            $masterSettingsDatas['consignment_order_and_return_type']->pluck('id','title')->toArray()
        );

        $orderStatusIds = array_change_key_case(
            $masterSettingsDatas['consignment_order_status']->pluck('id','title')->toArray()
        );

        $returnStatusIds = array_change_key_case(
            $masterSettingsDatas['consignment_return_status']->pluck('id','title')->toArray()
        );

        $orderReturnQuery = $this->consignmentOrderReturnObj
            ->where('id', $consignmentOrderReturnId);

        $invalidAction = false;

        switch ($action) {
            case "sendorderapproval":

                $consignmentTypeId = $orderReturnTypeIds[$this->consignmentOrderReturnTypeConfigCodes['order']];

                $consignmentStatusId = $orderStatusIds[$this->consignmentOrderStatusConfigCodes['pending']];

                $statusUpdateId = $orderStatusIds[$this->consignmentOrderStatusConfigCodes['approved']];

                break;

            case "sendorderdecline":

                $consignmentTypeId = $orderReturnTypeIds[$this->consignmentOrderReturnTypeConfigCodes['order']];

                $consignmentStatusId = $orderStatusIds[$this->consignmentOrderStatusConfigCodes['pending']];

                $statusUpdateId = $orderStatusIds[$this->consignmentOrderStatusConfigCodes['rejected']];

                break;

            case "sendreturnverified":

                $consignmentTypeId = $orderReturnTypeIds[$this->consignmentOrderReturnTypeConfigCodes['return']];

                $consignmentStatusId = $returnStatusIds[$this->consignmentReturnStatusConfigCodes['pending']];

                $statusUpdateId = $returnStatusIds[$this->consignmentReturnStatusConfigCodes['verified']];

                break;

            case "sendreturndecline":

                $consignmentTypeId = $orderReturnTypeIds[$this->consignmentOrderReturnTypeConfigCodes['return']];

                $consignmentStatusId = $returnStatusIds[$this->consignmentReturnStatusConfigCodes['pending']];

                $statusUpdateId = $returnStatusIds[$this->consignmentReturnStatusConfigCodes['rejected']];

                break;

            default :
                $invalidAction = true;

                $consignmentTypeId = 0;

                $consignmentStatusId = 0;

                $statusUpdateId = NULL;

                break;
        }

        $orderReturnQuery = $orderReturnQuery
            ->where('type_id', $consignmentTypeId)
            ->where('status_id', $consignmentStatusId);

        $orderReturnRecord = $orderReturnQuery->first();

        if($orderReturnRecord && !$invalidAction){

            $statusUpdateAction = true;

            $createDocumentNumber = false;

            $stockist = $this->stockistObj->find($orderReturnRecord->stockist_id);

            //Update deposit balance
            if($action == 'sendreturnverified' || $action == 'sendorderapproval'){

                $stockistMasterDepositRecord = $this->stockistDepositSettingObj
                    ->where('stockist_id',$orderReturnRecord->stockist_id)->first();

                $depositLimitBalance = floatval($stockistMasterDepositRecord->deposit_limit);

                $depositBalanceUpdate = true;

                if($action == 'sendreturnverified'){

                    $type = 'return';

                    //Update consignment return quantity
                    if(isset($event->stepInput['additional_info'])){

                        if(isset($event->stepInput['additional_info']['consignment_order_return'])){

                            $consignemntReturnDetail = $event->stepInput['additional_info']['consignment_order_return'];

                            $this->stockistRepositoryObj
                                ->updateConsignmentReturnProductQuantity($consignemntReturnDetail);

                            //Refresh Order Return Record
                            $orderReturnRecord = $orderReturnQuery->first();
                        }
                    }

                    $depositLimitBalance += floatval($orderReturnRecord->total_gmp);

                    $createDocumentNumber = true;

                    $documentNumber = $this->settingRepositoryObj
                        ->getRunningNumber('consignment_return',$stockist->country_id, 0);

                } else if ($action == 'sendorderapproval'){

                    $type = 'order';

                    //Check order amount whether excess deposit limit
                    if(floatval($orderReturnRecord->total_gmp) > floatval($depositLimitBalance)){

                        $depositBalanceUpdate = false;

                        $statusUpdateAction = false;

                    } else {

                        $depositLimitBalance -= floatval($orderReturnRecord->total_gmp);

                        $createDocumentNumber = true;

                        $documentNumber = $this->settingRepositoryObj
                            ->getRunningNumber('consignment_order',$stockist->country_id, 0);
                    }
                }

                if($depositBalanceUpdate){
                    $stockistMasterDepositRecord->update([
                        'deposit_limit' => $depositLimitBalance,
                        'updated_by' => Auth::id()
                    ]);

                    //Insert Consignment Transaction
                    $this->stockistRepositoryObj
                        ->createConsignmentTransaction($consignmentOrderReturnId, $type);

                    //Insert or Update Stockist Consignment Master Product
                    $this->stockistRepositoryObj
                        ->updateConsignmentProduct($consignmentOrderReturnId, $type);
                }
            }

            if($statusUpdateAction){

                $updateData = [
                    'updated_by' => Auth::id()
                ];

                if($createDocumentNumber == true){
                    $updateData['document_number'] = $documentNumber;
                }

                $updateData['status_id'] = $statusUpdateId;
                $updateData['action_by'] = Auth::id();
                $updateData['action_at'] = date('Y-m-d H:i:s');

                $orderReturnRecord->update($updateData);

            } else {
                return [
                    'step_pending' => true,
                    'event_detail' => $this->stockistRepositoryObj
                        ->consignmentOrderReturnDetails($consignmentOrderReturnId),
                ];
            }
        }

        return $this->stockistRepositoryObj
            ->consignmentOrderReturnDetails($consignmentOrderReturnId);
    }
}