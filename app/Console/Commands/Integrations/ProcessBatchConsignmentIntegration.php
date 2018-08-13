<?php
namespace App\Console\Commands\Integrations;

use App\Interfaces\{
    Integrations\YonyouInterface,
    Stockists\StockistInterface
};
use App\Jobs\{
    SendConsignmentToYY
};
use Illuminate\Console\Command;

class ProcessBatchConsignmentIntegration extends Command
{
    private $yonyouRepositoryObj, 
        $stockistRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProcessBatchConsignmentIntegration:processBatchConsignmentIntegration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send batch consignment order/return to yy';

    /**
     * ProcessBatchConsignmentIntegration constructor.
     *
     * @param YonyouInterface $yonyouInterface
     * @param StockistInterface $stockistInterface
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
        $this->processBatchConsignmentOrderToYY();
        $this->processBatchConsignmentReturnToYY();
    }

    /**
     * process consignment order
     * @return mixed
     */
    public function processBatchConsignmentOrderToYY()
    {
        $consignmentOrderReturns = $this->stockistRepositoryObj
            ->getYonyouIntegrationConsignmentOrder();

        $jobType = 'CONSIGNMENT_ORDER';

        $dataModel = 'consignments_orders_returns';

        $mappingModel = 'consignments_orders_returns';

        foreach ($consignmentOrderReturns as $consignmentOrderReturn) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $consignmentOrderReturn['id'])) 
            {
                SendConsignmentToYY::dispatch($jobType, 
                    $dataModel, $consignmentOrderReturn['id'], 
                    $mappingModel, $consignmentOrderReturn['id']);
            }
        }
    }

    /**
     * process consignment return
     * @return mixed
     */
    public function processBatchConsignmentReturnToYY()
    {
        $consignmentOrderReturns = $this->stockistRepositoryObj
            ->getYonyouIntegrationConsignmentReturn();

        $jobType = 'CONSIGNMENT_RETURN';

        $dataModel = 'consignments_orders_returns';

        $mappingModel = 'consignments_orders_returns';

        foreach ($consignmentOrderReturns as $consignmentOrderReturn) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $consignmentOrderReturn['id'])) 
            {
                SendConsignmentToYY::dispatch($jobType, 
                    $dataModel, $consignmentOrderReturn['id'], 
                    $mappingModel, $consignmentOrderReturn['id']);
            }
        }
    }
}
