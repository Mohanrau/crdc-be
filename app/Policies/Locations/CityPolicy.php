<?php
namespace App\Policies\Locations;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Locations\City,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class CityPolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * CityPolicy constructor.
     *
     * @param City $model
     */
    public function __construct(City $model)
    {
        $this->modelObj = $model;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param City $model
     * @return bool
     */
    public function view(User $user ,City $model)
    {
        return true;
    }
}
