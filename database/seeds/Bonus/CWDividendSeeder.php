<?php

use Illuminate\Database\Seeder;
use App\Models\General\CWDividendSchedule;
use App\Models\General\CWSchedule;

class CWDividendSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Q1 is 2016-41 to 2017-05
        //Q2 is 2017-06 to 2017-12
        //Q3 is 2017-13 to 2017-18
        //Q4 is 2017-19 to 2017-25

        $data = [
            ['2017-Q1', '2016-41','2017-05'],
            ['2017-Q2', '2017-06','2017-12'],
            ['2017-Q3', '2017-13','2017-18'],
            ['2017-Q4', '2017-19','2017-25'],
            ['2018-Q1', '2017-26','2018-05'],
            ['2018-Q2', '2018-06','2018-12'],
            ['2018-Q3', '2018-13','2018-18'],
            ['2018-Q4', '2018-19','2019-01'],
            ['2019-Q1', '2019-02','2019-07'],
            ['2019-Q2', '2019-08','2019-13'],
            ['2019-Q3', '2019-14','2019-19'],
            ['2019-Q4', '2019-20','2020-01'],
            ['2020-Q1', '2020-02','2020-07'],
            ['2020-Q2', '2020-08','2020-13'],
            ['2020-Q3', '2020-14','2020-19'],
            ['2020-Q4', '2020-20','2021-01'],
        ];

        foreach ($data as $item)
        {
            CWDividendSchedule::updateOrCreate(
                [
                    "cw_name" => $item[0]
                ],
                [
                    "cw_name" => $item[0],
                    "from_cw_id" => CWSchedule::where('cw_name',$item[1])->first()->id,
                    "to_cw_id" =>  CWSchedule::where('cw_name',$item[2])->first()->id,
                ]
            );
        }
    }
}
