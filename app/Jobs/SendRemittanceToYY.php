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
    Integrations\YonyouInterface
};
use GuzzleHttp;

class SendRemittanceToYY implements ShouldQueue
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
        $requestData;

    /**
     * SendRemittanceToYY constructor.
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
     * Execute the job. (Remittance) 
     *
     * @param YonyouInterface $yonyouInterface
     * @return void
     */
    public function handle(
        YonyouInterface $yonyouInterface
    )
    {
        //TODO: Ignore for now, account will do manually for the moment
        // try 
        // {
        //     $this->yonyouRepositoryObj = $yonyouInterface;
        
        //     if ($this->yonyouRepositoryObj->executeIntegrationJob($this->jobType, $this->mappingModel, $this->mappingId)) 
        //     {
        //         $this->jobData = $this->yonyouRepositoryObj
        //             ->getMappingModelObject($this->dataModel, $this->dataId, true);

        //         $this->processIntegration();
        //     }
        // }
        // catch (\Exception $exception)
        // {
        //     $this->yonyouRepositoryObj->createIntegrationLog(
        //         $this->jobType, $this->mappingModel, $this->mappingId, $this->requestData, 
        //         $this->yonyouRepositoryObj->serializeException($exception), '999'
        //     );
        // }
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
            case "REMITTANCE":
                $this->requestData = $this->formatDataStructure($this->getRemitttanceAPIParameter());
                break;
        }

        $apiPath = config('integrations.yonyou.remittance_api.path');

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
     * format data into yonyou remittance json structure 
     *
     * @param array $data
     * @return array
     */
    public function formatDataStructure($data)
    {
        $jsonbody = [
            array('cmp.cmp_transformbill'=> array(
                'pk_org' => config('integrations.yonyou.sales_api.yy_pk_org'), //Entity Code
                'pk_billtypecode' => config('integrations.yonyou.nctrntype.remittance'), //NC Transaction Type Code
                'vbillno' => $data['vbillno'], //Collection Bill No.
                'amount' => $data['amount'], //Amount
                'pk_currtype' => $data['pk_currtype'], //Currency Code
                'transforminbank' => $data['transforminbank'], //Remit-from Bank
                'transformoutbank' => $data['transformoutbank'], //Remit-to Bank
                'transformoutaccount' => $data['transformoutaccount'], //Remit-to Account
                'transforminaccount' => $data['transforminaccount'], //Remit-from Account
                'paydate' => $data['paydate'], //Payment date
                'vdef12' => $data['vdef12'], //Settlement NO.
                'vdef20' => config('integrations.yonyou.source_type'), //Source System
                'remark' => $data['remark'], //Note
                'vdef18' => $data['vdef18'], //GST Code
                'vdef19' => $data['vdef19'], //GST Amt
                'pk_balatype' => $data['pk_balatype'] //Payment method 
            ))
        ];

        return $jsonbody;
    }

    /**
     * get eWallet Transfer api parameter
     *
     * @return array
     */
    public function getRemitttanceAPIParameter()
    {
        $data = array();
        $data['vbillno'] = $this->jobData->transaction_number;
        $data['amount'] = number_format($this->jobData->amount, 2);
        $data['pk_currtype'] = $this->jobData->currency->code;
        $data['transforminbank'] = '';
        $data['transformoutbank'] = '';
        $data['transformoutaccount'] = 'EKGDEMO';
        $data['transforminaccount'] = 'EKGDEMO2';
        $data['paydate'] = $this->jobData->transaction_date;
        $data['vdef12'] = '';
        $data['remark'] = 'remittance';
        $data['vdef18'] = '';
        $data['vdef19'] = '';
        $data['pk_balatype'] = 'POL';
        return $data;
    }
}
