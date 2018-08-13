<?php
namespace App\Policies\Authorization;

use App\Helpers\Traits\GeneralPolicy;
use App\Models\Authorizations\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization, GeneralPolicy;

    private
        $modelObj,
        $moduleName
    ;

    /**
     * LocationPolicy constructor.
     *
     * @param Role $model
     */
    public function __construct(Role $model)
    {
        $this->modelObj = $model;

        $this->moduleName = 'roles';
    }
}
