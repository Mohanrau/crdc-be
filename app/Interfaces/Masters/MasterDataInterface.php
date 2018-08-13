<?php
namespace App\Interfaces\Masters;

use App\Interfaces\BaseInterface;

interface MasterDataInterface extends BaseInterface
{
    /**
     * get masterData for a given key matched with data given
     *
     * @param int $masterId
     * @param string $key
     * @param array $data
     * @param array $columns
     * @return mixed
     */
    public function findByKeys(int $masterId, string $key = 'id', array $data, array $columns = []);
}