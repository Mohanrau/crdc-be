<?php
namespace App\Interfaces\Campaigns;

interface CampaignInterface
{
    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param string|null $name
     * @param string|null $reportGroup
     * @param int|null $fromCwScheduleId
     * @param int|null $toCwScheduleId
     * @param string|null $search
     * @param int|null $active
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getCampaignsByFilters(
        int $countryId,
        string $name = null,
        string $reportGroup = null,
        int $fromCwScheduleId = null,
        int $toCwScheduleId = null,
        string $search = null,
        int $active = null,
        int $paginate = 20,
        string $orderBy = 'name',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get one campaign by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function show(int $id);

     /**
     * delete one campaign by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * create or update campaign
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data);
}