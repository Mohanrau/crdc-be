<?php

namespace App\Console\Commands\Members;

use App\Interfaces\Masters\MasterInterface;
use App\Models\Members\Member;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateMemberExpiredStatus extends Command
{
    protected
        $memberObj,
        $masterRepositoryObj,
        $memberStatusConfigCodes;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'member:update-member-expiry-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @param MasterInterface $masterInterface
     * @param Member $member
     * @return void
     */
    public function __construct
    (
        MasterInterface $masterInterface,
        Member $member
    )
    {
        parent::__construct();

        $this->description = trans('message.console-task-scheduling.update-member-expiry-status');

        $this->memberObj = $member;

        $this->masterRepositoryObj = $masterInterface;

        $this->memberStatusConfigCodes = config('mappings.member_status');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Always Run For Yesterday Transaction
        $expiredDate = Carbon::yesterday()->format('Y-m-d');

        //Get Setup Data
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(
            array('member_status'));

        $memberStatusValues = array_change_key_case(
            $settingsData['member_status']->pluck('id','title')->toArray());

        $expiredStatusId = $memberStatusValues[$this->memberStatusConfigCodes['expired']];

        $activeStatusId = $memberStatusValues[$this->memberStatusConfigCodes['active']];

        //Get Expired Member Record
        $memberRecords = $this->memberObj
            ->where('expiry_date', $expiredDate)
            ->where('status_id', $activeStatusId)
            ->get();

        collect($memberRecords)->each(function($memberRecord) use ($expiredStatusId) {

            $member = $this->memberObj
                ->find($memberRecord->id);

            $member->update([
                'status_id' => $expiredStatusId
            ]);
        });

        $this->info('Member Expiry Status Update Successfully!');
    }
}

?>