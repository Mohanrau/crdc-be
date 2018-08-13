<?php

use Illuminate\Database\Seeder;
use App\Models\
{
    General\CWSchedule,
    Members\MemberRankTransaction,
    Locations\Country,
    Users\User,
    Bonus\EnrollmentRank,
    Bonus\TeamBonusRank
};
//TODO - to be confirmed which new table for member rank transaction seeding.
//TODO - or to remove this seeder
class MemberRankTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."member_rank_transaction.txt"));

        foreach ($data as $item)
        {
            $cw = CWSchedule::where('cw_name', $item->f_batch_no)->first();

            $enrollmentRank =EnrollmentRank::where('rank_code',$item->f_enrollment)->first();

            $highestRank = TeamBonusRank::where('rank_code',$item->highest_rank)->first();

            $item->f_company_code = $item->f_company_code == 'ISG' ? 'SG' : $item->f_company_code;

            $item->f_company_code = $item->f_company_code == 'IBN' ? 'BN' : $item->f_company_code;

            $country = Country::where('code_iso_2',$item->f_company_code)->first();

            $user = User::where('old_member_id', '=', $item->MemberCode)->first();

            if(!empty($highestRank) and !empty($enrollmentRank))
            {
                MemberRankTransaction::updateOrCreate(
                    [
                        "country_id" =>$country->id,
                        "user_id"=> trim($user->id),
                        "cw_id"=> $cw->id,
                        "enrollment_rank_id"=> $enrollmentRank->id,
                        "highest_rank_id"=> $highestRank->id,
                        'case_reference_number' =>$item->f_doc_no,
                        "type" => $item->f_doc_subtype,
                        "created_at" => $item->DocDate,
                        "updated_at" => $item->DocDate

                    ]
                );
            }

        }
    }
}
