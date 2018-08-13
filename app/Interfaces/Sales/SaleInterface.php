<?php
namespace App\Interfaces\Sales;

interface SaleInterface
{
    /**
     * get all records or subset based on pagination
     *
     * @param int $countryId
     * @param string $text
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $channel
     * @param int $deliveryMethod
     * @param int $deliveryStatus
     * @param int $orderStatus
     * @param int $esacRedemption
     * @param int $corporateSales
     * @param int $rentalSaleOrder
     * @param int $withTrashed
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getSalesByFilters(
        int $countryId,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $channel = 0,
        int $deliveryMethod = 0,
        int $deliveryStatus = 0,
        int $orderStatus = 0,
        int $esacRedemption = -1,
        int $corporateSales = -1,
        int $rentalSaleOrder = -1,
        int $withTrashed = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get sale by id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * get sale details for a given saleId
     *
     * @param int $saleId
     * @return mixed
     */
    public function saleDetails(int $saleId);

    /**
     * create new sales order
     *
     * @param array $data
     * @param bool $orderCompleteStatus
     * @return mixed
     */
    public function createSale(array $data, bool $orderCompleteStatus = false);

    /**
     * Create sale workflow
     *
     * @param int $saleId
     * @return mixed
     */
    public function createSaleWorkflow(int $saleId);

     /**
     * update sales
     *
     * @param array $data
     * @param int $saleId
     * @return array|mixed
     */
    public function updateSale(array $data, int $saleId);

    /**
     * eligible Sales Promo Items
     *
     * @param int $downLineMemberId
     * @param int $countryId
     * @param int $locationId
     * @param array $products
     * @param array $kittings
     * @param array $parameter
     * @return mixed
     */
    public function eligibleSalesPromo(
        int $downLineMemberId,
        int $countryId,
        int $locationId,
        array $products = array(),
        array $kittings = array(),
        array $parameter
    );

    /**
     * Calculate Promotion Price by given selected promotion
     *
     * @param array $selectedPromos
     * @param array $parameter
     * @return mixed
     */
    public function calculatePromoPrice(array $selectedPromos, array $parameter);

    /**
     * Calculate admin fees
     *
     * @param int $downLineMemberId
     * @param array $parameter
     * @return mixed
     */
    public function calculateSalesAdminFees(int $downLineMemberId, array $parameter);

    /**
     * Calculate delivery fees
     *
     * @param int $downLineMemberId
     * @param array $parameter
     * @return mixed
     */
    public function calculateSalesDeliveryFees(int $downLineMemberId, array $parameter);

    /**
     * Calculate other fees
     *
     * @param array $parameter
     * @return mixed
     */
    public function calculateOtherFees(array $parameter);

    /**
     * Calculate amount rounding
     *
     * @param int $countryId
     * @param float $amount
     * @return string $payAmount
     */
    public function roundingAdjustment(int $countryId, float $amount);

    /**
     * get sale cancellation invoice details for a given userId, invoiceId
     *
     * @param string $saleCancellationMethod
     * @param int $userId
     * @param int $invoiceId
     * @param int $countryId
     * @return mixed
     */
    public function getSalesCancellationInvoiceDetails(string $saleCancellationMethod, int $userId, int $invoiceId, int $countryId);

    /**
     * Insert Purchase Cv by given saleId
     *
     * @param int $saleId
     * @return boolean
     */
    public function insertPurchaseCv(int $saleId);

    /**
     * Remove Sale Cancellation Cv by given saleCancellationId
     *
     * @param int $saleCancellationId
     */
    public function removeSaleCancellationCv(int $saleCancellationId);

    /**
     * Create Auto Maintenance Purchase Cv in future CW by given saleId
     *
     * @param int $saleId
     * @return boolean
     */
    public function createAmpCvAllocations(int $saleId);

    /**
     * Swap or Remove Auto Maintenance Purchase Cv in future CW by given saleCancellationId
     *
     * @param int $saleCancellationId
     */
    public function swapAmpCvAllocations(int $saleCancellationId);

    /**
     * Calculate Sale Accumulate Cv within Cw
     *
     * @param int $userId
     */
    public function saleAccumulationCalculation(int $userId);

    /**
     * get sales cancellation filtered by the following parameters
     *
     * @param int $countryId
     * @param string $text
     * @param string $dateFrom
     * @param string $dateTo
     * @param int|NULL $statusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getSalesCancellationByFilters(
        int $countryId,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $statusId = NULL,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get sale cancellation detail by given saleCancellationId
     *
     * @param int $saleCancellationId
     * @return mixed
     */
    public function saleCancellationDetail(int $saleCancellationId);

    /**
     * create new sales cancellation
     *
     * @param array $data
     * @return mixed
     */
    public function createSalesCancellation(array $data);

    /**
     * create new legacy sales cancellation
     *
     * @param array $data
     * @return mixed
     */
    public function createLegacySalesCancellation(array $data);

    /**
     * sales cancellation refund by batch
     *
     * @param array $saleCancellationIds
     * @param string $remark
     * @return mixed
     */
    public function salesCancellationBatchRefund(array $saleCancellationIds, string $remark = "");

