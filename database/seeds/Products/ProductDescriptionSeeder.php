<?php

use App\Models\Languages\Language;
use App\Models\Products\Product;
use App\Models\Products\ProductDescription;
use Illuminate\Database\Seeder;

class ProductDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/' . "product_descriptions.txt"));

        foreach ($data as $item) {
            $product = Product::where('sku', $item->sku)->first();
            $language = Language::where('key', $item->language_key)->first();

            if ($product) {
                ProductDescription::updateOrCreate([
                    'language_id' => $language->id,
                    'product_id' => $product->id,
                ],
                [
                    'marketing_description' => $item->marketing_description,
                    'benefits' => $item->specification,
                    'specification' => $item->benefits,
                    'active' => $item->active
                ]);
            }
        }
    }
}
