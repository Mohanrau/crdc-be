<?php
namespace App\Policies\Locations;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Locations\Country,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class CountryPolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * CountryPolicy constructor.
     *
     * @param Country $model
     */
    public function __construct(Country $model)
    {
        $this->modelObj = $model;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param Country $model
     * @return bool
     */
    public function view(User $user, Country $model)
    {
        return true;
    }
}
