<?php
namespace App\Jobs;

use App\Repositories\{
    Integrations\YonyouRepository
};
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
    Stockists\StockistInterface,
    Locations\StateInterface,
    Locations\CountryInterface
};
use App\Models\{
    Sales\SaleProductClone,
    Sales\SalePromotionFreeItemClone,
    Stockists\Stockist
};
use App\Helpers\Classes\MemberAddress;
use Carbon\Carbon;

class SendSalesToYY implements ShouldQueue
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
        $saleProductCloneObj, 
        $stockistObj,
        $memberAddressHelper,
        $release,
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
     * Execute the job. (Sales_Order)
     *
     * @param YonyouInterface $yonyouInterface
     * @param StockistInterface $stockistInterface
     * @param LocationInterface $locationInterface
     * @param SaleProductClone $saleProductClone
     * @param Stockist $stockist
     * @param MemberAddress $memberAddress
     * @return void
     */
    public function handle(
        YonyouInterface $yonyouInterface,
        StockistInterface $stockistInterface,
        LocationInterface $locationInterface,
        SaleProductClone $saleProductClone,
        Stockist $stockist,
        MemberAddress $memberAddress
    )
    {
        $this->yonyouRepositoryObj = $yonyouInterface;
        
        $this->stockistRepositoryObj = $stockistInterface;
        
        $this->locationRepositoryObj = $locationInterface;
        
        $this->saleProductCloneObj = $saleProductClone;
        
        $this->stockistObj = $stockist;
        
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
        switch (strtoupper($this->jobType)) {
            case "STOCKIST_SALES":
            case "SALES_EXCHANGE_INV":
            case "SALES":
                $this->requestData = $this->formatDataStructure($this->getSalesAPIParameter());
                break;
            case "SALES_CANCELLATION":
                if ($this->jobData->is_legacy == 1) {
                    $this->requestData = $this->formatDataStructure($this->getLegacySalesCancellationAPIParameter());
                }
                else {
                    $this->requestData = $this->formatDataStructure($this->getSalesCancellationAPIParameter());
                }
                break;
            case "SALES_EXCHANGE_CCN":
                if ($this->jobData->is_legacy == 1) {
                    $this->requestData = $this->formatDataStructure($this->getLegacySalesExchangeCancellationAPIParameter());
                }
                else {
                    $this->requestData = $this->formatDataStructure($this->getSalesExchangeCancellationAPIParameter());
                }
                break;
        }

        $apiPath = config('integrations.yonyou.sales_api.path');
        
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
     * format data into yonyou sales order json structure 
     *
     * @param array $data
     * @return array
     */
    public function formatDataStructure($data)
    {
        foreach ($data['dataProduct'] as $key => $saleProduct) {
            $saleDetails['so.so_saleorder_b'][] = [
                'crowno' => $saleProduct['crowno'], //Line No.
                'cmaterialvid' => $saleProduct['cmaterialvid'], //Product Code
                'nqtunitnum' => $saleProduct['nqtunitnum'], //Qty,
                'nqtorigprice' => $saleProduct['nqtorigprice'], //Unit Price (Excl. Tax)
                'nqtorigtaxprice' => $saleProduct['nqtorigtaxprice'], //Unit Price (Incl. Tax)
                'ctaxcodeid' => $saleProduct['ctaxcodeid'], //Tax Code
                'ntaxrate' => $saleProduct['ntaxrate'], //Tax Rate
                'ftaxtypeflag' => $saleProduct['ftaxtypeflag'], //Tax Type
                'ntax' => $saleProduct['ntax'], //Tax Amt
                'norigmny' =>  $saleProduct['norigmny'], //Amt Excl. Tax
                'norigtaxmny' => $saleProduct['norigtaxmny'], //Amt Incl. Tax
                'csendstordocid' => $saleProduct['csendstordocid'],//Warehouse
                'csettleorgid' => $data['csettleorgid'], //Financial Org to Settle With
                'vrownote' => $data['vrownote'], //Remark
                'cmemberid' => $data['membercode'], //Member ID
                'cmembername' => $data['membername'], //Member Name
                'cmemberaddr' => $data['memberaddress'], //Member Address
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
                'cphoneno' => $data['memberhomecontact'], //Home Phone No
                'cspsflag' => 'N', //SPS Flag
                'cbatchnumber' => '', //Batch Number (EKWeb Stockist)
                'cbookingno'=>'', //Booking Ref No (EKWEN)
                'cnibsrowid'=>$saleProduct['crowno'], //NIBS Row ID
                'cpromocode'=>$saleProduct['cpromocode'], //Promcode
                'csrcrowno' => $saleProduct['csrcrowno'], //Source Row No.
                'blargessflag'=> $saleProduct['foc_qty'], //FOC
                'vbdef11' =>$data['vbdef11'], //Event
                'vbdef10' =>$data['vbdef10'], //Event Location
                'vbdef9' =>$data['vbdef9'], //Event Department
                'vbdef8' =>$saleProduct['vbdef8'], //FOC Type
                'vbdef13' =>$data['vbdef13'], //Cost Centre
                'csendstockorgid' => $data['csendstockorgid'] //Shipment Inventory Org
            ];
        }

        $jsonBody = [
            array('so.so_saleorder' => array(
                'pk_org' => $data['entity'], //Sales Entity Code
                'vtrantypecode' => $data['vtrantypecode'], //NC Transaction Type Code
                'vbillcode' => $data['billno'], //SO No.
                'dbilldate' => $data['billdate'], //Invoice Date
                'ccustomerid' => $data['customerid'], //Customer Code
                'cinvoicecustid' => $data['customerid'], //Invoice Customer Code
                'cdeptid' => $data['tranloc'], //NC Department Code
                'corigcurrencyid' => $data['currency'], //Currency Code
                'vnote' => $data['remark'], //Memo
                'cinvoiceno' => $data['invoicenumber'], //Tax Invoice No.
                'csrcsono' => $data['csrcsono'], //Src SO No.
                'csrcinvoiceno' => $data['csrcinvoiceno'], //Src Invoice No.
                'csrcsodate' => $data['csrcsodate'], //Src Invoice Date
                'ctransactiontype' => $data['trntype'], //NIBS/EKWEB Transaction Type
                'csourcetype' => config('integrations.yonyou.source_type'), //Source Type
                'csostatus' => $data['release'], //SO Status
                'cexchangeno' => $data['cexchangeno'], //Product Exchange No.
                'cselfpickupcode' => $data['selfpickupcode'], //Self pickup code
                'ccwsvno' => $data['srccwno'], //CW-No
                'ccwsvyear' => $data['srccwyear'], //CW-Year
                'csvno'=> '' , //SV-No
                'csvyear'=> '', //SV-Year
                'vdef1' => $data['vdef1'], //Type of Sales
                'vdef12' => $data['vdef12'] //Sales Group
            ),
                'so.so_saleorder_b' => $saleDetails['so.so_saleorder_b']
            )
        ];
        return $jsonBody;
    }

    /**
     * get sales & sales exchange api parameter
     *
     * @return array
     */
    public function getSalesAPIParameter()
    {
        $shippingAddress1 = '';
        $shippingAddress2 = '';
        $shippingAddress3 = '';
        $shippingAddress4 = '';
        $shippingPostcode = '';
        $shippingCity = '';
        $shippingState = '';
        $shippingCountry = '';
        $memAddress = '';
        $recipient = '';
        $recipientMobile = '';
        $homeContact = '';

        $sale = $this->jobData;

        if (strtoupper($this->jobType) == 'SALES_EXCHANGE_INV') {
            $saleExchange = $sale->saleExchange;
        }

        $invoice = $sale->invoices()->first();
        
        $saleProducts = $sale->saleProducts;

        $locationChannel = $this->locationRepositoryObj->find($sale->transaction_location_id);

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
        
        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

        $taxRate = $sale->tax_rate;

        $this->release = true;
        if ($sale->is_rental_sale_order == 1) {
            $this->release = false;
        }
        else if ($isStockistSale) {
            $salePayments = $sale->salePayments;
            foreach ($salePayments as $key => $salePayment) {
                if ($salePayment->paymentModeProvider['is_stockist_payment_verification'] == 1) {
                    $this->release = false;
                    break;
                }
            }
        }

        if ($isStockistSale && (strtoupper($this->jobType) == 'STOCKIST_SALES')) {
            $stockistInfo = $this->stockistObj
                ->where('stockist_number', $locationChannel->code)
                ->first();

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
                $recipientMobile = $stockistInfo->businessAddress->mobile_1_num;
            }
        }
        else
        {
            if ($sale->selfCollectionPoint != null) {
                $addressInfo =  $this->memberAddressHelper
                    ->getAddressAsYYStructure($sale->selfCollectionPoint->address_data, '');
                $shippingAddress1 = $addressInfo['addr1'];
                $shippingAddress2 = $addressInfo['addr2'];
                $shippingAddress3 = $addressInfo['addr3'];
                $shippingAddress4 = $addressInfo['addr4'];
                $shippingPostcode = $addressInfo['postcode'];
                $shippingCity = $addressInfo['city'];
                $shippingState = $addressInfo['state'];
                $shippingCountry = $addressInfo['country'];

                $recipient = '';

                if (!empty($sale->selfCollectionPoint->location->code)) {
                    $stockistInfo = $this->stockistObj
                        ->where('stockist_number', $sale->selfCollectionPoint->location->code)
                        ->first();
                    if ($stockistInfo != null) {
                        $recipient = $stockistInfo->name;
                    }
                }

                $recipientMobile = $sale->selfCollectionPoint->mobile_phone_num;
            }
            else if (!empty($sale->saleShippingAddress->address)) {
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
                $recipientMobile = $sale->saleShippingAddress->mobile;
            }
        }

        if (!empty($sale->member->address->address_data)) {
            $memAddress = $this->memberAddressHelper
                ->getAddress($sale->member->address->address_data, '');
        }

        if (!empty($sale->member->memberContactInfo)) {
            $homeContact = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($sale->member->memberContactInfo->tel_home_1_country_code_id, $sale->member->memberContactInfo->tel_home_1_num);
        }

        //eSAC sales, assign top up amt to 1st line item, the rest show 0 amt
        if ($sale->esac_redemption == 1) {
            $i = 1;
            foreach ($saleProducts as $saleProduct) {
                if ($saleProduct->foc_qty > 0 || $amountInclTax == 0) {
                    $isFoc = true;
                }
                else {
                    $isFoc = false;
                }

                if ($i == 1) {
                    $productDetails[] = [
                        'crowno' => $saleProduct->id,
                        'cmaterialvid' => $saleProduct->product->sku,
                        'nqtunitnum' => ($saleProduct->quantity + $saleProduct->foc_qty),
                        'nqtorigprice' => number_format($sale->total_gmp, 2),
                        'nqtorigtaxprice' => number_format($sale->total_gmp, 2),
                        'ctaxcodeid' => $taxCode,
                        'ntaxrate' => $taxRate,
                        'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0 ),
                        'foc_qty' => ($isFoc ? 'Y' : 'N'),
                        'ntax' => number_format($sale->tax_amount, 2),
                        'norigmny' => number_format($sale->total_amount, 2),
                        'norigtaxmny' => number_format($sale->total_gmp, 2),
                        'csendstordocid' => $sale->stockLocation->code,
                        'cpromocode' => (!empty($saleProduct->getMappedModel->code) ? $saleProduct->getMappedModel->code : ''),
                        'csrcrowno' => '',
                        'vbdef8' => ($isFoc ? config('integrations.yonyou.nc_foc_type.promo') : config('integrations.yonyou.nc_foc_type.na')),
                    ];
                }
                else
                {
                    $productDetails[] = [
                        'crowno' => $saleProduct->id,
                        'cmaterialvid' => $saleProduct->product->sku,
                        'nqtunitnum' => ($saleProduct->quantity + $saleProduct->foc_qty),
                        'nqtorigprice' => 0,
                        'nqtorigtaxprice' => 0,
                        'ctaxcodeid' => $taxCode,
                        'ntaxrate' => $taxRate,
                        'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0 ),
                        'foc_qty' => 'Y',
                        'ntax' => 0,
                        'norigmny' => 0,
                        'norigtaxmny' => 0,
                        'csendstordocid' => $sale->stockLocation->code,
                        'cpromocode' => (!empty($saleProduct->getMappedModel->code) ? $saleProduct->getMappedModel->code : ''),
                        'csrcrowno' => '',
                        'vbdef8' =>  config('integrations.yonyou.nc_foc_type.sac'),
                    ];
                }
                $i++;
            }
        }
        else //normal sales
        {
            foreach ($saleProducts as $saleProduct) {
                $qty = $saleProduct->quantity + $saleProduct->foc_qty;
                $unitPriceExclTax = $saleProduct->nmp_price;
                $unitPriceInclTax = $saleProduct->average_price_unit;
                $amountExclTax = $qty * $unitPriceExclTax;
                $amountInclTax = $qty * $unitPriceInclTax;
                $taxAmount = $amountInclTax - $amountExclTax;
                
                if ($saleProduct->foc_qty > 0 || $amountInclTax == 0) {
                    $isFoc = true;
                }
                else {
                    $isFoc = false;
                }

                $productDetails[] = [
                    'crowno' => $saleProduct->id,
                    'cmaterialvid' => $saleProduct->product->sku,
                    'nqtunitnum' => $qty,
                    'nqtorigprice' => number_format($unitPriceExclTax, 2),
                    'nqtorigtaxprice' => number_format($unitPriceInclTax, 2),
                    'ctaxcodeid' => $taxCode,
                    'ntaxrate' => $taxRate,
                    'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0),
                    'foc_qty' => ($isFoc ? 'Y' : 'N'),
                    'ntax' => number_format($taxAmount, 2),
                    'norigmny' => number_format($amountExclTax, 2),
                    'norigtaxmny' => number_format($amountInclTax, 2),
                    'csendstordocid' => $sale->stockLocation->code,
                    'cpromocode' => (!empty($saleProduct->getMappedModel->code) ? $saleProduct->getMappedModel->code : ''),
                    'csrcrowno' => '',
                    'vbdef8' => ($isFoc ? config('integrations.yonyou.nc_foc_type.promo') : config('integrations.yonyou.nc_foc_type.na')),
                ];
            }
        }

        //send over the dummy code to yy(admin fee, delivery fee, miscellaneous fee
        if ($sale->admin_fees > 0) {
            $adminFee = [
                'crowno' => 1,
                'cmaterialvid' => config('integrations.yonyou.nc_dummy_code.adminFee'),
                'nqtunitnum' => 1,
                'nqtorigprice' => 1,
                'nqtorigtaxprice' => 1,
                'ctaxcodeid' => $taxCode,
                'ntaxrate' => $taxRate,
                'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0),
                'foc_qty' => 'N',
                'ntax' => 1,
                'norigmny' => 1,
                'norigtaxmny' => $sale->admin_fees,
                'csendstordocid' =>$sale->stockLocation->code,
                'cpromocode' => '',
                'csrcrowno' => '',
                'vbdef8' => config('integrations.yonyou.nc_foc_type.na')
            ];
            array_push($productDetails, $adminFee);
        }

        if ($sale->delivery_fees > 0) {
            $deliveryFee = [
                'crowno' => 1,
                'cmaterialvid' => config('integrations.yonyou.nc_dummy_code.deliveryFee'),
                'nqtunitnum' => 1,
                'nqtorigprice' => 1,
                'nqtorigtaxprice' => 1,
                'ctaxcodeid' => $taxCode,
                'ntaxrate' => $taxRate,
                'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0),
                'foc_qty' => 'N',
                'ntax' => 1,
                'norigmny' => 1,
                'norigtaxmny' => $sale->delivery_fees,
                'csendstordocid' =>$sale->stockLocation->code,
                'cpromocode' => '',
                'csrcrowno' => '',
                'vbdef8' => config('integrations.yonyou.nc_foc_type.na')
            ];
            array_push($productDetails, $deliveryFee);
        }

        if ($sale->other_fees > 0) {
            $adminFee = [
                'crowno' => 1,
                'cmaterialvid' => config('integrations.yonyou.nc_dummy_code.otherFee'),
                'nqtunitnum' => 1,
                'nqtorigprice' => 1,
                'nqtorigtaxprice' => 1,
                'ctaxcodeid' => $taxCode,
                'ntaxrate' => $taxRate,
                'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0),
                'foc_qty' => 'N',
                'ntax' => 1,
                'norigmny' => 1,
                'norigtaxmny' => $sale->other_fees,
                'csendstordocid' => $sale->stockLocation->code,
                'cpromocode' => '',
                'csrcrowno' => '',
                'vbdef8' => config('integrations.yonyou.nc_foc_type.na')
            ];
            array_push($productDetails,$adminFee);
        }

        $data = array();
        $data['billno'] = $sale->document_number;
        $data['remark'] = $sale->remarks;
        $data['entity'] = $entityName;
        $data['membercode'] = $sale->user->old_member_id;
        $data['membername'] = $sale->user->name;
        $data['memberaddress'] = $memAddress;
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
        $data['billdate'] = (!empty($invoice) ? $invoice->invoice_date: $sale->transaction_date);
        $data['customerid'] = $customerCode;
        $data['invoicenumber'] = (!empty($invoice) ? $invoice->invoice_number: '');
        $data['tranloc'] = config('integrations.yonyou.nc_dept_code.' . $entityName);
        $data['currency'] = $sale->country->currency->code;
        $data['selfpickupcode'] = (!empty($invoice) ? $invoice->self_collection_code : '');
        $data['cwno'] = substr($sale->cw->cw_name, 5, 2);
        $data['cwyear'] = substr($sale->cw->cw_name, 0, 4);
        $data['srccwno'] = '';
        $data['srccwyear'] = '';
        $data['csrcsodate'] = '';
        $data['dataProduct'] = $productDetails;
        $data['trntype'] = config('integrations.yonyou.nibs_transaction_type.sales');
        $data['cexchangeno'] = (!empty($saleExchange) ? $saleExchange->id : '');
        $data['vtrantypecode'] = config('integrations.yonyou.nc_trn_type.'  .$locationChannel->locationType->code);
        $data['csrcinvoiceno']= '';
        $data['csrcsono']= '';
        $data['release'] = ($this->release == true? 1 : 0);
        $data['vdef1'] = config('integrations.yonyou.nc_type_of_sales.' . $locationChannel->locationType->code);
        $data['vbdef11'] = '';
        $data['vbdef10'] = '';
        $data['vbdef9'] = '';
        $data['vbdef13'] = $costCentre;
        $data['csettleorgid'] = $entityName;
        $data['csendstockorgid'] = config('integrations.yonyou.nc_warehouse_entity_code.' . $entityName);
        $data['vrownote'] = '';
        $data['vdef12'] = $data['trntype'];
        return $data;
    }

    /**
     * get sales cancellation api parameter
     *
     * @return array
     */
    public function getSalesCancellationAPIParameter()
    {
        $memAddress = '';
        $homeContact = '';

        $saleCancellation = $this->jobData;

        $sale = $saleCancellation->sale;

        $saleProducts = $sale->saleProducts;

        $invoice = $sale->invoices()->first();

        $entityName = strtoupper($sale->country->entity->name);

        $locationChannel = $this->locationRepositoryObj->find($saleCancellation->transaction_location_id);

        $isStockistSale = strtolower($locationChannel->locationType->code) == config('mappings.locations_types.stockist');
        
        $isOnlineSale = strtolower($locationChannel->locationType->code) == config('mappings.locations_types.online');

        if ($isStockistSale) {
            $customerCode = $saleCancellation->transactionLocation->code;
            $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        } else if ($isOnlineSale) {
            $customerCode = config('integrations.yonyou.nc_online_customer_code.' . $entityName);
            if (strtolower($saleCancellation->transactionLocation->locationType->code) == config('mappings.locations_types.stockist')) {
                $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
            }
            else {
                $costCentre = $saleCancellation->transactionLocation->code;
            }
        } else {
            $customerCode = config('integrations.yonyou.nc_branch_customer_code.' . $entityName);
            $costCentre = $saleCancellation->transactionLocation->code;
        }
        
        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

        $taxRate = $sale->tax_rate;

        if (!empty($sale->member->address->address_data)) {
            $memAddress = $this->memberAddressHelper
                ->getAddress($sale->member->address->address_data, '');
        }
        
        if (!empty($sale->member->memberContactInfo)) {
            $homeContact = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($sale->member->memberContactInfo->tel_home_1_country_code_id, $sale->member->memberContactInfo->tel_home_1_num);
        }

        $saleCancelProducts = $saleCancellation->saleCancelProducts;

        foreach ($saleCancelProducts as $saleCancelProduct)
        {
            if ($saleCancelProduct->quantity > 0) {
                $saleProduct = $saleCancelProduct->saleProduct;

                $qty = $saleCancelProduct->quantity + $saleCancelProduct->foc_qty;
                $unitPriceExclTax = $saleProduct->nmp_price;
                $unitPriceInclTax = $saleProduct->gmp_price_gst;
                $amountExclTax = $saleCancelProduct->price * (100 / (100 + $taxRate));
                $amountInclTax = $saleCancelProduct->price;
                $taxAmount = $amountInclTax - $amountExclTax;

                if ($saleProduct->foc_qty > 0 || $amountInclTax == 0) {
                    $isFoc = true;
                }
                else {
                    $isFoc = false;
                }

                $productDetails[] = [
                    'crowno' => $saleCancelProduct->id,
                    'cmaterialvid' => $saleCancelProduct->saleProduct->product->sku,
                    'nqtunitnum' => 0 - $qty,
                    'nqtorigprice' => $unitPriceExclTax,
                    'nqtorigtaxprice' => $unitPriceInclTax,
                    'ctaxcodeid' => $taxCode,
                    'ntaxrate' => $taxRate,
                    'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0),
                    'foc_qty' => ($isFoc ? 'Y' : 'N'),
                    'ntax' => number_format(0 - $taxAmount, 2),
                    'norigmny' => number_format(0 - $amountExclTax, 2),
                    'norigtaxmny' =>  number_format(0 - $amountInclTax, 2),
                    'csendstordocid' => $sale->stockLocation->code,
                    'cpromocode' => (!empty($saleCancelProduct->getMappedModel->code) ? $saleCancelProduct->getMappedModel->code : ''),
                    'csrcrowno' => $saleCancelProduct->sale_product_id,
                    'vbdef8' => ($isFoc ? config('integrations.yonyou.nc_foc_type.promo') : config('integrations.yonyou.nc_foc_type.na')),
                ];
            }
        }

        $data = array();
        $data['billno'] = $saleCancellation->creditNote->credit_note_number;
        $data['remark'] = $saleCancellation->remarks;
        $data['entity'] = $entityName;
        $data['membercode'] = $saleCancellation->user->old_member_id;
        $data['membername'] = $saleCancellation->member->name;
        $data['memberaddress'] = $memAddress;
        $data['recipientname'] = '';
        $data['recipientmobile'] = '';
        $data['shippingaddr1'] = '';
        $data['shippingaddr2'] = '';
        $data['shippingaddr3'] = '';
        $data['shippingaddr4'] = '';
        $data['shippingpostcode'] = '';
        $data['shippingcity'] = '';
        $data['shippingstate'] = '';
        $data['shippingcountry'] = '' ;
        $data['memberhomecontact'] = $homeContact;
        $data['billdate'] = $saleCancellation->transaction_date;
        $data['customerid'] = $customerCode;
        $data['invoicenumber'] = $saleCancellation->creditNote->credit_note_number;
        $data['tranloc'] = config('integrations.yonyou.nc_dept_code.' . $entityName);
        $data['currency'] = $sale->country->currency->code;
        $data['selfpickupcode'] = '';
        $data['cwno'] = substr($saleCancellation->cw->cw_name, 5, 2);
        $data['cwyear'] = substr($saleCancellation->cw->cw_name, 0, 4);
        $data['srccwno'] = substr($sale->cw->cw_name, 5, 2);
        $data['srccwyear'] = substr($sale->cw->cw_name, 0, 4);
        $data['dataProduct'] = $productDetails;
        $data['trntype'] = config('integrations.yonyou.nibs_transaction_type.cancellation');
        $data['cexchangeno'] = '';
        $data['vtrantypecode'] = config('integrations.yonyou.nc_trn_type.' . $locationChannel->locationType->code);
        $data['csrcinvoiceno'] = $invoice->invoice_number;
        $data['csrcsono'] = $sale->document_number;
        $data['csrcsodate'] = $sale->transaction_date;
        $data['release'] = ($isStockistSale ? 0 : 1);
        $data['vdef1'] = config('integrations.yonyou.nc_type_of_sales.' . $locationChannel->locationType->code);
        $data['vbdef11'] = '';
        $data['vbdef10'] = '';
        $data['vbdef9'] = '';
        $data['vbdef13'] = $costCentre;
        $data['csettleorgid'] = $entityName;
        $data['csendstockorgid'] = config('integrations.yonyou.nc_warehouse_entity_code.' . $entityName);
        $data['vrownote'] = $saleCancellation->cancellationReason->title;
        $data['vdef12'] = $data['trntype'];
        return $data;
    }

    /**
     * get legacy sales cancellation api parameter
     *
     * @return array
     */
    public function getLegacySalesCancellationAPIParameter()
    {
        $memAddress = '';
        $homeContact = '';

        $saleCancellation = $this->jobData;

        $invoice = $saleCancellation->legacyInvoice()->first();

        $entityName = strtoupper($invoice->country->entity->name);

        $locationChannel = $this->locationRepositoryObj->find($saleCancellation->transaction_location_id);

        $isStockistSale = strtolower($locationChannel->locationType->code) == config('mappings.locations_types.stockist');

        $isOnlineSale = strtolower($locationChannel->locationType->code) == config('mappings.locations_types.online');

        if ($isStockistSale) {
            $customerCode = $saleCancellation->transactionLocation->code;
            $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        } else if ($isOnlineSale) {
            $customerCode = config('integrations.yonyou.nc_online_customer_code.' . $entityName);
            if (strtolower($saleCancellation->transactionLocation->locationType->code) == config('mappings.locations_types.stockist')) {
                $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
            }
            else {
                $costCentre = $saleCancellation->transactionLocation->code;
            }
        } else {
            $customerCode = config('integrations.yonyou.nc_branch_customer_code.' . $entityName);
            $costCentre = $saleCancellation->transactionLocation->code;
        }
        
        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

        $taxRate = $this->yonyouRepositoryObj->getYonyouTaxRate($entityName);

        if (!empty($saleCancellation->member->address->address_data)) {
            $memAddress = $this->memberAddressHelper
                ->getAddress($saleCancellation->member->address->address_data, '');
        }
        
        if (!empty($saleCancellation->member->memberContactInfo)) {
            $homeContact = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($saleCancellation->member->memberContactInfo->tel_home_1_country_code_id, $saleCancellation->member->memberContactInfo->tel_home_1_num);
        }

        $saleCancelProducts = $saleCancellation->legacySaleCancellationProduct;

        foreach ($saleCancelProducts as $saleCancelProduct)
        {
            if ($saleCancelProduct->quantity > 0) {
                $qty = $saleCancelProduct->quantity;
                if ($saleCancelProduct->legacy_sales_cancellations_kitting_clone_id == null) {
                    $unitPriceExclTax = $saleCancelProduct->nmp_price;
                    $unitPriceInclTax = $saleCancelProduct->gmp_price_gst;
                }
                else {
                    $unitPriceExclTax = $saleCancelProduct->average_price_unit * (100 / (100 + $taxRate));
                    $unitPriceInclTax = $saleCancelProduct->average_price_unit;
                }
                $amountExclTax = $saleCancelProduct->total * (100 / (100 + $taxRate));
                $amountInclTax = $saleCancelProduct->total;
                $taxAmount = $amountInclTax - $amountExclTax;
                if ($amountInclTax == 0) {
                    $isFoc = true;
                }
                else {
                    $isFoc = false;
                }
                $productDetails[] = [
                    'crowno' => $saleCancelProduct->id,
                    'cmaterialvid' => $saleCancelProduct->legacySaleCancellationProductClone->sku,
                    'nqtunitnum' => 0 - $qty,
                    'nqtorigprice' => $unitPriceExclTax,
                    'nqtorigtaxprice' => $unitPriceInclTax,
                    'ctaxcodeid' => $taxCode,
                    'ntaxrate' => $taxRate,
                    'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0),
                    'foc_qty' => ($isFoc ? 'Y' : 'N'),
                    'ntax' => number_format(0 - $taxAmount, 2),
                    'norigmny' => number_format(0 - $amountExclTax, 2),
                    'norigtaxmny' =>  number_format(0 - $amountInclTax, 2),
                    'csendstordocid' => $saleCancellation->stockLocation->code,
                    'cpromocode' => (!empty($saleCancelProduct->getMappedModel->code) ? $saleCancelProduct->getMappedModel->code : ''),
                    'csrcrowno' => $saleCancelProduct->sale_product_id,
                    'vbdef8' => ($isFoc ? config('integrations.yonyou.nc_foc_type.promo') : config('integrations.yonyou.nc_foc_type.na')),
                ];
            }
        }

        $data = array();
        $data['billno'] = $saleCancellation->creditNote->credit_note_number;
        $data['remark'] = $saleCancellation->remarks;
        $data['entity'] = $entityName;
        $data['membercode'] = $saleCancellation->user->old_member_id;
        $data['membername'] = $saleCancellation->member->name;
        $data['memberaddress'] = $memAddress;
        $data['recipientname'] = '';
        $data['recipientmobile'] = '';
        $data['shippingaddr1'] = '';
        $data['shippingaddr2'] = '';
        $data['shippingaddr3'] = '';
        $data['shippingaddr4'] = '';
        $data['shippingpostcode'] = '';
        $data['shippingcity'] = '';
        $data['shippingstate'] = '';
        $data['shippingcountry'] = '' ;
        $data['memberhomecontact'] = $homeContact;
        $data['billdate'] = $saleCancellation->transaction_date;
        $data['customerid'] = $customerCode;
        $data['invoicenumber'] = $saleCancellation->creditNote->credit_note_number;
        $data['tranloc'] = config('integrations.yonyou.nc_dept_code.' . $entityName);
        $data['currency'] = $invoice->country->currency->code;
        $data['selfpickupcode'] = '';
        $data['cwno'] = substr($saleCancellation->cw->cw_name, 5, 2);
        $data['cwyear'] = substr($saleCancellation->cw->cw_name, 0, 4);
        $data['srccwno'] = ''; 
        $data['srccwyear'] = '';
        $data['dataProduct'] = $productDetails;
        $data['trntype'] = config('integrations.yonyou.nibs_transaction_type.cancellation');
        $data['cexchangeno'] = '';
        $data['vtrantypecode'] = config('integrations.yonyou.nc_trn_type.' . $locationChannel->locationType->code);
        $data['csrcinvoiceno'] = $invoice->invoice_number;
        $data['csrcsono'] = '';
        $data['csrcsodate'] = '';
        $data['release'] = ($isStockistSale ? 0 : 1);
        $data['vdef1'] = config('integrations.yonyou.nc_type_of_sales.' . $locationChannel->locationType->code);
        $data['vbdef11'] = '';
        $data['vbdef10'] = '';
        $data['vbdef9'] = '';
        $data['vbdef13'] = $costCentre;
        $data['csettleorgid'] = $entityName;
        $data['csendstockorgid'] = config('integrations.yonyou.nc_warehouse_entity_code.' . $entityName);
        $data['vrownote'] = $saleCancellation->cancellationReason->title;
        $data['vdef12'] = $data['trntype'];
        return $data;
    }

    /**
     * get sales exchange credit note api parameter
     *
     * @return array
     */
    public function getSalesExchangeCancellationAPIParameter()
    {
        $memAddress = '';
        $homeContact = '';

        $saleExchange = $this->jobData;
        
        $sale = $saleExchange->parentSale;
        
        $saleExchangeProducts = $saleExchange->saleExchangeProducts;
        
        $saleProducts = $sale->saleProducts;
        
        $invoice = $sale->invoices()->first();
        
        $entityName = strtoupper($saleExchange->country->entity->name);

        $locationChannel = $this->locationRepositoryObj->find($saleExchange->transaction_location_id);

        $isStockistSale = strtolower($locationChannel->locationType->code) == config('mappings.locations_types.stockist');

        $isOnlineSale = strtolower($locationChannel->locationType->code) == config('mappings.locations_types.online');

        if ($isStockistSale) {
            $customerCode = $saleExchange->transactionLocation->code;
            $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        } else if ($isOnlineSale) {
            $customerCode = config('integrations.yonyou.nc_online_customer_code.' . $entityName);
            if (strtolower($saleExchange->transactionLocation->locationType->code) == config('mappings.locations_types.stockist')) {
                $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
            }
            else {
                $costCentre = $saleExchange->transactionLocation->code;
            }
        } else {
            $customerCode = config('integrations.yonyou.nc_branch_customer_code.' . $entityName);
            $costCentre = $saleExchange->transactionLocation->code;
        }
        
        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

        $taxRate = $sale->tax_rate;

        if (!empty($sale->member->address->address_data)) {
            $memAddress = $this->memberAddressHelper
                ->getAddress($sale->member->address->address_data, '');
        }
        
        if (!empty($sale->member->memberContactInfo)) {
            $homeContact = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($sale->member->memberContactInfo->tel_home_1_country_code_id, $sale->member->memberContactInfo->tel_home_1_num);
        }

        foreach ($saleExchangeProducts as $saleExchangeProduct)
        {
            $saleProduct = $saleExchangeProduct->product;

            $qty = $saleExchangeProduct->return_quantity;
            $unitPriceExclTax = $saleExchangeProduct->nmp_price;
            $unitPriceInclTax = $saleExchangeProduct->gmp_price_gst;
            $amountExclTax = $saleExchangeProduct->total * (100 / (100 + $taxRate));
            $amountInclTax = $saleExchangeProduct->total;
            $taxAmount = $amountInclTax - $amountExclTax;

            if ($saleProduct->foc_qty > 0 || $amountInclTax == 0) {
                $isFoc = true;
            }
            else {
                $isFoc = false;
            }

            $productDetails[] = [
                'crowno' => $saleExchangeProduct->id,
                'cmaterialvid' => $saleExchangeProduct->product->product->sku,
                'nqtunitnum' => 0 - $qty,
                'nqtorigprice' => $unitPriceExclTax,
                'nqtorigtaxprice' => $unitPriceInclTax,
                'ctaxcodeid' => $taxCode,
                'ntaxrate' => $taxRate,
                'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0),
                'foc_qty' => ($isFoc ? 'Y' : 'N') ,
                'ntax' => number_format(0 - $taxAmount, 2),
                'norigmny' => number_format(0 - $amountExclTax, 2),
                'norigtaxmny' => number_format(0 - $amountInclTax, 2),
                'csendstordocid' => $sale->stockLocation->code,
                'cpromocode' => (!empty($saleExchangeProduct->getMappedModel->code) ? $saleExchangeProduct->getMappedModel->code : ''),
                'csrcrowno' => $saleExchangeProduct->sale_product_id,
                'vbdef8' => ($isFoc ? config('integrations.yonyou.nc_foc_type.promo') : config('integrations.yonyou.nc_foc_type.na')),
            ];
        }

        $data = array();
        $data['billno'] = $saleExchange->creditNote->credit_note_number;
        $data['remark'] = $saleExchange->remarks;
        $data['entity'] = $entityName;
        $data['membercode'] = $saleExchange->user->old_member_id;
        $data['membername'] = $saleExchange->member->name;
        $data['memberaddress'] = $memAddress;
        $data['recipientname'] = '';
        $data['recipientmobile'] = '';
        $data['shippingaddr1'] = '';
        $data['shippingaddr2'] = '';
        $data['shippingaddr3'] = '';
        $data['shippingaddr4'] = '';
        $data['shippingpostcode'] = '';
        $data['shippingcity'] = '';
        $data['shippingstate'] = '';
        $data['shippingcountry'] = '' ;
        $data['memberhomecontact'] = $homeContact;
        $data['billdate'] = $saleExchange->transaction_date;
        $data['customerid'] = $customerCode;
        $data['invoicenumber'] = $saleExchange->creditNote->credit_note_number;
        $data['tranloc'] = config('integrations.yonyou.nc_dept_code.' . $entityName);
        $data['currency'] = $sale->country->currency->code;
        $data['selfpickupcode'] = '';
        $data['cwno'] = substr($saleExchange->cw->cw_name, 5, 2);
        $data['cwyear'] = substr($saleExchange->cw->cw_name, 0, 4);
        $data['srccwno'] = substr($sale->cw->cw_name, 5, 2);
        $data['srccwyear'] = substr($sale->cw->cw_name, 0, 4);
        $data['dataProduct'] = $productDetails;
        $data['trntype'] = config('integrations.yonyou.nibs_transaction_type.cancellation');
        $data['cexchangeno'] = (!empty($saleExchange) ? $saleExchange->id : '');
        $data['vtrantypecode'] = config('integrations.yonyou.nc_trn_type.' . $locationChannel->locationType->code);
        $data['release'] = ($isStockistSale ? 0 : 1);
        $data['csrcinvoiceno'] = $invoice->invoice_number;
        $data['csrcsono'] = $sale->document_number;
        $data['csrcsodate'] = $sale->transaction_date;
        $data['vdef1'] = config('integrations.yonyou.nc_type_of_sales.' . $locationChannel->locationType->code);
        $data['vbdef11'] = '';
        $data['vbdef10'] = '';
        $data['vbdef9'] = '';
        $data['vbdef13'] = $costCentre;
        $data['csettleorgid'] = $entityName;
        $data['csendstockorgid'] = config('integrations.yonyou.nc_warehouse_entity_code.' . $entityName);
        $data['vrownote'] = '';
        $data['vdef12'] = $data['trntype'];
        return $data;
    }

    /**
     * get legacy sales exchange credit note api parameter
     *
     * @return array
     */
    public function getLegacySalesExchangeCancellationAPIParameter()
    {
        $memAddress = '';
        $homeContact = '';

        $saleExchange = $this->jobData;
        
        $saleExchangeProducts = $saleExchange->legacySaleExchangeReturnProduct;
        
        $invoice = $saleExchange->legacyInvoice()->first();
        
        $entityName = strtoupper($saleExchange->country->entity->name);
        
        $locationChannel = $this->locationRepositoryObj->find($saleExchange->transaction_location_id);

        $isStockistSale = strtolower($locationChannel->locationType->code) == config('mappings.locations_types.stockist');

        $isOnlineSale = strtolower($locationChannel->locationType->code) == config('mappings.locations_types.online');

        if ($isStockistSale) {
            $customerCode = $saleExchange->transactionLocation->code;
            $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
        } else if ($isOnlineSale) {
            $customerCode = config('integrations.yonyou.nc_online_customer_code.' . $entityName);
            if (strtolower($saleExchange->transactionLocation->locationType->code) == config('mappings.locations_types.stockist')) {
                $costCentre = config('integrations.yonyou.nc_stockist_cost_centre.' . $entityName);
            }
            else {
                $costCentre = $saleExchange->transactionLocation->code;
            }
        } else {
            $customerCode = config('integrations.yonyou.nc_branch_customer_code.' . $entityName);
            $costCentre = $saleExchange->transactionLocation->code;
        }

        $taxCode = $this->yonyouRepositoryObj->getYonyouTaxCode($entityName);

        $taxRate = $this->yonyouRepositoryObj->getYonyouTaxRate($entityName);

        if (!empty($saleExchange->member->address->address_data)) {
            $memAddress = $this->memberAddressHelper
                ->getAddress($saleExchange->member->address->address_data, '');
        }
        
        if (!empty($saleExchange->member->memberContactInfo)) {
            $homeContact = $this->yonyouRepositoryObj
                ->getFormattedPhoneNumber($saleExchange->member->memberContactInfo->tel_home_1_country_code_id, $saleExchange->member->memberContactInfo->tel_home_1_num);
        }

        foreach ($saleExchangeProducts as $saleExchangeProduct)
        {
            $qty = $saleExchangeProduct->return_quantity;
            if ($saleExchangeProduct->legacy_sale_exchange_kitting_clone_id == null) {
                $unitPriceExclTax = $saleExchangeProduct->nmp_price;
                $unitPriceInclTax = $saleExchangeProduct->gmp_price_gst;
            }
            else {
                $unitPriceExclTax = $saleExchangeProduct->average_price_unit * (100 / (100 + $taxRate));
                $unitPriceInclTax = $saleExchangeProduct->average_price_unit;
            }
            $amountExclTax = $saleExchangeProduct->return_total * (100 / (100 + $taxRate));
            $amountInclTax = $saleExchangeProduct->return_total;
            $taxAmount = $amountInclTax - $amountExclTax;

            if ($amountInclTax == 0) {
                $isFoc = true;
            }
            else {
                $isFoc = false;
            }

            $productDetails[] = [
                'crowno' => $saleExchangeProduct->id,
                'cmaterialvid' => $saleExchangeProduct->legacySaleExchangeProductClone->sku,
                'nqtunitnum' => 0 - $qty,
                'nqtorigprice' => $unitPriceExclTax,
                'nqtorigtaxprice' => $unitPriceInclTax,
                'ctaxcodeid' => $taxCode,
                'ntaxrate' => $taxRate,
                'ftaxtypeflag' => ($taxRate > 0 ? 1 : 0),
                'foc_qty' => ($isFoc ? 'Y' : 'N'),
                'ntax' => number_format(0 - $taxAmount, 2),
                'norigmny' => number_format(0 - $amountExclTax, 2),
                'norigtaxmny' => number_format(0 - $amountInclTax, 2),
                'csendstordocid' => $saleExchange->stockLocation->code,
                'cpromocode' => '', 
                'csrcrowno' => $saleExchangeProduct->id,
                'vbdef8' => ($isFoc ? config('integrations.yonyou.nc_foc_type.promo') : config('integrations.yonyou.nc_foc_type.na'))
            ];
        }

        $data = array();
        $data['billno'] = $saleExchange->creditNote->credit_note_number;
        $data['remark'] = $saleExchange->remarks;
        $data['entity'] = $entityName;
        $data['membercode'] = $saleExchange->user->old_member_id;
        $data['membername'] = $saleExchange->member->name;
        $data['memberaddress'] = $memAddress;
        $data['recipientname'] = '';
        $data['recipientmobile'] = '';
        $data['shippingaddr1'] = '';
        $data['shippingaddr2'] = '';
        $data['shippingaddr3'] = '';
        $data['shippingaddr4'] = '';
        $data['shippingpostcode'] = '';
        $data['shippingcity'] = '';
        $data['shippingstate'] = '';
        $data['shippingcountry'] = '' ;
        $data['memberhomecontact'] = $homeContact;
        $data['billdate'] = $saleExchange->transaction_date;
        $data['customerid'] = $customerCode;
        $data['invoicenumber'] = $saleExchange->creditNote->credit_note_number;
        $data['tranloc'] = config('integrations.yonyou.nc_dept_code.' . $entityName);
        $data['currency'] = $saleExchange->country->currency->code;
        $data['selfpickupcode'] = '';
        $data['cwno'] = substr($saleExchange->cw->cw_name, 5, 2);
        $data['cwyear'] = substr($saleExchange->cw->cw_name, 0, 4);
        $data['srccwno'] = '';
        $data['srccwyear'] = '';
        $data['dataProduct'] = $productDetails;
        $data['trntype'] = config('integrations.yonyou.nibs_transaction_type.cancellation');
        $data['cexchangeno'] = (!empty($saleExchange) ? $saleExchange->id : '');
        $data['vtrantypecode'] = config('integrations.yonyou.nc_trn_type.' . $locationChannel->locationType->code);
        $data['release'] = ($isStockistSale ? 0 : 1);
        $data['csrcinvoiceno'] = '';
        $data['csrcsono'] = '';
        $data['csrcsodate'] = '';
        $data['vdef1'] = config('integrations.yonyou.nc_type_of_sales.' . $locationChannel->locationType->code);
        $data['vbdef11'] = ''; 
        $data['vbdef10'] = ''; 
        $data['vbdef9'] = ''; 
        $data['vbdef13'] = $costCentre;
        $data['csettleorgid'] = $entityName;
        $data['csendstockorgid'] = config('integrations.yonyou.nc_warehouse_entity_code.' . $entityName);
        $data['vrownote'] = '';
        $data['vdef12'] = $data['trntype'];
        return $data;
    }
}
