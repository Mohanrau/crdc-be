<?php

namespace App\Console\Commands\Stockists;

use App\{
    Interfaces\Masters\MasterInterface,
    Interfaces\Payments\PaymentInterface,
    Models\Invoices\Invoice,
    Models\Payments\Payment,
    Models\Sales\CreditNote,
    Models\Sales\SaleCancellation,
    Models\Stockists\Stockist,
    Models\Stockists\StockistDepositSetting,
    Models\Stockists\StockistSalePayment
};
use Illuminate\Console\Command;
use Carbon\Carbon;

class StockistDailySalePayment extends Command
{
    protected $masterRepositoryObj,
        $paymentRepositoryObj,
        $invoiceObj,
        $paymentObj,
        $creditNoteObj,
        $saleCancellationObj,
        $stockistObj,
        $stockistDepositSettingObj,
        $stockistSalePaymentObj,
        $saleCancellationStatusConfigCodes,
        $saleCancellationModeConfigCodes,
        $saleCancellationTypeConfigCodes;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stockist:stockist-daily-sale-payment';

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
     * @param Invoice $invoice
     * @param Payment $payment
     * @param CreditNote $creditNote
     * @param SaleCancellation $saleCancellation
     * @param Stockist $stockist
     * @param StockistDepositSetting $stockistDepositSetting
     * @param StockistSalePayment $stockistSalePayment
     * @return void
     */
    public function __construct
    (
        MasterInterface $masterInterface,
        PaymentInterface $paymentInterface,
        Invoice $invoice,
        Payment $payment,
        CreditNote $creditNote,
        SaleCancellation $saleCancellation,
        Stockist $stockist,
        StockistDepositSetting $stockistDepositSetting,
        StockistSalePayment $stockistSalePayment
    )
    {
        parent::__construct();

        $this->description = trans('message.console-task-scheduling.stockist-daily-sales-payment');

        $this->masterRepositoryObj = $masterInterface;

        $this->paymentRepositoryObj = $paymentInterface;

        $this->invoiceObj = $invoice;

        $this->paymentObj = $payment;

        $this->creditNoteObj = $creditNote;

        $this->saleCancellationObj = $saleCancellation;

        $this->stockistObj = $stockist;

        $this->stockistDepositSettingObj = $stockistDepositSetting;

        $this->stockistSalePaymentObj = $stockistSalePayment;

        $this->saleCancellationStatusConfigCodes =
            config('mappings.sale_cancellation_status');

        $this->saleCancellationModeConfigCodes =
            config('mappings.sale_cancellation_mode');

        $this->saleCancellationTypeConfigCodes =
            config('mappings.sale_cancellation_type');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Always Run For Yesterday Transaction
        $transactionDate = Carbon::yesterday()->format('Y-m-d');

        //Get Setup Data
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('sale_cancellation_status', 'sale_cancellation_mode', 'sale_cancellation_type'));

        $cancelStatusValues = array_change_key_case(
            $settingsData['sale_cancellation_status']->pluck('id','title')->toArray());

        $cancelModeValues = array_change_key_case(
            $settingsData['sale_cancellation_mode']->pluck('id','title')->toArray());

        $cancelTypeValues = array_change_key_case(
            $settingsData['sale_cancellation_type']->pluck('id','title')->toArray());

        $cancellationCompleteStatus = $this->saleCancellationStatusConfigCodes['completed'];

        $cancellationFullMode = $this->saleCancellationModeConfigCodes['full'];

        $cancellationSameDayType = $this->saleCancellationTypeConfigCodes['same day'];

        $cancellationCompleteStatusId = $cancelStatusValues[$cancellationCompleteStatus];

        $cancellationFullModeId = $cancelModeValues[$cancellationFullMode];

        $cancellationSameDayTypeId = $cancelTypeValues[$cancellationSameDayType];

        //Retrieve Completed and Same Day Cancellation Ids to ignore in stockist invoice selection
        $cancellationSaleIds = $this->creditNoteObj
            ->join('sales_cancellations', function ($join){
                $join->on('credit_notes.mapping_id', '=', 'sales_cancellations.id')
                    ->where('credit_notes.mapping_model', 'sales_cancellations');
            })
            ->where('credit_notes.credit_note_date', $transactionDate)
            ->where('sales_cancellations.cancellation_status_id', $cancellationCompleteStatusId)
            ->where('sales_cancellations.cancellation_mode_id', $cancellationFullModeId)
            ->where('sales_cancellations.cancellation_type_id', $cancellationSameDayTypeId)
            ->select('credit_notes.*')
            ->pluck('sale_id')
            ->toArray();

