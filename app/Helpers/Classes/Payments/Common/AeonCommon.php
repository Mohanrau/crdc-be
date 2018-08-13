<?php
namespace App\Helpers\Classes\Payments\Common;

use App\{
    Helpers\Classes\Payments\Payment,
    Models\Payments\AeonTransaction,
    Interfaces\Masters\MasterInterface,
    Interfaces\Settings\SettingsInterface
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class AeonCommon extends Payment
{
    protected
        $approvalStatusConfigCodes,
        $docStatusConfigCodes,
        $aeonTransactionObj;

    /**
     * AeonCommon constructor.
     */
    public function __construct(AeonTransaction $aeonTransaction)
    {
        parent::__construct();

        $this->isManual = false;

        $this->isFormGenerateRequired = false;

        $this->isCreationGeneratePaymentDetail = true;

        $this->approvalStatusConfigCodes = Config::get('mappings.aeon_payment_approval_status');

        $this->docStatusConfigCodes = Config::get('mappings.aeon_payment_document_status');

        $this->aeonTransactionObj = $aeonTransaction;
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
            array('aeon_payment_approval_status', 'aeon_payment_document_status'));

        //Get Pending status ID
        $approvalStatus = array_change_key_case($settingsData['aeon_payment_approval_status']
            ->pluck('id','title')->toArray());

        $pendingCode = $this->approvalStatusConfigCodes['pending'];

        $pendingId = $approvalStatus[$pendingCode];

        //Get Document status ID
        $documentStatus = array_change_key_case($settingsData['aeon_payment_document_status']
            ->pluck('id','title')->toArray());

        $newDocumentCode = $this->docStatusConfigCodes['n'];

        $newDocumentId = $documentStatus[$newDocumentCode];

        //Get Member Detail
        $memberDetail = $mappingObject['members'];

        //Get Input Fields Detail
        $fields = collect($mappingObject['fields']);

        //Form Aeon Payment Detial Json
        $paymentDetail = [
            'ic_no' => $memberDetail->ic_passport_number,
            'application_date' => Carbon::now()->format('Y-m-d'),
            'agent_code' => $fields->where('name', 'agent_code')->first()['value'],
            'agreement_no' => '',
            'application_form_document' => '',
            'ic_document' => '',
            'salary_slip_document' => '',
            'bank_book_document' => '',
            'auto_debit' => '',
            'approved_amount' => '',
            'remarks' => '',
            'pending_remarks' => '',
            'approval_status'  => $pendingId, //application_status
            'approval_date' => '',
            'converted_by' => '',
            'converted_date' => '',
            'doc_status' => $newDocumentId,
        ];

        //Need to Insert or Update into Aeon Transaction
        //Check Application Date & Ic Number
        $aeonTransaction = $this->aeonTransactionObj
            ->where('approval_status_id', $pendingId)
            ->where('country_id', $mappingObject['country']->id)
            ->where('user_id', $memberDetail->user_id)
            ->where('ic_number', $memberDetail->ic_passport_number)
            ->where('application_date', Carbon::now()->format('Y-m-d'))
            ->where('agent_code', $fields->where('name', 'agent_code')->first()['value'])
            ->whereNull('request_file_name')
            ->first();

        if($aeonTransaction){

            $runningNumber = $aeonTransaction->aeon_number;

            $aeonTransaction->update(
                array(
                    'request_amount' => floatval($aeonTransaction->request_amount) + floatval($mappingObject['amount'])
                )
            );

        } else {

            //Get Aeon Payment Running Number
            $runningNumber = $settingsRepositoryObj
                ->getRunningNumber('aeon_payment_id', $mappingObject['country']->id, $mappingObject['location']->id);

            $aeonTransactionData = [
                'country_id' => $mappingObject['country']->id,
                'user_id' => $memberDetail->user_id,
                'aeon_number' => $runningNumber,
                'ic_number' => $memberDetail->ic_passport_number,
                'application_date' => Carbon::now()->format('Y-m-d'),
                'agent_code' => $fields->where('name', 'agent_code')->first()['value'],
                'request_amount' => $mappingObject['amount'],
                'approval_status_id' => $pendingId
            ];

            Auth::user()->createdBy($this->aeonTransactionObj)->create($aeonTransactionData);
        }

        $paymentDetail['aeon_id'] = $runningNumber;

        return $paymentDetail;
    }
}