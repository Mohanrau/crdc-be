<?php
namespace App\Console\Commands\Integrations;

use App\Interfaces\{
    Integrations\YonyouInterface,
    Sales\SaleInterface
};
use App\Jobs\{
    SendSalesToYY,
    SendSalesUpdateToYY
};
use Illuminate\Console\Command;

class ProcessBatchSalesIntegration extends Command
{
    private $yonyouRepositoryObj,
        $saleRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProcessBatchSalesIntegration:processBatchSalesIntegration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sales integration to yy';

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
        $this->processBatchSalesToYY();
        $this->processBatchSalesExchangeINVToYY();
        $this->processBatchSalesUpdateToYY();
        $this->processBatchSalesRentalUpdateToYY();
    }

    /**
     *  process non stockist sale
     * @return mixed
     */
    public function processBatchSalesToYY()
    {
        $sales = $this->saleRepositoryObj
            ->getYonyouIntegrationNonStockistSales();

        $jobType = 'SALES';

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

    /**
     *  process sales exchange (exchanged product)
     * @return mixed
     */
    public function processBatchSalesExchangeINVToYY()
    {
        $saleExchanges = $this->saleRepositoryObj
            ->getYonyouIntegrationSaleExchangeInvoice();

        $jobType = 'SALES_EXCHANGE_INV';

        $dataModel = 'sales';

        $mappingModel = 'sales';

        foreach ($saleExchanges as $saleExchange) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $saleExchange['sale_id'])) 
            {
                SendSalesToYY::dispatch($jobType, 
                    $dataModel, $saleExchange['sale_id'], 
                    $mappingModel, $saleExchange['sale_id']);
            }
        }
    }

    /**
     *  process sale update
     * @return mixed
     */
    public function processBatchSalesUpdateToYY()
    {
        $invoices = $this->saleRepositoryObj
            ->getYonyouIntegrationSalesUpdate();

        $jobType = 'SALES_UPDATE';

        $dataModel = 'sales';

        $mappingModel = 'invoices';

        foreach ($invoices as $invoice) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $invoice['id'])) 
            {
                SendSalesUpdateToYY::dispatch($jobType, 
                    $dataModel, $invoice['sale_id'], 
                    $mappingModel, $invoice['id']);
            }
        }
    }

    /**
     *  send batch sales rental update to YY
     * @return mixed
     */
    public function processBatchSalesRentalUpdateToYY()
    {
        $sales = $this->saleRepositoryObj
            ->getYonyouIntegrationSalesRentalUpdate();

        $jobType = 'SALES_RENTAL_UPDATE';

        $dataModel = 'sales';

        $mappingModel = 'sales';
        
        foreach ($sales as $sale) 
        {
            if ($this->yonyouRepositoryObj->scheduleIntegrationJob($jobType, $mappingModel, $sale['id'])) 
            {
                SendSalesUpdateToYY::dispatch($jobType, 
                    $dataModel, $sale['id'], 
                    $mappingModel, $sale['id']);
            }
        }
    }
}
