<?php

use Illuminate\Database\Seeder;
use App\Models\{
    Bonus\BonusSummary,
    Bonus\BonusTeamBonusDetails,
    Users\User,
    Locations\Country,
    General\CWSchedule
};

class BonusTeamBonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/bonus/' . "bonus_team_bonus.txt"));

        foreach ($data as $item) {

            $user = User::where('old_member_id', $item->member_id)->first();

            if(empty($user)) {
                continue;
            };

            $child = User::where('old_member_id', $item->gcv_bf_by)->first();

            if(empty($child)) {
                continue;
            };

            $bonuses_summary_id = BonusSummary::where('user_id',  User::where('old_member_id', $item->member_id)->first()->id)
                ->where('country_id', Country::where('code_iso_2', $item->country_code)->first()->id)
                ->where('cw_id', CWSchedule::where('cw_name', $item->cw)->first()->id)
                ->first();

            if(empty($bonuses_summary_id)) {
                continue;
            };

            BonusTeamBonusDetails::updateOrCreate(
                [
                    "bonuses_summary_id" =>  $bonuses_summary_id->id,
                ],
                [
                    "bonuses_summary_id" =>  $bonuses_summary_id->id,
                    "placement_child_user_id" => $child->id,
                    "gcv" => $item->gcv,
                    "optimising_personal_sales" => $item->ops,
                    "gcv_calculation" => $item->gcv_calculation,
                    "gcv_bring_forward" => $item->gcv_bf,
                    "gcv_bring_forward_position" => $item->gcv_bf_position,
                    "gcv_leg_group" => $item->gcv_leg_group,
                    "gcv_flush" => $item->gcv_flush,
                    "gcv_bring_over" => $item->gcv_bring_over,
                    "team_bonus_percentage" => $item->team_bonus_percentage,
                    "team_bonus_cv" => $item->team_bonus_cv
            ]);
        }
    }
}
