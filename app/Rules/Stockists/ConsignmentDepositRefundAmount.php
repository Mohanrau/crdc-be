<?php
namespace App\Rules\Stockists;

use App\Interfaces\Stockists\StockistInterface;
use Illuminate\Contracts\Validation\Rule;

class ConsignmentDepositRefundAmount implements Rule
{
    private $stockistRepositoryObj, $stockistUserId, $depositRefundType;

    /**
     * ConsignmentDepositRefundAmount constructor.
     *
     * @param StockistInterface $stockistInterface
     * @param int $stockistUserId
     * @param string $depositRefundType
     */
    public function __construct(
        StockistInterface $stockistInterface,
        int $stockistUserId,
        string $depositRefundType
    )
    {
        $this->stockistRepositoryObj = $stockistInterface;

        $this->stockistUserId = $stockistUserId;

        $this->depositRefundType = $depositRefundType;
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
        $consignmentSettings = $this->stockistRepositoryObj
            ->validatesConsignmentDepositsRefunds($this->stockistUserId, $this->depositRefundType);

        $consignmentDepositSettings = $consignmentSettings['consignment_deposit_refund'];

        if($this->depositRefundType == 'deposit'){

            $minimumAmount = $consignmentDepositSettings['minimum_amount'];

            $maximumAmount = $consignmentDepositSettings['maximum_amount'];

            $result = ( floatval($value) >= floatval($minimumAmount)
                && floatval($value) <= floatval($maximumAmount)) ?
                    true : false;

        } else {

            $ratio = $consignmentDepositSettings['ratio'];

            $minimumCapping = $consignmentDepositSettings['minimum_capping'];

            $minimumCreditLimitCapping = $consignmentDepositSettings['minimum_credit_limit_capping'];

            $depositBalance = $consignmentDepositSettings['deposit_balance'];

            $depositCreditLimitBalance = $consignmentDepositSettings['deposit_limit'];

            $cappingValue = floatval($depositBalance) - floatval($value);

            $cappingCreditLimitValue = floatval($depositCreditLimitBalance) -
                (floatval($value) * floatval($ratio));

            $result = ((floatval($cappingValue) >= floatval($minimumCapping)) &&
                (floatval($cappingCreditLimitValue) >= floatval($minimumCreditLimitCapping))) ?
                    true : false;
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
        return ($this->depositRefundType == 'deposit') ?
            trans('message.consignment-transaction-message.consignment-deposit-amount-range') :
                trans('message.consignment-transaction-message.consignment-refund-capping-amount');
    }
}
