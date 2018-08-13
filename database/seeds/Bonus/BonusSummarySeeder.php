<?php

use Illuminate\Database\Seeder;
use App\Models\{
    Bonus\BonusSummary,
    Locations\Country,
    General\CWSchedule,
    Users\User,
    Bonus\TeamBonusRank,
    Bonus\EnrollmentRank,
    Members\MemberAddress,
    Currency\Currency
};


class BonusSummarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/bonus/'."bonuses_summary.txt"));

        foreach ($data as $item)
        {
            if (empty(User::where('old_member_id', $item->member_id)->first())){
                continue;
            }

            $taxData = [
                "tax_company_name" => $item->tax_company_name,
                "tax_no" => $item->tax_no,
                "tax_type" => $item->tax_type,
                "tax_rate" => $item->tax_rate
            ];

            BonusSummary::updateOrCreate(
                [
                    "country_id" => Country::where('code_iso_2',$item->country_code)
                        ->first()
                        ->id,
                    "cw_id" => CWSchedule::where('cw_name', $item->cw)
                        ->first()
                        ->id,
                    "user_id" => User::where('old_member_id', $item->member_id)
                        ->first()
                        ->id,
                    "statement_date" => $item->statementDate,
                    "tax_data" => json_encode($taxData),
                    "highest_rank_id" => TeamBonusRank::where('rank_code',$item->highest_rank)
                        ->first()
                        ->id,
                    "effective_rank_id" => TeamBonusRank::where('rank_code',$item->effective_rank)
                        ->first()
                        ->id,
                    "enrollment_rank_id" => ($item->enrollment_rank !=null) ?
                        EnrollmentRank::where('rank_code',$item->enrollment_rank)
                            ->first()
                            ->id :
                        EnrollmentRank::where('rank_code','MEMBER')
                            ->first()
                            ->id,
                    "address_data" => $memberAddr =
                        empty(MemberAddress::where('user_id', User::where('old_member_id', $item->member_id)->first()->id)->first()->address_data) ?
                            new MemberAddress (['address_data'=>'']) :
                            (MemberAddress::where('user_id', User::where('old_member_id', $item->member_id)->first()->id)->first()->address_data),
                    "welcome_bonus" => $item->welcomeBonus,
                    "team_bonus" => $item->originalTeamBonus,
                    "team_bonus_diluted" => $item->teamBonus,
                    "mentor_bonus" => $item->originalMentorBonus,
                    "mentor_bonus_diluted" => $item->mentorBonus,
                    "quarterly_dividend" => $item->quarterlyDividend,
                    "incentive" => $item->incentive,
                    "total_gross_bonus" => $item->total_gross_bonus,
                    "default_currency_id" => Currency::where('code',$item->currency_code)
                        ->first()
                        ->id,
                    "currency_rate" => $item->currency_rate,
                    "total_gross_bonus_local_amount" => $item->total_gross_bonus_local_amount,
                    "total_net_bonus_payable" => $item->total_net_bonus_payable,
                    "total_tax_amount" => $item->total_tax_amount,
                    "diluted_percentage" => $item->diluted_percentage
                ]
            );
        }
    }
}
