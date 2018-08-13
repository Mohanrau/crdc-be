<?php
namespace App\Policies\Products;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Products\ProductCategory,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductCategoryPolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * ProductCategoryPolicy constructor.
     *
     * @param ProductCategory $model
     */
    public function __construct(ProductCategory $model)
    {
        $this->modelObj = $model;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param ProductCategory $model
     * @return bool
     */
    public function view(User $user, ProductCategory $model)
    {
        return true;
    }
}
