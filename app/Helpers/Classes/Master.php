<?php
namespace App\Helpers\Classes;

use App\Interfaces\Masters\MasterInterface;
use App\Exceptions\Masters\MasterNotFoundException;
use App\Models\Masters\MasterData;

class Master
{
    private
        $masterRepositoryObj,
        $masters
    ;

    /**
     * Master constructor.
     *
     * @param MasterInterface $masterRepositoryObj
     */
    public function __construct (MasterInterface $masterRepositoryObj) {
        $this->masterRepositoryObj = $masterRepositoryObj;
    }

    /**
     * Returns the master data for a given master
     *
     * @param string $masterKey
     * @return array
     */
    public function getMasterData (string $masterKey) {
        if (!isset($this->masters[$masterKey])) {
            $this->masters[$masterKey] = $this->masterRepositoryObj->getMasterDataByKey(array($masterKey))->pop();
        }
        
        return $this->masters[$masterKey];
    }

    /**
     * Returns the master data for a given master
     *
     * @param string $masterKey
     * @return array
     */
    public function getMasterDataArray (string $masterKey) {
        return collect($this->getMasterData($masterKey))
            ->pluck('title','id')
            ->toArray();
    }

    /**
     * Checks if master data belongs to a given master
     *
     * @param string $masterKey
     * @param int $masterId
     *
     * @return bool
     */
    public function idBelongsToMaster (string $masterKey, int $masterId) {
        $masterKeys = $this->getMasterData($masterKey);

        return collect($masterKeys)->contains('id', $masterId);
    }

    /**
     * Get master data by id
     *
     * @param string $masterKey
     * @param int $masterId
     *
     * @return MasterData|null
     */
    public function getMasterDataById(string $masterKey, int $masterId) {
       $masterKeys = $this->getMasterData($masterKey);

       return collect($masterKeys)->first(function ($masterData) use ($masterId) { return $masterData->id === $masterId; });
    }

    /**
     * @param string $masterKey
     * @param string $title
     *
     * @return MasterData|null
     */
    public function getMasterDataByTitle(string $masterKey, string $title) {
        $masterKeys = $this->getMasterData($masterKey);

        return collect($masterKeys)->first(function ($masterData) use ($title) { return strtolower($masterData->title) === strtolower($title); });
    }

    /**
     * Returns the master data by title
     *
     * @param string $masterKey
     * @param int $masterDataId
     *
     * @return mixed
     */
    public function getMasterDataTitleById (string $masterKey, int $masterDataId) {
        return $this->getMasterData($masterKey)
                    ->pluck('id','title')
                    ->flip()
                    ->get($masterDataId);
    }

    /**
     * Returns the master id
     *
     * @param string $masterKey
     * @return mixed
     * @throws MasterNotFoundException
     */
    public function getMasterId (string $masterKey) {
        if ($master = $this->getMasterData($masterKey)) {
            return $this->getMasterData($masterKey)->first()['id'];
        } else {
            throw new MasterNotFoundException;
        }

    }
}