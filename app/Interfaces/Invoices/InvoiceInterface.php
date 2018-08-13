<?php
namespace App\Interfaces\Invoices;

use App\Interfaces\Payments\PaymentInterface;
use App\Models\Sales\Sale;

interface InvoiceInterface
{
    /**
     * get all invoices records or subset based on pagination
     *
     * @param int $countryId
     * @param string $text
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $userId
     * @param bool $isSaleCancellation
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getInvoicesByFilters(
        int $countryId,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $userId = NULL,
        bool $isSaleCancellation = false,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get invoice by id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * get invoice details for a given invoiceId
     *
     * @param int $invoiceId
     * @return mixed
     */
    public function invoiceDetails(int $invoiceId);

    /**
     * generate invoice for a given sale
     *
     * @param Sale $sale
     * @return mixed
     */
    public function generateInvoice(Sale $sale);

    /**
     * Download pdf of invoice
     *
     * @param int $invoiceId
     * @param boolean $isLegacy
     * @return \Illuminate\Support\Collection
     * @throws \Mpdf\MpdfException
     */
    public function downloadPDF(int $invoiceId, bool $isLegacy);

    /**
     * get stockist daily invoice transcation list by below parameter
     *
     * @param PaymentInterface $paymentRepositoryObj
     * @param int $stockistNumber
     * @param $filterDate
     * @param int $stockistDailyTransactionStatusId
     * @return mixed
     */
    public function getStockistDailyInvoiceTransactionList
    (
        PaymentInterface $paymentRepositoryObj,
        int $stockistNumber = 0,
        $filterDate = '',
        int $stockistDailyTransactionStatusId = 0
    );

    /**
     * batch release stockist invoice transaction
     *
     * @param array $stockistInvoices
     * @return mixed
     */
    public function batchReleaseStockistDailyInvoiceTransaction(array $stockistInvoices);
    
    /**
     * To get stockist sales with integrated_flag = 0'
     *
     * @param bool $isIntegrated
     * @return mixed
     */
    public function getIntegrationReleaseStockistSales(bool $isIntegrated);
    
    /**
     * To download pdf and export as content-stream header 'application/pdf'
     *
     * @param int $invoiceId
     * @param boolean $isLegacy
     * @return mixed
     */
    public function downloadAutoMaintenanceInvoice(int $invoiceId, bool $isLegacy = false);

    /**
     * To download tax invoice summary report in excel format
     *
     * @param int $countryId
     * @param array $locationIds
     * @param string $fromDate
     * @param string $toDate
     * @param int $fromCw
     * @param int $toCw
     * @param array $iboIds
     * @param int $status
     * @return mixed
     */
    public function downloadTaxInvoiceSummaryReport
    (
        $countryId = 0,
        array $locationIds,
        $fromDate = "",
        $toDate = "",
        $fromCw = 0,
        $toCw = 0,
        array $iboIds,
        $status = 0
    );
    
    /**
     * To download tax invoice product details report in excel format
     *
     * @param int $countryId
     * @param array $locationIds
     * @param string $fromDate
     * @param string $toDate
     * @param int $fromCw
     * @param int $toCw
     * @param array $iboIds
     * @return mixed
     */
    public function downloadTaxInvoiceDetailsReport
    (
        $countryId = 0,
        array $locationIds,
        $fromDate = "",
        $toDate = "",
        $fromCw = 0,
        $toCw = 0,
        array $iboIds
    );
}