<?php
namespace App\Interfaces\Campaigns;

interface EsacPromotionInterface
{
    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param int $campaignId
     * @param int $taxable
     * @param int $voucherTypeId
     * @param string $entitledBy
     * @param int $maxPurchaseQty
     * @param string $search
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getEsacPromotionsByFilters(
        int $countryId,
        int $campaignId = null,
        int $taxable = null,
        int $voucherTypeId = null,
        string $entitledBy = null,
        int $maxPurchaseQty = null,
        string $search = null,
        int $active = null,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

     /**
     * get one esac promotion by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function show(int $id);

     /**
     * delete one esac promotion by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * create or update esac promotion
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data);
}