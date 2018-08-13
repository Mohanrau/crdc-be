<?php
namespace App\Interfaces\Dummy;

use App\Interfaces\BaseInterface;

interface DummyInterface extends BaseInterface
{
    /**
     * get dummy by filters
     *
     * @param int $countryId
     * @param int $isLingerie
     * @param string $dummyData
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getDummyFilters(
        int $countryId,
        int $isLingerie = 2,
        string $dummyData = '',
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get dummy details for a given countryId and dummyId/optional
     *
     * @param int $countryId
     * @param int $dummyId
     * @return mixed
     */
    public function dummyDetails(int $countryId, int $dummyId);

    /**
     * create or update dummy
     *
     * @param array $data
     * @return mixed
     */
    public function createOrUpdate(array $data);
}