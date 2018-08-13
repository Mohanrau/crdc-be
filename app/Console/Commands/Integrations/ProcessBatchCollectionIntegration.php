<?php
namespace App\Console\Commands\Integrations;

use App\Interfaces\{
    Integrations\YonyouInterface,
    Sales\SaleInterface,
    Stockists\StockistInterface
};
use App\Jobs\{
    SendCollectionToYY
};
use Illuminate\Console\Command;

class ProcessBatchCollectionIntegration extends Command
{
    private $yonyouRepositoryObj,
        $saleRepositoryObj, 
        $stockistRepositoryObj;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProcessBatchCollectionIntegration:processBatchCollectionIntegration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send collection to yy - consignment deposit/refund';

    /**
     * ProcessBatchCollectionIntegration constructor.
     *
     * @param YonyouInterface $yonyouInterface
     * @param SaleInterface $saleInterface
     * @param StockistInterface $stockistInterface
     */
    public function __construct
    (
        YonyouInterface $yonyouInterface,
        SaleInterface $saleInterface,
        StockistInterface $stockistInterface
    )
    {
        parent::__construct();
        $this->yonyouRepositoryObj = $yonyouInterface;
        $this->saleRepositoryObj = $saleInterface;
        $this->stockistRepositoryObj = $stockistInterface;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->processBatchConsignmentDepositAndRefundToYY();
        $this->processBatchConsignmentDepositRejectToYY();
        $this->processBatchNonStockistSalePaymentToYY();
        $this->processBatchNonStockistPreOrderRefundToYY();
    }

    /**
     *  process consignment deposit and consignment refund
     * @return mixed
     */
    public function processBatchConsignmentDepositAndRefundToYY()
    {
        $consignmentDepositRefunds = $this->stockistRepositoryObj
            ->getYonyouIntegrationConsignmentDepositAndRefund();

        $jobType = 'CONSIGNMENT_DEPOSIT_AND_REFUND';

        $dataModel = 'consignments_deposits_refunds';
        
        $mappingModel = 'consignments_deposits_refunds';
        
        foreach ($consignmentDepositRefunds as $consignmentDepositRefund)
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $consignmentDepositRefund['id'])) 
            {
                SendCollectionToYY::dispatch($jobType, 
                    $dataModel, $consignmentDepositRefund['id'], 
                    $mappingModel, $consignmentDepositRefund['id']);
            }
        }
    }

    /**
     *  process rejected consignment deposit
     * @return mixed
     */
    public function processBatchConsignmentDepositRejectToYY()
    {
        $consignmentDepositRefunds = $this->stockistRepositoryObj
            ->getYonyouIntegrationConsignmentDepositReject();

        $jobType = 'CONSIGNMENT_DEPOSIT_REJECT';

        $dataModel = 'consignments_deposits_refunds';

        $mappingModel = 'consignments_deposits_refunds';
        
        foreach ($consignmentDepositRefunds as $consignmentDepositRefund)
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $consignmentDepositRefund['id'])) 
            {
                SendCollectionToYY::dispatch($jobType, 
                    $dataModel, $consignmentDepositRefund['id'], 
                    $mappingModel, $consignmentDepositRefund['id']);
            }
        }
    }

    /**
     *  process pre-order payment
     * @return mixed
     */
    public function processBatchNonStockistSalePaymentToYY()
    {
        $payments = $this->saleRepositoryObj
            ->getYonyouIntegrationNonStockistSalePayment();

        $jobType = 'PREORDER_DEPOSIT';

        $dataModel = 'payments';
        
        $mappingModel = 'payments';

        foreach ($payments as $payment) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $payment['id'])) 
            {
                SendCollectionToYY::dispatch($jobType, 
                    $dataModel, $payment['id'], 
                    $mappingModel, $payment['id']);
            }
        }
    }

    /**
     *  process refund due to pre-order cancellation
     * @return mixed
     */
    public function processBatchNonStockistPreOrderRefundToYY()
    {
        $payments = $this->saleRepositoryObj
            ->getYonyouIntegrationNonStockistPreOrderRefund();

        $jobType = 'PREORDER_REFUND';

        $dataModel = 'payments';

        $mappingModel = 'payments';

        foreach ($payments as $payment) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $payment['id'])) 
            {
                SendCollectionToYY::dispatch($jobType, 
                    $dataModel, $payment['id'], 
                    $mappingModel, $payment['id']);
            }
        }
    }
}
