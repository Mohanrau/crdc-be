<?php
namespace App\Policies\Users;

use App\{
    Helpers\Traits\GeneralPolicy,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization, GeneralPolicy;

    private
        $modelObj,
        $moduleName
    ;

    /**
     * UserPolicy constructor.
     *
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->modelObj = $model;

        $this->moduleName = 'users';
    }
}
