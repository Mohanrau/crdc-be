<?php
namespace App\Rules\Stockists;

use App\Interfaces\Stockists\StockistInterface;
use Illuminate\Contracts\Validation\Rule;

class ConsignmentReturnProductValidate implements Rule
{
    private
        $stockistRepositoryObj,
        $stockistUserId,
        $orderReturnType,
        $productTitle,
        $productSku,
        $availableQuantity;

    /**
     * ConsignmentReturnProductValidate constructor.
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

        if($this->orderReturnType == 'return'){

            $stockistDetail = $this->stockistRepositoryObj
                ->stockistDetails($this->stockistUserId);

            $stockistId = $stockistDetail['stockist_data']['details']['id'];

            $productId = $value['product_id'];

            $productQuantity = $value['quantity'];

            $productValidation = $this->stockistRepositoryObj
                ->validatesConsignmentReturnProduct($stockistId, $productId);

            $this->productTitle = $productValidation['product_name'];

            $this->productSku = $productValidation['product_sku'];

            $this->availableQuantity = $productValidation['available_quantity'];

            $result = (intval($this->availableQuantity) >= intval($productQuantity)) ?
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
        return __('message.consignment-transaction-message.un-available-quantity-return-product', [
            'name' => $this->productTitle,
            'sku' => $this->productSku,
            'quantity' => $this->availableQuantity
        ]);
    }
}
