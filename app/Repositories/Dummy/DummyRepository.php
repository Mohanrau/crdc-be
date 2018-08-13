<?php
namespace App\Repositories\Dummy;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Dummy\DummyInterface,
    Models\Dummy\Dummy,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;

class DummyRepository extends BaseRepository implements DummyInterface
{
    use ResourceRepository;

    /**
     * DummyRepository constructor.
     * @param Dummy $model
     */
    public function __construct(Dummy $model)
    {
        parent::__construct($model);
    }

    /**
     * get one dummy details
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get dummy filters by a given data
     *
     * @param int $countryId
     * @param int $isLingerie
     * @param string $dummyData
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return static
     */
    public function getDummyFilters(
        int $countryId,
        int $isLingerie = 2,
        string $dummyData = '',
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->where('dummies.country_id', $countryId);

        if ($isLingerie != 2) {
            $data->where('dummies.is_lingerie', $isLingerie);
        }

        if ($dummyData != '') {
            $data->where(function ($query) use ($dummyData) {
                $query->where('dummies.dmy_code', 'like', '%' . $dummyData . '%');
                $query->orWhere('dummies.dmy_name', 'like', '%' . $dummyData . '%');
            });
        }

        $totalRecords = collect([
            'total' => $data->count()
        ]);

        $data->orderBy($orderBy, $orderMethod);

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords->merge(['data' => $data]);
    }

    /**
     * get dummy details for a given countryId and dummyId
     *
     * @param int $countryId
     * @param int $dummyId
     * @return array|string
     */
    public function dummyDetails(int $countryId, int $dummyId)
    {
        $data = '';

        if ($dummyId > 0){

            $dummy = $this->find($dummyId);

            //DummyInterface products----------------------------------------------------------------
            $dummyProducts = $dummy->dummyProducts()->get();

            $selectProductIds = $dummy->dummyProducts()->pluck('id');

            $data = [
                'dummy_id' => $dummy->id,
                'country_id' => $countryId,
                'dmy_name' => $dummy->dmy_name,
                'dmy_code' => $dummy->dmy_code,
                'is_lingerie' => $dummy->is_lingerie,
                'active' => $dummy->active,
                'dummy_products' => [
                    'product_ids' => $selectProductIds,
                    'products' => $dummyProducts
                ]
            ];

        } else {
            $data = [
                'dummy_id' => '',
                'country_id' => $countryId,
                'dmy_name' => '',
                'dmy_code' => '',
                'is_lingerie' => 0,
                'active' => 0,
                'dummy_products' => [
                    'product_ids' => [],
                    'products' => []
                ]
            ];
        }

        return $data;
    }

    /**
     * update or create new dummy
     *
     * @param array $data
     * @return array
     */
    public function createOrUpdate(array $data)
    {
        $dummy = ''; $errorBag = [];

        $dummyData = [
            'country_id' => $data['country_id'],
            'dmy_code' => strtoupper($data['dmy_code']),
            'dmy_name' => $data['dmy_name'],
            'is_lingerie' => $data['is_lingerie'],
            'active' => $data['active'],
        ];

        //update dummy if dummy_id not null-----------------------------------------------------------------------
        if ($data['dummy_id'] != null){

            $dummy = $this->find($data['dummy_id']);

            $dummy->update($dummyData);

        } else { //create new dummy
            $dummy = $this->modelObj->create($dummyData);
        }

        //dummy products section-------------------------------------------------------------------------------------
        if (!empty($data['dummy_products']['product_ids'])) {

            if ($dummy->dummyProducts()->count() > 0) {
                $dummy->dummyProducts()->sync($data['dummy_products']['product_ids']);
            } else {
                $dummy->dummyProducts()->attach($data['dummy_products']['product_ids']);
            }
        }

        return array_merge(['errors' => $errorBag ] ,
            $this->dummyDetails($data['country_id'], $dummy->id));
    }
}