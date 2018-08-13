<?php

use Illuminate\Database\Seeder;
use App\Models\Sales\Sale;
use App\Models\Sales\SaleDetail;
use App\Models\General\CWSchedule;
use App\Models\Masters\Master;
use App\Models\Locations\Country;
use App\Models\Users\User;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $limit = 10;

        for ($i = 0; $i < $limit; $i++) {
            factory(App\Models\Sales\Sale::class)->create();
            factory(App\Models\Sales\SaleProduct::class)->create();
            factory(App\Models\Invoices\Invoice::class)->create();
        }
    }
}
