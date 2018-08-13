<?php
namespace App\Rules\Product;

use App\Models\Products\Product;
use Illuminate\Contracts\Validation\Rule;

class ProductAvailableInCountry implements Rule
{
    private $productObj, $countryId, $productTitle, $productSku, $isSku;

    /**
     * ProductAvailableInCountry constructor.
     *
     * @param Product $product
     * @param int $countryId
     */
    public function __construct(Product $product, int $countryId, bool $sku = false)
    {
        $this->productObj = $product;

        $this->countryId = $countryId;

        $this->isSku = $sku;
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
        if($this->isSku)
        {
            $product = $this->productObj->where('sku', $value)->firstOrFail();
        }
        else
        {
            $product = $this->productObj->findOrFail($value);
        }

        $productAvailable = $product->productAvailableInCountry($this->countryId)->count();

        if ($productAvailable === 0){
            $this->productTitle = $product->name;

            $this->productSku = $product->sku;
        }

        return $productAvailable === 1;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.product.un-available-in-country', [
            'name' => $this->productTitle,
            'sku' => $this->productSku
        ]);
    }
}
