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
    Locations\LocationInterface
};

class SendEwalletToYY implements ShouldQueue
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
        $locationRepositoryObj,
        $requestData;

    /**
     * SendSalesToYY constructor.
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
     * Execute the job. (Payment)
     *
     * @param YonyouInterface $yonyouInterface
     * @param LocationInterface $locationInterface
     * @return void
     */
    public function handle(
        YonyouInterface $yonyouInterface,
        LocationInterface $locationInterface
    )
    {
        try 
        {
            $this->yonyouRepositoryObj = $yonyouInterface;

            $this->locationRepositoryObj = $locationInterface;

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
        switch (strtoupper($this->jobType)) {
            case "EWALLET_SALESCANCELLATION":
                $this->requestData = $this->formatDataStructure($this->getSalesCancellationEWalletAPIParameter());
                break;
        }

        $apiPath = config('integrations.yonyou.ewallet_api.path');

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
     * format data into yonyou payment json structure 
     *
     * @param array $data
     * @return array
     */
    public function formatDataStructure($data)
    {
        $collectionDetails['cmp.cmp_paybilldetail'][] = [
            'billdetail_no' => $data['billdetail_no'], //Line No.
            'pk_currtype' => $data['pk_currtype'], //Currency Code
            'pay_primal' => ($data['pay_primal']), //Total Amount (Include Tax)
            'objecttype' => 0, //Collection objects (0-customer, 1-supplier) 
            'pk_customer' => $data['pk_customer'], //Customer Code (do not follow interface file, we have changed to this)
            'pk_dept' => $data['pk_dept'], //Department
            'pk_balatype' => $data['pk_balatype'], //Payment method 
            'cash_item' => $data['cash_item'], //Cash item
            'memo' => $data['memo'], //Comment
            'def12' => $data['def12'], //Settlement No
            'def13' => $data['def13'], //Division
            'def14' => $data['def14'], //State
            'def15' => $data['def15'], //Expense Group
            'def16' => $data['def16'], //Event Date
            'def17' => $data['def17'], //Type of Sales
            'def18' => $data['def18'], //GST Code
            'def19' => $data['def19'], //GST Amt
            'def20' => $data['def20'], //Invoice No.
            'pk_subjct' => $data['pk_subjct'] //Chart of Account
        ];

        $jsonBody = [
            array('cmp.cmp_paybill' => array(
                'pk_org' => $data['entity'],
                'trade_type' => config('integrations.yonyou.nc_trn_type.paymentsettlement'), //Fixed value
                'bill_no' => $data['bill_no'], //collection number
                'bill_date' => $data['bill_date'], //collection date
                'def20' => config('integrations.yonyou.source_type'), //source system
                'memo' => $data['memo'], //collection remark,
                "pk_oppaccount" => $data['pk_oppaccount']
            ),
                'cmp.cmp_paybilldetail' => $collectionDetails['cmp.cmp_paybilldetail']
            )
        ];

        return $jsonBody;
    }

    //TODO: revise after bonus calculation ready
    // /**
    //  * get eWallet Bonus Payout api parameter
    //  *
    //  * @return array
    //  */
    // public function getEWalletAPIParameter()
    // {
    //     $data = array();
    //     $data['bill_no'] = $this->jobData->transaction_number . $this->jobData->id;
    //     $data['bill_date'] = $this->jobData->transaction_date;
    //     $data['SettlementNo'] = ''; //Settlement No.
    //     $data['billdetail_no'] = $this->jobData->id;
    //     $data['pk_currtype'] = $this->jobData->currency->code;
    //     $data['pay_primal'] = number_format(strtoupper($this->jobData->amountType->title) == 'CREDIT' ? $this->jobData->amount : (0 - $this->jobData->amount), 2);
    //     $data['pk_customer'] = config('integrations.yonyou.nc_branch_customer_code.' . 'MYEG'); //TODO
    //     $data['pk_dept'] = config('integrations.yonyou.nc_dept_code.' . 'MYEG'); //TODO
    //     $data['pk_oppaccount'] = config('integrations.yonyou.nc_wallet_virtual_bank_account.' . 'MYEG'); //TODO
    //     $data['pk_balatype'] = config('integrations.yonyou.nc_virtual_payment_mode.eWalletRefund');
    //     $data['cash_item'] = '';
    //     $data['memo'] = $this->jobData->transaction_details;
    //     $data['def12'] = ''; //settlement no
    //     $data['def13'] = ''; //division
    //     $data['def14'] = ''; //state
    //     $data['def15'] = ''; //Expense Group
    //     $data['def16'] = ''; //Event Date
    //     $data['def17'] = config('integrations.yonyou.nc_type_of_sales.branch'); //Type of Sales
    //     $data['def18'] = ''; //GST Code
    //     $data['def19'] = ''; //GST Amt
    //     $data['def20'] = ''; //Invoice No.,
    //     $data['pk_subjct'] = '';
    //     $data['entity'] = 'MYEG';
    //     return $data;
    // }

    /**
     * get sales cancellation payout api parameter
     *
     * @return array
     */
    public function getSalesCancellationEWalletAPIParameter()
    {
        $saleCancellation = $this->jobData;

        $sale = $saleCancellation->sale;

        $entityName = strtoupper($sale->country->entity->name);

        $isStockistSale = strtolower($sale->channel->code) == config('mappings.locations_types.stockist');

        $isOnlineSale = strtolower($sale->channel->code) == config('mappings.locations_types.online');

        if ($isStockistSale) {
            $customerCode = $sale->transactionLocation->code;
        } else if ($isOnlineSale) {
            $customerCode = config('integrations.yonyou.nc_online_customer_code.' . $entityName);
        } else {
            $customerCode = config('integrations.yonyou.nc_branch_customer_code.' . $entityName);
        }

        $data = array();
        $data['bill_no'] = $saleCancellation->creditNote->credit_note_number;;
        $data['bill_date'] = $saleCancellation->transaction_date;
        $data['SettlementNo'] = '';
        $data['billdetail_no'] = $saleCancellation->id;
        $data['pk_currtype'] = $sale->country->currency->code;
        $data['pay_primal'] = number_format($saleCancellation->total_buy_back_amount, 2);
        $data['pk_customer'] = $customerCode;
        $data['pk_dept'] = config('integrations.yonyou.nc_receipt_dept_code.' . $entityName);
        $data['pk_oppaccount'] = config('integrations.yonyou.nc_wallet_virtual_bank_account.' . $entityName);
        $data['pk_balatype'] = config('integrations.yonyou.nc_virtual_payment_mode.eWalletRefund');
        $data['cash_item'] = config('integrations.yonyou.ewallet_api.cash_item'); 
        $data['memo'] = $saleCancellation->remarks;
        $data['def12'] = '';
        $data['def13'] = '';
        $data['def14'] = '';
        $data['def15'] = '';
        $data['def16'] = '';
        $data['def17'] = config('integrations.yonyou.nc_type_of_sales.branch');
        $data['def18'] = '';
        $data['def19'] = '';
        $data['def20'] = '';
        $data['pk_subjct'] = '';
        $data['entity'] = $entityName;
        return $data;
    }
}
