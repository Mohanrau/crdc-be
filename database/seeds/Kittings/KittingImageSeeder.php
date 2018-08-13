<?php

use App\Models\Kitting\Kitting;
use App\Models\Kitting\KittingImage;
use Illuminate\Database\Seeder;

class KittingImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $kittingNotInDB = [];

        $data = json_decode(file_get_contents('database/seeding/' . "images.txt"));

        foreach ($data as $item)
        {
            $kitting = Kitting::where('code', $item->products_sku)->where('country_id', $item->country_id)->first();

            if($kitting)
            {
                KittingImage::updateOrCreate([
                    'kitting_id' => $kitting->id,
                    'image_path' => $item->image_path,
                    'default' => 1,
                    'active' => 1
                ]);
            }
            else
            {
                $kittingNotInDB[] = $item;
            }
        }

//        TODO: this is for testing purpose. Need to remove once done.
        \Illuminate\Support\Facades\Log::info(json_encode($kittingNotInDB));
    }
}
