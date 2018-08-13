<?php

use Illuminate\Database\Seeder;
use App\Models\{
    Bonus\BonusSummary,
    Bonus\BonusWelcomeBonusDetails,
    Users\User,
    Locations\Country,
    General\CWSchedule
};

class BonusWelcomeBonusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/bonus/' . "bonus_welcome_bonus.txt"));

        foreach ($data as $item) {

            $user = User::where('old_member_id', $item->child)->first();

            if(empty($user)) {
                continue;
            };

            $bonuses_summary_id = BonusSummary::where('user_id',  User::where('old_member_id', $item->member_id)->first()->id)
                ->where('country_id', Country::where('code_iso_2', $item->country_code)->first()->id)
                ->where('cw_id', CWSchedule::where('cw_name', $item->cw)->first()->id)
                ->first();

            if(empty($bonuses_summary_id)) {
                continue;
            };

            BonusWelcomeBonusDetails::updateOrCreate(
                [
                    "bonuses_summary_id" =>  $bonuses_summary_id->id,
                ],
                [
                    "bonuses_summary_id" =>  $bonuses_summary_id->id,
                    "sponsor_child_user_id" => $user->id,
                    "sponsor_child_depth_level" => $item->f_level,
                    "join_date" => $item->join_date,
                    "total_local_amount" => 0.00, //ibs stored USD only.
                    "total_local_amount_currency" => 0.00, //ibs store USD no conversion rate to refer
                    "total_amount" => $item->local_amount,
                    "total_amount_currency" => $item->currency_rate,
                    "total_usd_amount" => $item->f_bonus_amount,
                    "nett_usd_amount" => 0.00 //ibs no store nett amount
                ]
            );
        }
    }
}
