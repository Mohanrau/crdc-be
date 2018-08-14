<?php

use Illuminate\Database\Seeder;

class StagingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            LanguageSeeder::class,

            //Roles and permissions-------------------------------------------------------------------------------------
            UserTypesSeeder::class,
            OperationSeeder::class,
            ModuleSeeder::class,

            //master data ----------------------------------------------------------------------------------------------
            MasterSeeder::class,

            //location data---------------------------------------------------------------------------------------------
            CurrencySeeder::class,
            LocationTypesSeeder::class,
            CountryAndEntitySeeder::class,
            StateAndCitySeeder::class,
            BankSeeder::class,
            CountryDynamicContentSeeder::class,
            SettingSeeder::class,
            RunningNumberSeeder::class,
            PaymentModeSeeder::class,
        ]);
    }
}
