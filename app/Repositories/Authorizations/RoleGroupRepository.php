<?php
namespace App\Repositories\Authorizations;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Authorizations\RoleGroupInterface,
    Models\Authorizations\RoleGroup,
    Models\Locations\Country,
    Repositories\BaseRepository
};
use Illuminate\Support\Facades\Auth;

class RoleGroupRepository extends BaseRepository implements RoleGroupInterface
{
    use ResourceRepository{
        create as oldCreate;
        getAll as oldGetAll;
    }

    private $countryObj;

    /**
     * RoleGroupRepository constructor.
     *
     * @param RoleGroup $model
     * @param Country $country
     */
    public function __construct(
        RoleGroup $model,
        Country $country
    )
    {
        parent::__construct($model);

        $this->countryObj = $country;

        $this->with = ['userType','createdBy', 'updatedBy'];
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
    public function getAll(
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $totalRecords = collect(
            [
                'total' => $this->modelObj->orderBy($orderBy, $orderMethod)->count()
            ]
        );

        $data = $this->modelObj
            ->with(['userType',
                'createdBy',
                'updatedBy'
            ])
            ->orderBy($orderBy, $orderMethod);

        //check if no relations required.
        if ($this->with != null) {
            $data = $data->with($this->with);
        }

        $data = ($paginate) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        collect($data)->each(function ($roleGroup){
           $roleGroup->roles = $roleGroup->roles()->pluck('id')->toArray();
        });

        return $totalRecords -> merge(['data' => $data]);
    }


    /**
     * store new role Group
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $data = Auth::user()->createdBy($this->modelObj)->create($data);

        return $this->find($data->id);
    }

    /**
     * get one user by id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        $data = $this->modelObj->findOrFail($id);

        $data->user_type = $data->userType()->get();

        $data->roles = $data->roles()->get();

        $prefix = explode('-', $data->title);

        $data->country_id = optional($this->countryObj
            ->where('code', $prefix[0])
            ->first())->id;

        $data->prefix = $prefix[0];    

        return $data;
    }

    /**
     * attach role to roleGroup
     *
     * @param array $roles
     * @param int $roleGroupId
     * @return array|\Illuminate\Contracts\Translation\Translator|null|string
     */
    public function attachRoles(array $roles, int $roleGroupId)
    {
        $roleGroup = $this->find($roleGroupId);

        $roleGroup->roles()->sync($roles);

        return $this->find($roleGroupId);
    }
}