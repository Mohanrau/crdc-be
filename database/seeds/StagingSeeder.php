<?php

use Illuminate\Database\Seeder;
use App\Models\Members\MemberTree;
use App\Models\Members\Member;

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

            //Cw data---------------------------------------------------------------------------------------------------
            CWScheduleSeeder::class,
            CWDividendSeeder::class,

            TeamBonusRankSeeder::class,
            EnrollmentRankSeeder::class,

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

            //Products data --------------------------------------------------------------------------------------------
            ProductCategorySeeder::class,
            ProductSeeder::class,
            ProductPriceSeeder::class,

            //member tree data------------------------------------------------------------------------------------------
            MemberTreeSeeder::class,
            MemberTreePyramidAlgoritmSeeder::class,
            MemberSeeder::class,
            TeamBonusSeeder::class,
            MemberRankTransactionSeeder::class,
            StockLocationSeeder::class,
            LocationStockLocationSeeder::class,
            StockLocationCitySeeder::class,

            //workflow data---------------------------------------------------------------------------------------------
            WorkflowMasterSeeder::class,

            //Enrollments seeders---------------------------------------------------------------------------------------
            EnrollmentTypesSeeder::class,
            EnrollmentCountriesSeeder::class,

            //default roles seeder--------------------------------------------------------------------------------------
            MemberRoleSeeder::class,
            StockistRoleSeeder::class,
        ]);
    }
}
