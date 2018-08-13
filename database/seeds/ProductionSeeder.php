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

            //Cw data---------------------------------------------------------------------------------------------------
            CWScheduleSeeder::class,
            CWDividendSeeder::class,

            TeamBonusRankSeeder::class,
            EnrollmentRankSeeder::class,

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
            PaymentModeSeeder::class,

            //Enrollments seeders---------------------------------------------------------------------------------------
            EnrollmentTypesSeeder::class,
            EnrollmentCountriesSeeder::class,

            //Workflow seeders---------------------------------------------------------------------------------------
            WorkflowMasterSeeder::class,

            //Enrollments seeders---------------------------------------------------------------------------------------
            EnrollmentTypesSeeder::class,
            EnrollmentCountriesSeeder::class,

            //default roles seeder--------------------------------------------------------------------------------------
            MemberRoleSeeder::class,
            StockistRoleSeeder::class,

            //
        ]);
    }
}
