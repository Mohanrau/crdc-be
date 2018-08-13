<?php
namespace App\Interfaces\Locations;

use App\Interfaces\BaseInterface;

interface StateInterface extends BaseInterface
{
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
    );
}