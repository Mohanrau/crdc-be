<?php
namespace App\Repositories\Enrollments;

use App\Models\{
    Enrollments\EnrollmentTemp,
    Enrollments\EnrollmentTempTree,
    Enrollments\EnrollmentTypes,
    Locations\Country,
    Masters\MasterData,
    Members\Member,
    Users\User,
    Users\UserType
};
use App\{Events\Enrollments\EnrollUserDone,
    Helpers\Classes\RandomPassword,
    Helpers\Classes\Sms,
    Interfaces\Enrollments\EnrollmentInterface,
    Interfaces\General\CwSchedulesInterface,
    Interfaces\Members\MemberInterface,
    Interfaces\Members\MemberTreeInterface,
    Interfaces\Sales\SaleInterface,
    Interfaces\Settings\SettingsInterface,
    Interfaces\Users\UserInterface,
    Mail\EnrollSucceeded,
    Mail\EnrollmentTempNotify,
    Repositories\BaseRepository,
    Models\Sales\Sale};
use Illuminate\{
    Support\Facades\Auth,
    Support\Facades\Log,
    Support\Facades\Mail
};

class EnrollmentRepository extends BaseRepository implements EnrollmentInterface
{
    private
        $enrollmentTempObj,
        $enrollmentTypesObj,
        $enrollmentTempTreeObj,
        $saleRepositoryObj,
        $userObj,
        $userTypeObj,
        $masterDataObj,
        $memberRepository,
        $smsObj,
        $countryObj,
        $randomPasswordObj,
        $cwScheduleObj,
        $settingRepositoryObj,
        $userRepositoryObj,
        $memberTreeRepositoryObj,
        $memberPreferredContactCodes,
        $enrollmentTypesCodes,
        $enrollmentStatuesCodes
    ;

    /**
     * EnrollmentRepository constructor.
     *
     * @param EnrollmentTemp $enrollmentTemp
     * @param EnrollmentTypes $enrollmentTypes
     * @param EnrollmentTempTree $enrollmentTempTree
     * @param Member $model
     * @param User $user
     * @param UserType $userType
     * @param MasterData $masterData
     * @param Sms $sms
     * @param Country $country
     * @param RandomPassword $randomPassword
     * @param MemberInterface $memberInterface
     * @param SaleInterface $saleInterface
     * @param CwSchedulesInterface $cwSchedules
     * @param SettingsInterface $settingsInterface
     * @param UserInterface $userInterface
     * @param MemberTreeInterface $memberTreeInterface
     */
    public function __construct(
        EnrollmentTemp $enrollmentTemp,
        EnrollmentTypes $enrollmentTypes,
        EnrollmentTempTree $enrollmentTempTree,
        Member $model,
        User $user,
        UserType $userType,
        MasterData $masterData,
        Sms $sms,
        Country $country,
        RandomPassword $randomPassword,
        MemberInterface $memberInterface,
        SaleInterface $saleInterface,
        CwSchedulesInterface $cwSchedules,
        SettingsInterface $settingsInterface,
        UserInterface $userInterface,
        MemberTreeInterface $memberTreeInterface
    )
    {
        parent::__construct($model);

        $this->enrollmentTempObj = $enrollmentTemp;

        $this->enrollmentTypesObj = $enrollmentTypes;

        $this->enrollmentTempTreeObj = $enrollmentTempTree;

        $this->saleRepositoryObj = $saleInterface;

        $this->userObj = $user;

        $this->userTypeObj = $userType;

        $this->masterDataObj = $masterData;

        $this->smsObj = $sms;

        $this->countryObj = $country;

        $this->randomPasswordObj = $randomPassword;

        $this->cwScheduleObj = $cwSchedules;

        $this->memberRepository = $memberInterface;

        $this->settingRepositoryObj = $settingsInterface;

        $this->userRepositoryObj = $userInterface;

        $this->memberTreeRepositoryObj = $memberTreeInterface;

        $this->memberPreferredContactCodes = config('mappings.preferred_contact');

        $this->enrollmentTypesCodes = config('mappings.enrollment_types');

        $this->enrollmentStatuesCodes = config('mappings.enrollment_status');
    }

