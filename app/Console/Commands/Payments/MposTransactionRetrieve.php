<?php

namespace App\Console\Commands\Payments;

use App\{
    Interfaces\Integrations\CimbMposInterface
};
use Illuminate\Console\Command;

class MposTransactionRetrieve extends Command
{
    protected $cimbMposRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment:mpos-transaction-retrieve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @param CimbMposInterface $cimbMposInterface
     * @return void
     */
    public function __construct
    (
        CimbMposInterface $cimbMposInterface
    )
    {
        parent::__construct();

        $this->description = trans('message.console-task-scheduling.mpos-payment-transaction-retriever-success');

        $this->cimbMposRepositoryObj = $cimbMposInterface;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->cimbMposRepositoryObj->mposRetrieverJob();

        $this->info('MPOS payment transaction retrieve successfully!');
    }
}

?>