<?php
namespace App\Rules\Stockists;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Stockists\ConsignmentDepositRefund;
use Illuminate\Contracts\Validation\Rule;

class ConsignmentDepositReturnUpdateValidate implements Rule
{
    private
        $masterRepositoryObj,
        $consignmentDepositRefundObj,
        $consignmentDepositReturnId,
        $updateType,
        $consignmentDepositRefundStatusConfigCodes,
        $consignmentDepositRefundTypeConfigCodes;

    /**
     * ConsignmentDepositReturnUpdateValidate constructor.
     *
     * @param MasterInterface $masterInterface
     * @param ConsignmentDepositRefund $consignmentDepositRefund
     * @param int $consignmentDepositReturnId
     * @param $updateType
     */
    public function __construct(
        MasterInterface $masterInterface,
        ConsignmentDepositRefund $consignmentDepositRefund,
        int $consignmentDepositReturnId,
        $updateType
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->consignmentDepositRefundObj = $consignmentDepositRefund;

        $this->consignmentDepositReturnId = $consignmentDepositReturnId;

        $this->updateType = $updateType;

        $this->consignmentDepositRefundStatusConfigCodes =
            config('mappings.consignment_deposit_and_refund_status');

        $this->consignmentDepositRefundTypeConfigCodes =
            config('mappings.consignment_deposit_and_refund_type');
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

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey([
            'consignment_deposit_and_refund_status', 'consignment_deposit_and_refund_type'
        ]);

        $depositRefundStatus = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_status']
                ->pluck('id','title')
                ->toArray()
        );

        $depositRefundType = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_type']
                ->pluck('id','title')
                ->toArray()
        );

        $depositRecord = $this->consignmentDepositRefundObj->find($value);

        if($depositRecord){

            if($this->updateType == "cancel_deposit"){

                $initialStatusId = $depositRefundStatus[$this->consignmentDepositRefundStatusConfigCodes['initial']];

                $depositTypeId = $depositRefundType[$this->consignmentDepositRefundTypeConfigCodes['deposit']];

                $result = ($depositRecord->status_id == $initialStatusId && $depositRecord->type_id == $depositTypeId) ?
                    true : false;
            }
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
        return trans('message.consignment-transaction-message.invalid-update-id');
    }
}
