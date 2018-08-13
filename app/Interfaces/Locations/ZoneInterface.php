<?php
namespace App\Interfaces\Locations;

interface ZoneInterface
{
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
    );

    /**
     * get zone details for a given stateId
     *
     * @param int $id
     * @return mixed
     */
    public function show(int $id);

    /**
     * create or update zone
     * 
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data);

    /**
     * delete zone
     *
     * @param int $id
     * @return array|mixed
     */
    public function delete(int $id);

    /**
     * get stock location based on country+state+city+postcode
     * 
     * @param int $countryId
     * @param int $stateId
     * @param int $cityId
     * @param string $postcode
     * @return mixed
     */
    public function getStockLocation($countryId, $stateId, $cityId, $postcode);
}