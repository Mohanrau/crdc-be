<?php
namespace App\Listeners\Stockists;

use App\{
    Events\Stockists\ConsignmentDepositRefundEvents,
    Interfaces\Masters\MasterInterface,
    Interfaces\Settings\SettingsInterface,
    Interfaces\Stockists\StockistInterface,
    Models\Stockists\Stockist,
    Models\Stockists\StockistDepositSetting,
    Models\Stockists\ConsignmentDepositRefund
};
use Illuminate\{
    Queue\InteractsWithQueue,
    Contracts\Queue\ShouldQueue,
    Support\Facades\Auth,
    Support\Facades\Config
};

class ConsignmentDepositRefundListener
{
    private
        $masterRepositoryObj,
        $settingRepositoryObj,
        $stockistRepositoryObj,
        $stockistObj,
        $stockistDepositSettingObj,
        $consignmentDepositRefundObj,
        $consignmentDepositRefundTypeConfigCodes,
        $consignmentDepositRefundStatusConfigCodes,
        $consignmentRefundVerificationStatusConfigCodes;

    /**
     * Create the event listener.
     *
     * @param StockistInterface $stockistInterface
     * @param MasterInterface $masterInterface
     * @param SettingsInterface $settingsInterface
     * @param Stockist $stockist
     * @param StockistDepositSetting $stockistDepositSetting
     * @param ConsignmentDepositRefund $consignmentDepositRefund
     * @return void
     */
    public function __construct(
        StockistInterface $stockistInterface,
        MasterInterface $masterInterface,
        SettingsInterface $settingsInterface,
        Stockist $stockist,
        StockistDepositSetting $stockistDepositSetting,
        ConsignmentDepositRefund $consignmentDepositRefund
    )
    {
        $this->stockistRepositoryObj = $stockistInterface;

        $this->masterRepositoryObj = $masterInterface;

        $this->settingRepositoryObj = $settingsInterface;

        $this->stockistObj = $stockist;

        $this->stockistDepositSettingObj = $stockistDepositSetting;

        $this->consignmentDepositRefundObj = $consignmentDepositRefund;

        $this->consignmentDepositRefundTypeConfigCodes =
            Config::get('mappings.consignment_deposit_and_refund_type');

        $this->consignmentDepositRefundStatusConfigCodes =
            Config::get('mappings.consignment_deposit_and_refund_status');

        $this->consignmentRefundVerificationStatusConfigCodes =
            Config::get('mappings.consignment_refund_verification_status');
    }

