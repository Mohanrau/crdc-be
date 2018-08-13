<?php

use Faker\Generator as Faker;
use Illuminate\Support\Facades\DB;
use App\Models\{
  Bonus\BonusSummary,
  Bonus\BonusWelcomeBonusDetails,
  Bonus\BonusTeamBonusDetails,
  Bonus\BonusMentorBonusDetails,
  Bonus\BonusQuarterlyDividendDetails,
  Locations\Country,
  General\CWSchedule,
  Users\User,
  Bonus\TeamBonusRank,
  Bonus\EnrollmentRank,
  Members\MemberAddress

};

$factory->define(BonusSummary::class, function (Faker $faker) {

    $country = Country::where('active',1)
        ->get()
        ->random(1);

    $user = User::all()
        ->random(1);

    $userAddress = MemberAddress::where('user_id', $user[0]->id)
        ->get();

    if(empty($userAddress[0])){
        $userAddress[0] = new MemberAddress(['address_data'=>NULL]);
    }

    return [
        'country_id' => $country[0]->id,
        'cw_id' => CWSchedule::all()
            ->random()
            ->id,
        'user_id' => $user[0]->id,
        'statement_date' =>  $faker->unique()
            ->dateTimeBetween($startDate = "now", $endDate = "30 days")
            ->format('Y-m-d'),
        'tax_data' => json_encode(
            [
                "tax_company_name" => $faker->name,
                "tax_no" => $faker->numberBetween(1111111,9999999),
                "tax_type" => $faker->randomElement(['Add:GST', 'less:WHT']),
            ]
        ),
        'highest_rank_id' =>  TeamBonusRank::all()
            ->random()
            ->id,
        'effective_rank_id' => TeamBonusRank::all()
            ->random()
            ->id,
        'enrollment_rank_id' => EnrollmentRank::all()
            ->random()
            ->id,
        'address_data' => $userAddress[0]->address_data,
        'welcome_bonus' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'team_bonus' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'team_bonus_diluted' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'mentor_bonus' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'mentor_bonus_diluted' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'quarterly_dividend' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'incentive' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'total_gross_bonus' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'default_currency_id' => $country[0]->default_currency_id,
        'currency_rate' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'total_gross_bonus_local_amount' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'total_net_bonus_payable' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'total_tax_amount' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'diluted_percentage' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999)
    ];
});

$factory->define(BonusWelcomeBonusDetails::class, function (Faker $faker) {

    $bonus = BonusSummary::all()
        ->random(1);

    $user = User::all()
        ->random(1);

    return [
        'bonuses_summary_id' => $bonus[0]->id,
        'sponsor_child_user_id' => $user[0]->id,
        'sponsor_child_depth_level' => $faker->numberBetween(1,5),
        'join_date' => $faker->unique()
            ->dateTimeBetween($startDate = "now", $endDate = "30 days")
            ->format('Y-m-d'),
        'total_local_amount' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'total_local_amount_currency' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 99),
        'total_amount' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'total_amount_currency' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 99),
        'total_usd_amount' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999),
        'nett_usd_amount' => $faker->randomFloat($nbMaxDecimals = 2, $min = 2, $max = 999999)
    ];
});

$factory->define(BonusTeamBonusDetails::class, function (Faker $faker) {

    $bonus = BonusSummary::all()
        ->random(1);

    $user = User::all()
        ->random(1);

    return [
        'bonuses_summary_id' => $bonus[0]->id,
        'placement_child_user_id' => $user[0]->id,
        'gcv' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'optimising_personal_sales' => $faker->randomFloat($nbMaxDecimals = 0, $min = 0, $max = 100),
        'gcv_calculation' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'gcv_bring_forward' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'gcv_bring_forward_position' => $faker->numberBetween(1,2),
        'gcv_leg_group' => $faker->randomElement(['POWER', 'PAY']),
        'gcv_flush' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'gcv_bring_over' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'team_bonus_percentage' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 99),
        'team_bonus_cv' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999)
    ];
});

$factory->define(BonusMentorBonusDetails::class, function (Faker $faker) {

    $bonus = BonusSummary::all()
        ->random(1);

    $user = User::all()
        ->random(1);

    return [
        'bonuses_summary_id' => $bonus[0]->id,
        'sponsor_child_user_id' => $user[0]->id,
        'sponsor_generation_level' => $faker->numberBetween(1,5),
        'team_bonus_cv' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999),
        'mentor_bonus_percentage' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 1),
        'mentor_bonus_cv' => $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 999999)
    ];
});

$factory->define(BonusQuarterlyDividendDetails::class, function (Faker $faker) {

//    $bonus = BonusSummary::all()
//        ->random(1);

    $bonus = DB::table('bonuses_summary')->select('*')
            ->whereNotIn('user_id', function($query){
               $query->select('user_id') ->from('bonus_quarterly_dividend_details');
            })->get();

    return [
        'country_id' => $bonus[0]->country_id,
        'cw_id' => $bonus[0]->cw_id,
        'user_id' => $bonus[0]->user_id,
        'shares' => $faker->randomFloat($nbMaxDecimals = 0, $min = 1, $max = 5)
    ];
});
