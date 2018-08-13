<?php
namespace App\Repositories\Integrations;

use App\Interfaces\{
    Integrations\YonyouInterface
};
use App\Models\{
    Integrations\YonyouIntegrationLog,
    Stockists\ConsignmentDepositRefund,
    Stockists\ConsignmentOrderReturn,
    Locations\Country, 
    Locations\Entity,
    Invoices\Invoice,
    Payments\Payment,
    Sales\Sale,
    Sales\SaleCancellation,
    Sales\SaleExchange,
    Sales\SaleProductClone,
    Settings\Setting,
    Settings\SettingKey,
    Settings\Tax,
    Stockists\StockistSalePaymentTransaction
};
use App\Repositories\BaseRepository;
use GuzzleHttp;

class YonyouRepository extends BaseRepository implements YonyouInterface
{
    private $consignmentDepositRefundObj,
        $consignmentOrderReturnObj,
        $countryObj,
        $entityObj,
        $invoiceObj,
        $paymentObj,
        $saleObj,
        $saleCancellationObj,
        $saleExchangeObj,
        $saleProductCloneObj,
        $settingObj,
        $settingKeyObj,
        $taxObj,
        $stockistSalePaymentTransactionObj; 

    public function __construct(
        YonyouIntegrationLog $model,
        ConsignmentDepositRefund $consignmentDepositRefund,
        ConsignmentOrderReturn $consignmentOrderReturn,
        Country $country,
        Entity $entity,
        Invoice $invoice,
        Payment $payment,
        Sale $sale, 
        SaleCancellation $saleCancellation, 
        SaleExchange $saleExchange,
        SaleProductClone $saleProductClone,
        Setting $setting,
        SettingKey $settingKey,
        Tax $tax,
        StockistSalePaymentTransaction $stockistSalePaymentTransaction)
    {
        parent::__construct($model);
        
        $this->consignmentDepositRefundObj = $consignmentDepositRefund;
        $this->consignmentOrderReturnObj = $consignmentOrderReturn;
        $this->countryObj = $country;
        $this->entityObj = $entity;
        $this->invoiceObj = $invoice;
        $this->paymentObj = $payment;
        $this->saleObj  = $sale;
        $this->saleCancellationObj = $saleCancellation;
        $this->saleExchangeObj = $saleExchange;
        $this->saleProductCloneObj = $saleProductClone;
        $this->settingObj = $setting;
        $this->settingKeyObj = $settingKey;
        $this->taxObj = $tax;
        $this->stockistSalePaymentTransactionObj = $stockistSalePaymentTransaction; 
    }

    /**
     * get uap token from settings table
     * 
     * @return mixed
     */
    private function getUapToken() 
    {
        $settingKeyId = $this->settingKeyObj
            ->where('key', 'yonyou_uap_token')
            ->pluck('id')
            ->toArray();

        $setting = $this->settingObj
            ->whereIn('setting_key_id', $settingKeyId)
            ->first();

        return $setting['value'];
    }

    /**
     * update uap token to settings table
     * 
     * @param string $uapToken
     * @return mixed
     */
    private function updateUapToken($uapToken)
    {
        $settingKeyId = $this->settingKeyObj
            ->where('key', 'yonyou_uap_token')
            ->pluck('id')
            ->toArray();

        $setting = $this->settingObj
            ->whereIn('setting_key_id', $settingKeyId)
            ->first();

        $setting->update(['value' => $uapToken]);
    }

    /**
     * helper to send integration request to yonyou
     * 
     * @param string $uapToken
     * @param string $apiPath
     * @param mixed $requestData
     * @return mixed
     */
    private function sendIntegrationRequestHelper($uapToken, $apiPath, $requestData) 
    {
        $client = new GuzzleHttp\Client(['base_uri' => config('integrations.yonyou.base_api_url')]);
        
        $response = $client->request(
            'POST',
            $apiPath,
            [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'uap_dataSource' => config('integrations.yonyou.data_source'),
                    'uap_usercode' => config('integrations.yonyou.username'),
                    'uap_token' => $uapToken
                ],
                'json' => $requestData
            ]
        );
        
