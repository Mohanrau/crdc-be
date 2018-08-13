<?php
namespace App\Jobs;

use App\Models\{
    Products\Product,
    Stockists\ConsignmentOrderReturn
};
use Illuminate\{
    Bus\Queueable,
    Queue\SerializesModels,
    Queue\InteractsWithQueue,
    Contracts\Queue\ShouldQueue,
    Foundation\Bus\Dispatchable
};
use App\Interfaces\{
    Integrations\YonyouInterface,
    Stockists\StockistInterface
};
use App\Helpers\Classes\MemberAddress;
use Carbon\Carbon;

class SendConsignmentToYY implements ShouldQueue
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
        $memberAddressHelper,
        $productObj,
        $requestData;

    /**
     * SendConsignmentToYY constructor.
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
     * Execute the job. (Transfer_Order)
     *
     * @param YonyouInterface $yonyouInterface
     * @param StockistInterface $stockistInterface
     * @param MemberAddress $memberAddress
     * @param Product $product
     * @return void
     */
    public function handle(
        YonyouInterface $yonyouInterface,
        StockistInterface $stockistInterface,
        MemberAddress $memberAddress,
        Product $product
    )
    {
        try 
        {
            $this->yonyouRepositoryObj = $yonyouInterface;
        
            $this->stockistRepositoryObj = $stockistInterface;
            
            $this->memberAddressHelper = $memberAddress;

            $this->productObj = $product;
            
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
            case "CONSIGNMENT_ORDER":
                $this->requestData = $this->formatDataStructure($this->getConsignmentOrderAPIParameter());
                break;
            case "CONSIGNMENT_RETURN":
                $this->requestData = $this->formatDataStructure($this->getConsignmentReturnAPIParameter());
                break;
        }

        $apiPath = config('integrations.yonyou.consignment_api.path');

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
     * format data into yonyou transfer order json structure 
     *
     * @param array $data
     * @return array
     */
    public function formatDataStructure($data)
    {
        foreach ($data['dataProduct'] as $key=> $consignmentProduct) {
            $consignmentDetails['to.to_bill_b'][] = [
                'crowno' => $consignmentProduct['crowno'], //Line No.
                'cinventoryvid' => $consignmentProduct['cinventoryvid'], //Product Code
                'nnum' => $consignmentProduct['nnum'], //Qty
                'nqtorignetprice' => $consignmentProduct['nqtorignetprice'], //Unit Price (Excl. Tax)
                'ctaxcodeid' => $consignmentProduct['ctaxcodeid'],  //Tax Code
                'ntaxrate' => $consignmentProduct['ntaxrate'], //Tax Rate
                'ftaxtypeflag' => $consignmentProduct['ftaxtypeflag'], //Tax Type
                'ntax' => $consignmentProduct['ntax'], //Tax Amt
                'norigmny' => $consignmentProduct['norigmny'], //Amt Excl. Tax
                'norigtaxmny' => $consignmentProduct['norigtaxmny'], //Amt Incl. Tax
                'coutstordocid' =>  $data['coutstordocid'], //Transfer-out Warehouse
                'cinstordocid' => $consignmentProduct['cinstordocid'], //Transfer-in Warehouse
                'vbnote' => '', //Memo
                'creceipient' => $data['recipientname'], //Recipient
                'ccontactno' => $data['recipientmobile'], //Mobile No
                'caddr1' => $data['shippingaddr1'], //Addr1
                'caddr2' => $data['shippingaddr2'], //Addr2
                'caddr3' => $data['shippingaddr3'], //Addr3
                'caddr4' => $data['shippingaddr4'], //Addr4
                'cpostcode' => $data['shippingpostcode'], //PostCode
                'ccity' => $data['shippingcity'], //City
                'cstate' => $this->yonyouRepositoryObj->getYonyouStateCode($data['shippingcountry'], $data['shippingstate']), //State
                'ccountry' => $data['shippingcountry'], //Country
                'cemail' => $data['cemail'], //Email
                'cphoneno' => $data['cphoneno'] //Home Phone No
            ];
        }

        $jsonBody = [
            array('to.to_bill'=>array(
                'pk_org' => $data['entity'], //Transfer Out Entity Code
                'cinstockorgid' => $data['cinstockorgid'], //Transfer In Entity Code
                'ctrantypeid' => $data['ctrantypeid'], //NC Transaction Type Code
                'vbillcode' => $data['vbillcode'], //Transfer Order No.
                'dbilldate' => $data['dbilldate'], //Transfer Order Date
                'coutdeptid' => $data['coutdeptid'], //NC Department Code
                'corigcurrencyid' => $data['corigcurrencyid'], //Currency Code
                'vnote' => $data['vnote'], //Memo
                'csrcsono' => '', //Src SO No.
                'ctransactiontype' => $data['ctransactiontype'], //NIBS/EKWEB Transaction Type
                'csourcetype' => config('integrations.yonyou.source_type'), //Source Type
                'csostatus' => $data['release'], //SO Status
                'cexchangeno' => '', //Product Exchange No.
                'ccwsvno' => '', //CW/SV-No
                'ccwsvyear' => '', //CW/SV-Year
                'csrcsodate' => '', //Src SO Date
                'vdef11' => $data['vdef11'] //Customer Code
            ),
                'to.to_bill_b'=>$consignmentDetails['to.to_bill_b']
            )
        ];

        return $jsonBody;
    }

    /**
     * get consignment order api parameter
     *
     * @return array
     */
    public function getConsignmentOrderAPIParameter()
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
        $recipientEmail = '';
        $homeContact = '';

        $consignmentOrder = $this->jobData;

        $stockistInfo = $this->stockistRepositoryObj->find($consignmentOrder->stockist->id);

        $consignmentProducts = $consignmentOrder->getConsignmentProducts();

        $entityName = strtoupper($stockistInfo->country->entity->name);

        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

        $taxRate = $this->yonyouRepositoryObj->getYonyouTaxRate($entityName);

        if (!empty($stockistInfo->businessAddress->addresses)) {
            $addressInfo =  $this->memberAddressHelper
                ->getAddressAsYYStructure($stockistInfo->businessAddress->addresses, '');
            $shippingAddress1 = $addressInfo['addr1'];
            $shippingAddress2 = $addressInfo['addr2'];
            $shippingAddress3 = $addressInfo['addr3'];
            $shippingAddress4 = $addressInfo['addr4'];
            $shippingPostcode = $addressInfo['postcode'];
            $shippingCity = $addressInfo['city'];
            $shippingState = $addressInfo['state'];
            $shippingCountry = $addressInfo['country'];

            $recipient = $stockistInfo->name;
            $recipientEmail = ($stockistInfo->email != null ? $stockistInfo->email : '');
            $recipientMobile = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($stockistInfo->businessAddress->mobile_1_country_code_id, $stockistInfo->businessAddress->mobile_1_num);
        }

        if (!empty($stockistInfo->businessAddress)) {
            $homeContact = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($stockistInfo->businessAddress->telephone_office_country_code_id, $stockistInfo->businessAddress->telephone_office_num);
        }

        foreach ($consignmentProducts as $key => $consignmentProduct)
        {
            $product = $this->productObj
                ->find($consignmentProduct["product_id"]);

            if ((!empty($product)) && $product->inventorize == 1) {
                $productDetails[$key] = [
                    'crowno' => $consignmentProduct['id'],
                    'cinventoryvid' => $consignmentProduct['sku'],
                    'nnum' => number_format($consignmentProduct['quantity'], 2),
                    'nqtorignetprice' => number_format($consignmentProduct['unit_nmp_price'], 2),
                    'ctaxcodeid' => $taxCode,
                    'ntaxrate' => $taxRate,
                    'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0 ),
                    'ntax' => number_format( ($consignmentProduct['gmp_price_gst']) - ($consignmentProduct['nmp_price']), 2),
                    'norigmny' => number_format($consignmentProduct['nmp_price'], 2),
                    'norigtaxmny' => number_format($consignmentProduct['gmp_price_gst'], 2),
                    'cinstordocid'=>$consignmentOrder->stockist->stockistLocation->code
                ];
            }
        }

        $data = array();
        $data['ctrantypeid'] = config('integrations.yonyou.nc_trn_type.transferOrder');
        $data['vbillcode'] = $consignmentOrder->document_number;
        $data['dbilldate'] = Carbon::parse($consignmentOrder->created_at)->format('Y-m-d H:m:s');
        $data['entity'] = config('integrations.yonyou.nc_warehouse_entity_code.' . $stockistInfo->country->entity->name);
        $data['coutdeptid'] = config('integrations.yonyou.nc_warehouse_dept_code.' . $stockistInfo->country->entity->name);
        $data['corigcurrencyid'] = $stockistInfo->country->currency->code;
        $data['vnote'] = $consignmentOrder->remark;
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
        $data['dataProduct'] = collect($productDetails);
        $data['cemail'] = $recipientEmail;
        $data['cphoneno'] = $homeContact;
        $data['release'] = 1;
        $data['cinstockorgid'] = config('integrations.yonyou.nc_consignment_warehouse_entity.' . $stockistInfo->country->entity->name);
        $data['ctransactiontype'] = config('integrations.yonyou.nibs_transaction_type.consignmentOrder');
        $data['coutstordocid'] = $consignmentOrder->stockLocation->code; //config('integrations.yonyou.nc_country_warehouse.' .$stockistInfo->country->entity->name);
        $data['vdef11'] = $stockistInfo->stockistLocation->code;
        return $data;
    }

    /**
     * get consignment return api parameter
     *
     * @return array
     */
    public function getConsignmentReturnAPIParameter()
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
        $recipientEmail = '';
        $recipientMobile = '';
        $homeContact = '';

        $consignmentOrder = $this->jobData;

        $stockistInfo = $this->stockistRepositoryObj->find($consignmentOrder->stockist->id);

        $consignmentProducts = $consignmentOrder->getConsignmentProducts();

        $entityName = strtoupper($stockistInfo->country->entity->name);

        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

        $taxRate = $this->yonyouRepositoryObj->getYonyouTaxRate($entityName);
        
        if (!empty($stockistInfo->businessAddress->addresses)) {
            $addressInfo =  $this->memberAddressHelper
                ->getAddressAsYYStructure($stockistInfo->businessAddress->addresses, '');
            $shippingAddress1 = $addressInfo['addr1'];
            $shippingAddress2 = $addressInfo['addr2'];
            $shippingAddress3 = $addressInfo['addr3'];
            $shippingAddress4 = $addressInfo['addr4'];
            $shippingPostcode = $addressInfo['postcode'];
            $shippingCity = $addressInfo['city'];
            $shippingState = $addressInfo['state'];
            $shippingCountry = $addressInfo['country'];
            
            $recipient = $stockistInfo->name;
            $recipientEmail = ($stockistInfo->email != null ? $stockistInfo->email : '');
            $recipientMobile = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($stockistInfo->businessAddress->mobile_1_country_code_id, $stockistInfo->businessAddress->mobile_1_num);
        }

        if (!empty($stockistInfo->businessAddress)) {
            $homeContact = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($stockistInfo->businessAddress->telephone_office_country_code_id, $stockistInfo->businessAddress->telephone_office_num);
        }
        
        foreach ($consignmentProducts as $key => $consignmentProduct)
        {
            $product = $this->productObj
                ->find($consignmentProduct["product_id"]);

            if ((!empty($product)) && $product->inventorize == 1) {
                $productDetails[$key] = [
                    'crowno' => $consignmentProduct['id'],
                    'cinventoryvid' => $consignmentProduct['sku'],
                    'nnum' => number_format(0 - $consignmentProduct['quantity'], 2),
                    'nqtorignetprice' => number_format($consignmentProduct['unit_nmp_price'], 2),
                    'ctaxcodeid' => $taxCode,
                    'ntaxrate' => $taxRate,
                    'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0 ),
                    'ntax' => number_format(0 - ($consignmentProduct['gmp_price_gst'] - $consignmentProduct['nmp_price']), 2),
                    'norigmny' => number_format(0 - $consignmentProduct['nmp_price'], 2),
                    'norigtaxmny' => number_format(0 - $consignmentProduct['gmp_price_gst'], 2),
                    'cinstordocid'=>$consignmentOrder->stockist->stockistLocation->code
                ];
            }
        }

        $data = array();
        $data['ctrantypeid'] = config('integrations.yonyou.nc_trn_type.transferOrder');
        $data['entity'] = config('integrations.yonyou.nc_warehouse_entity_code.' . $stockistInfo->country->entity->name);
        $data['vbillcode'] = $consignmentOrder->document_number; //Consignment number
        $data['dbilldate'] = Carbon::parse($consignmentOrder->created_at)->format('Y-m-d H:m:s');
        $data['coutdeptid'] = config('integrations.yonyou.nc_warehouse_dept_code.' . $stockistInfo->country->entity->name);
        $data['corigcurrencyid'] = $stockistInfo->country->currency->code; //Currency
        $data['vnote'] = $consignmentOrder->remark;
        $data['recipientname'] = $recipient; //stockist name
        $data['recipientmobile'] = $recipientMobile; //stockist mobile
        $data['shippingaddr1'] = $shippingAddress1; //stockist address
        $data['shippingaddr2'] = $shippingAddress2;
        $data['shippingaddr3'] = $shippingAddress3;
        $data['shippingaddr4'] = $shippingAddress4;
        $data['shippingpostcode'] = $shippingPostcode;
        $data['shippingcity'] = $shippingCity;
        $data['shippingstate'] = $shippingState;
        $data['shippingcountry'] = $shippingCountry ;
        $data['dataProduct'] = collect($productDetails);
        $data['cemail'] = $recipientEmail;
        $data['cphoneno'] = $homeContact;
        $data['release'] = 1;
        $data['cinstockorgid'] = config('integrations.yonyou.nc_consignment_warehouse_entity.' . $stockistInfo->country->entity->name);
        $data['ctransactiontype'] = config('integrations.yonyou.nibs_transaction_type.consignmentReturn');
        $data['coutstordocid'] = $consignmentOrder->stockLocation->code;//config('integrations.yonyou.nc_country_warehouse.' .$stockistInfo->country->entity->name);
        $data['vdef11'] = $stockistInfo->stockistLocation->code;
        return $data;
    }
}
