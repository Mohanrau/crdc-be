<?php
namespace App\Console\Commands\Integrations;

use App\Interfaces\{
    Integrations\YonyouInterface,
    Sales\SaleInterface
};
use App\Jobs\{
    SendEwalletToYY
};
use Illuminate\Console\Command;

class ProcessBatchEwalletIntegration extends Command
{
    private $yonyouRepositoryObj,
        $saleRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProcessBatchEwalletIntegration:processBatchEwalletIntegration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send ewallet to yy - sales cancellation';

    /**
     * ProcessBatchEwalletIntegration constructor.
     *
     * @param YonyouInterface $yonyouInterface
     * @param SaleInterface $saleInterface
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
        $this->processBatchEwalletToYY();
    }

    /**
     * process refund due to sale cancellation
     * @return mixed
     */
    public function processBatchEwalletToYY()
    {
        $saleCancellations = $this->saleRepositoryObj
            ->getYonyouIntegrationSalesCancellationEWallet();

        $jobType = 'EWALLET_SALESCANCELLATION';

        $dataModel = 'sales_cancellations';

        $mappingModel = 'sales_cancellations';

        foreach ($saleCancellations as $saleCancellation) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $saleCancellation['id'])) 
            {
                SendEwalletToYY::dispatch($jobType, 
                    $dataModel, $saleCancellation['id'], 
                    $mappingModel, $saleCancellation['id']);
            }
        }
    }
}
