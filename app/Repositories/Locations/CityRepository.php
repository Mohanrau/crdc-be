<?php
namespace App\Repositories\Locations;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Locations\CityInterface,
    Models\Locations\City,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;

class CityRepository extends BaseRepository implements CityInterface
{
    use ResourceRepository;

    /**
     * CityRepository constructor.
     *
     * @param City $model
     */
    public function __construct(City $model)
    {
        parent::__construct($model);
    }

    /**
     * get city details for a given cityId
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get stock locations list by cityId
     *
     * @param int $cityId
     * @return mixed
     */
    public function getStockLocationsByCity(int $cityId)
    {
        return $this->modelObj->with('stockLocation')->find($cityId);
    }
}