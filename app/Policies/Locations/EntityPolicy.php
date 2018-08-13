<?php
namespace App\Policies\Locations;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Locations\Entity,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class EntityPolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * EntityPolicy constructor.
     *
     * @param Entity $model
     */
    public function __construct(Entity $model)
    {
        $this->modelObj = $model;
    }

    /**
    * Determine whether the user can view the location.
    *
    * @param User $user
    * @param Entity $model
    * @return bool
    */
    public function view(User $user, Entity $model)
    {
        return true;
    }
}
