<?php
namespace App\Interfaces\Integrations;

interface YonyouInterface
{
    /**
     * get new uap token from yonyou
     *
     * @return bool|string
     */
    public function getNewUapToken();

    /**
     * send integration request to yonyou
     * 
     * @param string $apiPath
     * @param mixed $requestData
     * @return mixed
     */
    public function sendIntegrationRequest($apiPath, $requestData);

    /**
     * serialize Exception object for integration log
     * 
     * @param \Exception $exception
     * @return mixed
     */
    public function serializeException(\Exception $exception);

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
    public function createIntegrationLog($jobType, $mappingModel, $mappingId, $requestData, $responseData, $returnCode);
   
    /**
     * get mapping model object
     * 
     * @param string $mappingModel
     * @param int $mappingId
     * @param bool $failIfNotFound
     * @return mixed
     */
    public function getMappingModelObject($mappingModel, $mappingId, $failIfNotFound);
    
    /**
     * get integration status field name
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @return mixed
     */
    public function getIntegrationStatusFieldName($jobType, $mappingModel);
    
    /**
     * schedule integration job
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @param int $mappingId
     * @return bool
     */
    public function scheduleIntegrationJob($jobType, $mappingModel, $mappingId);
    
    /**
     * execute integration job
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @param int $mappingId
     * @return bool
     */
    public function executeIntegrationJob($jobType, $mappingModel, $mappingId);
    
    /**
     * complete integration job
     * 
     * @param string $jobType
     * @param string $mappingModel
     * @param int $mappingId
     * @param string $returnCode
     * @return mixed
     */
    public function completeIntegrationJob($jobType, $mappingModel, $mappingId, $returnCode);
 
    /**
     * retry integration job (update status flag)
     * 
     * @param string $integrationType
     * @param string $mappingModel
     * @param int $mappingId
     * @return mixed
     */
    public function retryIntegrationJob($jobType, $mappingModel, $mappingId);
   
    /**
     * get array of country id that requires yy integration
     * 
     * @return mixed
     */
    public function getIntegrationCountryIdArray();
    
    /**
     * get phone number in formatted format
     * 
     * @param int $phoneCodeId
     * @param string $phoneNumber
     * @return mixed
     */
    public function getFormattedPhoneNumber($phoneCodeId, $phoneNumber);
    
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
    );
 
    /**
     * retry failed yonyou integration
     * 
     * @param mixed $yyIntegrationlogsIds
     * @return mixed
     */
    public function retryFailedYonyouIntegration($yyIntegrationlogsIds);

    /**
     * get yonyou state code
     * 
     * @param string $countryName
     * @param string $stateName
     * @return mixed
     */
    public function getYonyouStateCode($countryName, $stateName);

    /**
     * get yonyou tax code
     * 
     * @param string $entityName
     * @return mixed
     */
    public function getYonyouTaxCode($entityName);

    /**
     * get yonyou tax rate
     * 
     * @param string $entityName
     * @return mixed
     */
    public function getYonyouTaxRate($entityName);
}