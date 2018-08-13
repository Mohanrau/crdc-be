<?php
namespace App\Interfaces\Shop;

use App\Helpers\Classes\UserIdentifier;

interface ShopSaleInterface
{
    /**
     * Create sales from cart
     *
     * @param UserIdentifier $userIdentifier
     * @param int $locationId
     * @param int $countryId
     * @param array $saleData
     * @return mixed
     */
    public function createCartSale(
        UserIdentifier $userIdentifier,
        int $locationId,
        int $countryId,
        array $saleData
    );

    /**
     * Create sales in general
     *
     * @param array $data
     * @param bool $orderCompleteStatus
     * @return mixed
     */
    public function createSale(
        array $data,
        bool $orderCompleteStatus = false
    );
}