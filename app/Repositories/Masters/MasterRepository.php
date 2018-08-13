<?php
namespace App\Repositories\Masters;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\Masters\MasterInterface,
    Models\Locations\Country,
    Models\Masters\Master,
    Models\Masters\MasterData,
    Models\Masters\MasterType,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MasterRepository extends BaseRepository implements MasterInterface
{
    use ResourceRepository{
        create as oldCreate;
    }

    private
        $masterDataObj,
        $countryObj
;

    /**
     * MasterRepository constructor.
     *
     * @param Master $model
     * @param MasterData $masterData
     * @param Country $country
     */
    public function __construct(
        Master $model,
        MasterData $masterData,
        Country $country
    )
    {
        parent::__construct($model);

        $this->masterDataObj = $masterData;

        $this->countryObj = $country;

        $this->with = ['masterData'];
    }

    /**
     * create new module
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $module = Auth::user()->createdBy($this->modelObj)
            ->create(array_merge($data,
                    [
                        'key' => strtolower(
                            trim(str_replace(' ','_',$data['title']))
                        )
                    ])
            );

        return $module;
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

        $data->masterData = $data->masterData()->get();

        return $data;
    }

    /**
     * get master for a given key
     *
     * @param string $key
     * @param array $columns
     * @return mixed
     */
    public function getMasterByKey(string $key, array $columns = [])
    {
        $data = $this->modelObj
            ->where('key', $key);;

        return empty($columns) ? $data->first() : $data->first($columns);
    }

    /**
     * get masterData by master key
     *
     * @param array $key
     * @param int|null $countryId
     * @return \Illuminate\Support\Collection|mixed
     */
    public function getMasterDataByKey(array $key, int $countryId = null)
    {
        $masterDataCountryCollection = collect();

        $masterIds =  $this->modelObj
            ->whereIn('key', $key)
            ->pluck('id');

        $result = [];

        //check if country id is set
        if (!is_null($countryId)){
            $country = $this->countryObj->find($countryId);

            foreach ($masterIds as $key=>$master)
            {
                $masterDataIds = $country
                    ->countryRules()
                    ->where('master_id', $master)
                    ->pluck('master_data_id');

                if ($masterDataIds->count() > 0){
                    $masterIds->forget($key);
                }else{
                    continue;
                }

                $result[] = $country
                    ->countryRules()
                    ->distinct('master_id')
                    ->with(['masterData' => function($query) use ($masterDataIds){
                        $query->whereIn('id', $masterDataIds);
                    }])
                    ->where('master_id', $master)
                    ->first();
            }

            //process master data country based
            $masterDataCountryCollection =  collect($result)->mapWithKeys(function ($item) {
                $data[$item->key] =  $item->masterData;

                return $data;
            });
        }

        $masterData =  $this->modelObj
            ->with('masterData')
            ->whereIn('id', $masterIds)
            ->get();

        $generalizedMasterDataCollection =  collect($masterData)->mapWithKeys(function ($item) {
            $data[$item->key] =  $item->masterData;

            return $data;
        });

        return $generalizedMasterDataCollection->merge($masterDataCountryCollection);
    }
}