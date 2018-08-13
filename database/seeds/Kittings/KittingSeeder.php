<?php

use App\Models\{
    Kitting\Kitting,
    Kitting\KittingGeneralSetting,
    Kitting\KittingPrice,
    Kitting\KittingProduct,
    Locations\Country,
    Locations\Location,
    Masters\Master,
    Products\Product,
    Products\ProductPrice
};
use Illuminate\Database\Seeder;

class KittingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $productNotInDB = [];

        $saleTypes = [
            "AMP" => 'Auto-maintenance',
            "POP",
            "WP-Platinum",
            "WP-Gold",
            "WP-Silver",
            "WP-Diamond",
            "FPP",
            ""
        ];

        $data = json_decode(file_get_contents('database/seeding/' . "kitting.txt"));

        foreach ($data as $item)
        {
            $product = Product::where('sku', $item->product_sku)->first();

            if(isset($product))
            {
                $country = Country::where('code_iso_2',$item->country_code)->first();

                $tax = (new Country)->countryTax($country->id);

                $locations = Location::where('entity_id', $country->entity->id);

                $kitting = Kitting::updateOrCreate(
                    [
                        "country_id" => $country->id,
                        "code" => $item->kitting_code
                    ],
                    [
                        "name" => $item->kitting_name,
                        "is_esac" => $item->is_esac
                    ]
                );

                if($tax->rate > 0 and ($item->gmp_price_gst == $item->nmp_price))
                {
                    $percent = ($tax->rate/100) + 1;

                    $nmp = round($item->gmp_price_gst/$percent, 2);
                }
                else
                {
                    $nmp = $item->nmp_price;
                }

                KittingPrice::updateOrCreate(
                    [
                        'kitting_id' => $kitting->id,
                        'currency_id' => $country->default_currency_id
                    ],
                    [
                        'gmp_price_gst' => $item->gmp_price_gst,
                        'rp_price' => 0,
                        'rp_price_gst' => 0,
                        'nmp_price' => $nmp,
                        'effective_date' => empty($item->start_date) ? '2018-08-01' : date('Y-m-d', strtotime($item->start_date)),
                        'expiry_date' => '2099-12-31',
                        'base_cv' => $item->base_cv,
                        'wp_cv' => ($item->wp_cv == 0) ? $item->base_cv : $item->wp_cv,
                        'cv1' => ($item->amp_cv == 0) ? $item->base_cv : $item->amp_cv,
                        'cv2' => (!isset($item->cv2) or $item->cv2 == 0) ? $item->base_cv : $item->cv2,
                        'cv3' => (!isset($item->cv3) or $item->cv3 == 0) ? $item->base_cv : $item->cv3,
                        'cv4' => (!isset($item->cv4) or $item->cv4 == 0) ? $item->base_cv : $item->cv4,
                        'cv5' => (!isset($item->cv5) or $item->cv5 == 0) ? $item->base_cv : $item->cv5,
                        'cv6' => (!isset($item->cv6) or $item->cv6 == 0) ? $item->base_cv : $item->cv6,
                        'active' => 1
                    ]
                );

                if($item->Qty == 0)
                {
                    KittingProduct::updateOrCreate(
                        [
                            'kitting_id' => $kitting->id,
                            'product_id' => $product->id,
                            'quantity' => $item->Qty
                        ],
                        [
                            'foc_qty' => $item->FOC
                        ]
                    );
                }

                if($item->FOC == 0)
                {
                    KittingProduct::updateOrCreate(
                        [
                            'kitting_id' => $kitting->id,
                            'product_id' => $product->id,
                            'foc_qty' => $item->FOC
                        ],
                        [
                            'quantity' => $item->Qty
                        ]
                    );
                }


                Kitting::find($kitting->id)->kittingLocations()->syncWithoutDetaching(
                    $locations->pluck('id')->toArray()
                );
//
//                TODO: Set this for all the kitting
//                //general sale type
//                KittingGeneralSetting::updateOrCreate(
//                    [
//                        'kitting_id' => $kitting->id,
//                        'master_id' => Master::where('key', 'sale_types')->first()->id,
//                        'master_data_id' => (new App\Models\Masters\MasterData)->getIdByTitle($saleTypes[$item->sale_types], 'sale_types')
//                    ]
//                );

                //general cv_config
                $cvConfig = Master::where('key', 'cv_config')->first();

                foreach ($cvConfig->masterData as $masterData)
                {
                    KittingGeneralSetting::updateOrCreate(
                        [
                            'kitting_id' => $kitting->id,
                            'master_id' => $cvConfig->id,
                            'master_data_id' => $masterData->id
                        ]
                    );
                }

                //general product_additional_requirements
                $additionalRequirements = Master::where('key', 'product_additional_requirements')->first();

                foreach ($additionalRequirements->masterData as $masterData)
                {
                    KittingGeneralSetting::where([
                        'kitting_id' => $kitting->id,
                        'master_id' => $additionalRequirements->id,
                        'master_data_id' => $masterData->id
                    ])->delete();
                }

            }
            else
            {
                $productNotInDB[] = $item;
            }
        }

        $kittingData = Kitting::whereIn('code', collect($data)->unique('kitting_code')->pluck('kitting_code'))->get();

        foreach ($kittingData as $kitting)
        {
            $active = 1;

            foreach($kitting->kittingProducts($kitting->country_id)->get() as $kittingProduct)
            {
                if (is_null($kittingProduct->product->productPricesLatest))
                {
                    $active = 0;
                }
            }

            Kitting::where('id', $kitting->id)->update([
                'active' => $active
            ]);
        }

        foreach ($productNotInDB as $product)
        {
            Kitting::where('code', $product->kitting_code)->update([
                'active' => 0
            ]);
        }

//        TODO: this is for testing purpose. Need to remove once done.
        \Illuminate\Support\Facades\Log::info(json_encode($productNotInDB));
    }
}
