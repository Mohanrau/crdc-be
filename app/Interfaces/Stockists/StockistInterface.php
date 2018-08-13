<?php
namespace App\Interfaces\Stockists;

use App\Models\Sales\Sale;

interface StockistInterface
{
    /**
     * get stockist by id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * get stockist details filtered by below parameter
     *
     * @param int $countryId
     * @param string $text
     * @param int $stockistTypeId
     * @param int $stockistStatusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getStockistsByFilters(
        int $countryId = 0,
        string $text = '',
        int $stockistTypeId = 0,
        int $stockistStatusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * create or update stockist
     *
     * @param array $data
     * @return mixed
     */
    public function createOrUpdateStockist(array $data);

    /**
     * get stockist detail by given stockistUserId
     *
     * @param int $stockistUserId
     * @return mixed
     */
    public function stockistDetails(int $stockistUserId);

    /**
     * To download pdf and export as content-stream header 'application/pdf'
     *
     * @param int $consignmentOrderReturnId
     * @return \Illuminate\Support\Collection
     * @throws \Mpdf\MpdfException
     */
    public function downloadConsignmentNote(int $consignmentOrderReturnId);

    /**
     * get consignment deposit refund filtered by below parameter
     *
     * @param int $countryId
     * @param string $text
     * @param $dateFrom
     * @param $dateTo
     * @param int $typeId
     * @param int $statusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getConsignmentDepositRefundByFilters(
        int $countryId = 0,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $typeId = 0,
        int $statusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * validate stockist consignment operation for a given stockistUserId, deposit refund type
     *
     * @param int $stockistUserId
     * @param string $type
     * @return mixed
     */
    public function validatesConsignmentDepositsRefunds(int $stockistUserId, string $type);

    /**
     * create consignment deposits
     *
     * @param array $data
     * @return mixed
     */
    public function createConsignmentDeposit(array $data);

    /**
     * get consignment deposits refunds detail by given ID
     *
     * @param int $consignmentDepositRefundId
     * @return mixed
     */
    public function consignmentDepositsRefundsDetails(int $consignmentDepositRefundId);

    /**
     * create consignment refund
     *
     * @param array $data
     * @return mixed
     */
    public function createConsignmentRefund(array $data);

    /**
     * update consignment deposit return
     *
     * @param array $data
     * @param int $consignmentDepositReturnId
     * @return array|mixed
     */
    public function updateConsignmentDepositReturn(array $data, int $consignmentDepositReturnId);

    /**
     * Create consignment workflow
     *
     * @param int $consignmentId
     * @param string $consignmentType
     * @return mixed
     */
    public function createConsignmentWorkflow(int $consignmentId, string $consignmentType);

    /**
     * validates no pending consignment return before create new return by giving stockist user id
     *
     * @param int $stockistUserId
     * @return mixed
     */
    public function validatesConsignmentReturn(int $stockistUserId);

    /**
     * get consignment order return filtered by below parameter
     *
     * @param string $consignmentOrderReturnType
     * @param int $countryId
     * @param string $text
     * @param $dateFrom
     * @param $dateTo
     * @param int $statusId
     * @param int $warehouseReceivingStatusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getConsignmentOrderReturnByFilters(
        string $consignmentOrderReturnType = 'order',
        int $countryId = 0,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $statusId = 0,
        int $warehouseReceivingStatusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get consignment order return detail by given ID
     *
     * @param int $consignmentOrderReturnId
     * @return mixed
     */
    public function consignmentOrderReturnDetails(int $consignmentOrderReturnId);

    /**
     * create consignment order return
     *
     * @param array $data
     * @return mixed
     */
    public function createConsignmentOrderReturn(array $data);

    /**
     * validates total product quantity that can be return
     *
     * @param int $stockistId
     * @param int $productId
     * @return mixed
     */
    public function validatesConsignmentReturnProduct(int $stockistId, int $productId);

     /**
     * Create consignment transaction record
     *
     * @param int $consignmentId
     * @param string $consignmentType
     * @return mixed
     */
    public function createConsignmentTransaction(int $consignmentId, string $consignmentType);

    /**
     * Update stockist consignment product quantity
     *
     * @param int $consignmentOrderReturnId
     * @param string $type
     * @return mixed
     */
    public function updateConsignmentProduct(int $consignmentOrderReturnId, string $type);

    /**
     * Update stockist consignment return product quantity during approve session
     *
     * @param array $consignmentReturnDetail
     */
    public function updateConsignmentReturnProductQuantity(array $consignmentReturnDetail);

    /**
     * get stockist sales daily payment verification list by below parameter
     *
     * @param int $countryId
     * @param $dateFrom
     * @param $dateTo
     * @param bool $excludeZeroBalance
     * @param array $selectedStockistIds
     * @return mixed
     */
    public function getSalesDailyPaymentVerificationLists
    (
        int $countryId = 0,
        $dateFrom = '',
        $dateTo = '',
        bool $excludeZeroBalance = false,
        $selectedStockistIds = array()
    );

    /**
     * batch update stockist outstanding and ar payment balance
     *
     * @param array $datas
     * @return mixed
     */
    public function batchUpdateStockistOutstandingPayment(array $datas);

    /**
     * get consignment deposit and consignment refund
     *
     * @return mixed
     */
    public function getYonyouIntegrationConsignmentDepositAndRefund();

    /**
     * get consignment deposit (rejected)
     *
     * @return mixed
     */
    public function getYonyouIntegrationConsignmentDepositReject();

    /**
     * get consignment order for youyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationConsignmentOrder();

    /**
     * get consignment return for youyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationConsignmentReturn();

    /**
     * get stockist payment for youyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistPayment();

    /**
     * get stockist payment adjustment for youyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistPaymentAdjustment();

    /*
     * get stockist outstanding summary by below parameter
     *
     * @param int $countryId
     * @param bool $excludeZeroBalance
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getStockistOutstandingSummary(
        int $countryId = 0,
        bool $excludeZeroBalance = true,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * download stockist daily collection report by below parameter
     *
     * @param int $countryId
     * @param array $locations
     * @param $collectionDateFrom
     * @param $collectionDateTo
     * @param int $userId
     * @return mixed
     */
    public function downloadDailyCollectionReport(
        int $countryId = 0,
        array $locations = array(),
        $collectionDateFrom = '',
        $collectionDateTo = '',
        int $userId = 0
    );

    /**
     * download consignment deposit receipt note by consignment deposit refund id
     *
     * @param int $consignmentDepositRefundId
     * @return mixed
     */
    public function downloadDepositReceipt(int $consignmentDepositRefundId);

    /**
     * download consignment product list based on stockist ID
     *
     * @param int $stockistId
     * @return mixed
     */
    public function downloadConsignmentProduct(int $stockistId);
}