<?php

namespace App\Console\Commands\Payments;

use App\{
    Interfaces\Masters\MasterInterface,
    Models\Locations\Country,
    Models\Payments\AeonPaymentFtpLog,
    Models\Payments\AeonTransaction,
    Models\Payments\Payment
};
use Carbon\Carbon;
use SSH;
use Illuminate\Console\Command;

class AeonRequest extends Command
{
    private $aeonSupportedCountryCode = 'MY';

    private $aeonSupportedCountryFileMapping = 'malaysia';

    protected
        $masterRepositoryObj,
        $countryObj,
        $aeonPaymentFtpLogObj,
        $aeonTransactionObj,
        $paymentObj,
        $aeonStatusConfigCodes;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:aeon-request';

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
     * @param Country $country
     * @param AeonPaymentFtpLog $aeonPaymentFtpLog
     * @param Payment $payment
     * @param AeonTransaction $aeonTransaction
     * @return void
     */
    public function __construct
    (
        MasterInterface $masterInterface,
        Country $country,
        AeonPaymentFtpLog $aeonPaymentFtpLog,
        AeonTransaction $aeonTransaction,
        Payment $payment
    )
    {
        parent::__construct();

        $this->description = trans('message.console-task-scheduling.aeon-send-request');

        $this->masterRepositoryObj = $masterInterface;

        $this->countryObj = $country;

        $this->aeonPaymentFtpLogObj = $aeonPaymentFtpLog;

        $this->aeonTransactionObj = $aeonTransaction;

        $this->paymentObj = $payment;

        $this->aeonStatusConfigCodes = config('mappings.aeon_payment_approval_status');
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

        //Get Pending Status ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode', 'aeon_payment_approval_status'));

        $aeonStatus = array_change_key_case($settingsData['aeon_payment_approval_status']->pluck('id','title')->toArray());

        $aeonPendingCode = $this->aeonStatusConfigCodes['pending'];

        $aeonPendingId = $aeonStatus[$aeonPendingCode];

        $paymentDataQuery = $this->aeonTransactionObj
            ->where('country_id', $supportedCountryId)
            ->where('application_date', Carbon::now()->format('Y-m-d'))
            ->where('approval_status_id', $aeonPendingId)
            ->whereNull('request_file_name');

        //Get Aeon Config Setup
        $aeonConfig = config('payments.'.$this->aeonSupportedCountryFileMapping.'.aeon');

        //Generate Request and Response File Name
        $ftpCount = $this->aeonPaymentFtpLogObj
            ->where("country_id", $supportedCountryId)
            ->where("created_at", ">=", date('Y-m-d  H:i:s',strtotime(date('Y-m-d').' 00:00:00')))
            ->where("created_at", "<=", date('Y-m-d  H:i:s',strtotime(date('Y-m-d').' 23:59:59')))
            ->count();

        $ftpNumber = $ftpCount + 1;

        $requestFileName = $aeonConfig['aeon_request_file_name_prefix'] .
            date('Ymd') . '-' . sprintf("%02d", $ftpNumber) . $aeonConfig['aeon_request_file_name_suffix'] . '.txt';

        $responseFileName = $aeonConfig['aeon_response_file_name_prefix'] .
            date('Ymd') . '-' . sprintf("%02d", $ftpNumber) . $aeonConfig['aeon_response_file_name_suffix'] . '.txt.gpg';

        //Insert FTP Logs
        $aeonPaymentFtpLogData = [
            'country_id' => $supportedCountryId,
            'request_file_name' => $requestFileName,
            'response_file_name' => $responseFileName,
            'ftp_status' => 0
        ];

        $aeonFtpLog = $this->aeonPaymentFtpLogObj->create($aeonPaymentFtpLogData);

        //Output content
        $strDetail = '';

        $paymentDatas = $paymentDataQuery->get();

        collect($paymentDatas)->map(function ($payment)
            use (&$strDetail, $requestFileName){

                $dataline = str_pad($payment['aeon_number'], 10, ' ');

                $dataline .= str_pad($payment['ic_number'], 20, ' ');

                $dataline .= str_pad(date('Ymd', strtotime($payment['application_date'])), 8, ' ');

                $dataline .= str_pad($payment['agent_code'], 10, ' ');

                $dataline .= "\r\n";

                $strDetail .= $dataline;

                //Update record has make a request to Aeon
                $aeonTransactionDetail = $this->aeonTransactionObj
                    ->find($payment->id);

                $aeonTransactionDetail->update([
                    'request_file_name' => $requestFileName
                ]);
        });

        //Get SSH Config Info
        $aeonSshConfigName = "aeon_payment_".strtolower($this->aeonSupportedCountryCode)."_ftp";

        $remotePath = $aeonConfig['aeon_upload_file_directory'] . $requestFileName;

        //Write txt file to Aeon Server
        SSH::into($aeonSshConfigName)->putString($remotePath, $strDetail);

        //Encrypt File and Remove Original File
        SSH::into($aeonSshConfigName)->run([
            'cd ' . $aeonConfig['aeon_upload_file_directory'],
            'gpg --always-trust -r ' . $aeonConfig['aeon_gpg_secure_key'] . ' -e ' . $requestFileName,
            'rm ' . $requestFileName
        ]);

        $this->info('Send aeon payment request file successfully.!');
    }
}

?>