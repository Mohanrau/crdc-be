<?php
namespace App\Repositories\Locations;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\ResourceRepository,
    Interfaces\Locations\LocationInterface,
    Models\Locations\Location,
    Models\Locations\LocationTypes,
    Models\Locations\LocationAddresses,
    Models\Stockists\Stockist,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LocationRepository extends BaseRepository implements LocationInterface
{
    use AccessControl;

    private $locationTypesObj, $locationAddressObj, $stockistObj;

    /**
     * LocationRepository constructor.
     *
     * @param Location $model
     * @param LocationTypes $locationTypes
     * @param LocationAddresses $locationAddresses
     * @param Stockist $stockist
     */
    public function __construct(
        Location $model,
        LocationTypes $locationTypes,
        LocationAddresses $locationAddresses,
        Stockist $stockist
    )
    {
        parent::__construct($model);

        $this->locationTypesObj = $locationTypes;

        $this->locationAddressObj = $locationAddresses;

        $this->stockistObj = $stockist;

        $this->with = ['entity'];
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
        $data = $this->modelObj
            ->orderBy($orderBy, $orderMethod);

        //load the granted location give for the user if he back_office
        if (
            $this->isUser('back_office')
            or
            $this->isUser('stockist')
            or
            $this->isUser('stockist_staff')
        ){
            $data->whereIn('id', $this->getUserLocations());
        }

        //check if no relations required.
        if ($this->with != null) {
            $data = $data->with($this->with);
        }

        $totalRecords = collect(
            [
                'total' => $data->get()->count()
            ]
        );

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
        $location = Auth::user()
            ->createdBy($this->modelObj)
            ->create($data);

        //update location address-----------------------------------------------------------------------------------------
        if (!empty($data['address'])) {

            $addressData = $data['address'];

            $address = json_encode($addressData['address_data']);

            unset($addressData['address_data']);

            if (isset($addressData['id'])){
                $locationAddress = $this->locationAddressObj
                    ->find($addressData['id']);

                $locationAddress->update(array_merge($addressData, ['location_id' => $location->id, 'address_data' => $address]));
            } else {
                $this->locationAddressObj->create(array_merge($addressData, ['location_id' => $location->id, 'address_data' => $address]));
            }
        }
       
        return $this->find($location->id);
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

        $updateData->update(array_merge(['updated_by' => Auth::id()],$data));

        //update location address-----------------------------------------------------------------------------------------
        if (!empty($data['address'])) {

            $addressData = $data['address'];

            $address = json_encode($addressData['address_data']);

            unset($addressData['address_data']);

            if (isset($addressData['id'])){
                $locationAddress = $this->locationAddressObj
                    ->find($addressData['id']);

                $locationAddress->update(array_merge($addressData, ['address_data' => $address]));
            } else {
                $this->locationAddressObj->create(array_merge($addressData, ['address_data' => $address]));
            }
        }

        return $this->find($id);
    }

    /**
     * get specified master with all masterData related
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function find(int $id)
    {
        $location = $this->modelObj->findOrFail($id);

        $location['address'] = $this->locationAddressObj
            ->where('location_id', $id)
            ->first();

        return $location;
    }
    
    /**
     * get stock locations by locationId
     *
     * @param int $locationId
     * @return mixed
     */
    public function getStockLocationsByLocation(int $locationId)
    {
        return $this->modelObj->with('stockLocations')->findOrFail($locationId);
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

    /**
     * get locations types filtered by the following parameters
     *
     * @param string $code
     * @param string $name
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getLocationsTypesByFilters(
        string $code = '',
        string $name = '',
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->locationTypesObj;
        if ($code != '') {
            $data = $data->where('code', 'like','%' . $code . '%');
        }
        if ($name != '') {
            $data = $data->where('name', 'like','%' . $name . '%');
        }
        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );
        $data = $data->orderBy($orderBy, $orderMethod);
        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get locations address filtered by the following parameters
     *
     * @param int $countryId
     * @param int $stateId
     * @param int $locationId
     * @param array $locationTypeCodes
     * @param array $relations
     * @param bool $stockists_ibs_online
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getLocationsAddressesByFilters(
        int $countryId = 0,
        int $stateId = 0,
        int $locationId = 0,
        array $locationTypeCodes = [],
        array $relations = [],
        bool $stockists_ibs_online = false,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->locationAddressObj;

        if (count($relations) > 0) {
            $data = $data->with($relations);
        }

        if ($countryId > 0) {
            $data = $data->where('country_id', $countryId);
        }

        if ($stateId > 0) {
            $data = $data->where('state_id', $stateId);
        }

        if ($locationId > 0) {
            $data = $data->where('location_id', $locationId);
        }

        if (count($locationTypeCodes) > 0) {
            $data = $data->whereIn('location_id', 
                $this->modelObj
                    ->whereIn('location_types_id', 
                        $this->locationTypesObj
                            ->whereIn('code', $locationTypeCodes)
                            ->pluck('id')
                            ->toArray()
                    )
                    ->pluck('id')
                    ->toArray()
            );
        }

        if ($stockists_ibs_online) {
            $data = $data->whereIn('location_id', 
                $this->modelObj->whereIn('code', 
                    $this->stockistObj
                        ->where('ibs_online', '1')
                        ->pluck('stockist_number')
                        ->map(function ($item, $key) {
                            return '' . $item;
                        })                    
                        ->toArray()
                )
                ->pluck('id')
                ->toArray()
            );
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data = $data->orderBy($orderBy, $orderMethod);

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }
}