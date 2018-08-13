<?php
namespace App\Interfaces\Sales;

use App\Models\Sales\SaleExchange;

interface SaleExchangeInterface
{
    /**
     * get sales Exchanges by filters
     *
     * @param int $countryId
     * @param string $text
     * @param string $dateFrom
     * @param string $dateTo
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getSalesExchangeByFilters(
        int $countryId,
        string $text = '',
        $dateFrom = '',
        $dateTo = '',
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get saleExchange details for a given id
     *
     * @param int $saleExchangeId
     * @return mixed
     */
    public function saleExchangeDetails(int $saleExchangeId);

    /**
     * create new saleExchange
     *
     * @param array $data
     * @return mixed
     */
    public function createSaleExchange(array $data);

    /**
     * get sales exchange for a given id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

    /**
     * generate exchange bill and credit note for the given sales exchange model obj
     *
     * @param SaleExchange $saleExchange
     * @return mixed
     */
    public function generateExchangeBillAndCreditNote(SaleExchange $saleExchange);

    /**
     * deduct the returned products qty if payment is done and no amount to pay
     *
     * @param SaleExchange $saleExchange
     * @return mixed
     */
    public function deductReturnedProductQty(SaleExchange $saleExchange);

    /**
     * To download exchange bill in pdf and export as content-stream header 'application/pdf'
     *
     * @param int $salesExchangeBillId
     * @return Collection|mixed
     * @throws \Mpdf\MpdfException
     */ 
    public function downloadExchangeBill(int $salesExchangeBillId);
}