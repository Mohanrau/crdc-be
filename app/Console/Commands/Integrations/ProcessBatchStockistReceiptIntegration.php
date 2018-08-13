<?php
namespace App\Console\Commands\Integrations;

use App\Interfaces\{
    Integrations\YonyouInterface,
    Stockists\StockistInterface
};
use App\Jobs\{
    SendReceiptToYY
};
use Illuminate\Console\Command;

class ProcessBatchStockistReceiptIntegration extends Command
{
    private $yonyouRepositoryObj, 
        $stockistRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProcessBatchStockistReceiptIntegration:processBatchStockistReceiptIntegration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send batch stockist receipt to YY';

    /**
     * Create a new command instance.
     *
     * @param YonyouInterface $yonyouInterface
     * @param StockistInterface $stockistInterface
     * @return void
     */
    public function __construct
    (
        YonyouInterface $yonyouInterface,
        StockistInterface $stockistInterface
    )
    {
        parent::__construct();

        $this->yonyouRepositoryObj = $yonyouInterface;
        $this->stockistRepositoryObj = $stockistInterface;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->processBatchStockistPaymentToYY();
        $this->processBatchStockistPaymentAdjustmentToYY();
    }

    /**
     *  process stockist payment
     * @return mixed
     */
    public function processBatchStockistPaymentToYY()
    {
        $stockistSalePaymentTransactions = $this->stockistRepositoryObj
            ->getYonyouIntegrationStockistPayment();

        $jobType = 'STOCKIST_RECEIPT';

        $dataModel = 'stockists_sales_payments_transactions';

        $mappingModel = 'stockists_sales_payments_transactions';

        foreach ($stockistSalePaymentTransactions as $stockistSalePaymentTransaction) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $stockistSalePaymentTransaction['id'])) 
            {
                SendReceiptToYY::dispatch($jobType, 
                    $dataModel, $stockistSalePaymentTransaction['id'], 
                    $mappingModel, $stockistSalePaymentTransaction['id']);
            }
        }
    }

    /**
     *  process stockist payment adjustment
     * @return mixed
     */
    public function processBatchStockistPaymentAdjustmentToYY()
    {
        $stockistSalePaymentTransactions = $this->stockistRepositoryObj
            ->getYonyouIntegrationStockistPaymentAdjustment();

        $jobType = 'STOCKIST_RECEIPT_ADJ';

        $dataModel = 'stockists_sales_payments_transactions';

        $mappingModel = 'stockists_sales_payments_transactions';

        foreach ($stockistSalePaymentTransactions as $stockistSalePaymentTransaction) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $stockistSalePaymentTransaction['id'])) 
            {
                SendReceiptToYY::dispatch($jobType, 
                    $dataModel, $stockistSalePaymentTransaction['id'], 
                    $mappingModel, $stockistSalePaymentTransaction['id']);
            }
        }
    }
}
