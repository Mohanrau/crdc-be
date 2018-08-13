<?php
namespace App\Policies\Modules;

use App\Helpers\Traits\AllowedPolicy;
use App\Models\{
    Users\User,
    Modules\Module
};
use Illuminate\Auth\Access\HandlesAuthorization;

class ModulesPolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * CityPolicy constructor.
     *
     * @param Module $model
     */
    public function __construct(Module $model)
    {
        $this->modelObj = $model;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param Module $model
     * @return bool
     */
    public function view(User $user ,Module $model)
    {
        return true;
    }
}