    /**
     * create new enrollment temp save
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $uniqueSmsCode = uniqid();

        $country = $this->countryObj->find($data['region']['value']);

        $memberData = $data['member_data'];

        $enrollmentTemp = $this->enrollmentTempObj
            ->where('unique_id', Auth::user()->identifier()->identifier                                                                       )
            ->first();

        if (is_null($enrollmentTemp)){
            $enrollmentTemp = $this->enrollmentTempObj->create([
                'unique_id' => Auth::user()->identifier()->identifier,
                'sms_code' => $uniqueSmsCode,
                'temp_data' => json_encode($data),
                'status_id' => $this->masterDataObj->getIdByTitle(
                    $this->enrollmentStatuesCodes['pending'],
                    'enrollment_status'
                )
            ]);

            //check the preferred contact id------------------
            //send sms----------------------------------------
            $this->sendTempDataNotification($country, $memberData, $uniqueSmsCode);

            //update guest table sms code--------------------
            Auth::user()->identifier()->guest()->update([
                'login_code' => $uniqueSmsCode
            ]);
        }else{
            $enrollmentTemp->update([
                'unique_id' => Auth::user()->identifier()->identifier,
                'temp_data' => json_encode($data, true)
            ]);
        }

        //process the enrollment temp placement----------
        $this->processTempPlacement($data);

        return $enrollmentTemp;
    }

    /**
     * process back office enrollment
     *
     * @param array $data
     * @return mixed|void
     */
    public function createBackOfficeEnrollment(array $data)
    {
        //TODO
        //insert data to enrollment temp data.......
    }

    /**
     * process enrollment for the guest user
     *
     * @param Sale $sale
     * @return mixed
     */
    public function processEnrollment(Sale $sale)
    {
        //get guest temp data - enrollment temp data
        $enrollmentTemp = $this->enrollmentTempObj
            ->where('sale_id', $sale->id)
            ->first();

        $enrollmentData = json_decode($enrollmentTemp->temp_data, true);

        $memberData = array_merge($enrollmentData['member_data'], [
            'enrollment_type' => $enrollmentData['enrolment_type']
        ]);

        //country id
        $countryId = $enrollmentData['region']['value'];

        $country = $this->countryObj->find($countryId);

        $preferredContactId = $this->masterDataObj
            ->find($memberData['contact_info']['preferred_contact_id']);

        //generate temp password
        $password = $this->randomPasswordObj->generate(8);

        //create user---------------------------------------------------------------------------------------------------
        if (is_null($enrollmentTemp->user_id))
        {
            $iboMemberId = $this->settingRepositoryObj
                ->getRunningNumber('ibo_member_id', $countryId);

            //set the preferred contact
            if (strtolower($preferredContactId->title) == strtolower($this->memberPreferredContactCodes['email'])){
                $email = $memberData['contact_info']['email'];

                $mobile = '';
            }else{
                $email = 'ek_'.$iboMemberId.'@elken.com';

                $mobile = $country->call_code . $memberData['contact_info']['mobile_1_num'];
            }

            //create user---------------------------------------------
            $user = $this->userRepositoryObj->create([
                'name' => $memberData['details']['name'],
                'old_member_id' => $iboMemberId,
                'mobile' => $mobile,
                'email' => $email,
                'password' => bcrypt($password),
                'active' => 1,
                'login_count' => 0
            ]);

            //update sale user id to set it = the newly created user----------------
            $sale->update(['user_id' => $user->id]);

            //update enrollment temp data to set user_id
            $enrollmentTemp->update([
                'user_id' => $user->id
            ]);

            //attach user to member user type------------------------
            $userTypeId = $this->userTypeObj
                ->where('name', config('mappings.user_types.member'))
                ->pluck('id')
                ->toArray();

            $user->userType()->attach($userTypeId);
        }
        else
        {
            $user = $this->userObj->find($enrollmentTemp->user_id);

            $user->update([
                'password' => bcrypt($password)
            ]);
        }

        //member section------------------------------------------------------------------------------------------------
        if (is_null($enrollmentTemp->member_id)){
            $member = $this->memberRepository->create($user, $countryId,$memberData);

            //update enrollment temp data to set member_id
            $enrollmentTemp->update([
                'member_id' => $member->id
            ]);
        }

        $this->saleRepositoryObj->createAmpCvAllocations($sale->id);

        $this->saleRepositoryObj->insertPurchaseCv($sale->id);

        $this->saleRepositoryObj->saleAccumulationCalculation($sale->user_id);

        //hand the placement -------------------------------------------------------------------------------------------
        $this->processPlacement($enrollmentTemp->unique_id, $user->id);

        //update temp data to set status is completed-------------------------------------------------------------------
        $enrollmentTemp->update([
           'status_id' =>  $this->masterDataObj->getIdByTitle(
               $this->enrollmentStatuesCodes['completed'],
               'enrollment_status'
           )
        ]);

        //notify user by email if his preferred contact is email--------------------------------------------------------
        $this->sendNotification($user, $preferredContactId, $password);

        //notify enrollment done----------------------------------------------------------------------------------------
        event(new EnrollUserDone($enrollmentTemp->unique_id));

        return $user;
    }

