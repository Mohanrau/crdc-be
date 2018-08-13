<?php

use Illuminate\Database\Seeder;
use App\Models\Bonus\TeamBonus;
use App\Models\Users\User;
use App\Models\General\CWSchedule;

class TeamBonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."team_bonuses.txt"));

        foreach ($data as $item)
        {
            $user = User::where('old_member_id', '=', $item->member_id)->first();

            $gcv_by = User::where('old_member_id', '=', $item->gcv_bf_by)->first();

            $cw = CWSchedule::where('cw_name', $item->cw)->first();

            TeamBonus::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'effective_rank_id' => $item->effective_rank,
                    'highest_rank_id' => $item->highest_rank,
                    'cw_id' => $cw->id,
                    'gcv' => $item->gcv,
                    'gcv_bf' => $item->gcv_bf,
                    'gcv_bf_by' => $gcv_by->id,
                    'gcv_bf_position' => $item->gcv_bf_position,
                    'ops' => $item->ops,
                    'gcv_flush' => $item->gcv_flush,
                    'gcv_bring_over' => $item->gcv_bring_over,
                    'is_active_ba' => $item->is_active_ba
                ]
            );
        }

    }
}
