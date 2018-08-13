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

class ProcessBatchStockistSalesIntegration extends Command
{
    private $yonyouRepositoryObj,
        $saleRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProcessBatchStockistSalesIntegration:processBatchStockistSalesIntegration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'stockist sales integration to yy';

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
        $this->processBatchStockistSalesToYY();
    }

    /**
     *  process stockist sale (hold stock)
     * @return mixed
     */
    public function processBatchStockistSalesToYY()
    {
        $sales = $this->saleRepositoryObj
            ->getYonyouIntegrationStockistSales();

        $jobType = 'STOCKIST_SALES';

        $dataModel = 'sales';

        $mappingModel = 'sales';

        foreach ($sales as $sale) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $sale['id'])) 
            {
                SendSalesToYY::dispatch($jobType, 
                    $dataModel, $sale['id'], 
                    $mappingModel, $sale['id']);
            }
        }
    }
}
