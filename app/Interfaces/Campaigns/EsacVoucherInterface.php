<?php
namespace App\Interfaces\Campaigns;

interface EsacVoucherInterface
{
    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param int $campaignId
     * @param int $promotionId
     * @param int $voucherTypeId
     * @param int $voucherSubTypeId
     * @param string $voucherNumber
     * @param string $voucherStatus
     * @param string $voucherRemarks
     * @param int $voucherPeriodId
     * @param int $memberUserId
     * @param int $sponsorId
     * @param string $issuedDate
     * @param string $expiryDate
     * @param int $fromCampaignCwScheduleId
     * @param int $toCampaignCwScheduleId
     * @param string $fromCreatedAt
     * @param string $toCreatedAt
     * @param bool $forRedemption 
     * @param int $active
     * @param string $search
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getEsacVouchersByFilters(
        int $countryId,
        int $campaignId = null,
        int $promotionId = null,
        int $voucherTypeId = null,
        int $voucherSubTypeId = null,
        string $voucherNumber = null,
        string $voucherStatus = null,
        string $voucherRemarks = null,
        int $voucherPeriodId = null,
        int $memberUserId = null,
        int $sponsorId = null,
        string $issuedDate = null,
        string $expiryDate = null,
        int $fromCampaignCwScheduleId = null,
        int $toCampaignCwScheduleId = null,
        string $fromCreatedAt = null,
        string $toCreatedAt = null,
        bool $forRedemption = null,
        int $active = null,
        string $search = null,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get one esac voucher by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function show(int $id);

     /**
     * delete one esac voucher by id
     *
     * @param  int  $id
     * @return mixed
     */
    public function delete(int $id);

    /**
     * create or update esac voucher
     *
     * @param array $data
     * @return array|string
     */
    public function createOrUpdate(array $data);

    /**
     * update esac voucher status
     *
     * @param int $voucherId
     * @param string $voucherStatus
     */
    public function updateStatus(
        int $voucherId, 
        string $voucherStatus
    );
}