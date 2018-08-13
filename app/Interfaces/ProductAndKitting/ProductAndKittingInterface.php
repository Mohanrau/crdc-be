<?php
namespace App\Interfaces\ProductAndKitting;

interface ProductAndKittingInterface
{
    /**
     * search for product or kitting for sales
     *
     * @param int $userId
     * @param int $countryId
     * @param int $locationId
     * @param string $text
     * @param array $esacVouchers
     * @param array $saleTypes
     * @param bool $isConsignmentReturn
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @param bool $mixed
     * @return mixed
     */
    public function searchProductsAndKitting(
        int $userId,
        int $countryId,
        int $locationId,
        string $text,
        array $esacVouchers = null,
        array $saleTypes = [],
        bool $isConsignmentReturn = false,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0,
        bool $mixed = false
    );

    /**
     * search for the available product or kitting for the enrollment
     *
     * @param int $countryId
     * @param int $locationId
     * @param int $enrollmentTypeId
     * @param string $text
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function searchProductsAndKittingEnrollment(
        int $countryId,
        int $locationId,
        int $enrollmentTypeId,
        string $text,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );
}