    /**
     * To download pdf and export as content-stream header 'application/pdf'
     *
     * @param int $creditNoteId
     * @param string $section
     * @return mixed
     */
    public function downloadCreditNote(int $creditNoteId, string $section = 'sales_cancellation');

    /**
     * create new sales order (express edition)
     *
     * @param array $inputs
     * @return mixed
     */
    public function createSaleExpress(array $inputs);

    /**
     * Calculates CV
     *
     * @param array $price price array containing cvs
     *      $price = [
     *          'base_cv' => (float) Base Cv
     *          'wp_cv' => (float) Welcome Pack Cv
     *          'cv1' => (float) AMP cv
     *      ]
     * @param array $saleTypes
     * @param int $transactionTypeId master data id of the products general setting
     * @return float
     */
    public function calculateCv(array $price, array $saleTypes, int $transactionTypeId);

    /**
     * Get the current CWs Upgrade CV's for user
     *
     * @param int $userId
     * @return array
     * [
     *   'ampCvToUpgradeEachBaLevel' = <amount>,
     *   'upgradeAmpCv' = <amount>,
     *   'baUpgradeCv' = <amount>,
     *   'memberUpgradeCv' = <amount>,
     *   'currentCwId' = <id>,
     *   'currentCwLog' = <{@see \App\Models\Members\MemberEnrollmentRankUpgradeLog}>
     * ]
     */
    public function currentCwUpgradeCvForUser(int $userId);

    /**
     * get non stockist sale payment
     *
     * @return mixed
     */
    public function getYonyouIntegrationNonStockistSalePayment();
   
    /**
     * get stockist sale payment (exclude payment that need verification)
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistSalePayment();
   
    /**
     * get non stockist pre-order refund
     *
     * @return mixed
     */
    public function getYonyouIntegrationNonStockistPreOrderRefund();
   
    /**
     * get stockist pre-order refund (exclude stokist sales payment that need verification)
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistPreOrderRefund();
 
    /**
     * get non stockist sales for yonyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationNonStockistSales();
   
    /**
     * get stockist sales for yonyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationStockistSales();

    /**
     * get stockist sales that ready to release stock
     *
     * @return mixed
     */
    public function getYonyouIntegrationSalesUpdate();
  
    /**
     * get rental sales that ready to release stock
     *
     * @return mixed
     */
    public function getYonyouIntegrationSalesRentalUpdate();
   
    /**
     * get non stockist sales payment for receipt integration
     *
     * @return Payment
     */
    public function getYonyouIntegrationNonStockistSaleReceipt();
  
    /**
     * get sales exchanges (integrate revised sales order)
     *
     * @return mixed
     */
    public function getYonyouIntegrationSaleExchangeInvoice();
  
    /**
     * get sales exchanges (cancel old sales order)
     *
     * @return mixed
     */
    public function getYonyouIntegrationSaleExchangeCreditNote();
   
    /**
     * get sales cancellation for yonyou integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationSalesCancellation();
  
    /**
     * get sales cancellation for refund
     *
     * @return mixed
     */
    public function getYonyouIntegrationSalesCancellationEWallet();

    /**
     * download sales daily transaction report by below parameter
     *
     * @param int $countryId
     * @param array $locations
     * @param $dateFrom
     * @param $dateTo
     * @param array $userIds
     * @return mixed
     */
    public function downloadSalesDailyTransactionReport(
        int $countryId = 0,
        array $locations = array(),
        $dateFrom = '',
        $dateTo = '',
        array $userIds = array()
    );

    /**
     * download sales daily receipt report by below parameter
     *
     * @param int $countryId
     * @param array $locations
     * @param $dateFrom
     * @param $dateTo
     * @param array $userIds
     * @return mixed
     */
    public function downloadSaleDailyReceiptReport(
        int $countryId = 0,
        array $locations = array(),
        $dateFrom = '',
        $dateTo = '',
        array $userIds = array()
    );

    /**
     * download sales MPOS report by below parameter
     *
     * @param int $countryId
     * @param array $locations
     * @param string $dateFrom
     * @param string $dateTo
     * @return \Illuminate\Support\Collection|mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadSaleMposReport(
        int $countryId = 0,
        array $locations = array(),
        $dateFrom = '',
        $dateTo = ''
    );

    /**
     * download pre-order note
     * 
     * @param int $saleId
     * @return mixed 
     */
    public function downloadPreOrderNote(
        int $saleId
    );

    /**
     * download itemised sales report by below parameter
     *
     * @param array $countryIds
     * @param array $locationIds
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $fromCw
     * @param int $toCw
     * @param array $broadCategories
     * @param array $subCategories
     * @param array $minorCategories
     * @return \Illuminate\Support\Collection|mixed
     */
    public function downloadSaleProductReport(
        array $countryIds = array(),
        array $locationIds = array(),
        $dateFrom = '',
        $dateTo = '',
        $fromCw = 0,
        $toCw = 0,
        array $broadCategories = array(),
        array $subCategories = array(),
        array $minorCategories = array()
    );
}