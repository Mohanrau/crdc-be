<?php
namespace App\Console\Commands\Payments;

use App\Interfaces\{
    Masters\MasterInterface,
    Payments\PaymentInterface,
    Sales\SaleInterface,
    Settings\SettingsInterface
};
use App\Models\{
    Stockists\ConsignmentDepositRefund,
    EWallets\EWallet,
    Locations\LocationTypes,
    Payments\PaymentModeProvider,
    Payments\PaymentModeSetting,
    Payments\Payment
};
use Illuminate\Console\Command;

class PaymentTransactionQuery extends Command
{
    protected
        $masterRepositoryObj,
        $paymentRepositoryObj,
        $saleRepositoryObj,
        $settingRepositoryObj,
        $consignmentDepositRefundObj,
        $eWalletObj,
        $locationTypesObj,
        $paymentModeProviderObj,
        $paymentModeSettingObj,
        $paymentObj,
        $paymentModeConfigCodes,
        $locationTypeConfigCodes;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:transaction-query';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @param MasterInterface $masterInterface
     * @param PaymentInterface $paymentInterface
     * @param SaleInterface $saleInterface
     * @param SettingsInterface $settingsInterface
     * @param ConsignmentDepositRefund $consignmentDepositRefund
     * @param EWallet $eWallet
     * @param LocationTypes $locationTypes
     * @param PaymentModeProvider $paymentModeProvider
     * @param PaymentModeSetting $paymentModeSetting
     * @param Payment $payment
     * @return void
     */
    public function __construct
    (
        MasterInterface $masterInterface,
        PaymentInterface $paymentInterface,
        SaleInterface $saleInterface,
        SettingsInterface $settingsInterface,
        ConsignmentDepositRefund $consignmentDepositRefund,
        EWallet $eWallet,
        LocationTypes $locationTypes,
        PaymentModeProvider $paymentModeProvider,
        PaymentModeSetting $paymentModeSetting,
        Payment $payment
    )
    {
        parent::__construct();

        $this->description = trans('message.console-task-scheduling.payment-transaction-query');

        $this->masterRepositoryObj = $masterInterface;

        $this->paymentRepositoryObj = $paymentInterface;

        $this->saleRepositoryObj = $saleInterface;

        $this->settingRepositoryObj = $settingsInterface;

        $this->consignmentDepositRefundObj = $consignmentDepositRefund;

        $this->eWalletObj = $eWallet;

        $this->locationTypesObj = $locationTypes;

        $this->paymentModeProviderObj = $paymentModeProvider;

        $this->paymentModeSettingObj = $paymentModeSetting;

        $this->paymentObj = $payment;

        $this->paymentModeConfigCodes = config('mappings.payment_mode');

        $this->locationTypeConfigCodes = config('mappings.locations_types');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Get Online Payment Mode ID
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('payment_mode'));

        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $onlinePaymentId = $paymentMode[$this->paymentModeConfigCodes['online payment gateway']];

        //Payment Transaction Verify Time
        $currentCwSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('payment_transaction_verify_buffer_time'));

        $verifyBufferTime = $currentCwSettings['payment_transaction_verify_buffer_time'][0]->value;

        $transactionDateTime = date('Y-m-d H:i:s', strtotime('- '. $verifyBufferTime .' minute'));

        //Get Online Payment Provider Id
        $onlinePaymentProviderListIds = $this->paymentModeProviderObj
            ->where('master_data_id', $onlinePaymentId)
            ->pluck('id');

        //Get Payment Record
        $payments = $this->paymentObj
            ->whereIn('payment_mode_provider_id', $onlinePaymentProviderListIds)
            ->where("created_at", "<=", $transactionDateTime)
            ->where('status', 2)
            ->orderBy('created_at', 'asc')
            ->limit(10)
            ->get();

        $paymentListIds = $payments->pluck('id');

        //Lock the payment to prevent next task scheduler retrieve same record
        $this->paymentObj
            ->whereIn('payment_mode_provider_id', $onlinePaymentProviderListIds)
            ->where('status', 2)
            ->whereIn('id', $paymentListIds)
            ->update([
                'status' => 3
            ]);

        collect($payments)->each(function ($payment){

            if($payment->mapping_model == 'sales'){

                $saleDetail = $this->saleRepositoryObj->find($payment->mapping_id);

                $paymentCountry = $saleDetail->country()->first();

                $locationTypeId = $saleDetail->transactionLocation()->first()->location_types_id;

            } else if ($payment->mapping_model == 'consignments_deposits_refunds') {

                $consignmentDetail = $this->consignmentDepositRefundObj->find($payment->mapping_id);

                $paymentCountry = $consignmentDetail->stockist->country()->first();

                $locationTypeId = $consignmentDetail->stockist->stockistLocation()->first()->location_types_id;

            } else {

                //For user_ewallets Model
                $eWalletDetail = $this->eWalletObj->find($payment->mapping_id);

                $paymentCountry = $eWalletDetail->user->member->country()->first();

                $userCountryEntityId = $eWalletDetail->user->member->country->entity->id;

                $locationTypeId = $this->locationTypesObj
                    ->where('code', $this->locationTypeConfigCodes['online'])
                    ->first()->id;
            }

            $paymentProvider = $this->paymentModeSettingObj
                ->where('payment_mode_provider_id', $payment->paymentModeProvider->id)
                ->where('country_id', $paymentCountry->id)
                ->where('location_type_id', $locationTypeId)
                ->first();

            $paymentObject = $this->paymentRepositoryObj
                ->getPaymentObject($paymentCountry->code_iso_2, $paymentProvider->configuration_file_name);

            if($paymentObject->isManual()){ //ensure this is not a manual payment
                return true;
            }

            $paymentRecord = $this->paymentObj->find($payment->id);

            $results = $paymentObject->requeryPayment($paymentRecord);

            if(isset($results['success'])){
                $payment->status = $results['success'];
            } else {
                $payment->status = (isset($results['data']['payStatus']))
                    ? $results['data']['payStatus'] : 0;
            }

            if(!is_array($results['data'])){
                $results['data'] = json_decode($results['data']);
            }

            $paymentDetail = json_decode($payment->payment_detail, true);

            $paymentDetail['payment_response'] = $results['data'];

            $payment->payment_detail = json_encode($paymentDetail);

            $payment->save();

            if($payment->status){

                if($payment->mapping_model == 'sales'){

                    $sale = $this->saleRepositoryObj->find($payment->mapping_id);

                    $this->actualSalesGenerated($sale, true);

                } else if($payment->mapping_model == 'consignments_deposits_refunds'){

                    $consignmentDeposit = $this->consignmentDepositRefundObj->find($payment->mapping_id);

                    $this->consignmentDepositGenerated($consignmentDeposit);

                } else {

                    //For user_ewallets Model
                    $eWalletDetail = $this->eWalletObj->find($payment->mapping_id);

                    $this->eWalletTopUp($eWalletDetail, $payment);
                }
            }
        });

        $this->info('Payment transaction query run successfully!');
    }
}

?>