<?php
namespace App\Repositories\Locations;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Locations\StateInterface,
    Models\Locations\State,
    Models\Locations\City,
    Models\Locations\Location,
    Models\Locations\LocationAddresses,
    Models\Stockists\Stockist,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;

class StateRepository extends BaseRepository implements StateInterface
{
    use ResourceRepository;

    private $cityObj, 
        $locationObj,
        $locationAddressesObj,
        $stockistObj;

    /**
     * StateRepository constructor.
     *
     * @param State $model
     * @param City $city
     * @param Location $location
     * @param LocationAddressess $locationAddresses
     * @param Stockist $stockist
     */
    public function __construct(
        State $model, 
        City $city, 
        Location $location,
        LocationAddresses $locationAddresses,
        Stockist $stockist
    )
    {
        parent::__construct($model);

        $this->cityObj = $city;

        $this->locationObj = $location;

        $this->locationAddressObj = $locationAddresses;

        $this->stockistObj = $stockist;

        $this->with = ['cities'];
    }

    /**
     * get state details for a given stateId
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        $data =  $this->modelObj->findOrFail($id);

        $data->cities = $data->cities()->get();

        return $data;
    }

    /**
     * get all states or subset based on pagination
     *
     * @param int $countryId
     * @param bool $activeStockist
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getStatesByFilters(
        int $countryId,
        bool $activeStockist,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj;

        $locationIdArray = [];
        
        if ($countryId > 0) {
            $data = $data
                ->where('country_id', $countryId);
        }

        if ($activeStockist) {
            $locationIdArray = $this->locationObj
                ->whereIn('code', 
                    $this->stockistObj
                        ->where('ibs_online', '1')
                        ->pluck('stockist_number')
                        ->map(function ($item, $key) {
                            return '' . $item;
                        })                    
                        ->toArray()
                )
                ->pluck('id')
                ->toArray();

            $stateIdArray = $this->locationAddressObj
                ->whereIn('location_id', $locationIdArray)
                ->pluck('state_id')
                ->toArray();

            $data = $data
                ->whereIn('id', $stateIdArray);
        }
        else {
            $data = $data->with(['cities']);
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data = $data->orderBy($orderBy, $orderMethod);
        
        $data = ($paginate) ?
            $data->offset($offset)->limit($paginate)->get() :
            $data->get();

        if ($activeStockist) {
            $cityIdArray = $this->locationAddressObj
                ->whereIn('location_id', $locationIdArray)
                ->pluck('city_id')
                ->toArray();
            
            $cities = $this->cityObj
                ->whereIn('id', $cityIdArray)
                ->get();
            
            $data = $data->map(function ($item, $key) use ($cities) {
                $filter = $cities
                    ->where('state_id', $item->id)
                    ->all();
                
                $stockistCities = [];

                foreach($filter as $key => $value) {
                    array_push($stockistCities, $value);
                }
                
                $item->cities = $stockistCities;
                
                return $item;
            });
        }
        
        return $totalRecords->merge(['data' => $data]);
    }
}