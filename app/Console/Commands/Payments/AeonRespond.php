<?php

namespace App\Console\Commands\Payments;

use App\{
    Interfaces\Masters\MasterInterface,
    Interfaces\Payments\PaymentInterface,
    Interfaces\Sales\SaleInterface,
    Models\Locations\Country,
    Models\Payments\AeonPaymentFtpLog,
    Models\Payments\AeonTransaction,
    Models\Payments\PaymentModeProvider,
    Models\Payments\Payment
};
use SSH;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AeonRespond extends Command
{
    private $aeonSupportedCountryCode = 'MY';
    private $aeonSupportedCountryFileMapping = 'malaysia';

    protected
        $masterRepositoryObj,
        $paymentRepositoryObj,
        $saleRepositoryObj,
        $countryObj,
        $aeonPaymentFtpLogObj,
        $aeonTransactionObj,
        $paymentModeProviderObj,
        $paymentObj,
        $aeonStatusConfigCodes,
        $docStatusConfigCodes,
        $countriesMapping,
        $paymentModeConfigCodes;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:aeon-respond';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "";

    /**
     * Create a new command instance.
     *
     * @param MasterInterface $masterInterface
     * @param PaymentInterface $paymentInterface
     * @param SaleInterface $saleInterface
     * @param Country $country
     * @param AeonPaymentFtpLog $aeonPaymentFtpLog
     * @param AeonTransaction $aeonTransaction
     * @param PaymentModeProvider $paymentModeProvider
     * @param Payment $payment
     * @return void
     */
    public function __construct
    (
        MasterInterface $masterInterface,
        PaymentInterface $paymentInterface,
        SaleInterface $saleInterface,
        Country $country,
        AeonPaymentFtpLog $aeonPaymentFtpLog,
        AeonTransaction $aeonTransaction,
        PaymentModeProvider $paymentModeProvider,
        Payment $payment
    )
    {
        parent::__construct();

        $this->description = trans('message.console-task-scheduling.aeon-respond-receive');

        $this->masterRepositoryObj = $masterInterface;

        $this->paymentRepositoryObj = $paymentInterface;

        $this->saleRepositoryObj = $saleInterface;

        $this->countryObj = $country;

        $this->aeonPaymentFtpLogObj = $aeonPaymentFtpLog;

        $this->aeonTransactionObj = $aeonTransaction;

        $this->paymentModeProviderObj = $paymentModeProvider;

        $this->paymentObj = $payment;

        $this->aeonStatusConfigCodes = config('mappings.aeon_payment_approval_status');

        $this->docStatusConfigCodes = config('mappings.aeon_payment_document_status');

        $this->countriesMapping = config('payments.general.countries_mapping');

        $this->paymentModeConfigCodes = config('mappings.payment_mode');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Get Malaysia County ID (Aeon payment only used in Malaysia)
        $masCountryDetail = $this->countryObj
            ->where('code_iso_2', $this->aeonSupportedCountryCode)
            ->first();

        $supportedCountryId = $masCountryDetail->id;

        //Get Aeon Payment Status ID and Aeon Payment Mode ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('aeon_payment_approval_status', 'payment_mode', 'aeon_payment_document_status'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $aeonPaymentIds = $this->paymentModeProviderObj
            ->where('master_data_id', $paymentMode[$this->paymentModeConfigCodes['aeon']])
            ->pluck('id');

        $aeonStatus = array_change_key_case($settingsData['aeon_payment_approval_status']->pluck('id','title')->toArray());

        //Pending Status
        $aeonPendingCode = $this->aeonStatusConfigCodes['pending'];

        $aeonPendingId = $aeonStatus[$aeonPendingCode];

        //Approved Status
        $aeonApprovedCode = $this->aeonStatusConfigCodes['approved'];

        $aeonApprovedId = $aeonStatus[$aeonApprovedCode];

        //Declined Status
        $aeonDeclinedCode = $this->aeonStatusConfigCodes['declined'];

        $aeonDeclinedId = $aeonStatus[$aeonDeclinedCode];

        //Cancel Status
        $aeonCancelCode = $this->aeonStatusConfigCodes['cancel'];

        $aeonCancelId = $aeonStatus[$aeonCancelCode];

        //Get Document status ID
        $documentStatus = array_change_key_case($settingsData['aeon_payment_document_status']
            ->pluck('id','title')->toArray());

        $newDocumentId = $documentStatus[$this->docStatusConfigCodes['n']];

        $processDocumentId = $documentStatus[$this->docStatusConfigCodes['p']];

        $voidDocumentId = $documentStatus[$this->docStatusConfigCodes['v']];

        //Retrieve Pending Response Record
        $pendingFtpResponseLogs = $this->aeonPaymentFtpLogObj
            ->where('country_id', $supportedCountryId)
            ->where('ftp_status', 0)
            ->get();

        collect($pendingFtpResponseLogs)->each(function($ftpLog)
            use ($aeonPaymentIds, $aeonPendingId, $aeonApprovedId, $aeonDeclinedId, $aeonCancelId,
                $newDocumentId, $processDocumentId, $voidDocumentId){

                $gpgRequestFileName = $ftpLog->request_file_name;

                $gpgResponseFileName = $ftpLog->response_file_name;

                $explodeFileName = explode('.gpg', $gpgResponseFileName);

                $txtResponseFileName = $explodeFileName[0];

                //Get Country Details And Get Aeon Config Detail
                $countryDetail = $this->countryObj->find($ftpLog->country_id);

                $aeonSshConfigName = "aeon_payment_".strtolower($countryDetail->code_iso_2)."_ftp";

                $aeonConfig = config('payments.'.$this->aeonSupportedCountryFileMapping.'.aeon');

                //Check File Exist
                $gpgFileExists = SSH::into($aeonSshConfigName)
                    ->exists($aeonConfig['aeon_download_file_directory'] . $gpgResponseFileName);

                if($gpgFileExists){

                    //Decrypt gpg file to txt file
                    SSH::into($aeonSshConfigName)->run([
                        'cd ' . $aeonConfig['aeon_download_file_directory'],
                        'gpg --batch --passphrase-fd 0 < ' . $aeonConfig['aeon_gpg_passphrase_path'] .
                            ' -o ' . $txtResponseFileName . ' -d '. $gpgResponseFileName
                    ]);

                    $textFileExists = SSH::into($aeonSshConfigName)
                        ->exists($aeonConfig['aeon_download_file_directory'] . $txtResponseFileName);

                    if($textFileExists){

                        //Update Aeon Ftp Logs
                        $ftpLogDetail = $this->aeonPaymentFtpLogObj->find($ftpLog->id);

                        $ftpLogDetail->update(['ftp_status' => 1]);

                        //Get Response File Content
                        $responseContents = SSH::into($aeonSshConfigName)
                            ->getString($aeonConfig['aeon_download_file_directory'] . $txtResponseFileName);

                        $responseDatas = preg_split('/\r\n|\r|\n/', $responseContents);

                        //Get Aeon Payment Records
                        $aeonPaymentDatas = $this->aeonTransactionObj
                            ->where('approval_status_id', $aeonPendingId)
                            ->where('request_file_name', $gpgRequestFileName)
                            ->get();

                        collect($responseDatas)->each(function($responseData)
                            use ($aeonPaymentDatas, $aeonPaymentIds, $aeonPendingId, $aeonApprovedId,
                                $aeonDeclinedId, $aeonCancelId, $newDocumentId, $processDocumentId, $voidDocumentId){

                                    $aeonId = trim(substr($responseData,0,10));

                                    $agentCode = trim(substr($responseData,10,10));

                                    $applicationStatus = trim(substr($responseData,31,2));

                                    $aeonTransaction = collect($aeonPaymentDatas)
                                        ->where('aeon_number', $aeonId)
                                        ->where('agent_code', $agentCode)
                                        ->first();

                                    if($aeonTransaction){

                                        $requestFileName = $aeonTransaction->request_file_name;

                                        //Match approval status id with master data id
                                        if($applicationStatus == '00'){

                                            $requestFileName = '';
                                            $approvalStatus = $aeonPendingId;
                                            $docStatus = $newDocumentId;
                                            $payStatus = 2;

                                        } else if ($applicationStatus == '01'){

                                            $approvalStatus = $aeonApprovedId;
                                            $docStatus = $processDocumentId;
                                            $payStatus = 1;

                                        } else if ($applicationStatus == '02'){

                                            $approvalStatus = $aeonDeclinedId;
                                            $docStatus = $voidDocumentId;
                                            $payStatus = 0;

                                        } else if ($applicationStatus == '03'){

                                            $approvalStatus = $aeonCancelId;
                                            $docStatus = $voidDocumentId;
                                            $payStatus = 0;

                                        } else {

                                            $approvalStatus = '';
                                            $docStatus = $voidDocumentId;
                                            $payStatus = 0;
                                        }

                                        //update aeon transaction
                                        $updateAeonTransactionData = [
                                            'agreement_no' => trim(substr($responseData,33,12)),
                                            'approved_amount' => trim(substr($responseData,45,11)),
                                            'application_form_document' => trim(substr($responseData,20,3)),
                                            'ic_document' => trim(substr($responseData,23,3)),
                                            'salary_slip_document' => trim(substr($responseData,26,1)),
                                            'bank_book_document' => trim(substr($responseData,27,3)),
                                            'auto_debit' => trim(substr($responseData,30,1)),
                                            'remarks' => trim(substr($responseData,56,244)),
                                            'pending_remarks' => trim(substr($responseData,300,194)),
                                            'approval_status_id' => $approvalStatus,
                                            'approval_date' => date('Y-m-d'),
                                            'request_file_name' => $requestFileName,
                                        ];

                                        $aeonTransactionDetail = $this->aeonTransactionObj
                                            ->find($aeonTransaction->id);

                                        $aeonTransactionDetail->update($updateAeonTransactionData);

                                        $approvedAmount = $updateAeonTransactionData['approved_amount'];

                                        //get aeon payment record
                                        $aeonSalePayments = $this->paymentObj
                                            ->whereIn('payment_mode_provider_id', $aeonPaymentIds)
                                            ->where('status', 2)
                                            ->where('payment_detail', 'like','%"aeon_id":"' . $aeonId . '"%')
                                            ->where('payment_detail', 'like','%"agent_code":"'.$agentCode.'"%')
                                            ->get();

                                        //update aeon payment record
                                        collect($aeonSalePayments)->each(function($aeonSalePayment)
                                            use ($responseData, &$approvedAmount, $approvalStatus, $applicationStatus, $docStatus, $payStatus){

                                                $aeonPaidAmount = 0;

                                                if($applicationStatus == '01'){

                                                    if(floatval($aeonSalePayment->amount) > floatval($approvedAmount)){

                                                        $aeonPaidAmount = $approvedAmount;

                                                        $approvedAmount = 0;

                                                    } else {

                                                        $aeonPaidAmount = $aeonSalePayment->amount;

                                                        $approvedAmount = floatval($approvedAmount) -
                                                            floatval($aeonPaidAmount);
                                                    }
                                                }

                                                //update aeon payment record
                                                $paymentDetail = json_decode($aeonSalePayment->payment_detail, true);

                                                $aeonPaymentDetail = $paymentDetail['payment_response'];

                                                if(empty($aeonPaymentDetail['agreement_no'])){

                                                    $aeonPaymentDetail['doc_status'] = $docStatus;

                                                    $aeonPaymentDetail['approved_amount'] = $aeonPaidAmount;

                                                    $aeonPaymentDetail['application_form_document'] =
                                                        trim(substr($responseData,20,3));

                                                    $aeonPaymentDetail['ic_document'] =
                                                        trim(substr($responseData,23,3));

                                                    $aeonPaymentDetail['salary_slip_document'] =
                                                        trim(substr($responseData,26,1));

                                                    $aeonPaymentDetail['bank_book_document'] =
                                                        trim(substr($responseData,27,3));

                                                    $aeonPaymentDetail['auto_debit'] =
                                                        trim(substr($responseData,30,1));

                                                    $aeonPaymentDetail['agreement_no'] =
                                                        trim(substr($responseData,33,12));

                                                    $aeonPaymentDetail['remarks'] =
                                                        trim(substr($responseData,56,244));

                                                    $aeonPaymentDetail['pending_remarks'] =
                                                        trim(substr($responseData,300,194));

                                                    $aeonPaymentDetail['approval_status'] = $approvalStatus;

                                                    $aeonPaymentDetail['approval_date'] = date('Y-m-d');

                                                    //update aeon payment response
                                                    $aeonPaymentRecord = $this->paymentObj
                                                        ->find($aeonSalePayment->id);

                                                    $paymentDetail['payment_response'] = $aeonPaymentDetail;

                                                    $updatedAeonPaymentDetail = json_encode($paymentDetail);

                                                    $aeonPaymentRecord->update([
                                                        'amount' => $aeonPaymentDetail['approved_amount'],
                                                        'status' => $payStatus,
                                                        'payment_detail' => $updatedAeonPaymentDetail,
                                                        'created_at' => Carbon::now()->format('Y-m-d H:i:s')
                                                    ]);

                                                    if($payStatus == 1 && $aeonPaymentRecord->mapping_model == "sales"){

                                                        $sale = $this->saleRepositoryObj->find($aeonPaymentRecord->mapping_id);

                                                        $this->paymentRepositoryObj->actualSalesGenerated($sale, true);
                                                    }
                                                }
                                            });
                                    }
                                });

                        //Update aeon Status to reject if no record was return
                        $aeonPaymentRejectedDatas = $this->aeonTransactionObj
                            ->where('approval_status_id', $aeonPendingId)
                            ->where('request_file_name', $gpgRequestFileName)
                            ->get();

                        collect($aeonPaymentRejectedDatas)->each(function($aeonPaymentRejectedData)
                            use ($aeonDeclinedId, $voidDocumentId, $aeonPaymentIds){

                                //update aeon transaction
                                $updateRejectAeonTransactionData = [
                                    'approval_status_id' => $aeonDeclinedId,
                                    'approval_date' => date('Y-m-d')
                                ];

                                $aeonPaymentRejectedTransaction = $this->aeonTransactionObj
                                    ->find($aeonPaymentRejectedData->id);

                                $aeonPaymentRejectedTransaction->update($updateRejectAeonTransactionData);

                                //get aeon payment record
                                $aeonRejectedSalePayments = $this->paymentObj
                                    ->whereIn('payment_mode_provider_id', $aeonPaymentIds)
                                    ->where('status', 2)
                                    ->where('payment_detail', 'like','%"aeon_id":"' . $aeonPaymentRejectedTransaction->aeon_number . '"%')
                                    ->where('payment_detail', 'like','%"agent_code":"'.$aeonPaymentRejectedTransaction->agent_code.'"%')
                                    ->get();

                                collect($aeonRejectedSalePayments)->each(function($aeonRejectedSalePayment)
                                    use ($aeonDeclinedId, $voidDocumentId){

                                        //update aeon payment record
                                        $paymentDetail = json_decode($aeonRejectedSalePayment->payment_detail, true);

                                        $aeonRejectedPaymentDetail = $paymentDetail['payment_response'];

                                        if(empty($aeonRejectedPaymentDetail['agreement_no'])){

                                            $aeonRejectedPaymentDetail['doc_status'] = $voidDocumentId;

                                            $aeonRejectedPaymentDetail['approved_amount'] = 0;

                                            $aeonRejectedPaymentDetail['approval_status'] = $aeonDeclinedId;

                                            $aeonRejectedPaymentDetail['approval_date'] = date('Y-m-d');

                                            //update aeon payment response
                                            $aeonRejectedPaymentRecord = $this->paymentObj
                                                ->find($aeonRejectedSalePayment->id);

                                            $paymentDetail['payment_response'] = $aeonRejectedPaymentDetail;

                                            $updatedRejectedAeonPaymentDetail = json_encode($paymentDetail);

                                            $aeonRejectedPaymentRecord->update([
                                                'amount' => $aeonRejectedPaymentDetail['approved_amount'],
                                                'status' => 0,
                                                'payment_detail' => $updatedRejectedAeonPaymentDetail,
                                                'created_at' => Carbon::now()->format('Y-m-d H:i:s')
                                            ]);
                                        }
                                    });
                            });

                        //Remove Request File
                        SSH::into($aeonSshConfigName)->run([
                            'cd ' . $aeonConfig['aeon_upload_file_directory'],
                            'rm ' . $gpgRequestFileName . '.gpg'
                        ]);

                        //Remove Response File
                        SSH::into($aeonSshConfigName)->run([
                            'cd ' . $aeonConfig['aeon_download_file_directory'],
                            'rm ' . $gpgResponseFileName,
                            'rm ' . $txtResponseFileName
                        ]);
                    }
                }
        });

        $this->info('Aeon payment status update successfully!');
    }
}

?>