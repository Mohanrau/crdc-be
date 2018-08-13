<?php
namespace App\Repositories\Modules;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Modules\ModuleInterface,
    Models\Authorizations\Permission,
    Models\Modules\Module,
    Models\Modules\Operation,
    Repositories\BaseRepository
};
use Illuminate\Support\Facades\Auth;

class ModuleRepository extends BaseRepository implements ModuleInterface
{
    protected $permissionObj, $operationObj;

    /**
     * ModuleRepository constructor
     * .
     * @param Module $model
     * @param Permission $permission
     * @param Operation $operation
     */
    public function __construct(Module $model, Permission $permission, Operation $operation)
    {
        parent::__construct($model);

        $this->permissionObj = $permission;

        $this->operationObj = $operation;

        $this->with = ['parent','childs.permissions.operation', 'permissions.operation'];
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
                'total' => $this->modelObj
                    ->where('parent_id', 0)
                    ->orderBy($orderBy, $orderMethod)->count()
            ]
        );

        $data = $this->modelObj
            ->where('parent_id', 0)
            ->orderBy($orderBy, $orderMethod);

        //check if no relations required.
        if ($this->with != null) {
            $data = $data->with($this->with);
        }

        $data = ($paginate) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

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
        $data = $this->modelObj->findOrFail($id);

        $data->operations = $data->operations()->pluck('id');

        return $data;
    }

    /**
     * create new module
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $module = Auth::user()->createdBy(Module::class)
            ->create(array_merge($data,
            [
                'name' => strtolower(
                    trim(str_replace(' ','_',$data['label']))
                )
            ])
        );

        //attach operations to module and permissions
        if (!empty($data['operations']))
        {
            $module->operations()->attach($data['operations']);

            $this->generateModulePermissions($module->id);
        }

        return $module;
    }

    /**
     * update one record by id
     *
     * @param array $data
     * @param int $id
     * @return mixed
     */
    public function update(array $data, int $id)
    {
        $module = $this->modelObj->findOrFail($id);

        $module->update($data);

        //todo remove the bellow line of code
        //Auth::user()->updatedBy(Module::class)->findOrFail($id)->update($data);

        //attach operations to module and permissions
        if (!empty($data['operations']))
        {
            //sync all the operation if updated
            $moduleOperations = $module->operations()->sync($data['operations']);

            $this->syncModulePermission($id, $moduleOperations);
        }

        return $module;
    }

    /**
     * Generate Module Permissions for a given ModuleId
     *
     * @param int $moduleId
     */
    private function generateModulePermissions(int $moduleId)
    {
        $module = $this->modelObj->findOrFail($moduleId);

        $operations = $module->operations()->get();

        //generate permissions for that module---------------
        if ($operations->isNotEmpty())
        {
            foreach ($operations as $operation)
            {
                $this->permissionObj->createPermission($module, $operation);
            }
        }
    }

    /**
     * sync module permission based on operations attached to that module
     *
     * @param int $moduleId
     * @param $moduleOperation
     */
    private function syncModulePermission(int $moduleId, $moduleOperation)
    {
        //check if new operation attached to module
        if (!empty($moduleOperation['attached']))
        {
            foreach ($moduleOperation['attached'] as $val)
            {
                $operation = $this->operationObj->find($val);

                $module = $this->modelObj->findOrFail($moduleId);

                $this->permissionObj->createPermission($module, $operation);
            }
        }

        //check if operation delete from that module
        if (!empty($moduleOperation['detached']))
        {
            foreach ($moduleOperation['detached'] as $val)
            {
                $this->permissionObj
                    ->where('module_id', $moduleId)
                    ->where('operation_id', $val)
                    ->first()
                    ->delete();
            }
        }
    }

    /**
    * delete data by id
    *
    * @param int $id
    * @return array
    */
    public function delete(int $id)
    {
        $deleteStatus = $this->modelObj->findOrFail($id)->delete();

        return ($deleteStatus) ?
            ['data' => trans('message.delete.success')] :
            ['data' => trans('message.delete.fail')];
    }
}