        return json_decode($response->getBody());
    }

    /**
     * get new uap token from yonyou
     *
     * @return bool|string
     */
    public function getNewUapToken()
    {
        $client = new GuzzleHttp\Client(['base_uri' => config('integrations.yonyou.base_api_url')]);
        $response = $client->request(
            'POST',
            config('integrations.yonyou.auth_api.path'),
            [
                'headers' => [
                    'Content-type' => 'application/json',
                    'uap_dataSource' => config('integrations.yonyou.data_source'),
                    'uap_usercode' => config('integrations.yonyou.username')
                ],
                'json' => [
                    'usercode' => config('integrations.yonyou.username'),
                    'pwd' => config('integrations.yonyou.password')
                ]
            ]
        );

        $results = json_decode($response->getBody());

        return ($results->uap_token) ? $results->uap_token : false;
    }

    /**
     * send integration request to yonyou
     * 
     * @param string $apiPath
     * @param mixed $requestData
     * @return mixed
     */
    public function sendIntegrationRequest($apiPath, $requestData)
    {
        try
        {
            return $this->sendIntegrationRequestHelper($this->getUapToken(), $apiPath, $requestData);
        }
        catch (\Exception $exception)
        {
            $errorMessage = $exception->__toString();

            if (strpos($errorMessage, 'Failed: User session expired,please re login!') != FALSE) {
                $uapToken = $this->getNewUapToken();

                $this->updateUapToken($uapToken);

                return $this->sendIntegrationRequestHelper($uapToken, $apiPath, $requestData);
            }
            else {
                throw $exception;
            }
        }
    }

    /**
     * serialize Exception object for integration log
     * 
     * @param \Exception $exception
     * @return mixed
     */
    public function serializeException(\Exception $exception) 
    {
        try {
            $serializedException = array(
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace()
            );
        }
        catch (\Exception $exception2) {
            $serializedException = array(
                'exception' => $exception->__toString(),
                'exception2' => $exception2->__toString()
            );
        }
        return $serializedException;
    }

    /**
     * create integration log
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @param int $mappingId
     * @param mixed $requestData
     * @param mixed $responseData
     * @param string $returnCode
     * @return mixed
     */
    public function createIntegrationLog($jobType, $mappingModel, $mappingId, $requestData, $responseData, $returnCode)
    {
        return $this->modelObj->create(
            [
                'job_type' => $jobType,
                'mapping_model' => $mappingModel,
                'mapping_id' => $mappingId,
                'request_data' => json_encode($requestData),
                'response_data' => json_encode($responseData),
                'return_code' => $returnCode
            ]
        );
    }

    /**
     * get mapping model object
     * 
     * @param string $mappingModel
     * @param int $mappingId
     * @param bool $failIfNotFound
     * @return mixed
     */
    public function getMappingModelObject($mappingModel, $mappingId, $failIfNotFound)
    {
        $mappingModelObject = null;

        switch ($mappingModel) {
            case 'consignments_deposits_refunds':
                $mappingModelObject = $this->consignmentDepositRefundObj;
                break;

            case 'consignments_orders_returns':
                $mappingModelObject = $this->consignmentOrderReturnObj;
                break;

            case 'stockists_sales_payments_transactions':
                $mappingModelObject = $this->stockistSalePaymentTransactionObj;
                break;

            case 'sales':
                $mappingModelObject = $this->saleObj;
                break;

            case 'invoices':
                $mappingModelObject = $this->invoiceObj;
                break;

            case 'payments':
                $mappingModelObject = $this->paymentObj;
                break;

            case 'sales_cancellations':
                $mappingModelObject = $this->saleCancellationObj;
                break;

            case 'sales_exchanges':
                $mappingModelObject = $this->saleExchangeObj;
                break;
        }

        if ($failIfNotFound) {
            return $mappingModelObject->findOrFail($mappingId);    
        }
        else {
            return $mappingModelObject->find($mappingId);
        }
    }

    /**
     * get integration status field name
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @return mixed
     */
    public function getIntegrationStatusFieldName($jobType, $mappingModel)
    {
        $fieldName = 'yy_integration_status';

        switch ($mappingModel) {
            case 'consignments_deposits_refunds':
                if ($jobType == 'CONSIGNMENT_DEPOSIT_REJECT') {
                    $fieldName = 'yy_reject_integration_status';
                }
                break;

            // case 'consignments_orders_returns':
            //     break;

            case 'stockists_sales_payments_transactions':
                if ($jobType == 'STOCKIST_RECEIPT_ADJ') {
                    $fieldName = 'yy_adjustment_integration_status';
                }
                else {
                    $fieldName = 'yy_payment_integration_status';
                }
                break;

            case 'sales':
                if ($jobType == 'SALES_RECEIPT') {
                    $fieldName = 'yy_receipt_integration_status';
                }
                else if ($jobType == 'SALES_RENTAL_UPDATE') { //rental sale release/delivery update
                    $fieldName = 'yy_update_integration_status';
                }
                break;

            // case 'invoices':
            //     break;

            case 'payments':
                if ($jobType == 'PREORDER_REFUND' || $jobType == 'STOCKIST_PREORDER_REFUND'){
                    $fieldName = 'yy_refund_integration_status';
                }
                break;

            case 'sales_cancellations':
                if ($jobType == 'EWALLET_SALESCANCELLATION'){
                    $fieldName = 'yy_receipt_integration_status';
                }
                break;

            // case 'sales_exchanges':
            //     break;
        }

        return $fieldName;
    }

    /**
     * schedule integration job
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @param int $mappingId
     * @return bool
     */
    public function scheduleIntegrationJob($jobType, $mappingModel, $mappingId)
    {
        $jobModel = $this->getMappingModelObject($mappingModel, $mappingId, true);

        if ($jobModel != null) {
            $fieldName = $this->getIntegrationStatusFieldName($jobType, $mappingModel);

            if ($jobModel[$fieldName] == config('integrations.yonyou.yy_integration_status.new')) {
                $jobModel[$fieldName] = config('integrations.yonyou.yy_integration_status.queue');
                
                $jobModel->update();
                
                return true;
            }
        }
        return false;
    }

    /**
     * execute integration job
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @param int $mappingId
     * @return bool
     */
    public function executeIntegrationJob($jobType, $mappingModel, $mappingId)
    {
        $jobModel = $this->getMappingModelObject($mappingModel, $mappingId, true);

        if ($jobModel != null) {
            $fieldName = $this->getIntegrationStatusFieldName($jobType, $mappingModel);

            if ($jobModel[$fieldName] == config('integrations.yonyou.yy_integration_status.queue')) {
                $jobModel[$fieldName] = config('integrations.yonyou.yy_integration_status.running');
                
                $jobModel->update();

                return true;
            }
        }
        return false;
    }

    /**
     * complete integration job
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @param int $mappingId
     * @param string $returnCode
     * @return mixed
     */
    public function completeIntegrationJob($jobType, $mappingModel, $mappingId, $returnCode) 
    {
        $jobModel = $this->getMappingModelObject($mappingModel, $mappingId, true);
        
        if ($jobModel != null) {
            $fieldName = $this->getIntegrationStatusFieldName($jobType, $mappingModel);

            if ($returnCode == '200') {
                $jobModel[$fieldName] = config('integrations.yonyou.yy_integration_status.success');
            }
            else {
                $jobModel[$fieldName] = config('integrations.yonyou.yy_integration_status.error');
            }
            
            $jobModel->update();

            return true;
        }

        return false;
    }

    /**
     * retry integration job (update status flag)
     * 
     * @param string $integrationType
     * @param string $mappingModel
     * @param int $mappingId
     * @return mixed
     */
    public function retryIntegrationJob($jobType, $mappingModel, $mappingId) 
    {
        $jobModel = $this->getMappingModelObject($mappingModel, $mappingId, true);
        
        if ($jobModel != null) {
            $fieldName = $this->getIntegrationStatusFieldName($jobType, $mappingModel);

            if ($jobModel[$fieldName] == config('integrations.yonyou.yy_integration_status.success')) {
                return 'Integration completed successfully, no retry allowed.';
            }
            else if ($jobModel[$fieldName] == config('integrations.yonyou.yy_integration_status.new')) {
                return 'Integration is pending, no update needed.';
            }
            else { // error, queue, running, exclude
                $jobModel[$fieldName] = config('integrations.yonyou.yy_integration_status.new');
                $jobModel->update();
                return 'Record updated.';
            }
        }
        else {
            return 'Record not found.';
        }
    }

    /**
     * get array of country id that requires yy integration
     * 
     * @return mixed
     */
    public function getIntegrationCountryIdArray()
    {
        return $this->countryObj
            ->whereIn('code_iso_2', config('integrations.yonyou.supported_countries'))
            ->pluck('id')
            ->toArray();
    }

    /**
     * get phone number in formatted format
     * 
     * @param int $phoneCodeId
     * @param string $phoneNumber
     * @return mixed
     */
    public function getFormattedPhoneNumber($phoneCodeId, $phoneNumber)
    {
        $formattedPhoneNumber = '';

        if ($phoneCodeId != null) {
            $country = $this->countryObj
                ->find($phoneCodeId);

            if ($country != null) {
                if ($country->code_iso_2 != null) {
                    $formattedPhoneNumber = $formattedPhoneNumber . $country->code_iso_2;
                }
                if ($country->call_code != null) {
                    $formattedPhoneNumber = $formattedPhoneNumber . '+' . $country->call_code;
                }
            }
        }

        if ($phoneNumber != null) {
            $formattedPhoneNumber = $formattedPhoneNumber . $phoneNumber;
        }

        return $formattedPhoneNumber;
    }

    /**
     * get yonyou integration log details filtered by different var's
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @param int $mappingId
     * @param string $requestData
     * @param string $responseData
     * @param string $returnCode
     * @param mixed $createDateFrom
     * @param mixed $createDateTo
     * @param int $excludeJsonData
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getYonyouIntegrationLogsByFilters(
        string $jobType = '',
        string $mappingModel = '',
        int $mappingId = 0,
        string $requestData = '',
        string $responseData = '',
        string $returnCode = '',
        $createDateFrom = '',
        $createDateTo = '',
        int $excludeJsonData = 0,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj;

        if ($jobType != '') {
            $data = $data
                ->where('job_type', 'like', '%' . $jobType . '%');
        }

        if ($mappingModel != '') {
            $data = $data
                ->where('mapping_model', $mappingModel);
        }

        if ($mappingId != '') {
            $data = $data
                ->where('mapping_id', $mappingId);
        }

        if ($requestData != '') {
            $data = $data
                ->where('request_data', $requestData);
        }

        if ($responseData != '') {
            $data = $data
                ->where('response_data', $responseData);
        }

        if ($returnCode != '') {
            $data = $data
                ->where('return_code', $returnCode);
        }
        
        if ($createDateFrom != '') {
            $data = $data
                ->where('created_at','>=', $createDateFrom);
        }

        if ($createDateTo != '') {
            $data = $data
                ->where('created_at','<=', $createDateTo);
        }

        $totalRecords = collect(['total' => $data->count()]);

        $data = $data->orderBy($orderBy, $orderMethod);

        $data = ($paginate > 0) ?
            $data->offset($offset)->limit($paginate)->get() :
            $data->get();

        foreach($data as $log) {
            $mappingModelObject = $this->getMappingModelObject($log['mapping_model'], $log['mapping_id'], false);
            if ($mappingModelObject != null) {
                $statusFieldName = $this->getIntegrationStatusFieldName($log['job_type'], $log['mapping_model']);
                $log['yy_integration_status'] = $mappingModelObject[$statusFieldName];
            }
            else {
                $log['yy_integration_status'] = null;
            }
            if ($excludeJsonData == 1) {
                unset($log->request_data);
                unset($log->response_data);
            }
        }

        return $totalRecords->merge(['data' => $data]);
    }

    /**
     * retry failed yonyou integration
     * 
     * @param mixed $yyIntegrationLogsIds
     * @return mixed
     */
    public function retryFailedYonyouIntegration($yyIntegrationLogsIds) 
    {
        $result = [];

        foreach ($yyIntegrationLogsIds as $key => $yyIntegrationlogsId) {
            $result[$yyIntegrationlogsId] = 'Log record not found.';
        }
        
        $yyIntegrationLogs = $this->modelObj
            ->whereIn('id', $yyIntegrationLogsIds)
            ->get();

        foreach ($yyIntegrationLogs as $key => $yyIntegrationLog) {
            $result[$yyIntegrationLog['id']] = $this->retryIntegrationJob(
                $yyIntegrationLog['job_type'], 
                $yyIntegrationLog['mapping_model'], 
                $yyIntegrationLog['mapping_id']); 
        }
        
        return $result;
    }

    /**
     * get yonyou state code
     * 
     * @param string $countryName
     * @param string $stateName
     * @return mixed
     */
    public function getYonyouStateCode($countryName, $stateName) 
    {
        $stateCode = $stateName;

        if (!empty($countryName) && !empty($stateName)) {
            $configKey = 'integrations.yonyou.yy_state_code.' . strtoupper($countryName) . '.' . strtoupper($stateName);

            if (!empty(config($configKey))) {
                $stateCode = config($configKey);
            }
        }
        
        return $stateCode;
    }

    /**
     * get yonyou tax code
     * 
     * @param string $entityName
     * @return mixed
     */
    public function getYonyouTaxCode($entityName) 
    {
        $configKey = 'integrations.yonyou.yy_tax_code.' . strtoupper($entityName);
        
        if (empty(config($configKey))) {
            $configKey = 'integrations.yonyou.yy_default_tax_code';
        }

        return config($configKey);
    }

    /**
     * get yonyou tax rate
     * 
     * @param string $entityName
     * @return mixed
     */
    public function getYonyouTaxRate($entityName) 
    {
        $entity = $this->entityObj
            ->where('name', $entityName)
            ->first();

        if (!empty($entity)) {
            $tax = $this->taxObj
                ->where('country_id', $entity->country_id)
                ->first();
            
            if (!empty($tax)) {
                return $tax->rate;
            }
        }

        return 0.0;
    }
}