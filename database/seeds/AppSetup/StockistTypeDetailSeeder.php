<?php

use App\{
    Models\Masters\MasterData,
    Models\Stockists\StockistTypeDetail
};
use Illuminate\Database\Seeder;

class StockistTypeDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stockistTypes = MasterData::whereHas('master', function($query){
            $query->where('key', 'stockist_type');
        })->get();

        foreach ($stockistTypes as $stockistType)
        {
            switch ($stockistType->title){
                case 'SERVICE POINT':
                    $stockistTypeDetail = [
                        'stockist_type_id' => $stockistType->id,
                        'otc_wp_percentage' => '12',
                        'otc_other_percentage' => '6',
                        'online_wp_percentage' => '12',
                        'online_other_percentage' => '6'
                    ];
                    break;
                case 'STOCKIST CENTER':
                    $stockistTypeDetail = [
                        'stockist_type_id' => $stockistType->id,
                        'otc_wp_percentage' => '16',
                        'otc_other_percentage' => '8',
                        'online_wp_percentage' => '16',
                        'online_other_percentage' => '8'
                    ];
                    break;
                case 'SERVICE PARTNER':
                    $stockistTypeDetail = [
                        'stockist_type_id' => $stockistType->id,
                        'otc_wp_percentage' => '8',
                        'otc_other_percentage' => '4',
                        'online_wp_percentage' => '8',
                        'online_other_percentage' => '4'
                    ];
                    break;
                case 'BUSINESS AGENT':
                    $stockistTypeDetail = [
                        'stockist_type_id' => $stockistType->id,
                        'otc_wp_percentage' => '8',
                        'otc_other_percentage' => '4',
                        'online_wp_percentage' => '8',
                        'online_other_percentage' => '4'
                    ];
                    break;
            }

            StockistTypeDetail::updateOrCreate(
                [
                    'stockist_type_id' => $stockistType->id
                ],
                $stockistTypeDetail
            );
        }

    }
}
