<?php
namespace App\Repositories\Authorizations;

use App\{
    Helpers\Traits\ResourceRepository, 
    Interfaces\Authorizations\RoleInterface, 
    Models\Authorizations\Permission, 
    Models\Authorizations\Role, 
    Models\Authorizations\RoleGroup, 
    Models\Locations\Country, 
    Repositories\BaseRepository
};
use Illuminate\{
    Database\Eloquent\Model,
    Support\Facades\Auth
};

class RoleRepository extends BaseRepository implements RoleInterface
{
    use ResourceRepository{
        create as oldCreate;
        getAll as oldGetAll;
    }

    private
        $permissionObj,
        $roleGroupObj,
        $countryObj
    ;

    /**
     * RoleRepository constructor.
     *
     * @param Role $model
     * @param RoleGroup $roleGroup
     * @param Permission $permission
     * @param Country $country
     */
    public function __construct(
        Role $model,
        RoleGroup $roleGroup,
        Permission $permission,
        Country $country
    )
    {
        parent::__construct($model);

        $this->roleGroupObj = $roleGroup;

        $this->permissionObj = $permission;

        $this->countryObj = $country;

        $this->with = ['permissions','createdBy','updatedBy'];
    }

    /**
     * get all records
     *
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAll(int $paginate = 0, string $orderBy = 'id', string $orderMethod = 'desc', int $offset = 0)
    {
        $totalRecords = collect(
            [
                'total' => $this->modelObj->orderBy($orderBy, $orderMethod)->count()
            ]
        );

        $data = $this->modelObj
            ->orderBy($orderBy, $orderMethod);

        //check if no relations required.
        if ($this->with != null) {
            $data = $data->with($this->with);
        }

        $data = ($paginate) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        collect($data)->each(function ($item){
            $item->country_id = $item->countries()->first()->id;

            $item->prefix =  explode('-', $item->label)[0];
        });

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get one user by id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * create or update role with permissions
     *
     * @param array $data
     * @param int|null $roleId
     * @return array|mixed
     */
    public function createOrUpdate(array $data, int $roleId = null)
    {
        if (is_null($roleId)){
            //create new role
            $role = $this->modelObj->create([
                'label' => $data['label'],
                'name' => $data['label'],
                'active' => $data['active']
            ]);
        }else{
            $role = $this->find($roleId);

            //create new role
            $role->update([
                'label' => $data['label'],
                'name' => $data['label'],
                'active' => $data['active']
            ]);
        }

        //attach role to roleGroup
        if (count($data['role_group_ids'])){
            //TODO check if user click sync = true, then attach that role to all users.

            $role->roleGroups()->sync($data['role_group_ids']);
        }

        //attach role to country
        $role->countries()->sync([$data['country_id']]);

        //TODO Sync the user locations if permission location.list or view disabled
        //attach permissions to role
        $role->permissions()->sync($data['permissions']['ids']);

        return $this->roleDetails($role->id);
    }

    /**
     * get role details for a given roleId
     *
     * @param int $roleId
     * @return array|mixed
     */
    public function roleDetails(int $roleId)
    {
        $role = $this->find($roleId);

        $country = $role->countries()->first();

        //get the roleGroups--------------------------------------------------------------------------------------------
        $roleGroups = $role->roleGroups()->pluck('id')->toArray();

        //get role permissions and matrix-------------------------------------------------------------------------------
        $permissions = $role->permissions()->get();

        return [
            'id' => $role->id,
            'label' => $role->label,
            'name' => $role->name,
            'prefix' => explode('-', $role->label)[0].'-',
            'country_id' => $country->id,
            'active' => $role->active,
            'role_group_ids' => $roleGroups,
            'permissions' => [
                'ids' => $permissions->pluck('id')
            ]
        ];
    }
}