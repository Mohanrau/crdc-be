<?php
namespace App\Interfaces\Promotions;

interface PromotionFreeItemsInterface
{
    /**
     * get one user by id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * delete user by id
     *
     * @param int $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * get promotion free items by filters
     *
     * @param int $countryId
     * @param string $searchText
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getPromotionFreeItemsByFilters(
        int $countryId,
        string $searchText = '',
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get promotion free items details for a given countryId and promoId
     *
     * @param int $countryId
     * @param int $promoId
     * @return mixed
     */
    public function promotionFreeItemsDetails(int $countryId, int $promoId);

    /**
     * create or update promo free items
     *
     * @param array $data
     * @return mixed
     */
    public function createOrUpdate(array $data);

    /**
     * retrieve promotion free item
     *
     * @param array $saleType
     * @param array $promoTypes
     * @param int $countryId
     * @param int $memberType
     * @param string $duration
     * @param array $productFilters
     * @return mixed
     */
    public function retrievePromotionDetails(
        array $saleType,
        array $promoTypes,
        int $countryId,
        int $memberType,
        string $duration,
        array $productFilters
    );
}