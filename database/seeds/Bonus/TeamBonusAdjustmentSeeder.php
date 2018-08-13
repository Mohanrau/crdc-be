<?php

use Illuminate\Database\Seeder;
use App\Models\
{
    Bonus\TeamBonusAdjustment,
    General\CWSchedule,
    Users\User
};

class TeamBonusAdjustmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('public/seeding/'."team_bonus_adjustment.txt"));

        foreach ($data as $item) {

            $cw = CWSchedule::where('cw_name', $item->f_batch_no)->first();

            $user = User::where('old_member_id', '=', $item->f_code)->first();

            $pass_up_by = User::where('old_member_id', '=', $item->f_pass_up_by_code)->first();

            TeamBonusAdjustment::updateOrCreate(
                [
                    'cw_id' => $cw->id,
                    'user_id' => $user->id,
                    'pass_up_by_user_id' => $pass_up_by->id,
                    'adjustment_gcv' => $item->f_dailyv
                ]
            );
        }
    }
}
