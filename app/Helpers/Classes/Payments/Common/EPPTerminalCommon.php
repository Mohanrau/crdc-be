<?php
namespace App\Helpers\Classes\Payments\Common;

use App\{
    Helpers\Classes\Payments\Payment,
    Interfaces\Masters\MasterInterface,
    Interfaces\Settings\SettingsInterface
};

class EPPTerminalCommon extends Payment
{
    protected $approvalStatusConfigCodes, $docStatusConfigCodes;

    /**
     * EPPTerminalCommon constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->isManual = true;

        $this->isFormGenerateRequired = false;

        $this->isCreationGeneratePaymentDetail = true;

        $this->approvalStatusConfigCodes = config('mappings.epp_payment_approval_status');

        $this->docStatusConfigCodes = config('mappings.epp_payment_document_status');

        $this->requiredInputs = config('payments.general.payment_common_required_inputs.epp_terminal');
    }

    /**
     * Generate Payment Details Json.
     *
     * @param SettingsInterface $settingsRepositoryObj
     * @param MasterInterface $masterRepositoryObj
     * @param array $mappingObject
     * @return string
     */
    public function generatePaymentDetailJson(
        SettingsInterface $settingsRepositoryObj,
        MasterInterface $masterRepositoryObj,
        array $mappingObject
    )
    {
        //Get Master Data ID
        $settingsData = $masterRepositoryObj->getMasterDataByKey(
            array('epp_payment_approval_status', 'epp_payment_document_status'));

        //Get Pending status ID
        $approvalStatus = array_change_key_case($settingsData['epp_payment_approval_status']
            ->pluck('id','title')->toArray());

        $approvedId = $approvalStatus[$this->approvalStatusConfigCodes['approved']];

        //Get Document status ID
        $documentStatus = array_change_key_case($settingsData['epp_payment_document_status']
            ->pluck('id','title')->toArray());

        $processDocumentId = $documentStatus[$this->docStatusConfigCodes['p']];

        //Form Payment Detial Json
        $paymentDetail = [
            'doc_status' => $processDocumentId,
            'tenure' => ($this->params->get('tenure')) ? $this->params->get('tenure') : '',
            'card_type' => ($this->params->get('issuing_bank')) ? $this->params->get('issuing_bank') : '',
            'card_holder_name' => '',
            'card_number' => '',
            'cvv_code' => '',
            'card_expiry_date' => '',
            'approval_code' => ($this->params->get('approval_code')) ? $this->params->get('approval_code') : '',
            'approval_status' => $approvedId,
            'approved_by' => '',
            'approved_date' => date('Y-m-d'),
            'converted_by' => '',
            'converted_date' => ''
        ];

        return $paymentDetail;
    }
}