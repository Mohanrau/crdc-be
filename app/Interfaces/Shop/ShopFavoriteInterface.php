<?php
namespace App\Interfaces\Shop;

use App\Interfaces\BaseInterface;
use App\Models\Users\User;

interface ShopFavoriteInterface extends BaseInterface
{

    /**
     * Get user favorited kittings for country and location
     *
     * @param int $userId
     * @return mixed
     */
    public function favoriteProductsAndKitting(int $userId);

    /**
     * Rerturns teh users favorite products or kittings
     *
     * @param User $user
     * @return mixed
     */
    public function getUserFavorites(User $user);
}