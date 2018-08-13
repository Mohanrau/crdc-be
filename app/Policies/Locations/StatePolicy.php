<?php
namespace App\Policies\Locations;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Locations\State,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class StatePolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * StatePolicy constructor.
     *
     * @param State $model
     */
    public function __construct(State $model)
    {
        $this->modelObj = $model;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param State $model
     * @return bool
     */
    public function view(User $user ,State $model)
    {
        return true;
    }
}
