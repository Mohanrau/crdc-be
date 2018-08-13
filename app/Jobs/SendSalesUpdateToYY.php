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
    Sales\SaleInterface,
    Products\ProductInterface,
    Integrations\YonyouInterface,
    Locations\LocationInterface,
    Stockists\StockistInterface
};
use App\Models\{
    Sales\SaleProductClone
};
use App\Helpers\Classes\MemberAddress;

class SendSalesUpdateToYY implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private
        $jobData,
        $jobType,
        $dataModel,
        $dataId,
        $mappingModel,
        $mappingId,
        $stockRelease,
        $yonyouRepositoryObj,
        $locationRepositoryObj,
        $saleProductCloneObj,
        $memberAddressHelper,
        $requestData;

    /**
     * SendSalesUpdateToYY constructor.
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
        $this->stockRelease = TRUE;
        $this->requestData = '';
    }

    /**
     * Execute the job. (SO_Update)
     *
     * @param YonyouInterface $yonyouInterface
     * @param LocationInterface $locationInterface
     * @param SaleProductClone $saleProductClone
     * @param MemberAddress $memberAddress
     * @return void
     */
    public function handle(
        YonyouInterface $yonyouInterface,
        LocationInterface $locationInterface,
        SaleProductClone $saleProductClone,
        MemberAddress $memberAddress
    )
    {
        $this->yonyouRepositoryObj = $yonyouInterface;
        
        $this->locationRepositoryObj = $locationInterface;
        
        $this->saleProductCloneObj = $saleProductClone;
        
        $this->memberAddressHelper = $memberAddress;
        
        try 
        {
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
            case "SALES_UPDATE":
            case "SALES_RENTAL_UPDATE":
                $this->requestData = $this->formatDataStructure($this->getSalesUpdateAPIParameter());
                break;
        }
        
        $apiPath = config('integrations.yonyou.sales_update_api.path');
        
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
     * format data into yonyou so update json structure 
     *
     * @param array $data
     * @return array
     */
    public function formatDataStructure($data)
    {
        foreach ($data['dataProduct'] as $key=> $salesProduct) {
            $saleDetails['so.so_saleorder_b'][] = [
                'crowno' => $salesProduct['crowno'], //Line No.
                'cmaterialvid' => $salesProduct['cmaterialvid'], //Product Code
                'cnibsrowid' => $salesProduct['crowno'], //NIBS Row ID
                'creceipient' => $data['recipientname'], //Recipient
                'ccontactno' => $data['recipientmobile'], //Mobile No.
                'caddr1' => $data['shippingaddr1'], //Addr1
                'caddr2' => $data['shippingaddr2'], //Addr2
                'caddr3' => $data['shippingaddr3'], //Addr3
                'caddr4' => $data['shippingaddr4'], //Addr4
                'cpostcode' => $data['shippingpostcode'], //PostCode
                'ccity' => $data['shippingcity'], //City
                'cstate' => $this->yonyouRepositoryObj->getYonyouStateCode($data['shippingcountry'], $data['shippingstate']), //State
                'ccountry' => $data['shippingcountry'], //Country
                'cemail' => '', //Email
                'cphoneno' => $data['memberhomecontact'] //Home Phone No
            ];
        }

        $jsonBody = [
            array('so.so_saleorder'=>array(
                'pk_org' => $data['entity'], //Sales Entity Code
                'vbillcode' => $data['billno'], //SO No.
                'csostatus' => $data['release'] //SO Status
            ),
                'so.so_saleorder_b'=>$saleDetails['so.so_saleorder_b']
            )
        ];

        return $jsonBody;
    }

    /**
     * get sales update(stock release & address update) api parameter
     *
     * @return array
     */
    public function getSalesUpdateAPIParameter()
    {
        $shippingAddress1 = '';
        $shippingAddress2 = '';
        $shippingAddress3 = '';
        $shippingAddress4 = '';
        $shippingPostcode = '';
        $shippingCity = '';
        $shippingState = '';
        $shippingCountry = '';
        $recipient = '';
        $recipientMobile = '';
        $homeContact = '';

        $sale = $this->jobData;

        $saleProducts = $sale->saleProducts;

        if (!empty($sale->saleShippingAddress->address)) {
            $addressInfo =  $this->memberAddressHelper
                ->getAddressAsYYStructure($sale->saleShippingAddress->address, '');
            $shippingAddress1 = $addressInfo['addr1'];
            $shippingAddress2 = $addressInfo['addr2'];
            $shippingAddress3 = $addressInfo['addr3'];
            $shippingAddress4 = $addressInfo['addr4'];
            $shippingPostcode = $addressInfo['postcode'];
            $shippingCity = $addressInfo['city'];
            $shippingState = $addressInfo['state'];
            $shippingCountry = $addressInfo['country'];

            $recipient = $sale->saleShippingAddress->recipient_name;
            $recipientMobile = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($sale->saleShippingAddress->country_id, $sale->saleShippingAddress->mobile);
        }
        
        if (!empty($sale->member->memberContactInfo)) {
            $homeContact = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($sale->member->memberContactInfo->tel_home_1_country_code_id, $sale->member->memberContactInfo->tel_home_1_num);
        }
        
        foreach($saleProducts as $key => $saleProduct)
        {   
            $productDetails[$key] = [
                'crowno' => $saleProduct->id,
                'cmaterialvid' => $saleProduct->product->sku
            ];
        }
        
        $data = array();
        $data['billno'] = $sale->document_number;
        $data['entity'] = strtoupper($sale->country->entity->name);
        $data['recipientname'] = $recipient;
        $data['recipientmobile'] = $recipientMobile;
        $data['shippingaddr1'] = $shippingAddress1;
        $data['shippingaddr2'] = $shippingAddress2;
        $data['shippingaddr3'] = $shippingAddress3;
        $data['shippingaddr4'] = $shippingAddress4;
        $data['shippingpostcode'] = $shippingPostcode;
        $data['shippingcity'] = $shippingCity;
        $data['shippingstate'] = $shippingState;
        $data['shippingcountry'] = $shippingCountry ;
        $data['memberhomecontact'] = $homeContact;
        $data['dataProduct'] = $productDetails;
        $data['release'] = ($this->stockRelease == true ? 1 : 0);
        return $data;
    }
}
