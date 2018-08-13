<?php
use Illuminate\Database\Seeder;
use App\Models\Members\Member;
use App\Models\General\CWSchedule;
use App\Models\Bonus\EnrollmentRank;
use App\Models\Locations\Country;
use App\Models\Users\User;
use App\Models\Masters\Master;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."members.txt"));

        foreach ($data as $item) {

            $user = User::where('old_member_id', '=', $item->member_id)->first();

            if(empty($user)){
                continue;
            }

            $item->country = $item->country == 'ISG' ? 'SG' : $item->country;

            $item->country = $item->country == 'IBN' ? 'BN' : $item->country;

            $country = Country::where('code_iso_2',$item->country)->first();

            $item->nationality = $item->nationality == 'MALAYSIA' ? 'MY' : $item->nationality;

            $item->nationality = $item->nationality == 'ISG' ? 'SG' : $item->nationality;

            $item->nationality = $item->nationality == 'IBN' ? 'BN' : $item->nationality;

            if ($item->nationality === '0' || $item->nationality === '' || strlen($item->nationality) >3)
            {
                if ($item->nationality1 === '0' || $item->nationality1 === '' || strlen($item->nationality1) >3)
                {
                    $nationality = $country;
                }
                else
                {
                    $item->nationality1 = $item->nationality1 == 'ISG' ? 'SG' : $item->nationality1;

                    $item->nationality1 = $item->nationality1 == 'IBN' ? 'BN' : $item->nationality1;

                    $nationality = Country::where('code_iso_2',$item->nationality1)->first();
                }
            }
            else
            {
                $nationality = Country::where('code_iso_2',$item->nationality)->first();
            }


            $enrollmentRank = ($item->enrollment_rank !=null) ?
                EnrollmentRank::where('rank_code',$item->enrollment_rank)->first() :
                EnrollmentRank::where('rank_code','MEMBER')->first();


            if($item->join_date != null)
            {
                $cw = CWSchedule::where('date_from','<=',$item->join_date)
                    ->where('date_to','>=',$item->join_date)->first();

                if($cw == null)
                {
                    $cw = new CWSchedule(['cw_name' => '1900-00']);
                }
            }
            else
            {
                $cw = new CWSchedule(['cw_name' => '1900-00']);
            }

            $master =Master::where('key','=','ic_passport_type')->first();

            $item->ic_pass_type = ($item->ic_pass_type == '0') ? 'NRIC' : $item->ic_pass_type;

            $master->masterData = $master->masterData()->where('title',$item->ic_pass_type)->get();

            $icPassTypeMasterId =$master->masterData[0]->id;

            $item->ic_pass_type_number = ($item->ic_pass_type_number == null) ? ' ' : $item->ic_pass_type_number;

            $master =Master::where('key','=','member_status')->first();

            $master->masterData = $master->masterData()->where('title',$item->active)->get();

            $statusMasterId = (!empty($master->masterData)) ? $master->MasterData[0]->id : 0;

            Member::updateorCreate(
                [
                    'user_id' => $user->id,
                    'country_id' =>$country->id,
                    'name' =>$item->member_name,
                    'translated_name' => $item->member_name,
                    'nationality_id' =>$nationality->id,
                    'ic_pass_type_id' =>$icPassTypeMasterId,
                    'active_until_cw_id' => NULL,
                    'ic_passport_number' =>md5($item->ic_pass_type_number),
                    'date_of_birth' =>$item->date_of_birth,
                    'cw' =>$cw->cw_name,
                    'join_date' =>$item->join_date,
                    'expiry_date' =>$item->exp_date,
                    'personal_sales_cv' =>$item->personal_sales_cv,
                    'personal_sales_cv_percentage' =>$item->personal_sales_cv_percentage,
                    'effective_rank_id' =>$item->effective_rank,
                    'highest_rank_id' =>$item->highest_rank,
                    'enrollment_rank_id' =>$enrollmentRank->id,
                    'tin_no_taiwan' => NULL,
                    'tin_no_philippines' => NULL,
                    'enroll_from_received' => 0,
                    'ic_pass_verified' => 0,
                    'defer_bonus_commission' => 0,
                    'defer_reason_id' => NULL,
                    'bank_type' => NULL,
                    'status_id' =>$statusMasterId,
                    "created_by" => NULL,
                    "updated_by" => NULL,
                    "created_at" => NULL,
                    "updated_at" => NULL
                ]
            );
        }
    }
}
