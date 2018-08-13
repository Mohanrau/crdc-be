<?php

use Illuminate\Database\Seeder;
use App\Models\{
    Masters\Master,
    Masters\MasterData
};

class LingerieSizeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sizeGroup = [
            //groupSize of LXIIBB1
            [
                "title" => "Regular Bra Group Size",
                "key" => "regular_bra_group_size",
                'active' => 1,
                'master_data' => [
                    ["title" => "A65"],
                    ["title" => "A70"],
                    ["title" => "A75"],
                    ["title" => "A80"],
                    ["title" => "A85"],
                    ["title" => "A90"],
                    ["title" => "A95"],
                    ["title" => "A100"],
                    ["title" => "B65"],
                    ["title" => "B70"],
                    ["title" => "B75"],
                    ["title" => "B80"],
                    ["title" => "B85"],
                    ["title" => "B90"],
                    ["title" => "B95"],
                    ["title" => "B100"],
                    ["title" => "C65"],
                    ["title" => "C70"],
                    ["title" => "C75"],
                    ["title" => "C80"],
                    ["title" => "C85"],
                    ["title" => "C90"],
                    ["title" => "C95"],
                    ["title" => "C100"],
                    ["title" => "D65"],
                    ["title" => "D70"],
                    ["title" => "D75"],
                    ["title" => "D80"],
                    ["title" => "D85"],
                    ["title" => "D90"],
                    ["title" => "D95"],
                    ["title" => "E100"],
                    ["title" => "E65"],
                    ["title" => "E70"],
                    ["title" => "E75"],
                    ["title" => "E80"],
                    ["title" => "E85"],
                    ["title" => "E90"],
                    ["title" => "E95"]
                ]
            ],
            //groupSize of LXIIBB1I
            [
                "title" => "Irregular Bra Group Size",
                "key" => "irregular_bra_group_size",
                'active' => 1,
                'master_data' => [
                    ["title" => "F65"],
                    ["title" => "F70"],
                    ["title" => "F75"],
                    ["title" => "F80"],
                    ["title" => "F85"],
                    ["title" => "F90"],
                    ["title" => "G65"],
                    ["title" => "G70"],
                    ["title" => "G75"],
                    ["title" => "G80"],
                    ["title" => "G85"],
                    ["title" => "G90"],
                    ["title" => "H65"],
                    ["title" => "H70"],
                    ["title" => "H75"],
                    ["title" => "H80"],
                    ["title" => "H85"],
                    ["title" => "H90"]
                ]
            ],
            //groupSize of LXIIBL, LXIIBS, LXIIBW
            [
                "title" => "Long Girdle Group Size",
                "key" => "long_girdle_group_size",
                'active' => 1,
                'master_data' =>[
                    ["title" => "58"],
                    ["title" => "64"],
                    ["title" => "70"],
                    ["title" => "76"],
                    ["title" => "82"],
                    ["title" => "90"],
                    ["title" => "98"],
                    ["title" => "106"]
                ]
            ],
            //groupSize of LXIIBP
            [
                "title" => "Panties Group Size",
                "key" => "panties_group_size",
                'active' => 1,
                'master_data' =>[
                    ["title" => "M"],
                    ["title" => "L"],
                    ["title" => "2L"],
                    ["title" => "3L"]
                ]
            ],
            //groupSize of arm sharper
            [
                "title" => "Arm Shaper Group Size",
                "key" => "arm_shaper_group_size",
                'active' => 1,
                'master_data' =>[
                    ["title" => "70"],
                    ["title" => "75"],
                    ["title" => "80"],
                    ["title" => "85"],
                    ["title" => "90"],
                    ["title" => "95"],
                    ["title" => "100"],
                    ["title" => "105"]
                ]
            ]
        ];

        foreach ($sizeGroup as $data)
        {
            $masterData = $data['master_data'];

            unset($data['master_data']);

            $master = Master::updateOrCreate(
                [
                    'key' => $data['key']
                ],
                $data
            );

            if(MasterData::where('master_id',$master->id)->first()){
                MasterData::where('master_id',$master->id)->delete();
            }

            $master->masterData()->createMany($masterData);
        }
    }
}
