<?php

use Illuminate\Database\Seeder;

class BonusStatementSeeder extends Seeder
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
            factory(App\Models\Bonus\BonusSummary::class)->create();
            factory(App\Models\Bonus\BonusWelcomeBonusDetails::class)->create();
            factory(App\Models\Bonus\BonusWelcomeBonusDetails::class)->create();
            factory(App\Models\Bonus\BonusTeamBonusDetails::class)->create();
            factory(App\Models\Bonus\BonusTeamBonusDetails::class)->create();
            factory(App\Models\Bonus\BonusMentorBonusDetails::class)->create();
            factory(App\Models\Bonus\BonusMentorBonusDetails::class)->create();
            factory(App\Models\Bonus\BonusQuarterlyDividendDetails::class)->create();
        }
    }
}
