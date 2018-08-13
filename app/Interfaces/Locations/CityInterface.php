<?php
namespace App\Interfaces\Locations;

use App\Interfaces\BaseInterface;

interface CityInterface extends BaseInterface
{
    /**
     * get stock locations list by cityId
     *
     * @param int $cityId
     * @return mixed
     */
    public function getStockLocationsByCity(int $cityId);
}