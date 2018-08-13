<?php

namespace App\Console\Commands\Campaigns;

use App\Helpers\Classes\Campaign\CampaignCalculation;
use Illuminate\Console\Command;

class CalculateCampaign extends Command
{
    protected $campaignCalculationHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:calculate {cw_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CampaignCalculation $campaignCalculation)
    {
        parent::__construct();
        $this->campaignCalculationHelper = $campaignCalculation;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->campaignCalculationHelper->process($this->argument('cw_id'));
    }
}
