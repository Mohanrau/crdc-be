<?php

use App\{Models\Locations\Country,
	Models\Locations\Location,
	Models\Products\Product,
	Models\Products\ProductActive,
	Models\Products\ProductLocation,
	Models\Products\ProductPrice};
use Illuminate\Database\Seeder;

class ProductPriceCVSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productNotInDB = [];

		$data = json_decode(file_get_contents('database/seeding/' . "products_price_cv.txt"));

		foreach ($data as $item)
		{
			$country = Country::where('code_iso_2', $item->country_code)->first();

			$product = Product::where('sku', $item->product_sku)->first();

			if($product)
			{
				$productPrice = $product->getProductPriceByCountry($country->id);

				if($productPrice)
				{
                    if(($item->wp_cv > 0))
                    {
                        $wpCv =  $item->wp_cv;
                    }
                    else
                    {
                        $wpCv = ($item->cv > 0) ? $item->cv/2 : $item->cv;
                    }

                    ProductPrice::where('id', $productPrice->id)->update([
                        'base_cv' => $item->cv,
                        'wp_cv' => $wpCv,
                        'cv1' => $item->cv,
                        'cv2' => $item->cv
                    ]);

					$locations = Location::where('entity_id', $country->entity->id)->get();

					foreach ($locations as $location)
					{
						ProductLocation::updateOrCreate([
							'country_id' => $country->id,
							'entity_id' => $country->entity->id,
							'product_id' => $product->id,
							'location_id' => $location->id
						]);
					}

					ProductActive::updateOrCreate([
						'country_id' => $country->id,
						'product_id' => $product->id,
					],[
						'ibs_active' => 1
					]);

				}
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
