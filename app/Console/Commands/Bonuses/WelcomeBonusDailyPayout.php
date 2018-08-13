<?php

namespace App\Console\Commands\Bonuses;

use App\Repositories\Bonus\WelcomeBonusRepository;
use Illuminate\Console\Command;

class WelcomeBonusDailyPayout extends Command
{
    protected $welcomeBonusRepository;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonus:pay-welcomebonus {cw_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * WelcomeBonusDailyPayout constructor.
     * @param WelcomeBonusRepository $welcomeBonusRepository
     */
    public function __construct(WelcomeBonusRepository $welcomeBonusRepository)
    {
        parent::__construct();
        $this->welcomeBonusRepository = $welcomeBonusRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cwId = $this->argument('cw_id');
        $this->welcomeBonusRepository->runDailyPayout($cwId);
    }
}
