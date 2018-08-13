<?php
namespace App\Rules\Shop;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Shop\ProductAndKitting;

class ProductAndKittingAvailable implements Rule
{
    private
        $productAndKitting,
        $productId,
        $kittingId,
        $countryId,
        $locationId;

    /**
     * ProductAndKittingAvailable constructor.
     *
     * @param ProductAndKitting $productAndKitting
     * @param $countryId
     * @param $locationId
     * @param $productId
     * @param $kittingId
     */
    function __construct(ProductAndKitting $productAndKitting, $countryId, $locationId, $productId, $kittingId)
    {
        $this->productAndKitting = $productAndKitting;
        $this->productId = $productId;
        $this->kittingId = $kittingId;
        $this->countryId = $countryId;
        $this->locationId = $locationId;
    }

    /**
     * Validate the request
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     * @throws \App\Exceptions\Location\CountryAlreadySetException
     * @throws \Exception
     */
    public function passes($attribute, $value)
    {
        if ((!empty($this->productId) || !empty($this->kittingId))
            && !empty($this->locationId) && !empty($this->countryId)
        ) {
            $this->productAndKitting->setActiveOnly(1);
            // the model requires country to be set
            $this->productAndKitting->setCountry($this->countryId);
            // the model requires location to be set
            $this->productAndKitting->setLocation($this->locationId);
            // TODO: add sales type filter to product and kitting
            // filter by product or kitting
            (!empty($this->productId)) ?
                $this->productAndKitting->filterProductId([$this->productId])
                : $this->productAndKitting->filterKittingId([$this->kittingId]);
            return count($this->productAndKitting->get()) === 1;
        }
        return false;
    }

    /**
     * Returns the invalid error message
     *
     * @return array|null|string
     */
    public function message()
    {
        return __('message.product.un-available-in-shop');
    }
}
