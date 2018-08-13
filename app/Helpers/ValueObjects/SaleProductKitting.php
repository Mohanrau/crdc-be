<?php
namespace App\Helpers\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use App\Models\{
    Products\Product,
    Products\ProductPrice,
    Kitting\Kitting,
    Kitting\KittingPrice,
    Masters\MasterData
};

class SaleProductKitting extends ProductKitting implements Arrayable
{
    protected
        $saleType,
        $quantity
    ;

    /**
     * StatusMessage constructor.
     *
     * Creates an value object with a productOrKitting and message
     * @param Product|Kitting $productOrKitting
     * @param ProductPrice|KittingPrice $price
     * @param MasterData $saleType
     * @param int $quantity the quantity to purchase
     * @throws \Exception
     */
    public function __construct ($productOrKitting, $price, MasterData $saleType, int $quantity) {
        parent::__construct($productOrKitting, $price);

        $this->saleType = $saleType;

        $this->quantity = $quantity;
    }

    /**
     * Sale type master data
     *
     * @return MasterData
     */
    public function getSaleType () : MasterData
    {
        return $this->saleType;
    }

    /**
     * Returns the quantity
     *
     * @return int
     */
    public function getQuantity () : int
    {
        return $this->quantity;
    }

    /**
     * @return array
     */
    public function toArray() {
        return [
            "product_kitting" => $this->productOrKitting,
            "price" => $this->price,
            "sale_type" => $this->saleType,
            "quantity" => $this->quantity
        ];
    }
}