<?php
namespace App\Rules\Stockists;

use App\Interfaces\Stockists\StockistInterface;
use Illuminate\Contracts\Validation\Rule;

class ConsignmentOrderTotalGmp implements Rule
{
    private $stockistRepositoryObj, $stockistUserId, $orderReturnType;

    /**
     * ConsignmentOrderTotalGmp constructor.
     *
     * @param StockistInterface $stockistInterface
     * @param int $stockistUserId
     * @param string $orderReturnType
     */
    public function __construct(
        StockistInterface $stockistInterface,
        int $stockistUserId,
        string $orderReturnType
    )
    {
        $this->stockistRepositoryObj = $stockistInterface;

        $this->stockistUserId = $stockistUserId;

        $this->orderReturnType = $orderReturnType;
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

        if($this->orderReturnType == 'order'){

            $stockistDetail = $this->stockistRepositoryObj
                ->stockistDetails($this->stockistUserId);

            $depositLimit = $stockistDetail['stockist_data']['deposits']['deposit_limit'];

            $result = (floatval($value) > floatval($depositLimit)) ? false : true;
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
        return trans('message.consignment-transaction-message.consignment-order-deposit-limit-amount');
    }
}
