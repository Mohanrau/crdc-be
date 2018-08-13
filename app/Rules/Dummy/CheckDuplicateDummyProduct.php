<?php
namespace App\Rules\Dummy;

use App\Models\Dummy\Dummy;
use App\Models\Products\Product;
use Illuminate\Contracts\Validation\Rule;

class CheckDuplicateDummyProduct implements Rule
{
    private
        $productObj,
        $dummyObj,
        $countryId,
        $dummyId,
        $productTitle,
        $productSku;

    /**
     * CheckDuplicateDummyProduct constructor.
     *
     * @param Product $product
     * @param Dummy $dummy
     * @param int $countryId
     * @param int $dummyId
     */
    public function __construct
    (
        Product $product,
        Dummy $dummy,
        int $countryId,
        int $dummyId
    )
    {
        $this->productObj = $product;

        $this->dummyObj = $dummy;

        $this->countryId = $countryId;

        $this->dummyId = $dummyId;
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
        $product = $this->productObj->findOrFail($value);

        $productAvailable = $this->dummyObj
            ->whereHas('dummyProducts',
                function($dummyQuery) use ($value){
                    $dummyQuery->where('product_id', $value);
                }
            )
            ->where('country_id', $this->countryId)
            ->where('id', '!=', $this->dummyId)
            ->count();

        if ($productAvailable === 1){
            $this->productTitle = $product->name;

            $this->productSku = $product->sku;
        }

        return $productAvailable === 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.dummy.un-available-dummy-product-setup', [
            'name' => $this->productTitle,
            'sku' => $this->productSku
        ]);
    }
}
