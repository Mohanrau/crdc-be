<?php
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
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
            ZoneSeeder::class,
            StateAndCitySeeder::class,
            BankSeeder::class,
            CountryDynamicContentSeeder::class,
            CountryRulesSeeder::class,
            CountryLanguageSeeder::class,
            TaxSeeder::class,

            SettingSeeder::class,
            RunningNumberSeeder::class,
        ]);
    }
}
