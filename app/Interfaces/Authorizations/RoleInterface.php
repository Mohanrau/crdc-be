<?php
namespace App\Interfaces\Authorizations;

use App\Interfaces\BaseInterface;

interface RoleInterface extends BaseInterface
{
    /**
     * get role details for a given id
     *
     * @param int $roleId
     * @return mixed
     */
    public function roleDetails(int $roleId);

    /**
     * create or update role
     *
     * @param array $data
     * @param int|null $roleId
     * @return mixed
     */
    public function createOrUpdate(array $data, int $roleId = null);
}