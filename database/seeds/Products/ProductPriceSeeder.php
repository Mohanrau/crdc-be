<?php

use Illuminate\Database\Seeder;
use App\Models\{
    Locations\Entity,
    Locations\Location,
    Locations\LocationTypes,
    Products\Product,
    Currency\Currency,
    Locations\Country,
    Products\ProductPrice,
    Products\ProductActive,
    Products\ProductLocation
};

class ProductPriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."products_standard_prices.txt"));

        foreach ($data as $item){

            if(empty($item->effective_date)){
                continue;
            }

            $value =$item->country_code;

            $product = Product::where('sku', $item->products_sku)->first();

            if(empty($product)){
                continue;
            }

            $entity = Entity::with(['country'])
                ->whereHas('country', function($q) use($value) {
                    // Query the name field in country table
                    $q->where('code_iso_2', '=', $value);
                })
                ->first();

            //if entity is empty then seed entities table
            if(empty($entity)){
                $entity = Entity::updateOrCreate(
                    [
                        'country_id' => Country::where('code_iso_2',$item->country_code)->first()->id,
                        'name' => $item->f_code
                    ]
                );

                $product->entity()->attach(
                    [
                        $entity->id
                    ]
                );
            }//else if entity is there but product_entities is not there
            elseif(!empty($entity)){

                $productEntity = $product->entity()->where('country_id',$entity->country_id)->get();

                //echo($productEntity[0]->pivot->entity_id);

                if (empty($productEntity[0])){

                    $product->entity()->attach(
                        [
                            $entity->id
                        ]
                    );
                }
            }

            //--------------------

            ProductActive::updateOrCreate(
                [
                    'country_id' => $entity->country_id,
                    'product_id' => $product->id,
                    "ibs_active" => 0
                ]
            );

            $location = Location::where('code',$item->f_code)->first();

            if(empty($location))
            {
                $location = Location::updateOrCreate(
                    [
                        'name' => $item->products_operation
                    ],
                    [
                        'zone_id' => NULL,
                        'entity_id' => $entity->id,
                        'name' => $item->products_operation,
                        'code' => $item->f_code,
                        'location_types_id' => LocationTypes::where('code', 'branch')->first()->id,
                        'active' => 0
                    ]
                );
            }

            ProductLocation::updateOrCreate(
                [
                    'country_id' => $entity->country_id,
                    'entity_id' => $entity->id,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'promo' => 0
                ]
            );

            //-----------------------------

            $currency = Currency::where('code',$item->currency_code)->first();

            $rp_price = ($item->f_price_code == 'CRP') and ($item->f_gst_code == 'ZR' or empty($item->f_gst_code)) ?
                $item->tax_exclusive : 0;

            $nmp_price = ($item->f_price_code == 'GMP') and ($item->f_gst_code == 'ZR' or empty($item->f_gst_code)) ?
                $item->tax_exclusive : 0;

            $result = ProductPrice::firstOrCreate(
                [
                    'yy_id' => $item->yy_id,
                    'country_id' => $entity->country_id,
                    'entity_id' => $entity->id,
                    'product_id' => $product->id
                ],
                [
                    'yy_id' => $item->yy_id,
                    'country_id' => $entity->country_id,
                    'entity_id' => $entity->id,
                    'product_id' => $product->id,
                    'currency_id' => $currency->id,
                    'gmp_price_gst' => $item->gmp_price_gst,
                    'rp_price' => $rp_price,
                    'rp_price_gst' => $item->crp_price_gst,
                    'nmp_price' => $nmp_price,
                    'effective_date' => $item->effective_date,
                    'expiry_date' => $item->expiry_date,
                    'base_cv' => $item->base_cv,
                    'wp_cv' => $item->wp_cv,
                    'cv1' => $item->cv1,
                    'cv2' => 0,
                    'welcome_bonus_l1' => $item->welcome_bonus_l1,
                    'welcome_bonus_l2' => $item->welcome_bonus_l2,
                    'welcome_bonus_l3' => $item->welcome_bonus_l3,
                    'welcome_bonus_l4' => $item->welcome_bonus_l4,
                    'welcome_bonus_l5' => $item->welcome_bonus_l5
                ]);

            if($result->wasRecentlyCreated){
                //echo 'Created Successfully!';
            }
            else
            {
                //echo "already exist -".$result->id."-".$item->price_code.PHP_EOL;

                //TODO PWP & FOC
                if ($item->price_code == 'CRP')
                {
                    //echo "update CRP=".$item->crp_price_gst.PHP_EOL;
                    switch($item->f_gst_code)
                    {
                        case 'SR':
                            ProductPrice::where('yy_id' , $item->yy_id)
                                ->where('country_id' , $entity->country_id)
                                ->where('entity_id' , $entity->id)
                                ->where('product_id' , $product->id)
                                ->update(['rp_price_gst' => $item->crp_price_gst]);
                            break;
                        case 'ZR':
                        case '':
                        case NULL:
                            ProductPrice::where('yy_id' , $item->yy_id)
                                ->where('country_id' , $entity->country_id)
                                ->where('entity_id' , $entity->id)
                                ->where('product_id' , $product->id)
                                ->update(['rp_price' => $item->tax_exclusive]);
                    }

                }
                elseif ($item->price_code == 'GMP'){
                    //echo "update GMP=".$item->gmp_price_gst.PHP_EOL;
                    switch($item->f_gst_code)
                    {
                        case 'SR':
                            ProductPrice::where('yy_id' , $item->yy_id)
                                ->where('country_id' , $entity->country_id)
                                ->where('entity_id' , $entity->id)
                                ->where('product_id' , $product->id)
                                ->update(['gmp_price_gst' => $item->gmp_price_gst]);
                        case 'ZR':
                        case '':
                        case NULL:
                            ProductPrice::where('yy_id' , $item->yy_id)
                                ->where('country_id' , $entity->country_id)
                                ->where('entity_id' , $entity->id)
                                ->where('product_id' , $product->id)
                                ->update(['nmp_price' => $item->tax_exclusive]);
                    }
                }
            }
        }
    }
}
