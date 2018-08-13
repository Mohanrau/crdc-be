<?php
namespace App\Rules\Stockists;

use App\Interfaces\Stockists\StockistInterface;
use Illuminate\Contracts\Validation\Rule;

class ValidateGenerateConsignmentRefund implements Rule
{
    private $stockistRepositoryObj;

    /**
     * ValidateGenerateConsignmentRefund constructor.
     *
     * @param StockistInterface $stockistInterface
     */
    public function __construct(
        StockistInterface $stockistInterface
    )
    {
        $this->stockistRepositoryObj = $stockistInterface;
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

            $stockist = $this->stockistRepositoryObj->find($value);

            if($stockist){

                $validateDetail = $this->stockistRepositoryObj->validatesConsignmentDepositsRefunds(
                    $stockist->stockist_user_id , 'refund');

                $result = $validateDetail['is_allow_consignment_refund'];

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
        return trans('message.consignment-transaction-message.block-consignment-return');
    }
}
