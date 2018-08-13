<?php

use Illuminate\Database\Seeder;
use App\Models\Bonus\TeamBonusRank;

class TeamBonusRankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."team_bonus_rank.txt"));

        foreach ($data as $item)
        {
            TeamBonusRank::updateOrCreate(
                [
                    "id" => $item->id,
                    "rank_code" => $item->rank_code,
                    "rank_name" => $item->rank_name,
                    "rank_order" => $item->rank_order,
                    "min_ps" => $item->min_ps,
                    "no_of_lines" => $item->no_of_lines,
                    "min_line_rank_id" => $item->min_line_rank_id,
                    "line_rank_count" => $item->line_rank_count,
                    "min_payleg_gcv" => $item->min_payleg_gcv,
                    "max_gcv_bf" => $item->max_gcv_bf,
                    "max_payout" => $item->max_payout,
                    "total_active_ba" => $item->total_active_BA,
                    "status" => $item->status
                ]
            );
        }
    }
}
