<?php

use Illuminate\Database\Seeder;
use App\Models\{
    Bonus\BonusSummary,
    Bonus\BonusMentorBonusDetails,
    Users\User,
    Locations\Country,
    General\CWSchedule
};

class BonusMentorBonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/bonus/' . "bonus_mentor_bonus.txt"));

        foreach ($data as $item) {

            $user = User::where('old_member_id', $item->member_id)->first();

            if(empty($user)) {
                continue;
            };

            $child = User::where('old_member_id', $item->child)->first();

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

            BonusMentorBonusDetails::updateOrCreate(
                [
                    "bonuses_summary_id" =>  $bonuses_summary_id->id,
                ],
                [
                    "bonuses_summary_id" =>  $bonuses_summary_id->id,
                    "sponsor_child_user_id" => $child->id,
                    "sponsor_generation_level" => $item->f_level,
                    "team_bonus_cv" => $item->team_bonus_cv,
                    "mentor_bonus_percentage" => $item->mentor_bonus_percentage,
                    "mentor_bonus_cv" => $item->mentor_bonus_cv
                ]
            );
        }
    }
}
