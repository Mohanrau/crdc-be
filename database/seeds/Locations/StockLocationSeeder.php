<?php

use Illuminate\Database\Seeder;
use App\Models\Locations\
{
    StockLocation,
    Country
};

class StockLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $locations = [
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK ONE IPOH',
                'code' => 'IPEK01',
                'auto_release' => 1,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK ONE JOHOR BAHRU',
                'code' => 'JBEK01',
                'auto_release' => 1,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK ONE KUCHING',
                'code' => 'KGEK01',
                'auto_release' => 1,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK ONE PENANG',
                'code' => 'PGEK01',
                'auto_release' => 1,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK ONE KUANTAN',
                'code' => 'KNEK01',
                'auto_release' => 1,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK ONE KOTA KINABALU',
                'code' => 'KKEK01',
                'auto_release' => 1,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK ONE KOTA BHARU',
                'code' => 'KBEK01',
                'auto_release' => 1,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK ONE SEBERANG JAYA',
                'code' => 'SJEK01',
                'auto_release' => 1,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EDC',
                'code' => 'DCWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EDC',
                'code' => 'DCGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EDC',
                'code' => 'DCWH02',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EDC',
                'code' => 'DCGRB2',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK One - HQ',
                'code' => 'HQEK01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'EK One - WH',
                'code' => 'HQWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Penang Warehouse',
                'code' => 'PGWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Penang Damage Location',
                'code' => 'PGGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Seberang Jaya Warehouse',
                'code' => 'SJWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Seberang Jaya Damage Location',
                'code' => 'SJGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Ipoh Warehouse',
                'code' => 'IPWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Ipoh Damage Location',
                'code' => 'IPGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Kuantan Warehouse',
                'code' => 'KNWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Kuantan Damage Location',
                'code' => 'KNGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Kota Bharu Warehouse',
                'code' => 'KBWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Kota Bharu Damage Location',
                'code' => 'KBGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Johor Baru Warehouse',
                'code' => 'JBWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Johor Baru Damage Location',
                'code' => 'JBGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Kuching Warehouse',
                'code' => 'KGWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Kuching Damage Location',
                'code' => 'KGGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Kota Kinabalu Warehouse',
                'code' => 'KKWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','MY')->first()->id,
                'name' => 'Kota Kinabalu Damage Location',
                'code' => 'KKGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','SG')->first()->id,
                'name' => 'Singapore Warehouse',
                'code' => 'SGWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','SG')->first()->id,
                'name' => 'Singapore Damage Location',
                'code' => 'SGGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','BN')->first()->id,
                'name' => 'Brunei Warehouse',
                'code' => 'BNWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','BN')->first()->id,
                'name' => 'Brunei Damage Location',
                'code' => 'BNGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','BN')->first()->id,
                'name' => 'Brunei 3rd Party WH',
                'code' => 'BN3PL1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','HK')->first()->id,
                'name' => 'Hong Kong Warehouse',
                'code' => 'HKWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','HK')->first()->id,
                'name' => 'Hong Kong Damage Location',
                'code' => 'HKGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','TH')->first()->id,
                'name' => 'Thailand Warehouse',
                'code' => 'THWH01',
                'auto_release' => 0,
                'active' => 1
            ],            [
                'country_id' => Country::select('id')->where('code_iso_2','TH')->first()->id,
                'name' => 'Thailand Damage Location',
                'code' => 'THGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','TH')->first()->id,
                'name' => 'Thailand 3rd Party WH',
                'code' => 'TH3PL1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','TH')->first()->id,
                'name' => 'Thailand Event Location',
                'code' => 'THEVT1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','KH')->first()->id,
                'name' => 'Cambodia Warehouse',
                'code' => 'KHWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','KH')->first()->id,
                'name' => 'Cambodia Damage Location',
                'code' => 'KHGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','KH')->first()->id,
                'name' => 'Cambodia Event Location',
                'code' => 'KHEVT1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','PH')->first()->id,
                'name' => 'Philippines Warehouse',
                'code' => 'PHWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','PH')->first()->id,
                'name' => 'Philippines Damage Location',
                'code' => 'PHGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','PH')->first()->id,
                'name' => 'Philippines Event Location',
                'code' => 'PHEVT1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','TW')->first()->id,
                'name' => 'Tai Chung Warehouse',
                'code' => 'TWWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','TW')->first()->id,
                'name' => 'Tai Chung Damage Location',
                'code' => 'TWGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','TW')->first()->id,
                'name' => 'Kao Hsiung Warehouse',
                'code' => 'TWWH02',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','TW')->first()->id,
                'name' => 'Kao Hsiung Damage Location',
                'code' => 'TWGRB2',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','ID')->first()->id,
                'name' => 'Jakarta Dispensary',
                'code' => 'IDWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','ID')->first()->id,
                'name' => 'Jakarta Damage Location',
                'code' => 'IDGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','ID')->first()->id,
                'name' => 'Jakarta Distribution',
                'code' => 'IDWH02',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','ID')->first()->id,
                'name' => 'Jakarta Distribution Damage Location',
                'code' => 'IDGRB2',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','ID')->first()->id,
                'name' => 'Medan Warehouse',
                'code' => 'IDWH03',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','ID')->first()->id,
                'name' => 'Medan Damage Location',
                'code' => 'IDGRB3',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','VN')->first()->id,
                'name' => 'Ho Chi Minh Warehouse',
                'code' => 'VNWH01',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','VN')->first()->id,
                'name' => 'Ho Chi Minh Damage Location',
                'code' => 'VNGRB1',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','VN')->first()->id,
                'name' => 'Hanoi Warehouse',
                'code' => 'VNWH02',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','VN')->first()->id,
                'name' => 'Hanoi Damage Location',
                'code' => 'VNGRB2',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','VN')->first()->id,
                'name' => 'Danang Warehouse',
                'code' => 'VNWH03',
                'auto_release' => 0,
                'active' => 1
            ],
            [
                'country_id' => Country::select('id')->where('code_iso_2','VN')->first()->id,
                'name' => 'Danang Damage Location',
                'code' => 'VNGRB3',
                'auto_release' => 0,
                'active' => 1
            ]
        ];

        foreach ($locations as $location)
        {
            StockLocation::updateOrCreate($location);
        }
    }
}
