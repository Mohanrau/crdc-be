<?php

use App\Interfaces\Dummy\DummyInterface;
use App\Models\Dummy\Dummy;
use App\Models\Locations\Country;
use App\Models\Products\Product;
use Illuminate\Database\Seeder;

class ProductGroupingSeeder extends Seeder
{
    protected $dummyObj;
    /**
     * ProductGroupingSeeder constructor.
     *
     * @param DummyInterface $dummy
     */
    public function __construct(DummyInterface $dummy)
    {
        $this->dummyObj = $dummy;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dummyRecord = [];

        $data = json_decode(file_get_contents('database/seeding/' . "product_grouping.txt"));

        foreach ($data as $item)
        {
            $country = Country::where('code_iso_2', $item->country_code)->first();

            $product = Product::where('sku', $item->product_sku)->first();

            $dummy = Dummy::where('country_id', $country->id)->where('dmy_code', $item->dummy)->first();

            if(!isset($dummyRecord[$item->country_code.'_'.$item->dummy]))
            {
                $dummyRecord[$item->country_code.'_'.$item->dummy] = [
                    'country_id' => $country->id,
                    'dummy_id' => isset($dummy) ? $dummy->id : null,
                    'dmy_code' => $item->dummy,
                    'dmy_name' => $item->dummy,
                    'is_lingerie' => isset($dummy) ? $dummy->is_lingerie : 0,
                    'active' => isset($dummy) ? $dummy->active : 1,
                ];
            }

            $dummyRecord[$item->country_code.'_'.$item->dummy]['dummy_products']['product_ids'][] = $product->id;
        }

        foreach ($dummyRecord as $dummy)
        {
            $this->dummyObj->createOrUpdate($dummy);
        }
    }
}
