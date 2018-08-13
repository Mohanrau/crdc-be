<?php
namespace App\Interfaces\Masters;

use App\Interfaces\BaseInterface;

interface MasterInterface extends BaseInterface
{
    /**
     * get master by key
     *
     * @param string $key
     * @param array $columns
     * @return mixed
     */
    public function getMasterByKey(string $key, array $columns = []);

    /**
     * get master data by masterData by key
     *
     * @param array $key
     * @param int|null $countryId
     * @return mixed
     */
    public function getMasterDataByKey(array $key, int $countryId = null);
}