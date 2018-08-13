<?php
namespace App\Jobs;

use Illuminate\{
    Bus\Queueable,
    Queue\InteractsWithQueue,
    Contracts\Queue\ShouldQueue,
    Foundation\Bus\Dispatchable
};
use App\Interfaces\{
    Integrations\YonyouInterface,
    Sales\SaleInterface,
    Stockists\StockistInterface,
    Locations\LocationInterface
};
use App\Models\{
    Payments\Payment,
    Stockists\Stockist
};
use App\Helpers\Classes\MemberAddress;
use Carbon\Carbon;

class SendReceiptToYY implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    private
        $jobData,
        $jobType,
        $dataModel,
        $dataId,
        $mappingModel,
        $mappingId,
        $yonyouRepositoryObj,
        $stockistRepositoryObj,
        $locationRepositoryObj,
        $paymentObj,
        $memberAddressHelper,
        $requestData;

    /**
     * SendReceiptToYY constructor.
     *
     * @param string $jobType
     * @param string $dataModel
     * @param int $dataId
     * @param string $mappingModel
     * @param int $mappingId
     * @return void
     */
    public function __construct(
        string $jobType,
        string $dataModel,
        int $dataId,
        string $mappingModel,
        int $mappingId
    )
    {
        $this->jobType = $jobType;
        $this->dataModel = $dataModel;
        $this->dataId = $dataId;
        $this->mappingModel = $mappingModel;
        $this->mappingId = $mappingId;
        $this->requestData = '';
    }

    /**
     * Execute the job. (SO_Receipt)
     *
     * @param YonyouInterface $yonyouInterface
     * @param StockistInterface $stockistInterface
     * @param LocationInterface $locationInterface
     * @param Payment $payment
     * @param MemberAddress $memberAddress
     * @return void
     */
    public function handle(
        YonyouInterface $yonyouInterface,
        StockistInterface $stockistInterface,
        LocationInterface $locationInterface, 
        Payment $payment,
        MemberAddress $memberAddress
    )
    {
        try 
        {
            $this->yonyouRepositoryObj = $yonyouInterface;
            
            $this->stockistRepositoryObj = $stockistInterface;
            
            $this->locationRepositoryObj = $locationInterface;
            
            $this->paymentObj = $payment;
            
            $this->memberAddressHelper = $memberAddress;
            
            if ($this->yonyouRepositoryObj->executeIntegrationJob($this->jobType, $this->mappingModel, $this->mappingId)) 
            {
                $this->jobData = $this->yonyouRepositoryObj
                    ->getMappingModelObject($this->dataModel, $this->dataId, true);

                $this->processIntegration();
            }
        }
        catch (\Exception $exception)
        {
            $this->yonyouRepositoryObj->createIntegrationLog(
                $this->jobType, $this->mappingModel, $this->mappingId, $this->requestData, 
                $this->yonyouRepositoryObj->serializeException($exception), '999'
            );
        }
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        echo ($exception->getMessage());
    }

    /**
     * call Yonyou API based on the job type
     *
     */
    public function processIntegration()
    {
        switch (strtoupper($this->jobType))  {
            case "SALES_RECEIPT":
                $this->requestData = $this->formatDataStructure($this->getSalesReceiptAPIParameter());
                break;
            case "SALES_CANCELLATION":
                $this->requestData = $this->formatDataStructure($this->getSalesCancellationReceiptAPIParameter());
                break;
            case "SALES_EXCHANGE_RECEIPT":
                $this->requestData = $this->formatDataStructure($this->getSalesExchangeCancellationReceiptAPIParameter());
                break;
            case "STOCKIST_RECEIPT":
                $this->requestData = $this->formatDataStructure($this->getStockistReceiptAPIParameter());
                break;
            case "STOCKIST_RECEIPT_ADJ":
                $this->requestData = $this->formatDataStructure($this->getStockistReceiptAdjAPIParameter());
                break;
            case "STOCKIST_PREORDER_DEPOSIT":
                $this->requestData = $this->formatDataStructure($this->getStockistPreOrderPaymentAPIParameter());
                break;
            case "STOCKIST_PREORDER_REFUND":
                $this->requestData = $this->formatDataStructure($this->getStockistPreOrderRefundAPIParameter());
                break;
        }
        
        $apiPath = config('integrations.yonyou.receipt_api.path');
        
        $results = $this->yonyouRepositoryObj->sendIntegrationRequest($apiPath, $this->requestData);
        
        $returnCode = collect($results[0])->get('returncode');
        
        $this->yonyouRepositoryObj->completeIntegrationJob(
            $this->jobType, $this->mappingModel, $this->mappingId, $returnCode
        );
        
        $this->yonyouRepositoryObj->createIntegrationLog(
            $this->jobType, $this->mappingModel, $this->mappingId, $this->requestData, $results, $returnCode
        );
    }

    /**
     * format data into yonyou so receipt json structure 
     *
     * @param array $data
     * @return array
     */
    public function formatDataStructure($data)
    {
        if (!empty($data['salePayment'])) {
            foreach ($data['salePayment'] as $key => $salePayment) {
                 $receiptDetails['arap.gatheritem'][] = [
                    'rowno' => $salePayment['rowno'], //Line No.
                    'pk_currtype' => $data['currency'], //Currency Code
                    'money_cr' => $salePayment['money_cr'], //Total Amount (Include Tax)
                    'prepay' => 0, //Is Prepay
                    'objtype' => 0, //Collection objects
                    'customer' => $data['customerid'], //Customer Code
                    'recaccount' => $salePayment['recaccount'], //Collection Bank Account
                    'purchaseorder' => $data['purchaseorder'], //Sale Order No./Batchnumber
                    'invoiceno' => $data['invoicenumber'], //Tax Invoice No.
                    'pk_balatype' => $salePayment['pk_balatype'], //Payment method
                    'cashitem' => '', //Cash Item
                    'scomment' => $data['remark'], //Comment
                    'def1' => $data['membercode'], //Member ID
                    'def2' => $data['membername'], //Member Name
                    'def3' => $data['memberaddress'], //Member Address
                    'def4' => '', //Booking ref no
                    'def12' => $salePayment['def12'], //Approval Code/Settlement No.
                    'pk_deptid' => $data['tranloc'],// NC Department Code
                    'def13' => $data['def13'], //Cost Centre
                    'def5' => $salePayment['def5'], //CC Type
                    'def6' => $salePayment['def6'], //IPP Tenure
                ];
            }
        }
        //for sale exchange credit note - no refund (credit to other income, pe gain)
        else
        {
            $receiptDetails['arap.gatheritem'][] = [
                'rowno' => $data['rowno'], //Line No.
                'pk_currtype' => $data['currency'], //Currency Code
                'money_cr' => $data['amount'], //Total Amount (Include Tax)
                'prepay' => $data['prepay'], //Is Prepay
                'objtype' => 0, //Collection objects
                'customer' => $data['customerid'], //Customer Code
                'recaccount' => $data['recaccount'], //Collection Bank Account
                'cashaccount' => '', //Collection Cash Account
                'purchaseorder' => $data['purchaseorder'], //Sale Order No./Batchnumber
                'invoiceno' => $data['invoicenumber'], //Tax Invoice No.
                'pk_balatype' => config('integrations.yonyou.nc_virtual_payment_mode.peGain'), //Payment method
                'cashitem' => '', //Cash Item
                'scomment' => $data['remark'], //Comment
                'def1' => $data['membercode'], //Member ID
                'def2' => $data['membername'], //Member Name
                'def3' => $data['memberaddress'], //Member Address
                'def4' => '', //Booking ref no
                'def12' => '', //Approval Code/Settlement No.
                'pk_deptid' =>$data['tranloc'], //NC Department Code
                'def13' => $data['def13'], //Cost Centre
                'def5' => $data['def5'], //CC Type
                'def6' => $data['def6'], //IPP Tenure
            ];
        }

        $jsonBody = [
            array('arap.gatherbill' => array(
                'pk_org' => $data['entity'], //Entity Code
                'pk_tradetype' => config('integrations.yonyou.nc_trn_type.receiptar'), //NC Transaction Type Code
                'billno' => $data['billno'], //Receipt Bill No.
                'billdate' => $data['billdate'], //Receipt Document Date
                'paydate' => $data['paydate'], //Pay Date
                'def5' => '', //Source Doc No. (CN)
                'def20' => config('integrations.yonyou.source_type') //Source System
            ),
                'arap.gatheritem'=>$receiptDetails['arap.gatheritem']
            )
        ];

        return $jsonBody;
    }

    /**
     * get sales payment api parameter
     *
     * @return array
     */
    public function getSalesReceiptAPIParameter()
    {
        $sale = $this->jobData;

        $salePayments = $sale->salePayments;

        $memAddress = '';
        if (!empty($sale->member->address->address_data)) {
            $memAddress = $this->memberAddressHelper
                ->getAddress($sale->member->address->address_data, '');
        }

        $entityName = strtoupper($sale->country->entity->name);

        $isStockistSale = strtolower($sale->channel->code) == config('mappings.locations_types.stockist');

        $isOnlineSale = strtolower($sale->channel->code) == config('mappings.locations_types.online');

        if ($isStockistSale) {
            $customerCode = $sale->transactionLocation->code;
            $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        } else if ($isOnlineSale) {
            $customerCode = config('integrations.yonyou.nc_online_customer_code.' . $entityName);
            if (strtolower($sale->transactionLocation->locationType->code) == config('mappings.locations_types.stockist')) {
                $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
            }
            else {
                $costCentre = $sale->transactionLocation->code;
            }
        } else {
            $customerCode = config('integrations.yonyou.nc_branch_customer_code.' . $entityName);
            $costCentre = $sale->transactionLocation->code;
        }

        foreach ($salePayments as $key => $salePayment) {
            if (($isStockistSale == false) || ($isStockistSale == true && $salePayment->paymentModeProvider['is_stockist_payment_verification'] == 0)) {
                $bankAccount = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
                $IppTenure = '';
                $approvalCode = '';
                $paymentDetail = json_decode($salePayment->payment_detail);
                if (strtolower($salePayment->paymentModeProvider->code) == 'epp_online_ipay88') {
                    foreach ($paymentDetail->fields as $paymentDetailField) {
                        if (strtolower($paymentDetailField->name) == 'payment_id') {
                            $bankAccount = config('integrations.yonyou.nc_ipay88_epp_bank_account.' . $paymentDetailField->value);
                        } else if (strtolower($paymentDetailField->name) == 'plan') {
                            $IppTenure = $paymentDetailField->value;
                        } else if (strtolower($paymentDetailField->name) == 'approval_code') {
                            $approvalCode = $paymentDetailField->value;
                        }
                    }
                }
                else {
                    foreach ($paymentDetail->fields as $paymentDetailField) {
                        if (strtolower($paymentDetailField->name) == 'approval_code') {
                            $approvalCode = $paymentDetailField->value;
                        }
                    }
                    if (!empty($paymentDetail->payment_response->approval_code)) {
                        $approvalCode = $paymentDetail->payment_response->approval_code;
                    }
                }
                
                $receiptDetails[$key] = [
                    'rowno' => $salePayment->id,
                    'money_cr' => number_format($salePayment->amount, 2),
                    'prepay' => 0,
                    'pk_balatype' => config('integrations.yonyou.nc_virtual_payment_mode.preOrder'),
                    'cashitem' => '',
                    'objtype' => 0, 
                    'def5' => config('integrations.yonyou.nc_cc_type.' . $salePayment->paymentModeProvider->code),
                    'recaccount' => $bankAccount,
                    'def6' => $IppTenure,
                    'def12' => $approvalCode
                ];
            }
        }

        $data = array();
        $data['billno'] = $sale->document_number;
        $data['paydate'] = Carbon::parse($sale->salePayments->first()->created_at)->format('Y-m-d H:m:s');
        $data['membercode'] = $sale->user->old_member_id;
        $data['membername'] = $sale->member->name;
        $data['memberaddress'] = $memAddress;
        $data['invoicenumber'] = (!empty($sale->invoices->invoice_number) ? $sale->invoices->invoice_number : '');
        $data['customerid'] = $customerCode;
        $data['purchaseorder'] = $sale->document_number;
        $data['prepay'] = 0;
        $data['currency'] = $sale->country->currency->code;
        $data['remark'] = '';
        $data['rate'] = 1.00;
        $data['amount'] = $sale->total_gmp;
        $data['salePayment'] = $receiptDetails;
        $data['entity'] = $entityName;
        $data['billdate'] = $sale->transaction_date;
        $data['isProductExchange'] = $sale->is_product_exchange;
        $data['recaccount'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['tranloc'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['def13'] = $costCentre;
        return $data;
    }

    /**
     * get sales cancellation api parameter
     *
     * @return array
     */
    public function getSalesCancellationReceiptAPIParameter()
    {
        $saleCancellation = $this->jobData;

        $sale = $saleCancellation->sale;

        $salePayments = $sale->salePayments;

        $entityName = strtoupper($sale->country->entity->name);

        foreach ($salePayments as $key => $salePayment) {
            $approvalCode = '';
            $paymentDetail = json_decode($salePayment->payment_detail);
            foreach ($paymentDetail->fields as $paymentDetailField) {
                if (strtolower($paymentDetailField->name) == 'approval_code') {
                    $approvalCode = $paymentDetailField->value;
                }
            }
            if (!empty($paymentDetail->payment_response->approval_code)) {
                $approvalCode = $paymentDetail->payment_response->approval_code;
            }

            $receiptDetails[$key] = [
                'rowno' => $salePayment->id,
                'money_cr' => $salePayment->amount,
                'prepay' => 0,
                'pk_balatype' => config('integrations.yonyou.nc_payment_mode.' . $salePayment->paymentModeProvider->code),
                'cashitem' => '',
                'objtype' => 0,
                'def5' => config('integrations.yonyou.nc_cc_type.' . $salePayment->paymentModeProvider->code),
                'recaccount' => config('integrations.yonyou.nc_virtual_bank_account.' . $entityName),
                'def6' => '',
                'def12' => $approvalCode
            ];
        }

        $memAddress = '';
        if (!empty($sale->member->address->address_data)) {
            $memAddress = $this->memberAddressHelper
                ->getAddress($sale->member->address->address_data, '');
        }

        $isStockistSale = strtolower($sale->channel->code) == config('mappings.locations_types.stockist');

        $isOnlineSale = strtolower($sale->channel->code) == config('mappings.locations_types.online');

        if ($isStockistSale) {
            $customerCode = $sale->transactionLocation->code;
            $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        } else if ($isOnlineSale) {
            $customerCode = config('integrations.yonyou.nc_online_customer_code.' . $entityName);
            if (strtolower($sale->transactionLocation->locationType->code) == config('mappings.locations_types.stockist')) {
                $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
            }
            else {
                $costCentre = $sale->transactionLocation->code;
            }
        } else {
            $customerCode = config('integrations.yonyou.nc_branch_customer_code.' . $entityName);
            $costCentre = $sale->transactionLocation->code;
        }

        $data = array();
        $data['billno'] = $saleCancellation->creditNote->credit_note_number;
        $data['paydate'] = Carbon::parse($saleCancellation->transaction_date)->format('Y-m-d H:m:s');
        $data['membercode'] = $sale->user->old_member_id;
        $data['membername'] = $sale->member->name;
        $data['memberaddress'] = $memAddress;
        $data['invoicenumber'] = $saleCancellation->creditNote->credit_note_number;
        $data['customerid'] = $customerCode;
        $data['purchaseorder'] = $saleCancellation->creditNote->credit_note_number;
        $data['prepay'] = 0;
        $data['currency'] = $sale->country->currency->code;
        $data['remark'] = $saleCancellation->remarks;
        $data['rate'] = 1.00;
        $data['amount'] = 0 - $saleCancellation->total_amount;
        $data['salePayment'] = $receiptDetails;
        $data['entity'] = $entityName;
        $data['billdate'] = $sale->transaction_date;
        $data['isProductExchange'] = $sale->is_product_exchange;
        $data['recaccount'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['tranloc'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['def13'] = $costCentre;
        return $data;
    }

    /**
     * get sales exchange credit note api parameter
     *
     * @return array
     */
    public function getSalesExchangeCancellationReceiptAPIParameter()
    {
        $saleExchange = $this->jobData;

        $sale = $saleExchange->sale;

        $memAddress = '';
        if (!empty($sale->member->address->address_data)) {
            $memAddress = $this->memberAddressHelper
                ->getAddress($sale->member->address->address_data, '');
        }

        $entityName = strtoupper($sale->country->entity->name);

        $isStockistSale = strtolower($sale->channel->code) == config('mappings.locations_types.stockist');

        $isOnlineSale = strtolower($sale->channel->code) == config('mappings.locations_types.online');

        if ($isStockistSale) {
            $customerCode = $sale->transactionLocation->code;
            $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        } else if ($isOnlineSale) {
            $customerCode = config('integrations.yonyou.nc_online_customer_code.' . $entityName);
            if (strtolower($sale->transactionLocation->locationType->code) == config('mappings.locations_types.stockist')) {
                $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
            }
            else {
                $costCentre = $sale->transactionLocation->code;
            }
        } else {
            $customerCode = config('integrations.yonyou.nc_branch_customer_code.' . $entityName);
            $costCentre = $sale->transactionLocation->code;
        }

        $data = array();
        $data['billno'] = $saleExchange->creditNote->credit_note_number;
        $data['paydate'] = Carbon::parse($saleExchange->transaction_date)->format('Y-m-d H:m:s');
        $data['membercode'] = $saleExchange->user->old_member_id;
        $data['membername'] = $saleExchange->member->name;
        $data['memberaddress'] = $memAddress;
        $data['invoicenumber'] = $saleExchange->creditNote->credit_note_number;
        $data['customerid'] = $customerCode;
        $data['purchaseorder'] = $saleExchange->creditNote->credit_note_number;
        $data['prepay'] = 0;
        $data['currency'] = $saleExchange->country->currency->code;
        $data['remark'] = $saleExchange->remarks;
        $data['rate'] = 1.00;
        $data['amount'] = number_format(($saleExchange->balance>0 ? $saleExchange->balance : ($saleExchange->exchange_amount_total - $saleExchange->return_amount_total)), 2);
        $data['salePayment'] = '';
        $data['entity'] = $entityName;
        $data['rowno'] = $saleExchange->id;
        $data['billdate'] = $sale->transaction_date;
        $data['isProductExchange'] = $sale->is_product_exchange;
        $data['recaccount'] = config('integrations.yonyou.nc_virtual_bank_account.' . $saleExchange->country->entity->name);
        $data['tranloc'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['def13'] = $costCentre;
        $data['def5'] = '';
        $data['def6'] = '';
        return $data;
    }

    /**
     * get stockist receipt payment api parameter
     *
     * @return array
     */
    public function getStockistReceiptAPIParameter()
    {
        $stockistPaymentCollection = $this->jobData;

        $stockistPayments = $stockistPaymentCollection->stockistSalePayment;
        
        $entityName = strtoupper($stockistPayments->stockist->country->entity->name);

        $receiptDetails[] = [
            'rowno' => $stockistPaymentCollection->id,
            'money_cr' => $stockistPaymentCollection->paid_amount,
            'prepay' => 0,
            'pk_balatype' => config('integrations.yonyou.nc_payment_mode.' . $stockistPayments->paymentProvider->code),
            'cashitem' => '',
            'objtype' => 0,
            'def5' => config('integrations.yonyou.nc_cc_type.' . $stockistPayments->paymentProvider->code),
            'recaccount' => config('integrations.yonyou.nc_virtual_bank_account.' . $stockistPayments->stockist->country->entity->name),
            'def6' => '',
            'def12' => ''
        ];
        $data = array();
        $data['billno'] = 'STKP'. str_pad($stockistPaymentCollection->id, 10, '0', STR_PAD_LEFT);
        $data['paydate'] = Carbon::parse($stockistPaymentCollection->created_at)->format('Y-m-d H:m:s');
        $data['membercode'] = $stockistPayments->stockist->stockist_number;
        $data['membername'] = $stockistPayments->stockist->name;
        $data['memberaddress'] = '';
        $data['invoicenumber'] = 'STKP'. str_pad($stockistPaymentCollection->id, 10, '0', STR_PAD_LEFT);
        $data['customerid'] = $stockistPayments->stockist->stockist_number;
        $data['purchaseorder'] = 'STKP'. str_pad($stockistPaymentCollection->id, 10, '0', STR_PAD_LEFT);
        $data['prepay'] = 0;
        $data['currency'] = $stockistPayments->stockist->country->currency->code;
        $data['remark'] = '';
        $data['rate'] = 1.00;
        $data['amount'] = $stockistPaymentCollection->paid_amount;
        $data['salePayment'] = $receiptDetails;
        $data['entity'] = $entityName;
        $data['billdate'] = Carbon::parse($stockistPaymentCollection->created_at)->format('Y-m-d H:m:s');
        $data['isProductExchange'] = 0;
        $data['recaccount'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['tranloc'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['def13'] = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        return $data;
    }

    /**
     * get stockist receipt adjustment api parameter
     *
     * @return array
     */
    public function getStockistReceiptAdjAPIParameter()
    {
        $stockistPaymentCollection = $this->jobData;

        $stockistPayments = $stockistPaymentCollection->stockistSalePayment;

        $entityName = strtoupper($stockistPayments->stockist->country->entity->name);

        $receiptDetails[] = [
                'rowno' => $stockistPaymentCollection->id,
                'money_cr' => $stockistPaymentCollection->adjustment_amount,
                'prepay' => 0,
                'pk_balatype' => config('integrations.yonyou.nc_payment_mode.' . $stockistPayments->paymentProvider->code),
                'cashitem' => '',
                'objtype' => 0,
                'def5' => config('integrations.yonyou.nc_cc_type.' . $stockistPayments->paymentProvider->code),
                'recaccount' => config('integrations.yonyou.nc_virtual_bank_account.' . $entityName),
                'def6' => '',
                'def12' => ''
            ];
        $data = array();
        $data['billno'] = 'STKPA'. str_pad($stockistPaymentCollection->id, 10, '0', STR_PAD_LEFT);
        $data['paydate'] = Carbon::parse($stockistPaymentCollection->created_at)->format('Y-m-d H:m:s');
        $data['membercode'] = $stockistPayments->stockist->stockist_number;
        $data['membername'] = $stockistPayments->stockist->name;
        $data['memberaddress'] = '';
        $data['invoicenumber'] = 'STKPA'. str_pad($stockistPaymentCollection->id, 10, '0', STR_PAD_LEFT);
        $data['customerid'] = $stockistPayments->stockist->stockist_number;
        $data['purchaseorder'] = 'STKPA'. str_pad($stockistPaymentCollection->id, 10, '0', STR_PAD_LEFT);
        $data['prepay'] = 0;
        $data['currency'] = $stockistPayments->stockist->country->currency->code;
        $data['remark'] = '';
        $data['rate'] = 1.00;
        $data['amount'] = $stockistPaymentCollection->adjustment_amount;
        $data['salePayment'] = $receiptDetails;
        $data['entity'] = $entityName;
        $data['billdate'] = Carbon::parse($stockistPaymentCollection->created_at)->format('Y-m-d H:m:s');
        $data['isProductExchange'] = 0;
        $data['recaccount'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['tranloc'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['def13'] = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        return $data;
    }

    /**
     * get stockist pre order payment api parameter
     *
     * @return array
     */
    public function getStockistPreOrderPaymentAPIParameter()
    {
        $stockistPayments = $this->jobData;
        
        $sale = $this->jobData->sale;
        
        $locationChannel = $this->locationRepositoryObj->find($sale->transaction_location_id);
        
        $stockistObj = Stockist::where('stockist_number', $locationChannel->code)->get()->toArray()[0];
        
        $stockistInfo = $this->stockistRepositoryObj->find($stockistObj['id']);
        
        $entityName = strtoupper($stockistInfo->country->entity->name);

        $approvalCode = '';
        $paymentDetail = json_decode($stockistPayments->payment_detail);
        foreach ($paymentDetail->fields as $paymentDetailField) {
            if (strtolower($paymentDetailField->name) == 'approval_code') {
                $approvalCode = $paymentDetailField->value;
            }
        }
        if (!empty($paymentDetail->payment_response->approval_code)) {
            $approvalCode = $paymentDetail->payment_response->approval_code;
        }

        $receiptDetails[] = [
                'rowno' => $stockistPayments->id,
                'money_cr' => $stockistPayments->amount,
                'prepay' => 0,
                'pk_balatype' => config('integrations.yonyou.nc_payment_mode.' . $stockistPayments->paymentModeProvider->code),
                'cashitem' => '',
                'objtype' => 0,
                'def5' => config('integrations.yonyou.nc_cc_type.' . $stockistPayments->paymentModeProvider->code),
                'recaccount' => config('integrations.yonyou.nc_virtual_bank_account.' . $entityName),
                'def6' => '',
                'def12' => $approvalCode
            ];
     
        $data = array();
        $data['billno'] = $sale->document_number . '-' . $stockistPayments->id;
        $data['paydate'] = Carbon::parse($stockistPayments->created_at)->format('Y-m-d H:m:s');
        $data['membercode'] = $stockistInfo->stockist_number;
        $data['membername'] = $stockistInfo->name;
        $data['memberaddress'] = '';
        $data['invoicenumber'] = '';
        $data['customerid'] = $stockistInfo->stockist_number;
        $data['purchaseorder'] = $sale->document_number;
        $data['prepay'] = 0;
        $data['currency'] = $stockistPayments->currency->code;
        $data['remark'] = '';
        $data['rate'] = 1.00;
        $data['amount'] = $stockistPayments->amount;
        $data['salePayment'] = $receiptDetails;
        $data['entity'] = $entityName;
        $data['billdate'] = Carbon::parse($stockistPayments->created_at)->format('Y-m-d H:m:s');
        $data['isProductExchange'] = 0;
        $data['recaccount'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['tranloc'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['def13'] = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        return $data;
    }

    /**
     * get stockist pre order refund api parameter
     * 
     * @return array
     */
    public function getStockistPreOrderRefundAPIParameter()
    {
        $stockistPayments = $this->jobData;
                
        $sale = $this->jobData->sale;

        $locationChannel = $this->locationRepositoryObj->find($sale->transaction_location_id);

        $stockistObj = Stockist::where('stockist_number', $locationChannel->code)->get()->toArray()[0];

        $stockistInfo = $this->stockistRepositoryObj->find($stockistObj['id']);

        $entityName = strtoupper($stockistInfo->country->entity->name);

        $approvalCode = '';
        $paymentDetail = json_decode($stockistPayments->payment_detail);
        foreach ($paymentDetail->fields as $paymentDetailField) {
            if (strtolower($paymentDetailField->name) == 'approval_code') {
                $approvalCode = $paymentDetailField->value;
            }
        }
        if (!empty($paymentDetail->payment_response->approval_code)) {
            $approvalCode = $paymentDetail->payment_response->approval_code;
        }

        $receiptDetails[] = [
                'rowno' => $stockistPayments->id,
                'money_cr' => (0 - $stockistPayments->amount),
                'prepay' => 0,
                'pk_balatype' => config('integrations.yonyou.nc_payment_mode.' . $stockistPayments->paymentModeProvider->code),
                'cashitem' => '',
                'objtype' => 0,
                'def5' => config('integrations.yonyou.nc_cc_type.' . $stockistPayments->paymentModeProvider->code),
                'recaccount' => config('integrations.yonyou.nc_virtual_bank_account.' . $entityName),
                'def6' => '',
                'def12' => $approvalCode
            ];

        $data = array();
        $data['billno'] = $sale->document_number . '-' . $stockistPayments->id . 'R';
        $data['paydate'] = Carbon::parse($stockistPayments->created_at)->format('Y-m-d H:m:s');
        $data['membercode'] = $stockistInfo->stockist_number;
        $data['membername'] = $stockistInfo->name;
        $data['memberaddress'] = '';
        $data['invoicenumber'] = '';
        $data['customerid'] = $stockistInfo->stockist_number;
        $data['purchaseorder'] = $sale->document_number;
        $data['prepay'] = 0;
        $data['currency'] = $stockistPayments->currency->code;
        $data['remark'] = '';
        $data['rate'] = 1.00;
        $data['amount'] = $stockistPayments->amount;
        $data['salePayment'] = $receiptDetails;
        $data['entity'] = $entityName;
        $data['billdate'] = Carbon::parse($stockistPayments->created_at)->format('Y-m-d H:m:s');
        $data['isProductExchange'] = 0;
        $data['recaccount'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['tranloc'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['def13'] = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        return $data;
    }
}
