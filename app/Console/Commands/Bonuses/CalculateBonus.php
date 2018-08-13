<?php

namespace App\Console\Commands\Bonuses;

use App\Helpers\Classes\Bonus\MemberTreeBonus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CalculateBonus extends Command
{
    protected $cwId, $memberTreeBonusHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:calculate {cw_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To calculate the current CW bonuses for all the members';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MemberTreeBonus $memberTreeBonus)
    {
        parent::__construct();
        $this->memberTreeBonusHelper = $memberTreeBonus;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cwId = $this->argument('cw_id');

        $this->info('Starting to run Bonus Calculation');

        $time_start = microtime(true);
        $tree = $this->memberTreeBonusHelper->calculate($cwId);
        $time = microtime(true) - $time_start;
        $this->info('Bonus calculation based on current tree : '.$time.' seconds');

        $time_start = microtime(true);
        new \App\Helpers\Classes\Bonus\BonusStore($tree->getMemberTree(), $tree->getCwId(), $tree->getBringForwardInfo());
        $this->info('Bonus calculation insertion to table: '.$time.' seconds');
        $time = microtime(true) - $time_start;
        $this->info('Inserting data into DB : '.$time.' seconds');

        $this->info('End Running Bonus Calculation');

    }
}
