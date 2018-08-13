<?php

use Illuminate\Database\Seeder;
use App\Models\General\CWSchedule;

class CWScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."cw_schedule.txt"));

        foreach ($data as $item)
        {
            CWSchedule::updateOrCreate(
                ["cw_name" => $item->Batch_No],
                [
                    "cw_name" => $item->Batch_No,
                    "date_from" => $item->DateStart,
                    "date_to" =>  $item->DateEnd
                ]
            );
        }
    }
}
