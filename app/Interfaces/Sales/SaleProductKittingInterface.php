<?php
namespace App\Interfaces\Sales;

interface SaleProductKittingInterface
{
    /**
     * Get Products
     *
     * @return \App\Helpers\ValueObjects\SaleProductKitting[]
     */
    public function getProducts();

    /**
     * Get Kitting
     *
     * @return \App\Helpers\ValueObjects\SaleProductKitting[]
     */
    public function getKitting();
}