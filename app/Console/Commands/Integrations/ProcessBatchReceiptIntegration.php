<?php
namespace App\Console\Commands\Integrations;

use App\Interfaces\{
    Integrations\YonyouInterface,
    Sales\SaleInterface
};
use App\Jobs\{
    SendReceiptToYY
};
use Illuminate\Console\Command;

class ProcessBatchReceiptIntegration extends Command
{
    private $yonyouRepositoryObj, 
        $saleRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProcessBatchReceiptIntegration:processBatchReceiptIntegration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send batch receipt to YY';

    /**
     * Create a new command instance.
     *
     * @param YonyouInterface $yonyouInterface
     * @param SaleInterface $saleInterface
     * @return void
     */
    public function __construct
    (
        YonyouInterface $yonyouInterface,
        SaleInterface $saleInterface
    )
    {
        parent::__construct();

        $this->yonyouRepositoryObj = $yonyouInterface;
        $this->saleRepositoryObj = $saleInterface;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->processBatchNonStockistSaleReceiptToYY();
        $this->processBatchStockistSalePaymentToYY();
        $this->processBatchStockistPreOrderRefundToYY();
    }

    /**
     *  process non stockist sale receipt
     * @return mixed
     */
    public function processBatchNonStockistSaleReceiptToYY()
    {
        $sales = $this->saleRepositoryObj
            ->getYonyouIntegrationNonStockistSaleReceipt();

        $jobType = 'SALES_RECEIPT';

        $dataModel = 'sales';

        $mappingModel = 'sales';

        foreach ($sales as $sale) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $sale['id'])) 
            {
                SendReceiptToYY::dispatch($jobType, 
                    $dataModel, $sale['id'], 
                    $mappingModel, $sale['id']);
            }
        }
    }

    /**
     *  process stockist sale payment
     * @return mixed
     */
    public function processBatchStockistSalePaymentToYY()
    {
        $payments = $this->saleRepositoryObj
            ->getYonyouIntegrationStockistSalePayment();
        
        $jobType = 'STOCKIST_PREORDER_DEPOSIT';

        $dataModel = 'payments';

        $mappingModel = 'payments';

        foreach ($payments as $payment) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $payment['id'])) 
            {
                SendReceiptToYY::dispatch($jobType, 
                    $dataModel, $payment['id'], 
                    $mappingModel, $payment['id']);
            }
        }
    }

    /**
     *  process  stockist pre order refund
     * @return mixed
     */
    public function processBatchStockistPreOrderRefundToYY()
    {
        $payments = $this->saleRepositoryObj
            ->getYonyouIntegrationStockistPreOrderRefund();
        
        $jobType = 'STOCKIST_PREORDER_REFUND';

        $dataModel = 'payments';

        $mappingModel = 'payments';

        foreach ($payments as $payment) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $payment['id'])) 
            {
                SendReceiptToYY::dispatch($jobType, 
                    $dataModel, $payment['id'], 
                    $mappingModel, $payment['id']);
            }
        }
    }
}
