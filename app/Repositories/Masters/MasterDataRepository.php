<?php
namespace App\Repositories\Masters;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Masters\MasterDataInterface,
    Models\Masters\Master,
    Models\Masters\MasterData,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;

class MasterDataRepository extends BaseRepository implements MasterDataInterface
{
    use ResourceRepository;

    protected $masterObj;

    /**
     * MasterDataRepository constructor.
     *
     * @param MasterData $model
     * @param Master $master
     */
    public function __construct(MasterData $model, Master $master)
    {
        parent::__construct($model);

        $this->masterObj = $master;

        $this->with = ['master'];
    }

    /**
     * get specified master with all masterData related
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find(int $id)
    {
        $data =  $this->modelObj->findOrFail($id);

        $data->master = $data->master()->get();

        return $data;
    }

    /**
     * get master data by masterId and keys
     *
     * @param int $masterId
     * @param string $key
     * @param array $data
     * @param array $columns
     * @return mixed
     */
    public function findByKeys(int $masterId, string $key = 'id', array $data, array $columns = [])
    {
        $data = $this->modelObj
            ->whereIn($key, $data)
            ->where('master_id', $masterId);

        return empty($columns) ? $data->get() : $data->get($columns);
    }
}