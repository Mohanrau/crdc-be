<?php
namespace App\Repositories\Authorizations;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Authorizations\PermissionInterface,
    Models\Authorizations\Permission,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;

class PermissionRepository extends BaseRepository implements PermissionInterface
{
    use ResourceRepository;

    protected $modelObj;

    /**
     * Permission constructor.
     *
     * @param Permission $permission
     */
    public function __construct(Permission $permission)
    {
        $this->modelObj = $permission;
    }

}