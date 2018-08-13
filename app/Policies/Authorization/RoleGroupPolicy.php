<?php
namespace App\Policies\Authorization;

use App\Helpers\Traits\GeneralPolicy;
use App\Models\Authorizations\RoleGroup;
use Illuminate\Auth\Access\HandlesAuthorization;

class RoleGroupPolicy
{
    use HandlesAuthorization, GeneralPolicy;

    private
        $modelObj,
        $moduleName;

    /**
     * RoleGroupPolicy constructor.
     *
     * @param RoleGroup $model
     */
    public function __construct(RoleGroup $model)
    {
        $this->modelObj = $model;

        $this->moduleName = 'role.groups';
    }
}
