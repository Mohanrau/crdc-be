<?php

use Illuminate\Database\Seeder;
use App\Models\
{
    Products\ProductCategory,
    Products\Product,
    Masters\Master,
    Products\ProductGeneralSetting,
    Locations\Entity
};

class LingerieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //TODO Lingerie product Regular or Irregular SKU may change.

        $data = json_decode(file_get_contents('database/seeding/LiveDataMigration/'."products_lingerie.txt"));

        foreach ($data as $item){

            $master =  Master::where('key', $item->Size_Group)->first();

            $product = Product::updateOrCreate(
                [
                    'sku' => $item->Product_SKU
                ],
                [
                    'yy_product_id' => 'NULL'.$item->Product_SKU,
                    'category_id' => ProductCategory::where('name',$item->Product_Category)->first()->id,
                    'name' => $item->Product_Name,
                    'sku' => $item->Product_SKU,
                    'uom' => NULL,
                    'yy_active' => 1
                ]
            );

            if($item->Size == 1){
                $productSizeGroup = $product->sizeGroups()->where('master_id',$master->id)->get();

                if (empty($productSizeGroup[0])){

                    //var_dump($productSizeGroup[0]->pivot->master_id);

                    $product -> sizeGroups()->attach(
                        [
                            $master->id
                        ]
                    );
                }

                $entities = Entity::with(['country'])
                    ->whereHas('country', function($q) {
                        // Query the name field in country table
                        $q->where('active', 1);
                    })
                    ->get();

                foreach ($entities as $entity) {

                    $masterDataSizeGroup = Master::where('id',$master->id)
                        ->first()
                        ->masterData()
                        ->get();

                    $masterDataAdditional = Master::where('key', 'product_additional_requirements')
                        ->first()
                        ->masterData()
                        ->where('title','Size')
                        ->get();

                    $masterData = $masterDataAdditional->merge($masterDataSizeGroup);

                    foreach ($masterData as $item){

                        ProductGeneralSetting::updateOrCreate(
                            [
                                'country_id' => $entity->country_id,
                                'entity_id' => $entity->id,
                                'product_id' => $product->id,
                                'master_id' => $item->master_id,
                                'master_data_id' => $item->id
                            ],
                            [
                                'country_id' => $entity->country_id,
                                'entity_id' => $entity->id,
                                'product_id' => $product->id,
                                'master_id' => $item->master_id,
                                'master_data_id' => $item->id
                            ]
                        );
                    }
                }
            }
        }
    }
}
