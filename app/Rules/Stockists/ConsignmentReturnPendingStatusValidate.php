<?php
namespace App\Rules\Stockists;

use App\Interfaces\Stockists\StockistInterface;
use Illuminate\Contracts\Validation\Rule;

class ConsignmentReturnPendingStatusValidate implements Rule
{
    private $stockistRepositoryObj, $orderReturnType;

    /**
     * ConsignmentReturnPendingStatusValidate constructor.
     *
     * @param StockistInterface $stockistInterface
     * @param string $orderReturnType
     */
    public function __construct(
        StockistInterface $stockistInterface,
        string $orderReturnType
    )
    {
        $this->stockistRepositoryObj = $stockistInterface;

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

        if($this->orderReturnType == 'return'){

            $validateDetail = $this->stockistRepositoryObj
                ->validatesConsignmentReturn($value);

            $result = $validateDetail['is_allow_consignment_return'];
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
        return trans('message.consignment-transaction-message.consignment-return-under-pending-status');
    }
}
