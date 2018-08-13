<?php
namespace App\Helpers\Classes\Payments\Common;

use App\{
    Helpers\Classes\Payments\Payment,
    Interfaces\Masters\MasterInterface,
    Interfaces\Settings\SettingsInterface
};

class EPPMotoCommon extends Payment
{
    protected $approvalStatusConfigCodes, $docStatusConfigCodes;

    /**
     * EPPMotoCommon constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->isManual = false;

        $this->isFormGenerateRequired = false;

        $this->isCreationGeneratePaymentDetail = true;

        $this->approvalStatusConfigCodes = config('mappings.epp_payment_approval_status');

        $this->docStatusConfigCodes = config('mappings.epp_payment_document_status');

        $this->requiredInputs = config('payments.general.payment_common_required_inputs.epp_moto');
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

        $pendingId = $approvalStatus[$this->approvalStatusConfigCodes['pending']];

        //Get Document status ID
        $documentStatus = array_change_key_case($settingsData['epp_payment_document_status']
            ->pluck('id','title')->toArray());

        $newDocumentId = $documentStatus[$this->docStatusConfigCodes['n']];

        $expiryDateMonth = ($this->params->get('expiry_date_month')) ? $this->params->get('expiry_date_month') : '';

        $expiryDateYear = ($this->params->get('expiry_date_year')) ? $this->params->get('expiry_date_year') : '';

        //Form Payment Detial Json
        $paymentDetail = [
            'doc_status' => $newDocumentId,
            'tenure' => ($this->params->get('tenure')) ? $this->params->get('tenure') : '',
            'card_type' => ($this->params->get('issuing_bank')) ? $this->params->get('issuing_bank') : '',
            'card_holder_name' => ($this->params->get('cardholder_name')) ? $this->params->get('cardholder_name') : '',
            'card_number' => ($this->params->get('card_number')) ? $this->params->get('card_number') : '',
            'cvv_code' => ($this->params->get('cvv_code')) ? $this->params->get('cvv_code') : '',
            'card_expiry_date' => $expiryDateMonth . '-' . $expiryDateYear,
            'approval_code' => '',
            'approval_status' => $pendingId,
            'approved_by' => '',
            'approved_date' => '',
            'converted_by' => '',
            'converted_date' => ''
        ];

        return $paymentDetail;
    }
}