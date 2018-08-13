<?php
namespace App\Repositories\Locations;

use App\{
    Interfaces\Locations\ZoneInterface,
    Models\Locations\Zone,
    Models\Locations\Country,
    Models\Locations\State,
    Models\Locations\City,
    Models\Locations\ZonePostcode,
    Models\Locations\ZoneStockLocation,
    Repositories\BaseRepository
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class ZoneRepository extends BaseRepository implements ZoneInterface
{
    private $countryObj,
        $stateObj,
        $cityObj, 
        $zonePostcodeObj,
        $zoneStockLocationObj;

    /**
     * ZoneRepository constructor.
     *
     * @param Zone $model
     * @param Country $country
     * @param State $state
     * @param City $city
     * @param ZonePostcode $zonePostcode
     * @param ZoneStockLocation $zoneStockLocation
     */
    public function __construct(
        Zone $model, 
        Country $country, 
        State $state,
        City $city,
        ZonePostcode $zonePostcode,
        ZoneStockLocation $zoneStockLocation
    )
    {
        parent::__construct($model);

        $this->countryObj = $country;

        $this->stateObj = $state;

        $this->cityObj = $city;

        $this->zonePostcodeObj = $zonePostcode;

        $this->zoneStockLocationObj = $zoneStockLocation;
    }

    /**
     * get all zones or subset based on pagination
     *
     * @param string $code
     * @param string $name
     * @param int $isAllCountries
     * @param int $isAllStates
     * @param int $isAllCities
     * @param int $isAllPostcodes
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getZonesByFilters(
        string $code = null,
        string $name = null,
        int $isAllCountries = 2,
        int $isAllStates = 2,
        int $isAllCities = 2,
        int $isAllPostcodes = 2,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj;

        if (isset($code) && $code != '') {
            $data = $data
                ->where('code', 'like', '%' . $name . '%');
        }

        if (isset($name) && $name != '') {
            $data = $data
                ->where('name', 'like', '%' . $name . '%');
        }

        if ($isAllCountries < 2) {
            $data = $data
                ->where('is_all_countries', $isAllCountries);
        }

        if ($isAllStates < 2) {
            $data = $data
                ->where('is_all_states', $isAllStates);
        }

        if ($isAllCities < 2) {
            $data = $data
                ->where('is_all_cities', $isAllCities);
        }

        if ($isAllPostcodes < 2) {
            $data = $data
                ->where('is_all_postcodes', $isAllPostcodes);
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
        
        return $totalRecords->merge(['data' => $data]);
    }

    /**
     * get zone details for a given stateId
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id)
    {
        $data =  $this->modelObj
          ->with([
            'zoneCountries', 
            'zoneStates', 
            'zoneCities', 
            'zonePostcodes', 
            'zoneStockLocations'
          ])
          ->findOrFail($id);

        $data->countries = $data->zoneCountries()->pluck('country_id')->toArray();
        
        $data->states = $data->zoneStates()->pluck('state_id')->toArray();
        
        $data->cities = $data->zoneCities()->pluck('city_id')->toArray();

        return $data;
    }

    /**
     * create or update zone
     * 
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data)
    {
        $zone = null;
        $errorBag = [];

        $zoneData = [
            'code' => $data['code'],
            'name' => $data['name'],
            'is_all_countries' => $data['is_all_countries'],
            'is_all_states' => $data['is_all_states'],
            'is_all_cities' => $data['is_all_cities'],
            'is_all_postcodes' => $data['is_all_postcodes']
        ];

        if (isset($data['id'])) {
            $zone = $this->modelObj->findOrFail($data['id']);

            $zone->update(array_merge(['updated_by' => Auth::id()], $zoneData));
        }
        else {
            $zone = Auth::user()
                ->createdBy($this->modelObj)
                ->create($zoneData);
        }

        //sync country
        $zone->zoneCountries()
            ->sync($data['countries']);

        //sync state
        $zone->zoneStates()
            ->sync($data['states']);

        //sync city
        $zone->zoneCities()
            ->sync($data['cities']);

        //sync postcode
        $zone->zonePostcodes()
            ->whereNotIn('id', collect($data['zone_postcodes'])->pluck('id')->toArray())
            ->delete();

        foreach ($data['zone_postcodes'] as $zonePostcodeItem) {
            if (isset($zonePostcodeItem['id'])) {
                $zonePostcode = $this->zonePostcodeObj
                    ->findOrFail($zonePostcodeItem['id']);

                $zonePostcode->update($zonePostcodeItem);
            }
            else {
                $zonePostcode = $this->zonePostcodeObj
                    ->create(array_merge(['zone_id' => $zone->id], $zonePostcodeItem));
            }
        }
        
        //sync stock location
        $zone->zoneStockLocations()
            ->whereNotIn('id', collect($data['zone_stock_locations'])->pluck('id')->toArray())
            ->delete();

        foreach ($data['zone_stock_locations'] as $zoneStockLocationItem) {
            if (isset($zoneStockLocationItem['id'])) {
                $zoneStockLocation = $this->zoneStockLocationObj
                    ->findOrFail($zoneStockLocationItem['id']);

                $zoneStockLocation->update($zoneStockLocationItem);
            }
            else {
                $zoneStockLocation = $this->zoneStockLocationObj
                    ->create(array_merge(['zone_id' => $zone->id], $zoneStockLocationItem));
            }
        }

        // return result json
        return array_merge(['errors' => $errorBag],
            $this->show($zone['id'])->toArray()
        );
    }

    /**
     * delete zone
     *
     * @param int $id
     * @return array|mixed
     */
    public function delete(int $id)
    {
        $zone = $this->modelObj
            ->findOrFail($id);
        
        $zone->zoneCountries()->detach();

        $zone->zoneStates()->detach();

        $zone->zoneCities()->detach();

        $this->zonePostcodeObj
            ->where('zone_id', $id)
            ->delete();

        $this->zoneStockLocationObj
            ->where('zone_id', $id)
            ->delete();

        $deleteStatus = $zone
            ->delete(); 

        return ($deleteStatus) ?
            ['data' => trans('message.delete.success')] :
            ['data' => trans('message.delete.fail')];
    }

    /**
     * get stock location based on country ~ state ~ city ~ postcode
     * 
     * @param int $countryId
     * @param int $stateId
     * @param int $cityId
     * @param string $postcode
     * @return mixed
     */
    public function getStockLocation($countryId, $stateId, $cityId, $postcode) {
        $stockLocation =  $this->modelObj
            ->join('zones_stock_locations', function ($join) {
                $join->on('zones_stock_locations.zone_id', '=', 'zones.id')
                    ->where('effective_date', '<=', date('Y-m-d'))
                    ->where('expiry_date', '>=', date('Y-m-d'));
            })
            ->leftJoin('zones_countries', function ($join) use ($countryId) {
                $join->on('zones_countries.zone_id', '=', 'zones.id')
                    ->where('country_id', $countryId);
            })
            ->leftJoin('zones_states', function ($join) use ($stateId) {
                $join->on('zones_states.zone_id', '=', 'zones.id')
                    ->where('state_id', $stateId);
            })
            ->leftJoin('zones_cities', function ($join) use ($cityId) {
                $join->on('zones_cities.zone_id', '=', 'zones.id')
                    ->where('city_id', $cityId);
            })
            ->leftJoin('zones_postcodes', function ($join) use ($postcode) {
                $join->on('zones_postcodes.zone_id', '=', 'zones.id')
                    ->whereRaw('? LIKE postcode', [$postcode]);
            })
            ->whereRaw('(zones.is_all_countries = 1 OR (zones_countries.zone_id IS NOT NULL)) ')
            ->whereRaw('(zones.is_all_states = 1 OR (zones_states.zone_id IS NOT NULL)) ')
            ->whereRaw('(zones.is_all_cities = 1 OR (zones_cities.zone_id IS NOT NULL)) ')
            ->whereRaw('(zones.is_all_postcodes = 1 OR (zones_postcodes.zone_id IS NOT NULL)) ')
            ->orderByRaw(
                    'zones.is_all_countries, IF(zones_countries.zone_id IS NULL, 1, 0), ' .
                    'zones.is_all_postcodes, IF(zones_postcodes.zone_id IS NULL, 1, 0), ' .
                    'zones.is_all_cities, IF(zones_cities.zone_id IS NULL, 1, 0), ' .
                    'zones.is_all_states, IF(zones_states.zone_id IS NULL, 1, 0) '
                )
            ->select('zones_stock_locations.stock_location_id')
            ->limit(1)
            ->get()
            ->pluck('stock_location_id')
            ->toArray();

        return [
            'location_id' => (count($stockLocation) > 0) ? $stockLocation[0] : null
        ];
    }
}