    /**
     * get enrollment temp data for the given sms code
     *
     * @param string $smsCode
     * @return mixed
     */
    public function getEnrollmentTempData(string $smsCode)
    {
        return $this->enrollmentTempObj
            ->where('sms_code', $smsCode)
            ->first();
    }

    /**
     * get enrollment types by country id
     *
     * @param int $countryId
     * @return mixed
     */
    public function getEnrollmentsTypes(int $countryId)
    {
        return $this->countryObj
            ->findOrFail($countryId)
            ->enrollmentTypes()
            ->get();
    }

    /**
     * Update enrollment temp data sales details
     *
     * @param int $sale_id
     */
    public function updateEnrollmentTempSale(int $sale_id) 
    {
        if (
            $enrollmentTempData = $this->enrollmentTempObj
            ->where('unique_id', Auth::user()->identifier()->identifier                                                                       )
            ->first()
        ) {
            $enrollmentTempData->sale_id = $sale_id;

            $enrollmentTempData->save();
        }
    }

    /**
     * send enrollment temp data saved notification to resume
     *
     * @param $country
     * @param $memberData
     * @param string $smsCode
     */
    private function sendTempDataNotification($country, $memberData, string $smsCode)
    {
        $preferredContactId = $this->masterDataObj
            ->find($memberData['contact_info']['preferred_contact_id']);

        if (strtolower($preferredContactId->title) == strtolower($this->memberPreferredContactCodes['email']))
        {
            Mail::to($memberData['contact_info']['email'])->send(new EnrollmentTempNotify($memberData['details']['name'], $smsCode));
        }
        else
        {
            $sendResponse = $this->smsObj->sendSMS(
                $country->call_code. $memberData['contact_info']['mobile_1_num'],
                __('message.mobile.unique_id', ['unique_id' => $smsCode]));

            if($sendResponse['response_code'] !== 0){
                //log critical issue
                Log::critical('Enrollment Sms Send Failed', $sendResponse);
            }
        }
    }

    /**
     * send notification for the user after enrollment done
     *
     * @param User $user
     * @param $preferredContact
     * @param string $password
     */
    private function sendNotification(User $user, $preferredContact, string $password)
    {
        if (strtolower($preferredContact->title) == strtolower($this->memberPreferredContactCodes['email']))
        {
            Mail::to($user->email)->send(new EnrollSucceeded($user, $password));
        }
        else
        {
            $sendResponse = $this->smsObj->sendSMS(
                $user->mobile,
                __('enrollment.sms.content',
                    [
                        'url' => config('app.member_url'),
                        'password' => $password,
                        'iboMemberId' => $user->old_member_id
                    ]
                )
            );

            if($sendResponse['response_code'] !== 0){
                Log::critical('Enrollment Sms Send Failed', $sendResponse);
            }
        }
    }

    /**
     * process placement for enrollment TEMP
     *
     * @param array $data
     */
    private function processTempPlacement(array $data)
    {
        //check if placement set is auto
        if ($data['placement']['placement_position'] == 0){
            $this->memberTreeRepositoryObj->insertEnrollmentTempTree(
                Auth::user()->identifier()->identifier,
                ($data['sponsor_user_id'] != "") ? $data['sponsor_user_id'] : null
            );
        }else{
            $this->memberTreeRepositoryObj->insertEnrollmentTempTree(
                Auth::user()->identifier()->identifier,
                ($data['sponsor_user_id'] != "") ? $data['sponsor_user_id'] : null,
                ($data['placement']['placement_member_user_id'] != "") ?
                    $data['placement']['placement_member_user_id']
                : null,
                $data['placement']['placement_position']
            );
        }
    }

    /**
     * process placement for enrollment
     *
     * @param string $uniqueId
     * @param int $userId
     */
    private function processPlacement(string $uniqueId, int $userId)
    {
        $enrollmentTempTree = $this->enrollmentTempTreeObj->where('unique_id', $uniqueId)->first();

        if (!is_null($enrollmentTempTree)){
            $enrollmentTempTree->update([
                'user_id' => $userId
            ]);

            //check if sponsor known then process enrollment
            if (!is_null($enrollmentTempTree->sponsor_user_id)){
                $this->memberTreeRepositoryObj->insertToMemberTreeFromTemp(
                    $enrollmentTempTree->unique_id,
                    $userId
                );
            }
        }else{
            Log::critical('Enrollment Member Tree Placement Failed, user id = ', $userId);
        }
    }
}