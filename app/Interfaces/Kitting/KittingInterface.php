<?php
namespace App\Interfaces\Kitting;

use App\Interfaces\BaseInterface;

interface KittingInterface
{
    /**
     * get kitting by filters
     *
     * @param int $countryId
     * @param bool|null $isEsac
     * @param string $kittingCode
     * @param string $productSku
     * @param array $salesType
     * @param int $locationId
     * @param array|null $includeCategories
     * @param array|null $includeKittings
     * @param array|null $excludeKittings
     * @param string $text
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getKittingByFilters(
        int $countryId,
        bool $isEsac = null,
        string $kittingCode = '',
        string $productSku = '',
        array $salesType = [],
        int $locationId = 0,
        array $includeCategories = null,
        array $includeKittings = null,
        array $excludeKittings = null,
        string $text = '',
        int $active = 1,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get kitting details for a given countryId and kittingId
     *
     * @param int $countryId
     * @param int $kittingId
     * @return mixed
     */
    public function kittingDetails(int $countryId, int $kittingId = null);

    /**
     * create or update kitting
     *
     * @param array $data
     * @return mixed
     */
    public function createOrUpdate(array $data);

    /**
     * calculate total gmp for a given kittingId
     *
     * @param int $countryId
     * @param $kittingData
     * @param array $locations
     * @return mixed
     */
    public function calculateKittingTotalGmp(int $countryId, $kittingData, array $locations);
}