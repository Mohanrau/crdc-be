<?php
namespace App\Repositories\Locations;

use App\{
    Helpers\Traits\AccessControl,
    Helpers\Traits\ResourceRepository,
    Interfaces\Locations\CountryInterface,
    Models\Locations\Country,
    Models\Locations\LocationTypes,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;
use Auth;

class CountryRepository extends BaseRepository implements CountryInterface
{
    use AccessControl, ResourceRepository;

    private
        $locationTypesObj,
        $locationTypesConfigCodes;

    /**
     * CountryRepository constructor.
     *
     * @param Country $model
     * @param LocationTypes $locationTypes
     */
    public function __construct(
        Country $model,
        LocationTypes $locationTypes
    )
    {
        parent::__construct($model);

        $this->with = ['currency'];

        $this->locationTypesObj = $locationTypes;

        $this->locationTypesConfigCodes = config('mappings.locations_types');
    }

    /**
     * get all records
     *
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @param string $locationCode location code name to filter by location code
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public function countriesList(
        int $active = 2,
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0,
        string $locationCode = null
    )
    {
        $data = $this->modelObj
            ->orderBy($orderBy, $orderMethod);

        //get the authorized countries if back office user or stockist user
        if (
            $this->isUser('back_office') or
            $this->isUser('stockist') or
            $this->isUser('stockist_staff')
        )
        {
            $data = $data
                ->whereIn('id', $this->getAuthorizedCountries());
        }

        //check the active 
        if($active < 2){
            $data = $data
                ->where('active', $active);
        }    

        // filter by location code if the code is specified
        if ($locationCode != null) {
            $locationSelect = ['entity.locations' => function ($query) use ($locationCode) {
                $query->whereHas('locationType', function ($ltQuery) use ($locationCode) {
                    $ltQuery->where('code', $locationCode);
                });
            }];

            $data = $data->whereHas(key($locationSelect), current($locationSelect));

            $this->with = ($this->with != null) ?  array_merge($this->with, $locationSelect) : $locationSelect;
        }

        //check if no relations required.
        if ($this->with != null) {
            $data = $data->with($this->with);
        }

        $totalRecords = collect(
            [
                'total' => $data->count()
            ]
        );

        $data = ($paginate) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
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

        $data->taxes = $data->taxes()->get();

        $data->default_currency = $data->currency()->get();

        return $data;
    }

    /**
     * get country with a given relations - dynamic
     *
     * @param int $countryId
     * @param array $relations
     * @param array $criterias
     * @return \Illuminate\Support\Collection|mixed
     */
    public function getCountryWithRelations(
        int $countryId,
        array $relations,
        array $criterias = [])
    {
        if (in_array('entity.locations', $relations))
        {
            if (($key = array_search('entity.locations', $relations)) !== false) {
                unset($relations[$key]);
            }

            $data = $this->modelObj
                ->where('id', $countryId)
                ->with($relations)
                ->get();

            $entity = $this->modelObj
                ->find($countryId)
                ->entity()
                ->first();

            $entity->locations = $entity
                ->locationsRnp($countryId)
                ->where(function ($locationQuery) use ($criterias) {
                    if (in_array('entity.locations.online.exclude', $criterias)){
                        $locationType = $this->locationTypesObj
                            ->where('code', $this->locationTypesConfigCodes['online'])
                            ->first();
                        $locationQuery->where('location_types_id', '!=', $locationType->id);
                    }
                })
                ->active()
                ->get();

            $data->entity = $entity;

            return collect($data->first())->put('entity', $entity);
        }

        return $data = $this->modelObj
            ->where('id', $countryId)
            ->with($relations)
            ->first();
    }
}