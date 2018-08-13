<?php
namespace App\Helpers\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use App\Models\{
    Products\Product,
    Products\ProductPrice,
    Kitting\Kitting,
    Kitting\KittingPrice
};

class ProductKitting implements Arrayable
{
    const PRODUCT = 'product';
    const KITTING = 'kitting';

    protected
        $productOrKitting,
        $price
    ;

    /**
     * StatusMessage constructor.
     *
     * Creates an value object with a productOrKitting and message
     * @param Product|Kitting $productOrKitting
     * @param ProductPrice|KittingPrice $price
     * @throws \Exception
     */
    public function __construct ($productOrKitting, $price) {
        switch (get_class($productOrKitting)) {
            case (Product::class) :
                $this->productOrKitting = $productOrKitting;

                break;
            case (Kitting::class) :
                $this->productOrKitting = $productOrKitting;

                break;
            default :
                throw new \Exception('Unknown product type');
        }

        $this->price = $price;
    }

    /**
     * @return Kitting|Product
     */
    public function getProductKitting ()
    {
        return $this->productOrKitting;
    }

    /**
     * @return KittingPrice|ProductPrice
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @param array $productOrKitting
     * @param array $price
     * @param string $type self::PRODUCT|self::KITTING
     *
     * @return ProductKitting
     * @throws \Exception
     */
    public static function fill(array $productOrKitting, array $price, string $type) {
        switch ($type) {
            case (self::PRODUCT) :
                $productOrKittingObj = new Product;
                $priceObj = new ProductPrice;

                break;
            case (self::KITTING) :
                $productOrKittingObj = new Kitting;
                $priceObj = new KittingPrice;

                break;
            default :
                throw new \Exception('Unknown type ' . $type);
        }

        $productOrKittingObj->fill($productOrKitting);

        // hydrate general settings
        $productOrKittingObj->{$type . 'GeneralSetting'} = $productOrKittingObj->{$type . 'GeneralSetting'}()
                                                  ->hydrate(
                                                      isset($productOrKitting['general_settings'])
                                                          ? $productOrKitting['general_settings']
                                                          : (
                                                              isset($productOrKitting[$type. '_general_settings'])
                                                                  ? $productOrKitting[$type. '_general_settings']
                                                                  : []
                                                            )
                                                  );

        // hydrate prices
        $productOrKittingObj->{$type . 'Price' .  ($type === self::PRODUCT ? 's' : '')} =
            $productOrKittingObj->{$type . 'Price' . ($type === self::PRODUCT ? 's' : '')}()
                    ->hydrate($productOrKitting['prices']);

        $productOrKittingPrice = new $priceObj;

        $productOrKittingPrice->fill($price);

        return new ProductKitting($productOrKittingObj, $productOrKittingPrice);
    }

    /**
     * @return array
     */
    public function toArray() {
        return [
            "product_kitting" => $this->productOrKitting,
            "price" => $this->price
        ];
    }
}