<?php
namespace App\Policies\Masters;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Masters\Master,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterPolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * MasterPolicy constructor.
     *
     * @param Master $model
     */
    public function __construct(Master $model)
    {
        $this->modelObj = $model;
    }

    /**
     * Determine whether the user can view the resources.
     *
     * @param User $user
     * @param Master $model
     * @return bool
     */
    public function view(User $user, Master $model)
    {
        return true;
    }
}
