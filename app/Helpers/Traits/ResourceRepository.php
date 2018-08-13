<?php
namespace App\Helpers\Traits;

use Illuminate\Support\Facades\Auth;

trait ResourceRepository
{
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
     * store new role Group
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return Auth::user()->createdBy($this->modelObj)->create($data);
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
        $updateData = $this->modelObj->findOrFail($id);

        //TODO optimize this code to capture the updated by automatically
        //$updateData = Auth::user()->updatedBy($updateData)->update($data);

        $updateData->update(array_merge(['updated_by' => Auth::id()],$data));

        return $updateData;
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