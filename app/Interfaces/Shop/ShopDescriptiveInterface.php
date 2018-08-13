<?php
namespace App\Interfaces\Shop;

interface ShopDescriptiveInterface
{
    /**
     * List of categories that has products
     *
     * @return \App\Models\Products\ProductCategory[]
     */
    public function getShopCategories();
}