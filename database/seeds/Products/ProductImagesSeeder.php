<?php

use App\Models\Locations\Country;
use App\Models\Products\Product;
use App\Models\Products\ProductImage;
use Illuminate\Database\Seeder;

class ProductImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productNotInDB = [];

        $data = json_decode(file_get_contents('database/seeding/' . "images.txt"));

        foreach ($data as $item)
        {
            $product = Product::where('sku', $item->products_sku)->first();

            if($product)
            {
                ProductImage::updateOrCreate([
                    'country_id' => $item->country_id,
                    'entity_id' => $item->entity_id,
                    'product_id' => $product->id,
                    'image_path' => $item->image_path,
                    'default' => 1,
                    'active' => 1
                ]);
            }
            else
            {
                $productNotInDB[] = $item;
            }
        }

//        TODO: this is for testing purpose. Need to remove once done.
        \Illuminate\Support\Facades\Log::info(json_encode($productNotInDB));
    }
}
