<?php

namespace App\Console\Commands\General;

use App\{
    Interfaces\General\CwSchedulesInterface,
    Models\Settings\Setting,
    Models\Settings\SettingKey
};
use Illuminate\Console\Command;

class UpdateCurrentCw extends Command
{
    protected $settingKeyObj, $settingObj, $cwSchedulesRepositoryObj;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'general:update-current-cw';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @param CwSchedulesInterface $cwSchedulesInterface
     * @param SettingKey $settingKey
     * @param Setting $setting
     * @return void
     */
    public function __construct
    (
        CwSchedulesInterface $cwSchedulesInterface,
        SettingKey $settingKey,
        Setting $setting
    )
    {
        parent::__construct();

        $this->description = trans('message.console-task-scheduling.current-cw-update');

        $this->settingKeyObj = $settingKey;

        $this->settingObj = $setting;

        $this->cwSchedulesRepositoryObj = $cwSchedulesInterface;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //Get Setting Key ID
        $settingKey = $this->settingKeyObj->where('key', 'current_cw_id')->first();

        if($settingKey){

            //Get Setting Record
            $cwSetting = $this->settingObj
                ->where('setting_key_id', $settingKey->id)->first();

            //Get Current CW
            $currentCw = $this->cwSchedulesRepositoryObj
                ->getCwSchedulesList('current',
                    ['sort' => 'cw_name', 'order' => 'desc', 'limit' => 0]
                );

            $currentCw = $currentCw['data']->first();

            //Update Current Cw Id to Setting Table
            $cwSetting->update(
                array(
                    'value' => $currentCw['id']
                )
            );
        }

        $this->info('Current cw update successfully!');
    }
}

?>