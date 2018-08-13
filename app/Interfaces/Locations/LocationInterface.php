<?php
namespace App\Interfaces\Locations;

use App\Interfaces\BaseInterface;

interface LocationInterface extends BaseInterface
{
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
    );

    /**
     * get locations addresses filtered by the following parameters
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
    );

    /**
     * get stock locations list by locationId
     *
     * @param int $locationId
     * @return mixed
     */
    public function getStockLocationsByLocation(int $locationId);
}