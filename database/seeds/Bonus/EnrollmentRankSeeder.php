<?php

use Illuminate\Database\Seeder;
use App\Models\Bonus\EnrollmentRank;

class EnrollmentRankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."enrollment_ranks.txt"));

        foreach ($data as $item)
        {
            EnrollmentRank::updateOrCreate(
                [
                    "id" => $item->id,
                    "rank_code"=> $item->code,
                    "rank_name"=> $item->name,
                    "CV"=> $item->CV,
                    "entitlement_lvl"=> $item->entitlement_lvl,
                    "sales_types" => json_encode($item->sales_type)
                ]
            );
        }
    }
}
