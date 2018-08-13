<?php
namespace App\Console\Commands\Integrations;

use App\Interfaces\{
    Integrations\YonyouInterface,
    Sales\SaleInterface
};
use App\Jobs\{
    SendSalesToYY
};
use Illuminate\Console\Command;

class ProcessBatchSalesCancellationIntegration extends Command
{
    private $yonyouRepositoryObj,
        $saleRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProcessBatchSalesCancellationIntegration:processBatchSalesCancellationIntegration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send same day cancellation to yy';

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
        $this->processBatchSalesCancellationToYY();
        $this->processBatchSalesExchangeCCNToYY();
    }

    /**
     *  send sales cancellation
     * @return mixed
     */
    public function processBatchSalesCancellationToYY()
    {
        $saleCancellations = $this->saleRepositoryObj
            ->getYonyouIntegrationSalesCancellation();

        $jobType = 'SALES_CANCELLATION';

        $dataModel = 'sales_cancellations';

        $mappingModel = 'sales_cancellations';

        foreach ($saleCancellations as $saleCancellation) 
        {   
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $saleCancellation['id'])) 
            {
                SendSalesToYY::dispatch($jobType, 
                    $dataModel, $saleCancellation['id'], 
                    $mappingModel, $saleCancellation['id']);
            }
        }
    }

    /**
     *  process sales exchange (return product)
     * @return mixed
     */
    public function processBatchSalesExchangeCCNToYY()
    {
        $saleExchanges = $this->saleRepositoryObj
            ->getYonyouIntegrationSaleExchangeCreditNote();

        $jobType = 'SALES_EXCHANGE_CCN';

        $dataModel = 'sales_exchanges';

        $mappingModel = 'sales_exchanges';

        foreach ($saleExchanges as $saleExchange) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $saleExchange['id'])) 
            {
                SendSalesToYY::dispatch($jobType, 
                    $dataModel, $saleExchange['id'], 
                    $mappingModel, $saleExchange['id']);
            }
        }
    }
}
