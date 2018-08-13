<?php

namespace App\Console\Commands\Dashboard;

use App\Interfaces\Dashboard\DashboardInterface;
use Illuminate\Console\Command;

class UpdateMemberSnapshot extends Command
{
    protected $dashboardRepository;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'member:snapshot-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To update snapshot details for every members';

    /**
     * UpdateMemberSnapshot constructor.
     * @param DashboardInterface $dashboardRepository
     */
    public function __construct(DashboardInterface $dashboardRepository)
    {
        parent::__construct();
        $this->dashboardRepository = $dashboardRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       $this->dashboardRepository->updateMemberSnapshot();
    }
}
