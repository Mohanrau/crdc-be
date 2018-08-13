<?php

use Illuminate\Database\Seeder;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."products_standard.txt"));

        foreach ($data as $item){

            $category = ProductCategory::where('yy_category_id',$item->category_id)->first();

            $category = empty($category) ?
                ProductCategory::where('name', 'like', '%others%')->first():
                $category;

            $name = is_null($item->name) ? ' ' : $item->name;

            Product::updateOrCreate(
                [
                    'sku' => $item->sku
                ],
                [
                    'yy_product_id' => $item->yy_product_id,
                    'category_id' => $category->id,
                    'name' => $name,
                    'sku' => $item->sku,
                    'uom' => NULL
                ]
            );
        }
    }
}
