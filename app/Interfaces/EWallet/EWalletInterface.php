<?php
namespace App\Interfaces\EWallet;

interface EWalletInterface
{
    /**
     * Get eWallet Obj of Authorized User
     *
     * @param int $userId
     * @return mixed
     */
    public function getEWallet(int $userId = 0);

    /**
     * Create EWallet record
     *
     * @param $inputs
     * @return mixed
     */
    public function createEWallet(array $inputs);

    /**
     * Update EWallet record based on ewallet_id
     *
     * @param int $id
     * @param array $inputs
     * @return mixed
     */
    public function updateEWallet(int $id, array $inputs);

    /**
     * get all transaction history
     *
     * @param int $countryId
     * @param int $userId
     * @param string|null $fromDate
     * @param string|null $toDate
     * @param string|null $amountType
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getEWalletTransactions(
        int $countryId = 0,
        int $userId = 0,
        string $fromDate = null,
        string $toDate = null,
        string $amountType = null,
        int $paginate = 10,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0);

    /**
     * get transaction by id
     *
     * @param int $id
     * @return mixed
     */
    public function getEWalletTransaction(int $id);

    /**
     * create new transaction
     *
     * @param array $inputs
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function createNewTransaction(array $inputs);

    /**
     * send OTP Code
     *
     * @param string $mobile
     * @return array
     */
    public function sendOTPCode(string $mobile);

    /**
     * Activate e-Wallet
     *
     * @param array $inputs
     * @return mixed
     */
    public function activateEWallet(array $inputs);

    /**
     * Change Auto Withdrawal Status
     *
     * @param array $inputs
     * @return mixed
     */
    public function changeEWalletAutoWithdrawal(array $inputs);

    /**
     * Get Bank Payment Listing or generate
     *
     * @param int $registered_country_id
     * @param string $giro_type
     * @param bool $generate
     * @return array|mixed
     */
    public function getBankPaymentRecords(
        int $registered_country_id,
        string $giro_type,
        bool $generate = false);

    /**
     * Generate Bank Payment File
     *
     * @param array $inputs
     * @return mixed
     */
    public function generateBankPaymentFile(array $inputs);

    /**
     * Get Bank Payment History Listing
     *
     * @param array $inputs
     * @return mixed
     */
    public function getBankPaymentHistory(array $inputs);

    /**
     * get ewallet to bank(remittance) with pending integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationRemittance();

    /**
     * get ewallet(credit) with pending integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationEwallet();

    /*
     * Upload and Read GIRO Rejected Payment File
     *
     * @param array $inputs
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function readRejectedPaymentFile(array $inputs);

    /**
     * Submit GIRO Rejected Payment Records
     *
     * @param array $inputs
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function submitRejectedPaymentFile(array $inputs);

    /**
     * Get Rejected Payment Sample File
     *
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getRejectedPaymentSampleFile();

    /**
     * Rejected Payment Listing
     *
     * @param array $filters
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function rejectedPaymentListing(
        array $filters,
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0);

    /**
     * Update Rejected Payment Records
     *
     * @param array $inputs
     * @param bool $levelTwo
     * @return mixed
     */
    public function rejectedPaymentUpdate(array $inputs, bool $levelTwo = false);

    /**
     * eWallet Adjustment Listing
     *
     * @param array $filters
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function eWalletAdjustmentListing(
        array $filters,
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0);

    /**
     * Get eWallet Adjustment Record Details
     *
     * @param int $id
     * @param bool $memberData
     * @return mixed
     */
    public function eWalletAdjustmentRecord(int $id, bool $memberData = false);

    /**
     * add new record in eWallet Adjustment
     *
     * @param array $inputs
     * @return mixed
     */
    public function eWalletAdjustmentInsert(array $inputs);

    /**
     * eWallet Adjustment
     *
     * @param int $id
     * @param array $inputs
     * @param bool $levelTwo
     * @return mixed
     */
    public function eWalletAdjustmentUpdate(int $id, array $inputs, bool $levelTwo = false);
}