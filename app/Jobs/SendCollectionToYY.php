<?php
namespace App\Jobs;

use Illuminate\{
    Bus\Queueable,
    Queue\SerializesModels,
    Queue\InteractsWithQueue,
    Contracts\Queue\ShouldQueue,
    Foundation\Bus\Dispatchable
};
use App\Interfaces\{
    Integrations\YonyouInterface,
    Stockists\StockistInterface,
    Locations\LocationInterface,
    Masters\MasterInterface
};
use Carbon\Carbon;

class SendCollectionToYY implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $masterRepositoryObj,
        $consignmentDepositRefundTypeConfigCodes,
        $requestData;

    /**
     * SendCollectionToYY constructor.
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
     * Execute the job. (Collection_Settlement)
     *
     * @param YonyouInterface $yonyouInterface
     * @param StockistInterface $stockistInterface
     * @param LocationInterface $locationInterface
     * @param MasterInterface $masterInterface
     * @return void
     */
    public function handle(
        YonyouInterface $yonyouInterface,
        StockistInterface $stockistInterface,
        LocationInterface $locationInterface,
        MasterInterface $masterInterface
    )
    {   
        try 
        {
            $this->yonyouRepositoryObj = $yonyouInterface;
            
            $this->stockistRepositoryObj = $stockistInterface;
            
            $this->locationRepositoryObj = $locationInterface;

            $this->masterRepositoryObj = $masterInterface;

            $this->consignmentDepositRefundTypeConfigCodes = config('mappings.consignment_deposit_and_refund_type');
            
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
        switch ($this->jobType)  {
            case "CONSIGNMENT_DEPOSIT_AND_REFUND":
                $this->requestData = $this->formatDataStructure($this->getConsignmentDepositAndRefundAPIParameter());
                break;
            case "CONSIGNMENT_DEPOSIT_REJECT":
                $this->requestData = $this->formatDataStructure($this->getConsignmentDepositRejectAPIParameter());
                break;
            case "PREORDER_DEPOSIT":
                $this->requestData = $this->formatDataStructure($this->getPreOrderPaymentAPIParameter());
                break;
            case "PREORDER_REFUND":
                $this->requestData = $this->formatDataStructure($this->getPreOrderRefundAPIParameter());
                break;
        }

        $apiPath = config('integrations.yonyou.collection_api.path');

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
     * format data into yonyou collection settlement json structure 
     *
     * @param array $data
     * @return array
     */
    public function formatDataStructure($data)
    {
        if (!empty($data['salePayment'])) {
            foreach ($data['salePayment'] as $key => $salePayment) {
                $collectionDetails['cmp.cmp_recbilldetail'][] = [
                    'billdetail_no' => $salePayment['rowno'], //Line No
                    'pk_currtype' => $data['pk_currtype'], //Currency Code
                    'rec_primal' => $salePayment['money_cr'], //Total Amount (Include Tax)
                    'objecttype' => 0, //Collection objects
                    'pk_customer' => (string)$data['pk_customer'], //Customer Code
                    'pk_account' => $data['pk_account'], //Collection Bank Account
                    'pk_balatype' => $salePayment['pk_balatype'], //Payment method
                    'cash_item' => '0101', //Cash Item
                    'memo' => $data['memo'], //Comment
                    'def12' => $data['def12'], //Settlement No
                    'def13' => $data['def13'], //Division
                    'def14' => $data['def14'], //State
                    'def15' => $data['def15'], //Expense Group
                    'def16' => $data['def16'], //Event Date
                    'def18' => $data['def18'], //GST Code
                    'def19' => $data['def19'], //GST Amt
                    'def20' => $data['def20'], //Invoice No
                    'pk_dept' => $data['pk_dept'], //Department
                    'pk_subjct' => $data['pk_subjct'], //Chart of Account
                    'pk_jobid' => $data['pk_jobid'], //Project
                    'def9' => $data['def9'], //Event Department
                    'def1' => $data['def1'], //Member ID
                    'def2' => $data['def2'], //Member Name
                    'def3' => $data['def3'], //Member Address
                    'def17' => $data['def17'] //Category
                ];
            }
        }

        $jsonBody = [
            array('cmp.cmp_recbill'=>array(
                'pk_org' => $data['entity'], //Entity Code
                'trade_type' => $data['trade_type'], //NC Transaction Type Code
                'bill_no' => $data['bill_no'], //Collection Bill No.
                'bill_date' => $data['bill_date'], //Collection Bill Date
                'memo' => $data['memo'], //Note
                'def20' => config('integrations.yonyou.source_type'), //Source System
                'def1' => config('integrations.yonyou.nc_type_of_sales.collectionSettlement'), //Type Of Sales
                'def5' => '', //Source Invoice No (CN)
                'def6' => $data['def6'], //NIBS/EKWEB Transaction type
            ),
                'cmp.cmp_recbilldetail' => $collectionDetails['cmp.cmp_recbilldetail']
            )
        ];

        return $jsonBody;
    }

    /**
     * get consignment deposit and consignment refund api parameter
     *
     * @return array
     */
    public function getConsignmentDepositAndRefundAPIParameter()
    {
        $consignmentDepositRefund = $this->jobData;

        $payments = $consignmentDepositRefund->payments;

        $stockistInfo = $this->stockistRepositoryObj->find($consignmentDepositRefund->stockist->id);

        $entityName = strtoupper($consignmentDepositRefund->stockist->country->entity->name);

        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

        $masterSettingsDatas = $this->masterRepositoryObj->getMasterDataByKey(
            array(
                'consignment_deposit_and_refund_type'
            )
        );
        $depositRefundType = array_change_key_case(
            $masterSettingsDatas['consignment_deposit_and_refund_type']
                ->pluck('id', 'title')
                ->toArray()
        );

        $depositTypeId = $depositRefundType[$this->consignmentDepositRefundTypeConfigCodes['deposit']];

        if ($consignmentDepositRefund->type_id == $depositTypeId) {
            foreach ($payments as $key => $payment) {
                $receiptDetails[$key] = [
                    'rowno' => $payment->id,
                    'money_cr' => $payment->amount,
                    'pk_balatype' => config('integrations.yonyou.nc_payment_mode.' . $payment->paymentModeProvider->code)
                ];
            }
        }
        else
        {
            $receiptDetails[] = [
                'rowno' => $consignmentDepositRefund->id,
                'money_cr' => (0 - $consignmentDepositRefund->amount),
                'pk_balatype' => config('integrations.yonyou.nc_virtual_payment_mode.depositRefund')
            ];
        }

        $data = array();
        $data['trade_type'] = config('integrations.yonyou.nc_trn_type.consignmentdepositrefund');
        $data['bill_no'] = $consignmentDepositRefund->document_number;
        $data['bill_date'] = Carbon::parse($consignmentDepositRefund->created_at)->format('Y-m-d H:m:s');
        $data['SettlementNo'] = $consignmentDepositRefund->document_number;
        $data['billdetail_no'] = $consignmentDepositRefund->id;
        $data['pk_currtype'] = $consignmentDepositRefund->stockist->country->currency->code;
        $data['rec_primal'] = number_format($consignmentDepositRefund->amount, 2);
        $data['pk_customer'] = $consignmentDepositRefund->stockist->stockist_number;
        $data['pk_account'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['salePayment'] = $receiptDetails;
        $data['memo'] = ($consignmentDepositRefund->remark != null ? $consignmentDepositRefund->remark : '');
        $data['def12'] = '';
        $data['def13'] = '';
        $data['def14'] = '';
        $data['def15'] = '';
        $data['def16'] = '';
        $data['def17'] = '';
        $data['def18'] = $taxCode;
        $data['def19'] = '';
        $data['def20'] = '';
        $data['pk_dept'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['pk_subjct'] = '';
        $data['pk_jobid'] = '';
        $data['def9']='';
        $data['def1'] = '';
        $data['def2'] = '';
        $data['def3'] = '';
        $data['def17'] = '';
        $data['entity'] = $entityName;
        $data['def13'] = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        $data['def6'] = ($consignmentDepositRefund->type_id == $depositTypeId ? config('integrations.yonyou.nibs_transaction_type.consignmentDeposit') : config('integrations.yonyou.nibs_transaction_type.consignmentRefund'));
        return $data;
    }

    /**
     * get consignment deposit (reject) api parameter
     *
     * @return array
     */
    public function getConsignmentDepositRejectAPIParameter()
    {
        $consignmentDepositRefund = $this->jobData;

        $payments = $consignmentDepositRefund->payments;

        $stockistInfo = $this->stockistRepositoryObj->find($consignmentDepositRefund->stockist->id);

        $entityName = strtoupper($consignmentDepositRefund->stockist->country->entity->name);
        
        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

        $receiptDetails[] = [
            'rowno' => $consignmentDepositRefund->id,
            'money_cr' => (0 - $consignmentDepositRefund->amount),
            'pk_balatype' => config('integrations.yonyou.nc_virtual_payment_mode.depositRefund')
        ];

        $data = array();
        $data['trade_type'] = config('integrations.yonyou.nc_trn_type.consignmentdepositrefund');
        $data['bill_no'] = $consignmentDepositRefund->document_number . '-R';
        $data['bill_date'] = Carbon::parse($consignmentDepositRefund->created_at)->format('Y-m-d H:m:s');
        $data['SettlementNo'] = $consignmentDepositRefund->document_number;
        $data['billdetail_no'] = $consignmentDepositRefund->id;
        $data['pk_currtype'] = $consignmentDepositRefund->stockist->country->currency->code;
        $data['rec_primal'] = number_format($consignmentDepositRefund->amount, 2);
        $data['pk_customer'] = $consignmentDepositRefund->stockist->stockist_number;
        $data['pk_account'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['salePayment'] = $receiptDetails;
        $data['memo'] = ($consignmentDepositRefund->remark != null ? $consignmentDepositRefund->remark : '');
        $data['def12'] = '';
        $data['def13'] = '';
        $data['def14'] = '';
        $data['def15'] = '';
        $data['def16'] = '';
        $data['def17'] = '';
        $data['def18'] = $taxCode;
        $data['def19'] = '';
        $data['def20'] = '';
        $data['pk_dept'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['pk_subjct'] = '';
        $data['pk_jobid'] = '';
        $data['def9']='';
        $data['def1'] = '';
        $data['def2'] = '';
        $data['def3'] = '';
        $data['def17'] = '';
        $data['entity'] = $entityName;
        $data['def13'] = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        $data['def6'] = config('integrations.yonyou.nibs_transaction_type.consignmentRefund');
        return $data;
    }

    /**
     * get pre order payment api parameter
     *
     * @return array
     */
    public function getPreOrderPaymentAPIParameter()
    {
        $payment = $this->jobData;

        $sale = $payment->sale;

        $entityName = strtoupper($sale->country->entity->name);
        
        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

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

        $receiptDetails[] = [
            'rowno' => $payment->id,
            'money_cr' => $payment->amount,
            'pk_balatype' => config('integrations.yonyou.nc_payment_mode.' . $payment->paymentModeProvider->code)
        ];

        $data = array();
        $data['trade_type'] = config('integrations.yonyou.nc_trn_type.collectionsettlement');
        $data['bill_no'] = $sale->document_number . '-' . $payment->id;
        $data['bill_date'] = Carbon::parse($payment->created_at)->format('Y-m-d H:m:s');
        $data['SettlementNo'] = $sale->document_number;
        $data['pk_currtype'] = $sale->country->currency->code;
        $data['pk_customer'] = $customerCode;
        $data['pk_account'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['salePayment'] = $receiptDetails;
        $data['memo'] = $sale->remarks;
        $data['def12'] = '';
        $data['def13'] = '';
        $data['def14'] = '';
        $data['def15'] = '';
        $data['def16'] = '';
        $data['def17'] = '';
        $data['def18'] = $taxCode;
        $data['def19'] = '';
        $data['def20'] = '';
        $data['pk_dept'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['pk_subjct'] = '';
        $data['pk_jobid'] = '';
        $data['def9']='';
        $data['def1'] = '';
        $data['def2'] = '';
        $data['def3'] = '';
        $data['def17'] = '';
        $data['entity'] = $entityName;
        $data['def13'] = $costCentre;
        $data['def6'] = config('integrations.yonyou.nibs_transaction_type.preOrderDeposit');
        return $data;
    }

    /**
     * get pre order refund api parameter
     *
     * @return array
     */
    public function getPreOrderRefundAPIParameter()
    {
        $payment = $this->jobData;

        $sale = $payment->sale;

        $entityName = strtoupper($sale->country->entity->name);
        
        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

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

        $receiptDetails[] = [
            'rowno' => $payment->id,
            'money_cr' => (0 - $payment->amount),
            'pk_balatype' => config('integrations.yonyou.nc_payment_mode.' . $payment->paymentModeProvider->code)
        ];

        $data = array();
        $data['trade_type'] = config('integrations.yonyou.nc_trn_type.collectionsettlement');
        $data['bill_no'] = $sale->document_number . '-' . $payment->id . 'R';
        $data['bill_date'] = Carbon::parse($payment->created_at)->format('Y-m-d H:m:s');
        $data['SettlementNo'] = $sale->document_number;
        $data['pk_currtype'] = $sale->country->currency->code;
        $data['pk_customer'] = $customerCode;
        $data['pk_account'] = config('integrations.yonyou.nc_virtual_bank_account.' . $entityName);
        $data['salePayment'] = $receiptDetails;
        $data['memo'] = $sale->remarks;
        $data['def12'] = '';
        $data['def13'] = '';
        $data['def14'] = '';
        $data['def15'] = '';
        $data['def16'] = '';
        $data['def17'] = '';
        $data['def18'] = $taxCode;
        $data['def19'] = '';
        $data['def20'] = '';
        $data['pk_dept'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['pk_subjct'] = '';
        $data['pk_jobid'] = '';
        $data['def9']='';
        $data['def1'] = '';
        $data['def2'] = '';
        $data['def3'] = '';
        $data['def17'] = '';
        $data['entity'] = $entityName;
        $data['def13'] = $costCentre;
        $data['def6'] = config('integrations.yonyou.nibs_transaction_type.preOrderDeposit');
        return $data;
    }
}
