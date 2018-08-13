<?php
namespace App\Repositories\Locations;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Locations\EntityInterface,
    Repositories\BaseRepository,
    Models\Locations\Entity
};
use Illuminate\Database\Eloquent\Model;

class EntityRepository extends BaseRepository implements EntityInterface
{
    use ResourceRepository;

    /**
     * EntityRepository constructor.
     *
     * @param Entity $model
     */
    public function __construct(Entity $model)
    {
        parent::__construct($model);

        $this->with = ['country','locations'];
    }

    /**
     * get specified master with all masterData related
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

}