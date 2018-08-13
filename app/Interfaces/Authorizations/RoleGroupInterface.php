<?php
namespace App\Interfaces\Authorizations;

use App\Interfaces\BaseInterface;

interface RoleGroupInterface extends BaseInterface
{
    /**
     * Attach Role(s) to roleGroup by roleGroupId
     *
     * @param array $roles
     * @param int $roleGroupId
     * @return mixed
     */
    public function attachRoles(array $roles, int $roleGroupId);
}