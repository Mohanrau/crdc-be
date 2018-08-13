<?php
namespace App\Interfaces\Payments;

use App\Models\Sales\Sale;
use Illuminate\Http\Request;

interface PaymentInterface
{
    /**
     * Map payment object
     *
     * @param $countryCodes
     * @param string $paymentProvider - name of the payment provider, must be in iso_2 format
     * @return mixed
     * @throws \Exception
     */
    public function getPaymentObject(string $countryCodes, string $paymentProvider = '');

    /**
     * Return the supported payments for the specific country + location type
     *
     * @param int $countryId
     * @param int $locationId
     * @param array $excludePaymentModes
     * @param array $excludePaymentProviders
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getSupportedPayments
    (
        int $countryId,
        int $locationId,
        array $excludePaymentModes = [],
        array $excludePaymentProviders = []
    );

    /**
     * Consolidated Make Payment Function
     *
     * @param string $payType
     * @param int $paymentId
     * @param array $field
     * @param int $saleId
     * @param int $consignmentDepositId
     * @param int $ewalletId
     * @param bool $isShare
     * @return array
     */
    public function makePayment(
        string $payType,
        int $paymentId,
        array $field,
        int $saleId = 0,
        int $consignmentDepositId = 0,
        int $ewalletId = 0,
        bool $isShare = false
    );

    /**
     * get third party share payment post data for a given paymentId
     *
     * @param int $paymentId
     * @return array|mixed
     */
    public function sharePaymentDetail(int $paymentId);

    /**
     * To make a payment to a sale
     *
     * @param int $saleId
     * @param int $paymentId
     * @param $fields
     * @param bool $isShare
     * @return mixed
     */
    public function salesPay(
        int $saleId,
        int $paymentId,
        $fields,
        bool $isShare = false
    );

    /**
     * Provide callback function from payment processor
     *
     * @param int salePaymentId
     * @param bool isBackendCall
     * @param Request $request
     * @return bool
     */
    public function processCallback(int $salePaymentId, $isBackendCall, Request $request);

    /**
     * get EPP filtered by the following parameters
     *
     * @param int $countryId
     * @param $dateFrom
     * @param $dateTo
     * @param string $text
     * @param int $locationTypeId
     * @param int $eppModeId
     * @param int $approvalStatusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function eppPaymentListing(
        int $countryId,
        $dateFrom = '',
        $dateTo = '',
        string $text = '',
        int $locationTypeId = 0,
        int $eppModeId = 0,
        int $approvalStatusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * update epp moto approve code
     *
     * @param int $paymentId
     * @param string $approveCode
     * @return mixed
     */
    public function updateEppMotoApproveCode(int $paymentId, string $approveCode);

    /**
     * batch covert epp payment to valid sales order
     *
     * @param array $salePaymentIds
     * @return mixed
     */
    public function eppPaymentSaleConvert(array $salePaymentIds);

    /**
     * get aeon filtered by the following parameters
     *
     * @param int $countryId
     * @param $dateFrom
     * @param $dateTo
     * @param string $text
     * @param int $locationTypeId
     * @param int $approvalStatusId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function aeonPaymentListing(
        int $countryId,
        $dateFrom = '',
        $dateTo = '',
        string $text = '',
        int $locationTypeId = 0,
        int $approvalStatusId = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * update aeon application agreement number
     *
     * @param int $paymentId
     * @param string $agreementNumber
     * @param $approvedAmount
     * @return mixed
     */
    public function updateAeonAgreementNumber(int $paymentId, string $agreementNumber, $approvedAmount);

    /**
     * batch release aeon payment from cooling off period
     *
     * @param array $salePaymentIds
     * @return boolean
     */
    public function aeonPaymentCoolingOffRelease(array $salePaymentIds);

    /**
     * batch payment cancel
     *
     * @param string $paymentMode
     * @param array $salePaymentIds
     * @return mixed
     */
    public function paymentBatchCancel(string $paymentMode, array $salePaymentIds);

    /**
     * pre-order sale convert to actual sale
     *
     * @param Sale $sale
     * @param bool $skipConvertRentalSaleOrder
     * @return boolean
     */
    public function actualSalesGenerated(Sale $sale, bool $skipConvertRentalSaleOrder = true);

    /**
     * To make a payment to a consignment deposit
     * @param int $consignmentDepositId
     * @param int $paymentId
     * @param $fields
     * @param bool $isShare
     * @return array
     */
    public function consignmentDepositPay(
        int $consignmentDepositId,
        int $paymentId,
        $fields,
        bool $isShare = false
    );

    /**
     * To make a payment to topup E-Wallet
     *
     * @param int $eWalletId
     * @param int $paymentModeId
     * @param $fields
     * @param bool $isShare
     * @return array
     * @throws \Exception
     */
    public function eWalletPay(
        int $eWalletId,
        int $paymentModeId,
        $fields,
        bool $isShare = false
    );

    /**
     * payment api for external use
     *
     * @param array $inputs
     * @return mixed
     */
    public function externalPayment(array $inputs);

    /**
     * Get Payment Mode Document Details
     *
     * @param int $countryId
     * @param int $paymentModeProviderId
     * @return mixed
     */
    public function getPaymentModeDocumentDetails(int $countryId, int $paymentModeProviderId);
}