        //Get All Stockist Record
        $stockists = $this->stockistObj->get();

        collect($stockists)->each(function($stockist)
            use(
                $transactionDate,
                $cancellationSaleIds
            ){

                //Get Stockist Location Id
                $stockistLocationId = $stockist->stockistLocation->id;

                //Retrieve All Stockist Payment Provider
                $stockistSupportedPayments = $this->paymentRepositoryObj
                    ->getSupportedPayments($stockist->country_id, $stockistLocationId);

                $stockistPaymentProviders = [];

                collect($stockistSupportedPayments)->each(function($stockistSupportedPayment)
                    use(&$stockistPaymentProviders){

                        collect($stockistSupportedPayment['payment_mode_provider'])
                            ->where('is_stockist_payment_verification', 1)
                            ->each(function($provider)
                                use(&$stockistPaymentProviders){

                                 $stockistPaymentProviders[] = [
                                    'paymentModeProviderId' => $provider['id'],
                                    'totalAmount' => 0,
                                ];
                            });
                });

                //Retrieve Stockist All Sales Payment
                $salePayments = $this->paymentObj
                    ->join('sales', function ($join) {
                        $join->on('payments.mapping_id', '=', 'sales.id')
                            ->where('payments.mapping_model', 'sales');
                    })
                    ->where('sales.transaction_location_id', $stockistLocationId)
                    ->where("payments.created_at", ">=", date('Y-m-d  H:i:s',strtotime($transactionDate.' 00:00:00')))
                    ->where("payments.created_at", "<=", date('Y-m-d  H:i:s',strtotime($transactionDate.' 23:59:59')))
                    ->whereNotIn("payments.mapping_id", $cancellationSaleIds)
                    ->where('status', 1)
                    ->select('payments.*')
                    ->get();

                $invoicePaymentProviders = collect($salePayments)->groupBy('payment_mode_provider_id');

                $invoicePaymentProvidersTotals = [];

                collect($invoicePaymentProviders)->each(function($paymentData, $providerId)
                    use (&$invoicePaymentProvidersTotals) {

                        $totalAmount = collect($paymentData)->sum('amount');

                        $invoicePaymentProvidersTotals[] = [
                            'paymentModeProviderId' => $providerId,
                            'totalAmount' => $totalAmount,
                        ];
                    });

                $totalArBanlance = 0;

                collect($stockistPaymentProviders)->each(function($stockistPaymentProvider)
                    use (&$totalArBanlance, $stockist, $transactionDate, $invoicePaymentProvidersTotals){

                        $stockistPaymentProviderId = $stockistPaymentProvider['paymentModeProviderId'];

                        $stockistPaymentProviderTotal = $stockistPaymentProvider['totalAmount'];

                        $invoicePaymentTotal = collect($invoicePaymentProvidersTotals)
                            ->where('paymentModeProviderId', $stockistPaymentProviderId)
                            ->sum('totalAmount');
                            
                        $totalAmount = floatval($stockistPaymentProviderTotal) + floatval($invoicePaymentTotal);

                        //Create Stockist Payment Mode Collected Total
                        $stockistSalePaymentData = [
                            'stockist_id' => $stockist->id,
                            'payment_mode_provider_id' => $stockistPaymentProviderId,
                            'transaction_date' => $transactionDate,
                            'amount' => floatval($totalAmount),
                            'paid_amount' => 0,
                            'adjustment_amount' => 0,
                            'outstanding_amount' => floatval($totalAmount)
                        ];

                        $stockistPaymentMode = $this->stockistSalePaymentObj
                            ->create($stockistSalePaymentData);

                        $totalArBanlance += $totalAmount;
                    });

                //Update Stockist AR Balance
                $stockistDepositSetting = $this->stockistDepositSettingObj
                    ->where('stockist_id', $stockist->id)
                    ->first();

                $stockistArbalance = floatval($stockistDepositSetting->ar_balance) + floatval($totalArBanlance);

                $stockistDepositSetting->update([
                    'ar_balance' => $stockistArbalance
                ]);
            });

            $this->info('Stockist daily sales payment update successfully.');
    }
}

?>