    /**
     * Handle the event.
     *
     * @param ConsignmentDepositRefundEvents $event
     * @return mixed
     */
    public function handle(ConsignmentDepositRefundEvents $event)
    {
        $consignmentDepositRefundId = $event->consignmentDepositRefundId;

        $action = strtolower($event->stepInput['trigger']);

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_deposit_and_refund_type',
                'consignment_deposit_and_refund_status',
                'consignment_refund_verification_status'
            )
        );

        $depositRefundTypeIds = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_type']->pluck('id','title')->toArray()
        );

        $depositRefundStatusIds = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_status']->pluck('id','title')->toArray()
        );

        $refundVerificationIds = array_change_key_case(
            $masterSettingsDatas['consignment_refund_verification_status']->pluck('id','title')->toArray()
        );

        $depositRefundQuery = $this->consignmentDepositRefundObj
            ->where('id', $consignmentDepositRefundId);

        $invalidAction = false;

        switch ($action) {
            case "senddepositapproval":
                $depositRefundQuery->where('type_id',
                        $depositRefundTypeIds[
                            $this->consignmentDepositRefundTypeConfigCodes['deposit']
                        ]
                    )
                    ->where('status_id',
                        $depositRefundStatusIds[
                            $this->consignmentDepositRefundStatusConfigCodes['pending']
                        ]
                    )
                    ->whereNull('verification_status_id');

                $statusUpdate = $this->consignmentDepositRefundStatusConfigCodes['approved'];
                $verificationStatus = NULL;
                break;

            case "senddepositdecline":
                $depositRefundQuery->where('type_id',
                        $depositRefundTypeIds[
                            $this->consignmentDepositRefundTypeConfigCodes['deposit']
                        ]
                    )
                    ->where('status_id',
                        $depositRefundStatusIds[
                            $this->consignmentDepositRefundStatusConfigCodes['pending']
                        ]
                    )
                    ->whereNull('verification_status_id');

                $statusUpdate = $this->consignmentDepositRefundStatusConfigCodes['rejected'];
                $verificationStatus = NULL;
                break;

            case "sendrefundverified":
                $depositRefundQuery->where('type_id',
                        $depositRefundTypeIds[
                            $this->consignmentDepositRefundTypeConfigCodes['refund']
                        ]
                    )
                    ->where('status_id',
                        $depositRefundStatusIds[
                            $this->consignmentDepositRefundStatusConfigCodes['pending']
                        ]
                    )
                    ->where('verification_status_id',
                        $refundVerificationIds[
                            $this->consignmentRefundVerificationStatusConfigCodes['pending']
                        ]
                    );

                $statusUpdate = NULL;
                $verificationStatus = $this->consignmentRefundVerificationStatusConfigCodes['verified'];
                break;

            case "sendrefundrejected":
                $depositRefundQuery->where('type_id',
                        $depositRefundTypeIds[
                            $this->consignmentDepositRefundTypeConfigCodes['refund']
                        ]
                    )
                    ->where('status_id',
                        $depositRefundStatusIds[
                            $this->consignmentDepositRefundStatusConfigCodes['pending']
                        ]
                    )
                    ->where('verification_status_id',
                        $refundVerificationIds[
                            $this->consignmentRefundVerificationStatusConfigCodes['pending']
                        ]
                    );

                $statusUpdate = $this->consignmentDepositRefundStatusConfigCodes['rejected'];
                $verificationStatus = $this->consignmentRefundVerificationStatusConfigCodes['rejected'];
                break;

            case "sendrefundapproval":
                $depositRefundQuery->where('type_id',
                        $depositRefundTypeIds[
                            $this->consignmentDepositRefundTypeConfigCodes['refund']
                        ]
                    )
                    ->where('status_id',
                        $depositRefundStatusIds[
                            $this->consignmentDepositRefundStatusConfigCodes['pending']
                        ]
                    )
                    ->where('verification_status_id',
                        $refundVerificationIds[
                            $this->consignmentRefundVerificationStatusConfigCodes['verified']
                        ]
                    );

                $statusUpdate = $this->consignmentDepositRefundStatusConfigCodes['approved'];
                $verificationStatus = NULL;
                break;

            case "sendrefunddecline":
                $depositRefundQuery->where('type_id',
                        $depositRefundTypeIds[
                            $this->consignmentDepositRefundTypeConfigCodes['refund']
                        ]
                    )
                    ->where('status_id',
                        $depositRefundStatusIds[
                            $this->consignmentDepositRefundStatusConfigCodes['pending']
                        ]
                    )
                    ->where('verification_status_id',
                        $refundVerificationIds[
                            $this->consignmentRefundVerificationStatusConfigCodes['verified']
                        ]
                    );

                $statusUpdate = $this->consignmentDepositRefundStatusConfigCodes['rejected'];
                $verificationStatus = NULL;
                break;

            default :
                $invalidAction = true;
                $statusUpdate = NULL;
                $verificationStatus = NULL;
                break;
        };

        $depositRefundRecord = $depositRefundQuery->first();

        if($depositRefundRecord && !$invalidAction){

            $createDocumentNumber = false;

            $stockist = $this->stockistObj->find($depositRefundRecord->stockist_id);

            //Update deposit balance
            if($action == 'senddepositapproval' || $action == 'sendrefundapproval'){

                $stockistMasterDepositRecord = $this->stockistDepositSettingObj
                    ->where('stockist_id',$depositRefundRecord->stockist_id)->first();

                $depositBalance = floatval($stockistMasterDepositRecord->deposit_balance);

                $depositLimitBalance = floatval($stockistMasterDepositRecord->deposit_limit);

                $depositBalanceUpdate = true;

                if($action == 'senddepositapproval'){

                    $type = 'deposit';

                    $depositBalance += floatval($depositRefundRecord->amount);

                    $depositLimitBalance += floatval($depositRefundRecord->credit_limit);

                } else if ($action == 'sendrefundapproval'){

                    $type = 'refund';

                    //Check refund amount whether excess deposit limit
                    if((floatval($depositRefundRecord->amount) > floatval($depositBalance))
                        && (floatval($depositRefundRecord->credit_limit) > floatval($depositLimitBalance))){

                            $depositBalanceUpdate = false;

                            //Update transaction status to reject when refund amount excess deposit limit
                            $statusUpdate = $this->consignmentDepositRefundStatusConfigCodes['rejected'];

                    } else {

                        $depositBalance -= floatval($depositRefundRecord->amount);

                        $depositLimitBalance -= floatval($depositRefundRecord->credit_limit);

                        $createDocumentNumber = true;

                        $documentNumber = $this->settingRepositoryObj
                            ->getRunningNumber('consignment_refund',$stockist->country_id, 0);
                    }
                }

                if($depositBalanceUpdate){
                    $stockistMasterDepositRecord->update([
                        'deposit_balance' => $depositBalance,
                        'deposit_limit' => $depositLimitBalance,
                        'updated_by' => Auth::id()
                    ]);

                    //Insert Consignment Transaction
                    $this->stockistRepositoryObj
                        ->createConsignmentTransaction($consignmentDepositRefundId, $type);
                }
            }

            $updateData = [
                'updated_by' => Auth::id()
            ];

            if($createDocumentNumber == true){
                $updateData['document_number'] = $documentNumber;
            }

            if(!empty($statusUpdate)){
                $updateData['status_id'] = $depositRefundStatusIds[$statusUpdate];
                $updateData['action_by'] = Auth::id();
                $updateData['action_at'] = date('Y-m-d H:i:s');
            }

            if(!empty($verificationStatus)){
                $updateData['verification_status_id'] = $refundVerificationIds[$verificationStatus];
                $updateData['verified_by'] = Auth::id();
                $updateData['verified_at'] = date('Y-m-d H:i:s');
            }

            $depositRefundRecord->update($updateData);
        }

        return $this->stockistRepositoryObj
            ->consignmentDepositsRefundsDetails($consignmentDepositRefundId);
    }
}