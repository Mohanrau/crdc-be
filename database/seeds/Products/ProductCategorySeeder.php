<?php

use Illuminate\Database\Seeder;
use App\Models\Products\ProductCategory;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."products_categories.txt"));

        foreach ($data as $item){

            $category = $item->parent_id == 0 ? 0 :
                ProductCategory::where('yy_category_id','NULL'.$item->parent_id)
                    ->first()->id;

            if(substr($item->name,0,4) == 'NULL' ) {continue;};

            if($category == NULL) {$category = 0;};

            ProductCategory::updateOrCreate(
                [
                    'name' => $item->name
                ],
                [
                    'yy_category_id' => $item->yy_category_id,
                    'parent_id' => $category,
                    'name' => $item->name,
                    'code' => $item->code,
                    'active' => 0
                ]
            );
        }
    }
}
