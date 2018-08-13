<?php

namespace App\Console;

use App\Console\Commands\{
    Bonuses\CalculateBonus,
    Bonuses\WelcomeBonusDailyPayout,
    Campaigns\CalculateCampaign,
    Dashboard\UpdateMemberSnapshot,
    General\UpdateCurrentCw,
    Stockists\CalculateStockistCommission,
    Stockists\StockistDailySalePayment,
    Integrations\ProcessBatchCollectionIntegration,
    Integrations\ProcessBatchConsignmentIntegration,
    Integrations\ProcessBatchEwalletIntegration,
    Integrations\ProcessBatchReceiptIntegration,
    Integrations\ProcessBatchRemittanceIntegration,
    Integrations\ProcessBatchSalesCancellationIntegration,
    Integrations\ProcessBatchSalesIntegration,
    Integrations\ProcessBatchStockistReceiptIntegration,
    Integrations\ProcessBatchStockistSalesIntegration,
    Members\UpdateMemberExpiredStatus,
    Payments\AeonRequest,
    Payments\AeonRespond,
    Payments\MposTransactionRetrieve,
    Payments\PaymentTransactionQuery,
    Tree\DepthLevelUpdate
};

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        UpdateCurrentCw::class,
        CalculateStockistCommission::class,
        StockistDailySalePayment::class,
        ProcessBatchCollectionIntegration::class,
        ProcessBatchConsignmentIntegration::class,
        ProcessBatchEwalletIntegration::class,
        ProcessBatchReceiptIntegration::class,
        // ProcessBatchRemittanceIntegration::class,
        ProcessBatchSalesCancellationIntegration::class,
        ProcessBatchSalesIntegration::class,
        ProcessBatchStockistReceiptIntegration::class,
        ProcessBatchStockistSalesIntegration::class,
        UpdateMemberExpiredStatus::class,
        AeonRequest::class,
        AeonRespond::class,
        CalculateBonus::class,
        MposTransactionRetrieve::class,
        PaymentTransactionQuery::class,
        DepthLevelUpdate::class,
        WelcomeBonusDailyPayout::class,
        UpdateMemberSnapshot::class,
        CalculateCampaign::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //Update Stockist Commision
        $schedule->command('stockist:calculate-stockist-commission')
            ->everyFifteenMinutes();

        //Update Current CW into Setting Table
        $schedule->command('general:update-current-cw')
            ->daily();

        //Update member expiry status
        $schedule->command('member:update-member-expiry-status')
            ->daily();

        //Insert Stockist Daily Total Sale Payment
        $schedule->command('stockist:stockist-daily-sale-payment')
            ->daily();

        //Retrieve MPOS Payment Transaction
        $schedule->command('payment:mpos-transaction-retrieve')
            ->daily();

        //Payment Transaction Query Validation
        $schedule->command('payment:transaction-query')
            ->everyFiveMinutes();

        //Aeon Payment Send Request file to check aeon approval status
        $schedule->command('payment:aeon-request')
            ->cron('15 11 * * *');

        $schedule->command('payment:aeon-request')
            ->cron('15 14 * * *');

        $schedule->command('payment:aeon-request')
            ->cron('15 17 * * *');

        $schedule->command('payment:aeon-request')
            ->cron('15 21 * * *');

        //Aeon Payment Respond Receive to map aeon approval status
        $schedule->command('payment:aeon-respond')
            ->cron('15 12 * * *');

        $schedule->command('payment:aeon-respond')
            ->cron('15 15 * * *');

        $schedule->command('payment:aeon-respond')
            ->cron('15 18 * * *');

        $schedule->command('payment:aeon-respond')
            ->cron('15 22 * * *');

        // Yonyou integration
        if (strtoupper(config('setting.yy-integration')) == 'Y') {
            $schedule->command('ProcessBatchCollectionIntegration:processBatchCollectionIntegration')
                ->everyFifteenMinutes();

            $schedule->command('ProcessBatchConsignmentIntegration:processBatchConsignmentIntegration')
                ->everyFifteenMinutes();

            $schedule->command('ProcessBatchEwalletIntegration:processBatchEwalletIntegration')
                ->everyFifteenMinutes();

            $schedule->command('ProcessBatchReceiptIntegration:processBatchReceiptIntegration')
                ->everyFifteenMinutes();

            //TODO: Ignore for now, account will do manually for the moment
            // $schedule->command('ProcessBatchRemittanceIntegration:processBatchRemittanceIntegration')
            //     ->everyFifteenMinutes();

            $schedule->command('ProcessBatchSalesCancellationIntegration:processBatchSalesCancellationIntegration')
                ->everyFifteenMinutes();

            $schedule->command('ProcessBatchSalesIntegration:processBatchSalesIntegration')
                ->everyFifteenMinutes();

            $schedule->command('ProcessBatchStockistReceiptIntegration:processBatchStockistReceiptIntegration')
                ->everyFifteenMinutes();

            $schedule->command('ProcessBatchStockistSalesIntegration:processBatchStockistSalesIntegration')
                ->everyFifteenMinutes();
        }        

        //@todo for bonus calculation, we need to know the running interval
        //$schedule->command('bonus.calculate')->cron('0 * * * *'); // every hour
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
