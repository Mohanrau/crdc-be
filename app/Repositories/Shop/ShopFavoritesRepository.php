<?php
namespace App\Repositories\Shop;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Shop\ShopFavoriteInterface,
    Models\Shop\ProductAndKitting,
    Models\Shop\Favorites,
    Repositories\BaseRepository,
    Models\Users\User
};

class ShopFavoritesRepository extends BaseRepository implements ShopFavoriteInterface
{
    use ResourceRepository {
        create as baseCreate;
    }

    private $productAndKitting;

    /**
     * ShopFavoritesRepository constructor.
     * @param Favorites $model
     * @param ProductAndKitting $productAndKitting
     * @throws \Exception
     */
    public function __construct(Favorites $model, ProductAndKitting $productAndKitting)
    {
        parent::__construct($model);

        $this->productAndKitting = $productAndKitting;

        $this->productAndKitting->setActiveOnly(1);
    }

    public function find(int $id)
    {
        // TODO: Implement find() method.
    }

    /**
     * Creates a shop favorite item
     *
     * @param array $data
     * @return mixed
     */
    public function create (array $data) {
        $product = $this->modelObj->getFavoriteProductAndKitting(
            $data['user_id'],
            $data['product_id'] ?? null,
            $data['kitting_id'] ?? null
            );
        if ($product) {
            return $product;
        } else {
            return $this->baseCreate($data);
        }
    }

    /**
     * Retrieves favorite product and kitting for a given user
     *
     * @param int $userId
     * @return mixed
     */
    public function favoriteProductsAndKitting(int $userId)
    {
        $favorites = $this->modelObj->getFavoritesForUser($userId);

        return $favorites;
    }

    /**
     * Gets a users favorites
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserFavorites(User $user) {
        return $user->favorites()->with(["product", "kitting"])->get();
    }
}