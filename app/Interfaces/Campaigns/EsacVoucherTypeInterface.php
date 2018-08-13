<?php
namespace App\Interfaces\Campaigns;

interface EsacVoucherTypeInterface
{
    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param string $name
     * @param string $description
     * @param int $search
     * @param int $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getEsacVoucherTypesByFilters(
        int $countryId,
        string $name = null,
        string $description = null,
        string $search = null,
        int $active = null,
        int $paginate = 20,
        string $orderBy = 'name',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get one esac voucher type by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function show(int $id);

     /**
     * delete one esac voucher type by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * create or update esac voucher type
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data);
}