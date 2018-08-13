<?php
namespace App\Repositories\Members;

use Carbon\Carbon;
use DateTime;
use App\Models\Members\{
    Member, MemberAddress, MemberBeneficiary, MemberICPassport, MemberPayment, MemberPersonalData, MemberContactInfo, MemberSnapshot, MemberTax, MemberRankTransaction, MemberStatusTransaction, MemberMigrateTransaction, MemberTree
};
use App\Models\{
    Bonus\BonusMentorBonusDetails,
    Bonus\BonusQuarterlyDividendDetails,
    Bonus\BonusWelcomeBonusDetails,
    Bonus\TeamBonus,
    Campaigns\Campaign,
    Campaigns\CampaignPayoutPoint,
    General\CWSchedule,
    Masters\MasterData,
    Members\MemberActiveRecord,
    Sales\Sale,
    Users\User,
    Bonus\BonusSummary,
    Bonus\BonusTeamBonusDetails,
    Bonus\BonusMemberTreeDetails,
    Users\UserOTP
};
use App\{Helpers\Classes\OTPHelper,
    Helpers\Traits\AccessControl,
    Interfaces\General\CwSchedulesInterface,
    Mail\VerificationEmail,
    Repositories\BaseRepository,
    Interfaces\Masters\MasterInterface,
    Interfaces\Members\MemberInterface,
    Interfaces\Members\MemberTreeInterface};
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MemberRepository extends BaseRepository implements MemberInterface
{
    use AccessControl;

    private
        $memberPersonalDataObj,
        $memberBeneficiaryObj,
        $memberPaymentObj,
        $memberAddressObj,
        $memberICPassportObj,
        $memberContactInfoObj,
        $memberTaxObj,
        $memberRankTransactionObj,
        $memberStatusTransactionObj,
        $memberMigrateTransactionObj,
        $cwScheduleObj,
        $cwScheduleInterfaceObj,
        $userObj,
        $userOtpObj,
        $bonusSummaryObj,
        $bonusTeamBonusDetailsObj,
        $bonusMemberTreeDetailsObj,
        $bonusMentorBonusDetailsObj,
        $bonusWelcomeBonusDetailsObj,
        $bonusQuarterlyDividendDetailsObj,
        $memberTreeObj,
        $masterRepositoryObj,
        $memberTreeRepositoryObj,
        $campaignObj,
        $campaignPayoutPointsObj,
        $teamBonusObj,
        $masterDataObj,
        $salesObj,
        $memberSaleActivitiesStatusConfigCodes,
        $memberActiveRecordObj,
        $otpHelperObj,
        $memberSnapshotObj;

    /**
     * MemberRepository constructor.
     *
     * @param Member $model
     * @param MemberPersonalData $memberPersonalData
     * @param MemberBeneficiary $memberBeneficiary
     * @param MemberPayment $memberPayment
     * @param MemberAddress $memberAddress
     * @param MemberICPassport $ICPassport
     * @param MemberContactInfo $contactInfo
     * @param MemberTax $memberTax
     * @param MemberRankTransaction $memberRankTransaction
     * @param MemberStatusTransaction $memberStatusTransaction
     * @param MemberMigrateTransaction $memberMigrateTransaction
     * @param CWSchedule $cwSchedule
     * @param CwSchedulesInterface $cwSchedulesInterface
     * @param User $user
     * @param UserOTP $userOTP
     * @param BonusSummary $bonusSummary
     * @param BonusTeamBonusDetails $bonusTeamBonusDetails
     * @param BonusMemberTreeDetails $bonusMemberTreeDetails
     * @param BonusWelcomeBonusDetails $bonusWelcomeBonusDetails
     * @param BonusMentorBonusDetails $bonusMentorBonusDetails
     * @param BonusQuarterlyDividendDetails $bonusQuarterlyDividendDetails
     * @param MemberTree $memberTree
     * @param MasterInterface $masterInterface
     * @param MemberTreeInterface $memberTreeInterface
     * @param Campaign $campaign
     * @param CampaignPayoutPoint $campaignPayoutPoint
     * @param TeamBonus $teamBonus
     * @param MasterData $masterData
     * @param Sale $sale
     * @param MemberActiveRecord $memberActiveRecord
     * @param OTPHelper $OTPHelper
     * @param MemberSnapshot $memberSnapshot
     */
    public function __construct(
        Member $model,
        MemberPersonalData $memberPersonalData,
        MemberBeneficiary $memberBeneficiary,
        MemberPayment $memberPayment,
        MemberAddress $memberAddress,
        MemberICPassport $ICPassport,
        MemberContactInfo $contactInfo,
        MemberTax $memberTax,
        MemberRankTransaction $memberRankTransaction,
        MemberStatusTransaction $memberStatusTransaction,
        MemberMigrateTransaction $memberMigrateTransaction,
        CWSchedule $cwSchedule,
        CwSchedulesInterface $cwSchedulesInterface,
        User $user,
        UserOTP $userOTP,
        BonusSummary $bonusSummary,
        BonusTeamBonusDetails $bonusTeamBonusDetails,
        BonusMemberTreeDetails $bonusMemberTreeDetails,
        BonusWelcomeBonusDetails $bonusWelcomeBonusDetails,
        BonusMentorBonusDetails $bonusMentorBonusDetails,
        BonusQuarterlyDividendDetails $bonusQuarterlyDividendDetails,
        MemberTree $memberTree,
        MasterInterface $masterInterface,
        MemberTreeInterface $memberTreeInterface,
        Campaign $campaign,
        CampaignPayoutPoint $campaignPayoutPoint,
        TeamBonus $teamBonus,
        MasterData $masterData,
        Sale $sale,
        MemberActiveRecord $memberActiveRecord,
        OTPHelper $OTPHelper,
        MemberSnapshot $memberSnapshot
    )
    {
        parent::__construct($model);

        $this->memberPersonalDataObj = $memberPersonalData;

        $this->memberBeneficiaryObj = $memberBeneficiary;

        $this->memberPaymentObj = $memberPayment;

        $this->memberAddressObj = $memberAddress;

        $this->memberICPassportObj = $ICPassport;

        $this->memberContactInfoObj = $contactInfo;

        $this->memberTaxObj = $memberTax;

        $this->memberRankTransactionObj = $memberRankTransaction;

        $this->memberStatusTransactionObj = $memberStatusTransaction;

        $this->memberMigrateTransactionObj = $memberMigrateTransaction;

        $this->cwScheduleObj = $cwSchedule;

        $this->cwScheduleInterfaceObj = $cwSchedulesInterface;

        $this->userObj = $user;

        $this->userOtpObj = $userOTP;

        $this->bonusSummaryObj = $bonusSummary;

        $this->bonusTeamBonusDetailsObj = $bonusTeamBonusDetails;

        $this->bonusMemberTreeDetailsObj = $bonusMemberTreeDetails;

        $this->bonusMentorBonusDetailsObj = $bonusMentorBonusDetails;

        $this->bonusWelcomeBonusDetailsObj = $bonusWelcomeBonusDetails;

        $this->bonusQuarterlyDividendDetailsObj = $bonusQuarterlyDividendDetails;

        $this->memberTreeObj = $memberTree;

        $this->masterRepositoryObj = $masterInterface;

        $this->memberTreeRepositoryObj = $memberTreeInterface;

        $this->campaignObj = $campaign;

        $this->campaignPayoutPointsObj = $campaignPayoutPoint;

        $this->teamBonusObj = $teamBonus;

        $this->masterDataObj = $masterData;

        $this->salesObj = $sale;

        $this->memberActiveRecordObj = $memberActiveRecord;

        $this->otpHelperObj = $OTPHelper;

        $this->memberSnapshotObj = $memberSnapshot;

        $this->memberSaleActivitiesStatusConfigCodes = config('mappings.member_sale_activities_status');
    }

    /**
     * get member details for a given id
     *
     * @param int $id
     * @param array $relations
     * @return mixed
     */
    public function find(int $id, array $relations = [])
    {
        if (!empty($relations))
        {
            return $this->modelObj
                ->with($relations)
                ->where('user_id',$id)
                ->first();
        }

        return $this->modelObj->where('user_id',$id)->first();
    }

    /**
     * get member details filtered by different var's
     *
     * @param int $countryId
     * @param int $icPassportVerified
     * @param string $text
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @param int $sponsorId
     * @param string $exactSearch
     * @param int $treeFilter
     *
     * @return \Illuminate\Support\Collection|mixed
     */
    public function getMembersByFilters(
        int $countryId = 0,
        int $icPassportVerified = 3,
        string $text = '',
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0,
        int $sponsorId = 0,
        string $exactSearch = '',
        int $treeFilter = 0
    )
    {

        $whereInUsers = collect(); // to add Where in if there is a record here
        switch($treeFilter){
            case 1 : // without sponsor only, has placement
                $whereInUsers = $this->memberTreeObj->select('user_id')->whereNull('sponsor_parent_user_id')
                    ->whereNotNull('placement_parent_user_id')
                    ->where('id','<>', 1) // not root
                    ->get()->map(
                    function($result){
                        return $result->user_id;
                    }); // array of users that doesnt have any sponsor but has
                break;
            case 2 : // without , has sponsor
                $whereInUsers = $this->memberTreeObj->select('user_id')->whereNull('placement_parent_user_id')
                    ->whereNotNull('sponsor_parent_user_id')
                    ->where('id','<>', 1) // not root
                    ->get()->map(
                        function($result){
                            return $result->user_id;
                        }); // array of users that doesnt have any sponsor
                break;
            case 3 : // without both placement and sponsor
                $memberResults = $this->modelObj->select('user_id')->get()->map(
                    function($result){
                        return $result->user_id;
                    });

                $memberTreeResults = $this->memberTreeObj->select('user_id')->get()->map(
                    function($result){
                        return $result->user_id;
                    });

                $whereInUsers = $memberResults->diff($memberTreeResults)->unique();
                break;
        }

        $data = $this->modelObj
            ->with(['sponsor.parent','status','user','country', 'enrollmentRank','createdBy', 'updatedBy']);

        //check if countryId is applied
        if ($countryId > 0) {
            $data = $data
                ->where('country_id', $countryId);
        }

        if ($sponsorId > 0)
        {
            $downlineUserIds = $this->memberTreeRepositoryObj
                ->getAllSponsorChildUserId($sponsorId, false);

            array_push($downlineUserIds, $sponsorId); // include myself

            if (count($downlineUserIds) > 50000) {
                $data = $data->whereRaw('user_id in (' . implode(',', $downlineUserIds) . ')');
            }
            else {
                $data = $data->whereIn('user_id', $downlineUserIds);
            }
        }

         //check if member ic or passport verified
        if ($icPassportVerified < 3) {
            $data = $data
                ->where('ic_pass_verified', $icPassportVerified);
        }

        //check if member ic or passport verified
        if ($text != '') {
            $data = $data
                ->join('users', function ($join) use ($text){
                    $join->on('users.id', '=', 'members.user_id')
                        ->where(function ($query) use ($text) {
                            $query
                                ->where('members.name', 'like', '%' . $text . '%')
                                ->orWhere('members.ic_passport_number', 'like', '%' . $text . '%')
                                ->orWhere('users.old_member_id', 'like','%' . $text . '%');
                            ;
                        });
                });
        }

        //check for the excact search11
        if ($exactSearch != '') {
            $data = $data
                ->join('users', 'users.id', '=', 'members.user_id')
                ->where(function ($query) use($exactSearch){

                    if (ctype_digit($exactSearch)){
                        $query->where('users.old_member_id','=', $exactSearch);
                    }else{
                        $query
                            ->where('members.name', '=', $exactSearch);
                    }
                });
        }


        if($treeFilter && $whereInUsers->count()){
            $data->whereIn('user_id', $whereInUsers);
        }

        if ($icPassportVerified < 3 || $text != '' || $exactSearch != '') {
            $totalRecords = collect(['total' => $data->count()]);
        }else{
            $totalRecords = collect(['total' => $this->modelObj->count()]);
        }

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get member details based on userId
     *
     * @param int $userId
     * @param int $uplinedId
     * @return array|mixed
     */
    public function memberDetails(int $userId, int $uplinedId = 0)
    {
        $member = $this->find(
            $userId,
            ['effectiveRank','highestRank','country','enrollmentRank']
        );

        if ($member == null){
            $user = $this->userObj
                ->where('old_member_id', $userId)
                ->first();

            $member = $this->find(
                $user->id,
                ['effectiveRank','highestRank','country','enrollmentRank']
            );
        }

        $sponsor = optional($member->sponsor())->first();

        //get member placement-----------------------------------
        $memberTree = optional($member->tree())->first();

        $placementPosition = ($memberTree != null)? $memberTree->getPlacementPosition($memberTree->placement_position) : null;

        $placement = ($sponsor != null) ? optional($sponsor->placement())->first() : null;

        //get current and previous cw active status
        $saleActivityStatusCw = $member->getMemberSaleActivityStatus(
            $this->cwScheduleInterfaceObj,
            $this->masterRepositoryObj,
            $this->memberActiveRecordObj,
            $this->memberSaleActivitiesStatusConfigCodes
        );

        return [
            'member_data' => [
                'details' => array_merge(
                    $member->toArray(),
                    [
                        'sponsor' => (!is_null($sponsor)) ? optional($sponsor->parent())->first() : null,
                        'placement' => array_merge(
                            [
                                'name' => (!is_null($placement))? $placement->member->name : null,
                                'ibo' => (!is_null($placement))? $placement->member->user_id : null
                            ],
                            ['leg' => $placementPosition]
                            ),
                        'member_id' => $member->user()->first()->old_member_id,
                        'user' => $member->user()->first(),
                        'status' => optional($member->status())->first(),
                        'sale_activity_status_cw' => $saleActivityStatusCw
                    ]
                ),
                'address' => optional($member->address())->first(),
                'information' => optional($member->personalData())->first(),
                'beneficiary' => optional($member->beneficiary())->first(),
                'banking' => optional($member->payment())->first(),
                'verification' => optional($member->iCPassport())->get(),
                'contact_info' => optional($member->contactInfo())->first(),
                'tax' => optional($member->tax())->first(),
                'sales_history' => [],
                'upline' => (($uplinedId > 0) ? $this->find($uplinedId) : []),
                'ewallet' => optional($member->user()->first()->eWallet())->first()
            ]
        ];
    }

    /**
     * create new member
     *
     * @param User $user
     * @param int $countryId
     * @param array $data
     * @return Model|mixed
     */
    public function create(User $user, int $countryId, array &$data)
    {
        //get the current active cw -----------------------------------------------------------------
        $currentCw = $this->cwScheduleInterfaceObj
            ->getCwSchedulesList('current',
                ['sort' => 'cw_name', 'order' => 'desc', 'limit' => 1, 'offset' => 0]
            );

        $currentCw = $currentCw['data']->first();

        //get the pre-order status id-----------------------------------------------------------------------------------
        $orderStatus = $this->masterDataObj->getIdByTitle(
            config('mappings.member_status.active'), 'member_status'
        );

        $member = $user->member;

        if (is_null($member)){
            //create member data-----------------------------------------------
            $member = $user->member()->create([
                'country_id' => $countryId,
                'name' => $data['details']['name'],
                'translated_name' => $data['details']['translated_name'],
                'nationality_id' => $data['details']['nationality_id'],
                'ic_pass_type_id' => $data['details']['ic_pass_type_id'],
                'ic_passport_number' => $data['details']['ic_passport_number'],
                'date_of_birth' => $data['details']['date_of_birth'],
                'tin_no_taiwan' => $data['details']['tin_no_taiwan'],
                'join_date' => Carbon::now(),
                'expiry_date' => Carbon::now()->addMonth(12),
                'cw' => $currentCw['cw_name'],
                'ic_pass_verified' => 0,
                'enrollment_type_id' => $data['enrollment_type'],
                'status_id' => $orderStatus,
            ]);
        }

        //create member personal data--------------------------------------
        if (!empty($data['information'])) {
            $memberInformation = $user->member->personalData;

            if (is_null($memberInformation)){
                $member->personalData()->create($data['information']);
            }
        }

        //create member contact info data----------------------------------
        if (!empty($data['contact_info'])) {
            $memberContactInfo = $user->member->contactInfo;

            if (is_null($memberContactInfo)){

                foreach ($data['contact_info'] as $key => $value) {
                    if (empty($value)) {
                        unset($data['contact_info'][$key]);
                    }
                }

                $member->contactInfo()->create($data['contact_info']);
            }
        }

        //create member address--------------------------------------------
        if (!empty($data['address'])) {
            $memberAddress = $user->member->address;

            if (is_null($memberAddress)){
                $addressData = $data['address'];

                $address = json_encode($addressData['address_data']);

                unset($addressData['address_data']);

                $member->address()->create(array_merge($addressData, ['address_data' => $address]));
            }
        }

        return $member;
    }

    /**
     * update member data
     *
     * @param array $data
     * @param int $userId
     * @return array
     */
    public function update(array $data, int $userId)
    {
        $member = $this->find($userId);

        $newFileAdded = false;

        //update member avatar------------------------------------------------------------------------------------------
        if (!empty($data['member_data']['details']['avatar_image_path'])) {

            $oldFile[] = $member->avatar_image_path;

            $newFile[] = $data['member_data']['details']['avatar_image_path'];

            if($oldFile[0] != $newFile[0])
            {
                if ($oldFile[0] == null)
                {
                    $oldFile = [];
                }

                Uploader::synchronizeServerFile(Uploader::getUploaderSetting(true)['member_avatar'], $oldFile, $newFile, false);
            }
        }

        //update member ic/passport-------------------------------------------------------------------------------------
        if (isset($data['member_data']['verification'])) {
            //image_link, image_path, type, user_id
            $oldFileIds = $member->iCPassport->pluck('id')->toArray();
            $oldFilePaths = $member->iCPassport->pluck('image_path')->toArray();

            $newFileIds = [];
            $newFilePaths = [];

            foreach ($data['member_data']['verification'] as $item) {
                array_push($newFilePaths, $item['image_path']);

                if (empty($item['id'])) {
                    $memberIcPassportData = [
                        'user_id' => $item['user_id'],
                        'type' => $item['type'],
                        'image_path' => $item['image_path']
                    ];
                    
                    $memberIcPassport = $this->memberICPassportObj
                        ->create($memberIcPassportData);

                    array_push($newFileIds, $memberIcPassport['id']);

                    $newFileAdded = true;
                }
                else {
                    array_push($newFileIds, $item['id']);
                }
            }

            Uploader::synchronizeServerFile(Uploader::getUploaderSetting(true)['member_ic_passport'], $oldFilePaths, $newFilePaths, false);

            foreach ($oldFileIds as $oldFileId) {
                if (!in_array($oldFileId, $newFileIds)) {
                    $memberIcPassport = $this->memberICPassportObj
                        ->findOrFail($oldFileId);

                    $memberIcPassport->delete();
                }
            }

            if ($newFileAdded) { //if new document uploaded, reset flag to pending
                $member['ic_pass_verified'] = 0;
                $member['updated_by'] = Auth::id();
                $member->update();
            }
        }

        //update member data--------------------------------------------------------------------------------------------
        if (!empty($data['member_data']['details'])) {
            if ($newFileAdded) {
                $data['member_data']['details']['ic_pass_verified'] = 0;
            }
            else {
                if (isset($data['member_data']['details']['ic_pass_verified'])) {
                    if ($data['member_data']['details']['ic_pass_verified'] != $member['ic_pass_verified']) {
                        if ($this->isUser('member')) {
                            $data['member_data']['details']['ic_pass_verified'] = $member['ic_pass_verified'] ;
                        }
                        else {
                            $memberIcPassports = $member->iCPassport->all();

                            foreach ($memberIcPassports as $memberIcPassport) {
                                if (empty($memberIcPassport['verified_by'])) {
                                    $memberIcPassport['verified_by'] = Auth::id();
    
                                    $memberIcPassport->update();
                                }
                            }
                        }
                    }
                }
            }

            $member->update($data['member_data']['details']);
        }

        //update member address-----------------------------------------------------------------------------------------
        if (!empty($data['member_data']['address'])) {

            $addressData = $data['member_data']['address'];

            $address = json_encode($addressData['address_data']);

            unset($addressData['address_data']);

            if (isset($addressData['id'])){
                $memberAddress = $this->memberAddressObj
                    ->find($addressData['id']);

                $memberAddress->update(array_merge($addressData, ['address_data' => $address]));
            }else{
                $this->memberAddressObj->create(array_merge($addressData, ['address_data' => $address]));
            }
        }

        //update member information-------------------------------------------------------------------------------------
        if (!empty($data['member_data']['information'])) {

            $informationData = $data['member_data']['information'];

            $spouse  =  [
                'spouse_elken_member' => $informationData['spouse']['spouse_elken_member'],
                'spouse_name' => $informationData['spouse']['spouse_name'],
                'spouse_ibo_id' => $informationData['spouse']['spouse_ibo_id'],
                'ic_pass_type_id' => $informationData['spouse']['ic_pass_type_id'],
                'ic_pass_type_number' => $informationData['spouse']['ic_pass_type_number'],
            ];

            if (isset($informationData['id'])){
                $memberInformation = $this->memberPersonalDataObj
                    ->find($informationData['id']);

                $memberInformation->update(array_merge($informationData,$spouse));
            }else{
                $this->memberPersonalDataObj->create(array_merge($informationData,$spouse));
            }
        }

        //update member beneficiary-------------------------------------------------------------------------------------
        if (!empty($data['member_data']['beneficiary'])) {

            $beneficiaryData = $data['member_data']['beneficiary'];

            if (isset($beneficiaryData['id'])){
                $memberBeneficiary = $this->memberBeneficiaryObj
                    ->find($beneficiaryData['id']);

                $memberBeneficiary->update($beneficiaryData);
            }else{
                $this->memberBeneficiaryObj->create($beneficiaryData);
            }
        }

        //update member contact info------------------------------------------------------------------------------------
        if (!empty($data['member_data']['contact_info'])) {

            $contactInfoData = $data['member_data']['contact_info'];            

            if (isset($contactInfoData['id'])){
                $memberContactInfo =
                    $this->memberContactInfoObj
                        ->find($contactInfoData['id']);

                $memberContactInfo->update($contactInfoData);
            }else{
                $this->memberContactInfoObj->create($contactInfoData);
            }
        }

        //update member banks(Payments)---------------------------------------------------------------------------------
        if(!empty($data['member_data']['banking'])){

            $bankData = $data['member_data']['banking'];

            $payment_data = json_encode($bankData['payment_data']);

            unset($bankData['payment_data']);

            if (isset($bankData['id'])){
                $memberPayment =
                    $this->memberPaymentObj
                        ->find($bankData['id']);

                $memberPayment->update(array_merge($bankData, ['payment_data' => $payment_data]));
            }else{
                $this->memberPaymentObj->create(array_merge($bankData, ['payment_data' => $payment_data]));
            }
        }

        //update member tax---------------------------------------------------------------------------------------------
        if (!empty($data['member_data']['tax'])) {

            $taxData = $data['member_data']['tax'];

            $tax = json_encode($taxData['tax_data']);

            unset($taxData['tax_data']);

            $taxContent = array_merge($taxData, ['tax_data' => $tax]);

            if (isset($taxData['id'])){
                $memberTax = $this->memberTaxObj
                    ->find($taxData['id']);

                $memberTax->update($taxContent);
            }else{
                $this->memberTaxObj->create($taxContent);
            }
        }

        return $this->memberDetails($userId);
    }

    /**
     * update member rank [enrollment rank and highest rank]
     *
     * @param array $data
     * @return array|mixed
     */
    public function updateMemberRank(array $data)
    {
        $member = $this->modelObj->where('user_id', $data['user_id'])->first();

        $member->update($data);

        return $this->memberDetails($data['user_id']);
    }

    /**
     * verify or reject member Ic or Passport
     *
     * @param array $data
     * @param int $userId
     * @return mixed|void
     */
    public function verifyMemberIcOrPassport(array $data, int $userId)
    {
        $member = $this->modelObj->where('user_id', $userId)->first();

        //update member ICPassport--------------------------------------------------------------------------------------
        if(!empty($data['member_data']['verification'])){

            foreach ($data['verification'] as $item) {
                if (isset($item['id'])){
                    $memberICPassport =
                        $this->memberICPassportObj
                            ->find($item['id']);

                    $memberICPassport->update(
                        array_merge(['verified_by' => Auth::id()],$item)
                    );
                }else{
                    Auth::user()->verifiedBy($this->memberICPassportObj)->create($item);
                }
            }
        }
    }

    /**
     * get all member ranks records
     *
     * @param array $parameter
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function getMemberRanksList(array $parameter)
    {
        $member = $this->find(
            $parameter['user_id'],
            ['user','effectiveRank','highestRank','country','enrollmentRank']
        );

        $memberRankQuery = $this->memberRankTransactionObj
            ->where('user_id',$parameter['user_id'])
            ->orderBy($parameter['sort'], $parameter['order'])
            ->with('cwSchedule', 'previousEnrollmentRank', 'enrollmentRank', 'highestRank', 'previousHighestRank', 'createdBy');

        $totalRecords = collect([
            'total' => $memberRankQuery->count(),
        ]);

        $data = ($parameter['limit']) ? 
            $memberRankQuery->offset($parameter['offset'])->limit($parameter['limit'])->get() : 
            $memberRankQuery->get();

        return $totalRecords->merge(['data' => array('member' => $member, 'member_rank' => $data)]);
    }

    /**
     * Get the specified member ranks resource.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function getMemberRanks($id)
    {
        return $this->memberRankTransactionObj
            ->with(
                'member.country',
                'cwSchedule',
                'previousEnrollmentRank',
                'enrollmentRank',
                'highestRank',
                'previousHighestRank',
                'createdBy'
            )
            ->find($id);
    }

    /**
     * Store a newly created member ranks resource.
     *
     * @param array $data
     * @return mixed
     */
    public function memberRanksStore(array $data)
    {
        $errorBag = [];

        $member = $this->find($data['user_id']);

        $data['previous_enrollment_rank_id'] = $member->enrollment_rank_id;

        $data['previous_highest_rank_id'] = $member->highest_rank_id;

        return Auth::user()->createdBy($this->memberRankTransactionObj)->create($data);
    }

    /**
     * get all member status records
     *
     * @param array $parameter
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function getMemberStatusList(array $parameter)
    {
        $member = $this->find(
            $parameter['user_id'],
            ['user','effectiveRank','highestRank','country','enrollmentRank']
        );

        $memberStatusQuery = $this->memberStatusTransactionObj
            ->where('user_id',$parameter['user_id'])
            ->orderBy($parameter['sort'], $parameter['order'])
            ->with('cwSchedule', 'previousStatus', 'status', 'reason', 'createdBy');

        if(!empty($parameter['status_id'])){
            $memberStatusQuery->where('status_id',$parameter['status_id']);
        }

        $totalRecords = collect([
            'total' => $memberStatusQuery->count(),
        ]);

        $data = ($parameter['limit']) ? 
            $memberStatusQuery->offset($parameter['offset'])->limit($parameter['limit'])->get() : 
            $memberStatusQuery->get();

        foreach($data as &$detail){

            $detail->suspend = [];

            if($detail->status->title == 'SUSPENDED'){

                $effectiveDate = new DateTime($detail->effective_date);

                $today = new DateTime(date('Y-m-d'));

                if($today > $effectiveDate){

                    $interval = $effectiveDate->diff($today);

                    $detail->suspend = array(
                        'day' => $interval->format('%d'),
                        'month' => $interval->format('%m'),
                        'year' => $interval->format('%y')
                    );
                }
            }

            $effectiveDateCwScheduleQuery = $this->cwScheduleObj
                ->where('date_from', '<=', $detail->effective_date)
                ->where('date_to', '>=', $detail->effective_date)
                ->first();

            $detail->effective_date_cw_id = $effectiveDateCwScheduleQuery->id;

            $detail->effective_date_cw_schedule = $effectiveDateCwScheduleQuery;
        }

        return $totalRecords->merge(['data' => array('member' => $member, 'member_status' => $data)]);
    }

    /**
     * Get the specified member status resource.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function getMemberStatus($id)
    {
        return $this->memberStatusTransactionObj
            ->with('member.country', 'cwSchedule', 'previousStatus', 'status', 'reason', 'createdBy')
            ->find($id);
    }

    /**
     * Store a newly created member status resource.
     *
     * @param array $data
     * @return mixed
     */
    public function memberStatusStore(array $data)
    {
        $errorBag = [];

        $member = $this->find($data['user_id']);

        $data['previous_status_id'] = $member->status_id;

        return Auth::user()->createdBy($this->memberStatusTransactionObj)->create($data);
    }

    /**
     * get all member migrate records
     *
     * @param array $parameter
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function getMemberMigrateList(array $parameter)
    {
        $member = $this->find(
            $parameter['user_id'],
            ['user','effectiveRank','highestRank','country','enrollmentRank']
        );

        $memberMigrateQuery = $this->memberMigrateTransactionObj
            ->where('user_id',$parameter['user_id'])
            ->orderBy($parameter['sort'], $parameter['order'])
            ->with('cwSchedule', 'previousCountry', 'country', 'reason', 'createdBy');

        $totalRecords = collect([
            'total' => $memberMigrateQuery->count(),
        ]);

        $data = ($parameter['limit']) ? 
            $memberMigrateQuery->offset($parameter['offset'])->limit($parameter['limit'])->get() : 
            $memberMigrateQuery->get();

        return $totalRecords->merge(['data' => array('member' => $member, 'member_migrate' => $data)]);
    }

    /**
     * Get the specified member migrate resource.
     *
     * @param  int  $id
     * @return \Illuminate\Database\Eloquent\Collection|Model
     */
    public function getMemberMigrate($id)
    {
        return $this->memberMigrateTransactionObj
            ->with('member', 'cwSchedule', 'previousCountry', 'country', 'reason')
            ->find($id);
    }

    /**
     * Store a newly created member migrate resource.
     *
     * @param array $data
     * @return mixed
     */
    public function memberMigrateStore(array $data)
    {
        $errorBag = [];

        $member = $this->find($data['user_id']);

        $data['previous_country_id'] = $member->country_id;

        return Auth::user()->createdBy($this->memberMigrateTransactionObj)->create($data);;
    }

    /**
     * verify classic member given user id
     *
     * @param string $nationalId
     * @return array
     */
    public function verifyClassicMember(string $nationalId)
    {
        $api_host = env('CLASSIC_MEMBER_VERIFY_HOST') . '/' . $nationalId;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $api_host
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $result = json_decode($response, true);

        return ['result' => (!empty($result)) ? ($result[0]['ResponseStatus'] == 'True') ? true :false : false];
    }

    /**
     * get placement network performance
     *
     * @param int $userId
     * @return array
     */
    public function getPlacementNetworkPerformance(int $userId)
    {
        $lastUpdate = '';

        $data = $this->bonusSummaryObj
            ->with(['cw', 'highestRank', 'effectiveRank'])
            ->join('cw_schedules', 'cw_schedules.id', '=', 'cw_id')
            ->where('user_id', $userId)
            ->orderBy('cw_schedules.cw_name', 'desc')
            ->get()->map(function($bonus) use($userId, $lastUpdate) {
                if ($lastUpdate == '') {
                    if ($bonus->updated_at != null) {
                        $lastUpdate = $bonus->updated_at;
                    }
                    else if ($bonus->created_at != null) {
                        $lastUpdate = $bonus->created_at;
                    }
                }
                
                $left = $this->bonusTeamBonusDetailsObj
                    ->where('bonuses_summary_id', $bonus->id)
                    ->where('gcv_bring_forward_position', 1);

                $right = $this->bonusTeamBonusDetailsObj
                    ->where('bonuses_summary_id', $bonus->id)
                    ->where('gcv_bring_forward_position', 2);

                $tree = $this->bonusMemberTreeDetailsObj
                    ->where('user_id', $userId)
                    ->where('cw_id', $bonus->cw_id)
                    ->first();

                return [
                    "Week" => $bonus->cw->cw_name,
                    "HR" => isset($bonus->highestRank->rank_code) ? $bonus->highestRank->rank_code : null,
                    "ER" => isset($bonus->effectiveRank->rank_code) ? $bonus->effectiveRank->rank_code : null,
                    "AInA" => optional($tree)->is_active_brand_ambassador,
                    "PCV" => optional($tree)->personal_sales_cv,
                    "WeekGcvL" => $left->sum('gcv'),
                    "WeekGcvR" => $right->sum('gcv'),
                    "CarryForwardGcvL" => $left->sum('gcv_bring_forward'),
                    "CarryForwardGcvR" => $right->sum('gcv_bring_forward'),
                    "SponsorLineL" => optional($tree)->total_active_ba_left,
                    "SponsorLineR" => optional($tree)->total_active_ba_right,
                    "NewBaL" => optional($tree)->total_new_ba_left,
                    "NewBaR" => optional($tree)->total_new_ba_right,
                    "TotalBaL" => optional($tree)->total_unique_line_left,
                    "TotalBaR" => optional($tree)->total_unique_line_right,
                    "ActiveBaL" => optional($tree)->total_ba_left,
                    "ActiveBaR" => optional($tree)->total_ba_right
                ];
            });
        
        return [
            "header" => [
                "user_id" => $userId,
                "last_update" => (($lastUpdate != '') ? $lastUpdate : date('Y-m-d H:i:s'))
            ],
            "data" => $data
        ];
    }

    /**
     * get member campaign report data
     *
     * @param int $campaignId
     * @param int $userId
     * @return mixed
     */
    public function memberCampaignReport(int $campaignId, int $userId)
    {
        $campaign = $this->campaignObj->findOrFail($campaignId);

        $cwSchedules = $this->cwScheduleObj->whereBetween('id', [ $campaign->from_cw_schedule_id, $campaign->to_cw_schedule_id ])->get();

        $data = array();

        foreach ($cwSchedules as $cwSchedule)
        {
            $payoutPointObj = $this->campaignPayoutPointsObj
                ->where('campaign_id', $campaign->id)
                ->where('user_id', $userId)
                ->where('cw_id', $cwSchedule->id);

            if($payoutPointObj->count())
            {
                $payoutPoint = $payoutPointObj->first();

                $data[$cwSchedule->cw_name] = [
                    "is_active" => 1,
                    "rank" => 0,
                    "pre_condition" => 0,
                    "level_1" => $payoutPoint->level == 1 ? $payoutPoint->payout_points : 0,
                    "level_2" => $payoutPoint->level == 2 ? $payoutPoint->payout_points : 0
                ];

                $userTeamBonusObj = $this->teamBonusObj->with(['highestRank'])->where('user_id', $payoutPoint->user_id)->where("cw_id", $payoutPoint->cw_id);

                if($userTeamBonusObj->count())
                {
                    $data[$cwSchedule->cw_name]['rank'] = $userTeamBonusObj->first()->highestRank->rank_code;
                }

                $bonusMemberTree = $this->bonusMemberTreeDetailsObj
                    ->where('user_id', $userId)
                    ->where('cw_id', $payoutPoint->cw_id);

                if($bonusMemberTree->count())
                {
                    $data[$cwSchedule->cw_name]['pre_condition'] = $bonusMemberTree->first()->total_direct_downline_active_ba;
                }
            }
            else
            {
                $data[$cwSchedule->cw_name] = [
                    "is_active" => 0,
                    "rank" => 0,
                    "pre_condition" => 0,
                    "level_1" => 0,
                    "level_2" => 0
                ];
            }
        }

        $result = collect(["data" => $data]);

        $tempData = collect($data);

        $result->put('is_active_total', $tempData->where('is_active', '=', true)->count());
        $result->put('rank_total', $tempData->where('rank', '!==', 0)->count());
        $result->put('pre_condition_total', $tempData->sum('pre_condition'));
        $result->put('level_1_total', $tempData->sum('level_1'));
        $result->put('level_2_total', $tempData->sum('level_2'));

        return $result;
    }

    /**
     * get member dashboard information
     *
     * @return mixed
     */
    public function memberDashboard()
    {
        $currentCW = $this->cwScheduleInterfaceObj->getCwSchedulesList('current')->get('data')->toArray()[0];

        $currentCWBonusDetails = $this->bonusMemberTreeDetailsObj->where('user_id', Auth::id())->where('cw_id', $currentCW['id'])->first();

        $cwSchedules = $this->cwScheduleInterfaceObj->getCwSchedulesList('custom_current_past', [
            'custom_cw_name' => $currentCW['cw_name'],
            'sort' => 'id',
            'order' => 'desc',
            'offset' => 0,
            'limit' => 7
        ])->get('data')->toArray();

        $cwSchedules = collect(array_reverse($cwSchedules));

        foreach ($cwSchedules as $cwSchedule)
        {
            $bonusMemberObj = $this->bonusMemberTreeDetailsObj->where('user_id', Auth::id())->where('cw_id', $cwSchedule['id'])->first();

            $totalSponsored[ $cwSchedule['cw_name'] ] = $totalGCV[ $cwSchedule['cw_name'] ] = [];

            if(isset($bonusMemberObj))
            {
                $totalSponsored[ $cwSchedule['cw_name'] ] = [
                    'left' => $bonusMemberObj->total_active_ba_left,
                    'right' => $bonusMemberObj->total_active_ba_right
                ];

                $totalGCV[ $cwSchedule['cw_name'] ] = [
                    'left' => $bonusMemberObj->total_active_ba_left,
                    'right' => $bonusMemberObj->total_active_ba_right
                ];
            }
            else
            {
                $totalSponsored[ $cwSchedule['cw_name'] ] = [
                    'left' => 0,
                    'right' => 0
                ];

                $totalGCV[ $cwSchedule['cw_name'] ] = [
                    'left' => 0,
                    'right' => 0
                ];
            }

            $bonusQuarterlyDividend[ $cwSchedule['cw_name'] ] = $this->bonusQuarterlyDividendDetailsObj
                ->where('user_id', Auth::id())
                ->where('cw_id', $cwSchedule['id'])
                ->pluck('shares');
        }

        // TODO: Need to complete Member Snapshot
        /*
        $currentMemberTreeUserIds = $this->bonusMemberTreeDetailsObj->where('sponsor_parent_user_id', 1)->where('cw_id', 47)->pluck('user_id');

        if( count($currentMemberTreeUserIds) )
        {
            foreach ($currentMemberTreeUserIds as $userId)
            {

            }
        }
        */

        $bonusSummary = $this->bonusSummaryObj->where('user_id', Auth::id())
            ->where('cw_id', $currentCW['id'])
            ->orderBy('id', 'desc')->first();

        if($bonusSummary) {
            //Calculate Welcome Bonus
            $bonusWelcomeDepthLevels = $this->bonusWelcomeBonusDetailsObj
                ->where('bonuses_summary_id', $bonusSummary->id)
                ->distinct()
                ->pluck('sponsor_child_depth_level')->toArray();

            foreach ($bonusWelcomeDepthLevels as $bonusWelcomeDepthLevel) {
                $bonusWelcomeDetails['welcome_bonus_level_' . $bonusWelcomeDepthLevel] = $this->bonusWelcomeBonusDetailsObj
                    ->where('bonuses_summary_id', $bonusSummary->id)
                    ->where('sponsor_child_depth_level', $bonusWelcomeDepthLevel)
                    ->sum('total_usd_amount');
            }

            //Get Team Bonus * 1 = left, 2 = right
            $bonusTeamDetails = $this->bonusTeamBonusDetailsObj->select(
                'gcv',
                'team_bonus_percentage AS total_team_bonus_percentage',
                'gcv_leg_group',
                'gcv_bring_forward',
                'gcv_bring_forward_position AS position'
            )->where('bonuses_summary_id', $bonusSummary->id)
                ->get()
                ->mapWithKeys(function ($leg) {
                    $leg['total_gcv'] = $leg['gcv'] + $leg['gcv_bring_forward'];
                    return [$leg->position => $leg];
                })->toArray();

            //Get Mentor Bonus
            $bonusMentorGenerations = $this->bonusMentorBonusDetailsObj
                ->where('bonuses_summary_id', $bonusSummary->id)
                ->distinct()
                ->pluck('sponsor_generation_level')->toArray();

            foreach ($bonusMentorGenerations as $bonusMentorGeneration) {
                $bonusMentorDetails['g' . $bonusMentorGeneration] = $this->bonusMentorBonusDetailsObj
                    ->where('bonuses_summary_id', $bonusSummary->id)
                    ->where('sponsor_generation_level', $bonusMentorGeneration)
                    ->sum('mentor_bonus');
            }
        }
        //Get Member Snapshot
        $memberSnapshot = $this->memberSnapshotObj->select('data')->where([['cw_id', $cwSchedule['id']],['user_id', Auth::id()]])->get();
        $memberSnapshot = ($memberSnapshot->count() > 0) ? collect(json_decode($memberSnapshot->first()->data)) : null;

        //Formatting Response Data
        $data = [
            'updated_at' => isset($bonusSummary) ? $bonusSummary->updated_at->toDateTimeString() : Carbon::now(config('app.timezone'))->toDateTimeString(),
            'personal_sales_cv' => isset($currentCWBonusDetails) ? $currentCWBonusDetails->personal_sales_cv : 0,
            'tri_formation' => [
                'is_tri_formation' => isset($currentCWBonusDetails) ? $currentCWBonusDetails->is_tri_formation : 0,
                'total_unique_line_left' => isset($currentCWBonusDetails) ? $currentCWBonusDetails->total_unique_line_left : 0,
                'total_unique_line_right' => isset($currentCWBonusDetails) ? $currentCWBonusDetails->total_unique_line_right : 0
            ],
            'newly_sponsored' => isset($currentCWBonusDetails) ? $currentCWBonusDetails->total_new_ba_left + $currentCWBonusDetails->total_new_ba_right : 0,
            'direct_sponsored' => isset($currentCWBonusDetails) ? $currentCWBonusDetails->total_direct_downline : 0,
            'direct_sponsored_active' => isset($currentCWBonusDetails) ? $currentCWBonusDetails->total_direct_downline_active_ba : 0,
            'total_sponsored' => isset($currentCWBonusDetails) ? $currentCWBonusDetails->total_ba_left + $currentCWBonusDetails->total_ba_right : 0,
            'total_sponsored_active' => isset($currentCWBonusDetails) ? $currentCWBonusDetails->total_active_ba_left + $currentCWBonusDetails->total_active_ba_right : 0,
            'total_sponsored_graph' => [
                'cw_names' => $cwSchedules->pluck('cw_name'),
                'values' => $totalSponsored
            ],
            'total_gcv_graph' => [
                'cw_names' => $cwSchedules->pluck('cw_name'),
                'values' => $totalGCV
            ],
            'unredeem_esac' => 0,
            'my_snapshot' => $memberSnapshot,
            'welcome_bonus' => [
                'total' => ($bonusSummary) ? $bonusSummary->welcome_bonus : 0,
                'data' => isset($bonusWelcomeDetails) ? $bonusWelcomeDetails : null
            ],
            'team_bonus' => [
                'total' => ($bonusSummary) ? $bonusSummary->team_bonus : 0,
                'data' => isset($bonusTeamDetails) ? $bonusTeamDetails : null
            ],
            'mentor_bonus' => [
                'total' => ($bonusSummary) ? $bonusSummary->mentor_bonus : 0,
                'data' => isset($bonusMentorDetails) ? $bonusMentorDetails : null
            ],
            'quarterly_dividend' => [
                'total' => ($bonusSummary) ? $bonusSummary->quarterly_dividend : 0,
                'data' => isset($bonusQuarterlyDividend) ? $bonusQuarterlyDividend : null
            ]
        ];

        return collect($data);
    }

    /**
     * Validate Member Email Address
     *
     * @param array $inputs
     * @return \Illuminate\Support\Collection|mixed
     */
    public function validateEmail(array $inputs)
    {
        $this->userOtpObj->where('user_id', Auth::id())
            ->where('code', $inputs['code'])
            ->update([
                'verified' => true,
                'expired' => true
            ]);

        $this->memberContactInfoObj
            ->updateOrCreate(
                [
                    'user_id' => Auth::id()
                ],
                [
                    'user_id' => Auth::id(),
                    'email' => $inputs['email'],
                    'email_verified' => 1
                ]
            );

        $this->userObj->where('id', Auth::id())
            ->update([
                'email' => $inputs['email']
            ]);

        return collect(['code' => trans('message.member.email-verified')]);
    }

    /**
     * Generate Email Verification Code
     *
     * @param array $inputs
     * @return array|mixed
     * @throws \Exception
     */
    public function generateEmailVerificationCode(array $inputs)
    {
        $otpType = config('mappings.otp_code_type.email');

        $currentUserOtp = $this->otpHelperObj->getOTPCode(
            $inputs['email'],
            $otpType,
            0,
            Auth::id(),
            false
        );

        if (!is_null($currentUserOtp))
        {
            //check timestamp if less than 5 minutes using Carbon php lib
            if ($currentUserOtp->updated_at->diffInMinutes(now(config('app.timezone'))) < 5)
            {
                return [
                    'response_code' => 0,
                    'response_msg' => __('message.email.already_sent')
                ];
            }
        }

        $userOtp = $this->otpHelperObj->generateOTPCode($inputs['email'], $otpType, Auth::id());

        $message = trans('message.member.otp-code', ['otp' => $userOtp->code]);

        Mail::to($inputs['email'])
            ->queue(new VerificationEmail($message));

        return [
            'response_code' => 0,
            'response_msg' => __('message.member.verification-email-sent')
        ];
    }
}