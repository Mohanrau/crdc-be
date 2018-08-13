<?php

use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyConversion;
use App\Models\General\CWSchedule;
use App\Models\Locations\Country;
use Illuminate\Database\Seeder;

class CurrencyConversionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cwSchedules = CWSchedule::get();

        $countryCurrencyIds = Country::active()->pluck('default_currency_id');

        $currencyUSDId = Currency::where('code', 'USD')->first()->id;

        $rates = array(
            91 => 0.23809500,
            112 => 0.02173900,
            25 => 0.68965500,
            125 => 0.68965500,
            138 => 0.03030300,
            149 => 1.00000000,
            63 => 0.12531300,
            141 => 0.02857100,
            35 => 0.12742300,
            67 => 0.0000717139
        );

        foreach ($cwSchedules as $cwSchedule)
        {
            foreach ($countryCurrencyIds as $currencyId)
            {
                CurrencyConversion::updateOrCreate([
                    'from_currency_id' => $currencyId,
                    'to_currency_id' => $currencyUSDId,
                    'cw_id' => $cwSchedule->id
                ],
                [
                    'rate' => $rates[$currencyId]
                ]);
            }
        }
    }
}
