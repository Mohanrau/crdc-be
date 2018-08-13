<?php
namespace App\Interfaces\Shop;

use App\Interfaces\BaseInterface;
use App\Helpers\Classes\UserIdentifier;

interface ShopCartInterface extends BaseInterface
{
    /**
     * Get user cart products or kittings
     *
     * @param UserIdentifier $userIdentifier
     * @param int $countryId
     * @param int $locationId
     * @param int|null $orderForUserId
     * @return mixed
     */
    function userCartItems(
        UserIdentifier $userIdentifier,
        int $countryId,
        int $locationId,
        ?int $orderForUserId
    );

    /**
     * Clear user cart
     *
     * @param UserIdentifier $userIdentifier
     * @return mixed
     */
    function userCartClear(
        UserIdentifier $userIdentifier
    );
}