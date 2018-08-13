<?php
namespace App\Interfaces\Locations;

use App\Interfaces\BaseInterface;

interface CountryInterface extends BaseInterface
{
    /**
     * get all records or subset based on pagination
     *
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @param string|null $locationCode
     * @return mixed
     */
    public function countriesList(
        int $active = 2,
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0,
        string $locationCode = null
    );

    /**
     * get country with a given relations - dynamic
     *
     * @param int $countryId
     * @param array $relations
     * @param array $criterias
     * @return mixed
     */
    public function getCountryWithRelations(
        int $countryId,
        array $relations,
        array $criterias = []
    );
}