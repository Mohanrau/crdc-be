<?php
namespace App\Console\Commands\Integrations;

use App\Interfaces\{
    Integrations\YonyouInterface,
    EWallet\EWalletInterface
};
use App\Jobs\{
    SendRemittanceToYY
};
use Illuminate\Console\Command;

class ProcessBatchRemittanceIntegration extends Command
{
    private $yonyouRepositoryObj, 
        $eWalletRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ProcessBatchRemittanceIntegration:processBatchRemittanceIntegration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'eWallet Transaction - withdrawal/adjustment';

    /**
     * ProcessBatchRemittance constructor.
     *
     * @param YonyouInterface $yonyouInterface
     * @param EWalletInterface $eWalletInterface
     */
    public function __construct
    (
        YonyouInterface $yonyouInterface,
        EWalletInterface $eWalletInterface
    )
    {
        parent::__construct();
        $this->yonyouRepositoryObj = $yonyouInterface;
        $this->eWalletRepositoryObj = $eWalletInterface;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->processBatchRemittanceToYY();
    }

    /**
     *  process remittance
     * @return mixed
     */
    public function processBatchRemittanceToYY()
    {
        //TODO: Ignore for now, account will do manually for the moment
        // $eWalletRemittances = $this->eWalletRepositoryObj
        //     ->getIntegrationRemittance(false);

        // $jobType = 'REMITTANCE';

        // $dataModel = 'user_ewallet_transactions';

        // $mappingModel = 'user_ewallet_transactions';

        // foreach ($eWalletRemittances as $eWalletRemittance)
        // {
        //     SendRemittanceToYY::dispatch($jobType, 
        //         $dataModel, $eWalletRemittance['id'],
        //         $mappingModel, $eWalletRemittance['id']);
        // }
    }
}
