<?php
namespace App\Repositories\Members;
use Illuminate\Support\Facades\Auth;
use Log;
use App\{
    Helpers\Traits\AccessControl,
    Interfaces\General\CwSchedulesInterface,
    Interfaces\Masters\MasterInterface,
    Interfaces\Members\MemberTreeInterface,
    Interfaces\Settings\SettingsInterface,
    Models\Bonus\BonusMemberTreeDetails,
    Models\Bonus\BonusSummary,
    Models\Bonus\BonusTeamBonusDetails,
    Models\Bonus\EnrollmentRank,
    Models\Bonus\TeamBonusRank,
    Models\Enrollments\EnrollmentTempTree,
    Models\Locations\Country,
    Models\Members\Member,
    Models\Members\MemberActiveRecord,
    Models\Members\MemberTree,
    Models\Masters\Master,
    Models\Masters\MasterData,
    Models\Settings\Setting,
    Models\Users\User,
    Repositories\BaseRepository

};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

Class MemberTreeRepository extends BaseRepository implements MemberTreeInterface
{
    use AccessControl;

    protected
        $settingRepositoryObj,
        $masterObj,
        $enrollmentRankObj,
        $teamBonusRankObj,
        $bonusMemberTreeDetailsObj,
        $bonusSummaryObj,
        $bonusTeamBonusDetailsObj,
        $masterDataObj,
        $countryObj,
        $memberObj,
        $userIdOuterTree,
        $bonusMembersTreeRecord,
        $cwSchedulesRepositoryObj,
        $countryRecord,
        $masterDataRecord,
        $teamBonusRanksRecord,
        $enrollmentRanksRecord,
        $cwSchedulesRecord,
        $membersRecord,
        $usersRecord,
        $membersTreeRecord,
        $liteBonusMemberTreeDetailsRecord,
        $liteMasterDataRecord,
        $liteTeamBonusRanksRecord,
        $liteEnrollmentRanksRecord,
        $liteCwSchedulesRecord,
        $liteMembersRecord,
        $liteUsersRecord,
        $liteMemberTreesRecord,
        $userObj,
        $memberSaleActivitiesStatusConfigCodes,
        $masterRepositoryObj,
        $memberActiveRecordObj,
        $enrollmentTempTreeObj;

    /**
     * MemberTreeRepository constructor.
     *
     * @param MemberTree $model
     * @param SettingsInterface $settingsInterface
     * @param Master $master
     * @param EnrollmentRank $enrollmentRank
     * @param TeamBonusRank $teamBonusRank
     * @param MasterData $masterData
     * @param BonusTeamBonusDetails $bonusTeamBonusDetails
     * @param BonusSummary $bonusSummary
     * @param BonusMemberTreeDetails $bonusMemberTreeDetails
     * @param Country $country
     * @param Member $member
     * @param $cwSchedulesInterface $cwSchedulesRepository
     * @param User $user
     * @param MasterInterface $masterInterface
     * @param MemberActiveRecord $memberActiveRecord
     * @param EnrollmentTempTree $enrollmentTempTree
     */
    public function __construct(
        MemberTree $model,
        SettingsInterface $settingsInterface,
        Master $master,
        EnrollmentRank $enrollmentRank,
        TeamBonusRank $teamBonusRank,
        MasterData $masterData,
        BonusTeamBonusDetails $bonusTeamBonusDetails,
        BonusSummary $bonusSummary,
        BonusMemberTreeDetails $bonusMemberTreeDetails,
        Country $country,
        Member $member,
        CwSchedulesInterface $cwSchedulesInterface,
        User $user,
        MasterInterface $masterInterface,
        MemberActiveRecord $memberActiveRecord,
        EnrollmentTempTree $enrollmentTempTree
    )
    {
        parent::__construct($model);

        $this->settingRepositoryObj = $settingsInterface;

        $this->masterObj = $master;

        $this->enrollmentRankObj = $enrollmentRank;

        $this->teamBonusRankObj = $teamBonusRank;

        $this->bonusMemberTreeDetailsObj = $bonusMemberTreeDetails;

        $this->bonusSummaryObj = $bonusSummary;

        $this->bonusTeamBonusDetailsObj = $bonusTeamBonusDetails;

        $this->masterDataObj = $masterData;

        $this->countryObj = $country;

        $this->memberObj = $member;

        $this->cwSchedulesRepositoryObj = $cwSchedulesInterface;
      
        $this->userObj = $user;

        $this->masterRepositoryObj = $masterInterface;

        $this->memberActiveRecordObj = $memberActiveRecord;

        $this->enrollmentTempTreeObj = $enrollmentTempTree;

        $this->memberSaleActivitiesStatusConfigCodes = config('mappings.member_sale_activities_status');
    }

    /**
     * get member's placement tree outer with given member id , depth and outer left or right
     *
     * @param int $userId
     * @param int $depth
     * @param string $outer
     * @return mixed
     */
    public function getPlacementTreeOuter(int $userId, int $depth, string $outer)
    {

        // If this user is member, he can only see himself or anyone under his own sponsor network
        if($this->isUser('member') &&
            !$this->verifyMemberTreeDownline( 'sponsor', Auth::user()->id, $userId )['result'] &&
                $userId != Auth::user()->id){
            return ['result' => false];
        }

        $data = $this->modelObj
            ->where('user_id', $userId)
            ->first();

        if($outer == 'left') {
            $outer = 1;
        }

        if($outer == 'right') {
            $outer = 2;
        }

        $this->createLiteBonusMemberTreeDetailsRecord();

        $this->getChildrenOuter($data, $outer);

        if($this->userIdOuterTree == null || $this->userIdOuterTree == '' || empty($this->userIdOuterTree)){
            $this->userIdOuterTree = $userId;
        }

        $outerMemberTree = $this->modelObj
            ->where('user_id', $this->userIdOuterTree)
            ->first();

        $this->getChildren($outerMemberTree, 0, 1, $outer);

        return $outerMemberTree;
    }

    /**
     * get member's placement tree with given member id and depth
     *
     * @param int $userId
     * @param int $depth
     * @return mixed
     */
    public function getPlacementTree(int $userId, int $depth)
    {

        // If this is a member type, he can only see full information on his own network
        $showUserIds = [];
        if($this->isUser('member')){
            $showUserIds = $this->getAllSponsorChildUserId(Auth::id(), false);
            $showUserIds[] = Auth::id(); // include his own info too
        }

        $this->createLiteBonusMemberTreeDetailsRecord();

        $data = $this->modelObj
            ->select('user_id', 'sponsor_parent_user_id', 'placement_parent_user_id',
                'placement_depth_level', 'placement_position', 'sponsor_depth_level')
            ->where('user_id', $userId)
            ->first();

        $this->getChildren($data, 0, $depth, NULL, $showUserIds);
        return $data;
    }

    /**
     * get member's placement tree with given member id, depth and outer
     *
     * @param $data
     * @param $currentLevel
     * @param int $breakPoint
     * @param int $outer
     * @param array $showUserIds - if given, we will limit to show this users only
     */
    private function getChildren($data, $currentLevel, $breakPoint = 10, $outer = NULL, $showUserIds = [])
    {
        if($breakPoint == $currentLevel){
            return;
        }

        $hasFilter = (count($showUserIds) > 0) ? true : false;

        //TODO temporary solutions for auto maintenance and auto ship
        $data->auto_maintenance = 1;

        $data->autoship = 1;

        if($this->liteBonusMemberTreeDetailsRecord->has($data->user_id)){
            $data->is_active_ba =
                $this->liteBonusMemberTreeDetailsRecord->get($data->user_id)->is_active_brand_ambassador;
            $cwId = $this->liteBonusMemberTreeDetailsRecord->get($data->user_id)->cw_id;
            $data->gcv_details = BonusTeamBonusDetails::select('gcv', 'gcv_leg_group', 'gcv_bring_forward_position')
                ->whereHas('bonuses', function($query) use($cwId, $data){
                    $query->where('cw_id', $cwId)->where('user_id', $data->user_id);
            })->get();
        }else{
            $data->is_active_ba = 0;
        }

        $data->children = $data->placementChildren()->select('user_id', 'sponsor_parent_user_id',
            'placement_parent_user_id', 'placement_depth_level', 'placement_position', 'sponsor_depth_level')->get();

        // we will have to filter the information of this user because this guys is not in the sponsor
        if(!$hasFilter || in_array($data->user_id, $showUserIds)) {
            $data->member = $data->memberWithRank()->select('user_id', 'country_id', 'name', 'translated_name',
                'personal_sales_cv', 'personal_sales_cv_percentage',
                'join_date', 'effective_rank_id','highest_rank_id', 'enrollment_rank_id', 'status_id', 'avatar_image_path')
                ->with([
                    'country' => function($query){
                        $query->select('id', 'name', 'code', 'code_iso_2');
                    },
                    'user'=> function($query){
                        $query->select('id', 'name', 'active', 'old_member_id');
                    }, 'status'
                ])->get();

            //get current and previous cw active status
            $memberRecord = $data->member()->first();

            $saleActivityStatusCw = $memberRecord->getMemberSaleActivityStatus(
                $this->cwSchedulesRepositoryObj,
                $this->masterRepositoryObj,
                $this->memberActiveRecordObj,
                $this->memberSaleActivitiesStatusConfigCodes
            );

            $data->member[0]['sale_activity_status_cw'] = $saleActivityStatusCw;

            if($this->liteBonusMemberTreeDetailsRecord->has($data->user_id)){

                $leftGcv = $this->liteBonusMemberTreeDetailsRecord->get($data->user_id)->left_gcv;

                $rightGcv = $this->liteBonusMemberTreeDetailsRecord->get($data->user_id)->right_gcv;

                $data->member[0]['power_leg_gcv'] = ($leftGcv >= $rightGcv) ? $leftGcv : $rightGcv;

                $data->member[0]['pay_leg_gcv'] = ($leftGcv >= $rightGcv) ? $rightGcv : $leftGcv;

            } else {

                $data->member[0]['power_leg_gcv'] = 0;

                $data->member[0]['pay_leg_gcv'] = 0;
            }

        }else{
            // if we need to populate the data member default value for the hidden user, this is where we do it
            $data->member = $data->memberWithRank()->select('user_id', 'country_id',
                'highest_rank_id', 'enrollment_rank_id', 'avatar_image_path')
                ->with([
                    'country' => function($query){
                        $query->select('id', 'name', 'code', 'code_iso_2');
                    },
                    'user'=> function($query){
                        $query->select('id', 'old_member_id');
                    }
                ])->get();
        }


        if(empty($data->children[0])){
            $data->children[0] = new \stdClass();
        }else {
            $children = $data->children[0];

            $data->children[0] = new \stdClass();

            // Temporarily assign value for amt and autoship
            $children->auto_maintenance = 0;
            $children->autoship = 1;

            //if the [0] index belongs to right side, we will assign this to the right side to be processed below
            if ($children->placement_position == 2) {
                $data->children[1] = $children;
            } else {
                if ($outer == null) {
                    $this->getChildren($children, $currentLevel + 1, $breakPoint, $outer, $showUserIds);
                } else {
                    // this is to get the outer right child
                    $children->member = $children->memberWithRank()->select(
                        'user_id', 'country_id', 'name',
                        'translated_name', 'personal_sales_cv', 'personal_sales_cv_percentage',
                        'join_date', 'effective_rank_id', 'highest_rank_id', 'enrollment_rank_id', 'status_id',
                        'avatar_image_path')->with([
                        'country' => function ($query) {
                            $query->select('id', 'name', 'code', 'code_iso_2');
                        },
                        'user' => function ($query) {
                            $query->select('id', 'name', 'active', 'old_member_id');
                        },
                        'status'
                    ])->get();

                    //get current and previous cw active status
                    $memberRecord = $children->member()->first();

                    $saleActivityStatusCw = $memberRecord->getMemberSaleActivityStatus(
                        $this->cwSchedulesRepositoryObj,
                        $this->masterRepositoryObj,
                        $this->memberActiveRecordObj,
                        $this->memberSaleActivitiesStatusConfigCodes
                    );

                    $children->member[0]['sale_activity_status_cw'] = $saleActivityStatusCw;

                    if($this->liteBonusMemberTreeDetailsRecord->has($children->member->user_id)){

                        $leftGcv = $this->liteBonusMemberTreeDetailsRecord->get($children->member->user_id)->left_gcv;

                        $rightGcv = $this->liteBonusMemberTreeDetailsRecord->get($children->member->user_id)->right_gcv;

                        $children->member[0]['power_leg_gcv'] = ($leftGcv >= $rightGcv) ? $leftGcv : $rightGcv;

                        $children->member[0]['pay_leg_gcv'] = ($leftGcv >= $rightGcv) ? $rightGcv : $leftGcv;

                    } else {

                        $children->member[0]['power_leg_gcv'] = 0;

                        $children->member[0]['pay_leg_gcv'] = 0;
                    }
                }

                $data->children[0] = $children;
            }
        }

        if(empty($data->children[1])){
            $data->children[1] = new \stdClass();
        }else{
            //TODO temporary solutions for auto maintenance and auto ship
            $data->children[1]->auto_maintenance = 1;

            $data->children[1]->autoship = 0;

            if($outer == null){
                $this->getChildren($data->children[1], $currentLevel + 1, $breakPoint, $outer, $showUserIds);
            }else{
                // this is to get the outer left child
                $data->children[1]->member = $data->children[1]->memberWithRank()
                    ->select('user_id', 'country_id', 'name',
                        'translated_name', 'personal_sales_cv', 'personal_sales_cv_percentage',
                        'join_date', 'effective_rank_id','highest_rank_id', 'enrollment_rank_id', 'status_id',
                        'avatar_image_path'
                    )->with([
                        'country' => function($query){
                            $query->select('id', 'name', 'code', 'code_iso_2');
                        },
                        'user'=> function($query){
                            $query->select('id', 'name', 'active', 'old_member_id');
                        }, 'status'
                    ])->get();

                //get current and previous cw active status
                $memberRecord = $data->children[1]->member()->first();

                $saleActivityStatusCw = $memberRecord->getMemberSaleActivityStatus(
                    $this->cwSchedulesRepositoryObj,
                    $this->masterRepositoryObj,
                    $this->memberActiveRecordObj,
                    $this->memberSaleActivitiesStatusConfigCodes
                );

                $data->children[1]->member[0]['sale_activity_status_cw'] = $saleActivityStatusCw;

                if($this->liteBonusMemberTreeDetailsRecord->has($data->children[1]->member->user_id)){

                    $leftGcv = $this->liteBonusMemberTreeDetailsRecord->get($data->children[1]->member->user_id)->left_gcv;

                    $rightGcv = $this->liteBonusMemberTreeDetailsRecord->get($data->children[1]->member->user_id)->right_gcv;

                    $data->children[1]->member[0]['power_leg_gcv'] = ($leftGcv >= $rightGcv) ? $leftGcv : $rightGcv;

                    $data->children[1]->member[0]['pay_leg_gcv'] = ($leftGcv >= $rightGcv) ? $rightGcv : $leftGcv;

                } else {

                    $data->children[1]->member[0]['power_leg_gcv'] = 0;

                    $data->children[1]->member[0]['pay_leg_gcv'] = 0;
                }
            }
        }

        return;
    }

    /**
     *  get member's placement tree with given member object, depth and outer left or right
     *
     * @param $data
     * @param $outer
     */
    private function getChildrenOuter($data, $outer)
    {

        $data->member = $data->member()->get();

        $data->children = $data->placementChildren()->get();

        if(empty($data->children[0]) && empty($data->children[1])) {

            if(empty($this->userIdOuterTree)){
                $this->userIdOuterTree = $data->user_id;
            }

            return;
        }

        if(!empty($data->children[0]) && $data->children[0]->placement_position == $outer) {
            $this->getChildrenOuter($data->children[0], $outer);
        }elseif(empty($data->children[0])){

            if(empty($this->userIdOuterTree)){
                $this->userIdOuterTree = $data->user_id;
            }

            return;
        }

        if(!empty($data->children[1])  && $data->children[1]->placement_position == $outer) {
            $this->getChildrenOuter($data->children[1], $outer);
        }elseif(empty($data->children[1])) {

            if(empty($this->userIdOuterTree)){
                $this->userIdOuterTree = $data->user_id;
            }

            return;
        }
    }

    /**
     * get member's placement network with given member id and depth
     *
     * @param int $userId
     * @param int $depth
     * @return array|mixed
     */
    public function getPlacementNetwork(int $userId, int $depth)
    {
        $cacheKey = md5(vsprintf('%s.%s.%s.%s', [
            'member',
            'placement-netwrok',
            $userId,
            $depth
        ]));

        $result = Cache::remember($cacheKey, now()->addDays(14), function () use ($userId, $depth)
        {
            $data = $this->modelObj
                ->where('user_id', $userId)
                ->first();

            $this->getPlacementChildren($data, $depth);

            $this->getPlacementParent($data, 0, $data->uplines);

            $this->formatParentObjectWithLevel($data, 0, $uplines);

            $uplines = (empty($uplines)) ? [] : $uplines;

            $this->removeParentObject($uplines);

            $reversed = array_reverse($uplines);

            $reversed[count($reversed)] = [
                'id' => $data->id,
                'user_id' => $data->user_id,
                'sponsor_parent_user_id' => $data->sponsor_parent_user_id,
                'placement_parent_user_id' => $data->placement_parent_user_id,
                'placement_position' => $data->placement_position,
                'sponsor_position' => $data->sponsor_position,
                'created_at' => $data->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $data->updated_at->format('Y-m-d H:i:s'),
                'ibo_id' => $data->user()->first()->old_member_id,
                'ibo_name' => $data->member[0]->name,
                'status' => $data->member[0]->status,
                'root' => false
            ];

            $this->restructureChildrenObject($data, $data->children, $downlines);

            return [
                'member_data' =>[
                    'details' =>
                        [
                            'ibo_id' => $data->user()->first()->old_member_id,
                            'ibo_name' => $data->member[0]->name,
                            'sales_activity_status' => $data->member[0]->status,
                            'joined_date' => $data->member[0]->join_date,
                            'expiry_date' => $data->member[0]->expiry_date,
                            'membership_type' => $data->member[0]->enrollmentRank,
                            'highest_rank' =>  $data->member[0]->highestRank,
                            'effective_rank' => $data->member[0]->effectiveRank,
                            'personal_sales_cv' => $data->member[0]->personal_sales_cv,
                            'personal_sales_cv_percentage' => $data->member[0]->personal_sales_cv_percentage,
                            'country' => $data->member[0]->country
                        ]
                ],
                'total_direct_downlines' => $data->total_direct_downlines,
                'total_downlines' => $data->total_downlines,
                'total_active_left_downlines' => $data->total_active_left_downlines,
                'total_active_right_downlines' => $data->total_active_right_downlines,
                'downlines' => $downlines,
                'uplines' => $reversed
            ];
        });

        return $result;
    }

    /**
     * Get all the placement network info
     *
     * @param int $userId
     * @param int $depth
     * @return array
     */
    public function getPlacementNetworkTuned(int $userId, int $depth)
    {
        ini_set('memory_limit', '-1');
        //Get Current CW ID and amp cv to upgrade each rank of ba enrollemnt
        $systemSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('current_cw_id', 'amp_cv_to_upgrade_each_rank_of_ba_enrollment'));

        $currentCwId = $systemSettings['current_cw_id'][0]->value;

        $this->bonusMembersTreeRecord = DB::table('bonus_member_tree_details')
            ->where('cw_id', 47)
            ->select(
                'user_id',
                'total_direct_downline',
                'total_ba_left',
                'total_ba_right',
                'total_active_ba_right',
                'total_active_ba_left'
            )
            ->get();


        $this->countryRecord = $this->countryObj->all()->keyBy('id');

        $this->masterDataRecord = $this->masterDataObj->all()->keyBy('id');

        $this->teamBonusRanksRecord = $this->teamBonusRankObj->all()->keyBy('id');

        $this->enrollmentRanksRecord = $this->enrollmentRankObj->all()->keyBy('id');

        $this->membersRecord = DB::table('members')
            ->select(
                'user_id',
                'join_date',
                'expiry_date',
                'enrollment_rank_id',
                'highest_rank_id',
                'effective_rank_id',
                'status_id',
                'country_id',
                'personal_sales_cv',
                'personal_sales_cv_percentage'
            )
            ->get()
            ->keyBy('user_id');

        $this->usersRecord = DB::table('users')
            ->select(
                'id',
                'name',
                'old_member_id'
            )
            ->get()
            ->keyBy('id');

        $this->membersTreeRecord = DB::table('member_trees')
            ->select(
                'id',
                'user_id',
                'sponsor_parent_user_id',
                'sponsor_node_key',
                'sponsor_depth_level',
                'sponsor_node_left',
                'sponsor_node_right',
                'placement_parent_user_id',
                'placement_node_key',
                'placement_depth_level',
                'placement_node_left',
                'placement_node_right',
                'placement_position',
                'sponsor_position'
            )
            ->get()
            ->keyBy('user_id');

        $this->membersTreeRecord->each(function($member){
            if($member->placement_parent_user_id){
                if($member->placement_position == 1){
                    $this->membersTreeRecord->get($member->placement_parent_user_id)->children[0] = $member;
                } else {
                    //must be right
                    $this->membersTreeRecord->get($member->placement_parent_user_id)->children[1] = $member;
                }
            }
        });

        $targetedUser = $this->membersTreeRecord->get($userId);

        $targetedUser->ibo_name = $this->usersRecord->get($userId)->name;

        $targetedUser->ibo_id = $this->usersRecord->get($userId)->old_member_id;

        $targetedUser->sales_activity_status = $this->masterDataRecord->get(
            $this->membersRecord->get($userId)->status_id);

        $targetedUser->joined_date = $this->membersRecord->get($userId)->join_date;

        $targetedUser->expiry_date = $this->membersRecord->get($userId)->expiry_date;

        $targetedUser->membership_type = $this->enrollmentRanksRecord->get(
            $this->membersRecord->get($userId)->enrollment_rank_id);

        $targetedUser->highest_rank = $this->teamBonusRanksRecord->get(
            $this->membersRecord->get($userId)->highest_rank_id);

        $targetedUser->effective_rank = $this->teamBonusRanksRecord->get(
            $this->membersRecord->get($userId)->effective_rank_id);

        $targetedUser->personal_sales_cv = $this->membersRecord
            ->get($userId)->personal_sales_cv;

        $targetedUser->personal_sales_cv_percentage = $this->membersRecord
            ->get($userId)->personal_sales_cv_percentage;

        $targetedUser->country = $this->countryRecord->get(
            $this->membersRecord->get($userId)->country_id);

        $baDetails = [
            'total_direct_downlines' => 0,
            'total_downlines' => 0,
            'total_active_left_downlines' => 0,
            'total_active_right_downlines' => 0,
        ];

        if($this->bonusMembersTreeRecord->isNotEmpty()){

            $memberDetails = $this->bonusMembersTreeRecord->get($userId);

            $baDetails = [
                'total_direct_downlines' => $memberDetails->total_direct_downline,
                'total_downlines' => $memberDetails->total_ba_right + $memberDetails->total_ba_left,
                'total_active_left_downlines' => $memberDetails->total_active_ba_left,
                'total_active_right_downlines' => $memberDetails->total_active_ba_right,
            ];
        }

        $this->populatePlacementChildData($targetedUser, 0, $depth);

        $this->populateUplineData($userId, $uplines, 'placement');

        return array_merge([
            'member_data' =>[
                'details' =>[
                    'ibo_id' => $targetedUser->ibo_id,
                    'ibo_name' => $targetedUser->ibo_name,
                    'sales_activity_status' =>$targetedUser->sales_activity_status,
                    'joined_date' => $targetedUser->joined_date,
                    'expiry_date' => $targetedUser->expiry_date,
                    'membership_type' => $targetedUser->membership_type,
                    'highest_rank' =>  $targetedUser->highest_rank,
                    'effective_rank' => $targetedUser->effective_rank,
                    'personal_sales_cv' => $targetedUser->personal_sales_cv,
                    'personal_sales_cv_percentage' => $targetedUser->personal_sales_cv_percentage,
                    'country' => $targetedUser->country,
                ]
            ],
            'downlines' => [$targetedUser],
            'uplines' => array_reverse($uplines)
        ], $baDetails);
    }

    /**
     * Populate Placement Children Data
     *
     * @param $member
     * @param int $currentDepth
     * @param int $breakDepth
     */
    private function populatePlacementChildData($member, int $currentDepth, int $breakDepth)
    {
        if($currentDepth == $breakDepth && isset($member->children)){
            unset($member->children);
        }

        $member->ibo_name = $this->usersRecord->get($member->user_id)->name;

        $member->ibo_id = $this->usersRecord->get($member->user_id)->old_member_id;

        $member->joined_date = $this->membersRecord->get($member->user_id)->join_date;

        $member->expiry_date = $this->membersRecord->get($member->user_id)->expiry_date;

        $member->total_direct_downlines = 0;

        $member->total_downlines = 0;

        $member->total_active_left_downlines = 0;

        $member->total_active_right_downlines = 0;

        $member->depth = $currentDepth;

        if($this->bonusMembersTreeRecord->isNotEmpty()){

            $memberDetails = $this->bonusMembersTreeRecord->get($member->user_id);

            $member->total_direct_downlines = $memberDetails->total_direct_downline;

            $member->total_downlines = $memberDetails->total_ba_right + $memberDetails->total_ba_left;

            $member->total_active_left_downlines = $memberDetails->total_active_ba_left;

            $member->total_active_right_downlines = $memberDetails->total_active_ba_right;
        }

        if(!isset($member->children)){
            return;
        }

        if(isset($member->children[0]->user_id)){
            $this->populatePlacementChildData($member->children[0], $currentDepth + 1, $breakDepth);
        } else {
            $member->children[0] = [];
        }

        if(isset($member->children[1]->user_id)){
            $this->populatePlacementChildData($member->children[1], $currentDepth + 1, $breakDepth);
        } else {
            $member->children[1] = [];
        }

        return;
    }

    /**
     * get member's placement child with given member id and depth
     *
     * @param $data
     * @param int $breakPoint
     */
    private function getPlacementChildren($data, $breakPoint = 10)
    {
        $data->member = $data->member()->with(['country','status','enrollmentRank','effectiveRank','highestRank'])->get();

        $networkChilds = $this->modelObj
            ->where('placement_node_key', $data->placement_node_key)
            ->where('placement_depth_level', '>', $data->placement_depth_level)
            ->where('placement_depth_level', '<', $data->placement_depth_level + $breakPoint + 1)
            ->where('placement_node_left', '>', $data->placement_node_left)
            ->where('placement_node_right', '<', $data->placement_node_right)
            ->with('member.effectiveRank','user')
            ->get();

        $childrens = array();

        $totalDirectDownlineInfo = array();

        foreach($networkChilds as $networkChild){

            $networkChild->depth = $networkChild->placement_depth_level - $data->placement_depth_level;

            $networkChild->ibo_id = $networkChild->user->old_member_id;

            $networkChild->ibo_name = $networkChild->user->name;

            $networkChild->sales_activity_status = $networkChild->member->status;

            $networkChild->expiry_date = $networkChild->member->expiry_date;

            $networkChild->highest_rank = $networkChild->member->highestRank;

            $networkChild->effective_rank = $networkChild->member->effectiveRank;

            unset(
                $networkChild->member->user,
                $networkChild->user
            );

            $childrens[$networkChild->placement_parent_user_id]['detail'][] = $networkChild;

            (isset($childrens[$networkChild->placement_parent_user_id]['direct_downline'])) ? $childrens[$networkChild->placement_parent_user_id]['direct_downline'] += 1 : $childrens[$networkChild->placement_parent_user_id]['direct_downline'] = 1;

        }

        //Get Downline Info
        $downlineInfo = array(
            'downlines' => $this->getTotalDownlineInfo($data, 'placement', 'get', 'downlines'),
            'activeLeftDownlines' => $this->getTotalDownlineInfo($data, 'placement', 'get', 'activeLeftDownlines'),
            'activeRightDownlines' => $this->getTotalDownlineInfo($data, 'placement', 'get', 'activeRightDownlines')
        );

        $childDownline = $this->assignChildrenData($data, 'placement', $childrens, $downlineInfo);

        $data->total_downlines = $childDownline['downlines'];

        $data->total_active_left_downlines = $childDownline['activeLeftDownlines'];

        $data->total_active_right_downlines = $childDownline['activeRightDownlines'];

        return;
    }

    /**
     * get member's sponsor parent with given member id
     *
     * @param $data
     */
    private function getPlacementParent($data)
    {
        if ($data instanceof MemberTree){
            $data->parent = $data->placement()->with('member.status')->get();
        }

        if (!empty($data->parent[0])){

            $data->parent[0]->ibo_id = $data->parent[0]->member->user->old_member_id;
            $data->parent[0]->ibo_name = $data->parent[0]->member->name;
            $data->parent[0]->status = $data->parent[0]->member->status;
            $data->parent[0]->root = empty($data->parent[0]->placement_parent_user_id) ? true : false;
			
            $this->getPlacementParent($data->parent[0]);
        }

        return;
    }

    /**
     * Get all Sponsors descendants
     *
     * @param $userId
     * @return Collection
     */
    public function getAllSponsorDescendant($userId)
    {
        $memberTreeCollection = collect();
        $sponsorChildren = $this->modelObj->where('user_id', $userId)->get()->first()->sponsorChildrenRecursive;
        $this->getAllSponsorDescendantsRecursive($memberTreeCollection, $sponsorChildren);

        return $memberTreeCollection;
    }

    /**
     * Traverse through to get all the descendants
     *
     * @param $memberTreeCollection
     * @param $children
     */
    private function getAllSponsorDescendantsRecursive(&$memberTreeCollection, $children)
    {
        $children->each(function($member) use(&$memberTreeCollection){
            $memberTreeCollection->push($member);
            $this->getAllSponsorDescendantsRecursive($memberTreeCollection, $member->sponsorChildrenRecursive);
        });

    }

    /**
     * get member's sponsor network with given member id and depth
     *
     * @param int $userId
     * @param int $depth
     * @return mixed
     */
    public function getSponsorNetwork(int $userId, int $depth)
    {
        $cacheKey = md5(vsprintf('%s.%s.%s.%s', [
            'member',
            'sponsor-netwrok',
            $userId,
            $depth
        ]));

        $data = $this->modelObj
            ->where('user_id', $userId)
            ->first();

        $this->getSponsorChildren($data, $depth);

        $this->getSponsorParent($data, 0, $data->uplines);

        $this->formatParentObjectWithLevel($data, 0, $uplines);

        $uplines = (empty($uplines)) ? [] : $uplines;

        $this->removeParentObject($uplines);

        $reversed = array_reverse($uplines);

        $reversed[count($reversed)] = [
            'id' => $data->id,
            'user_id' => $data->user_id,
            'sponsor_parent_user_id' => $data->sponsor_parent_user_id,
            'placement_parent_user_id' => $data->placement_parent_user_id,
            'placement_position' => $data->placement_position,
            'sponsor_position' => $data->sponsor_position,
            'created_at' => $data->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $data->updated_at->format('Y-m-d H:i:s'),
            'ibo_id' => $data->user()->first()->old_member_id,
            'ibo_name' => $data->member[0]->name,
            'status' => $data->member[0]->status,
            'root' => false
        ];

        $this->restructureChildrenObject($data, $data->children, $downlines);

        return [
            'member_data' =>[
                'details' =>
                    [
                        'ibo_id' => $data->user()->first()->old_member_id,
                        'ibo_name' => $data->member[0]->name,
                        'sales_activity_status' => $data->member[0]->status,
                        'joined_date' => $data->member[0]->join_date,
                        'expiry_date' => $data->member[0]->expiry_date,
                        'membership_type' => $data->member[0]->enrollmentRank,
                        'highest_rank' =>  $data->member[0]->highestRank,
                        'effective_rank' => $data->member[0]->effectiveRank,
                        'personal_sales_cv' => $data->member[0]->personal_sales_cv,
                        'personal_sales_cv_percentage' => $data->member[0]->personal_sales_cv_percentage,
                        'country' => $data->member[0]->country
                    ]
                ],
            'total_direct_downlines' => $data->total_direct_downlines,
            'total_downlines' => $data->total_downlines,
            'total_active_left_downlines' => $data->total_active_left_downlines,
            'total_active_right_downlines' => $data->total_active_right_downlines,
            'downlines' => $downlines,
            'uplines' => $reversed
        ];
    }

    /**
     * Sponsor network traversal tuned version
     *
     * @param int $userId
     * @param int $depth
     * @return array
     */
    public function getSponsorNetworkTuned(int $userId, int $depth)
    {
        ini_set('memory_limit', '-1');

        //Get Current CW ID and amp cv to upgrade each rank of ba enrollemnt
        $systemSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('current_cw_id', 'amp_cv_to_upgrade_each_rank_of_ba_enrollment'));

        $currentCwId = $systemSettings['current_cw_id'][0]->value;
        
        $this->bonusMembersTreeRecord =  Cache::remember('bonusMembersTreeRecord', 10, function () use($currentCwId){
            return DB::table('bonus_member_tree_details')
                ->where('cw_id', $currentCwId)
                ->select(
                    'user_id',
                    'personal_sales_cv',
                    'total_direct_downline',
                    'total_ba_left',
                    'total_ba_right',
                    'total_active_ba_right',
                    'total_active_ba_left'
                )
                ->get()
                ->keyBy('user_id');
        });

        $this->countryRecord = Cache::remember('countryRecord', 10, function () {
            return $this->countryObj->all()->keyBy('id');;
        });

        $this->masterDataRecord = Cache::remember('masterDataRecord', 10, function () {
            return $this->masterDataObj->all()->keyBy('id');
        });

        $this->teamBonusRanksRecord = Cache::remember('teamBonusRanksRecord', 10, function () {
            return $this->teamBonusRankObj->all()->keyBy('id');
        });

        $this->enrollmentRanksRecord = Cache::remember('enrollmentRanksRecord', 10, function () {
            return $this->enrollmentRankObj->all()->keyBy('id');
        });

        $this->cwSchedulesRecord = Cache::remember('cwSchedulesRecord', 10, function () {
            return DB::table('cw_schedules')
                ->select(
                    'id',
                    'date_to'
                )
                ->get()
                ->keyBy('id');
        });

        $this->membersRecord = Cache::remember('membersRecord', 10, function () {
            return DB::table('members')
                ->select(
                    'user_id',
                    'active_until_cw_id',
                    'join_date',
                    'expiry_date',
                    'enrollment_rank_id',
                    'highest_rank_id',
                    'effective_rank_id',
                    'status_id',
                    'country_id',
                    'personal_sales_cv',
                    'personal_sales_cv_percentage'
                )
                ->get()
                ->keyBy('user_id');
        });

        $this->usersRecord = Cache::remember('usersRecord', 10, function () {
            return DB::table('users')
                ->select(
                    'id',
                    'name',
                    'old_member_id'
                )
                ->get()
                ->keyBy('id');
        });

        $this->membersTreeRecord = Cache::remember('memberTreesResults', 10, function () {
            return DB::table('member_trees')
                ->select(
                    'id',
                    'user_id',
                    'sponsor_parent_user_id',
                    'sponsor_node_key',
                    'sponsor_depth_level',
                    'sponsor_node_left',
                    'sponsor_node_right',
                    'placement_parent_user_id',
                    'placement_node_key',
                    'placement_depth_level',
                    'placement_node_left',
                    'placement_node_right',
                    'placement_position',
                    'sponsor_position'
                )
                ->get()
                ->keyBy('user_id');
        });


        $this->membersTreeRecord->each(function($member){
            if($member->sponsor_parent_user_id){
                $this->membersTreeRecord->get($member->sponsor_parent_user_id)->children[] = $member;
            }
        });

        $targetedUser = $this->membersTreeRecord->get($userId);

        $targetedUser->ibo_name = $this->usersRecord->get($userId)->name;

        $targetedUser->ibo_id = $this->usersRecord->get($userId)->old_member_id;

        $targetedUser->sales_activity_status = $this->masterDataRecord->get(
            $this->membersRecord->get($userId)->status_id);

        $targetedUser->joined_date = $this->membersRecord->get($userId)->join_date;

        $targetedUser->expiry_date = $this->membersRecord->get($userId)->expiry_date;

        $targetedUser->membership_type = $this->enrollmentRanksRecord->get(
            $this->membersRecord->get($userId)->enrollment_rank_id);

        $targetedUser->highest_rank = $this->teamBonusRanksRecord->get(
            $this->membersRecord->get($userId)->highest_rank_id);

        $targetedUser->effective_rank = $this->teamBonusRanksRecord->get(
            $this->membersRecord->get($userId)->effective_rank_id);

        $targetedUser->personal_sales_cv = $this->membersRecord
            ->get($userId)->personal_sales_cv;

        $targetedUser->personal_sales_cv_percentage = $this->membersRecord
            ->get($userId)->personal_sales_cv_percentage;

        $targetedUser->country = $this->countryRecord->get(
            $this->membersRecord->get($userId)->country_id);

        $targetedUser->current_cw_personal_sales_cv = optional($this->bonusMembersTreeRecord->get($userId))->personal_sales_cv;

        $activeUntilCwId = $this->membersRecord->get($userId)->active_until_cw_id;
        
        $targetedUser->active_until_date = optional($this->cwSchedulesRecord->get($activeUntilCwId))->date_to;

        $baDetails = [
            'total_direct_downlines' => 0,
            'total_downlines' => 0,
            'total_active_left_downlines' => 0,
            'total_active_right_downlines' => 0,
        ];

        $memberDetails = $this->bonusMembersTreeRecord->get($userId);

        if ($memberDetails != null) {    
            $baDetails = [
                'total_direct_downlines' => $memberDetails->total_direct_downline,
                'total_downlines' => $memberDetails->total_ba_right + $memberDetails->total_ba_left,
                'total_active_left_downlines' => $memberDetails->total_active_ba_left,
                'total_active_right_downlines' => $memberDetails->total_active_ba_right,
            ];
        }

        if(isset($targetedUser->children) && count($targetedUser->children) > 0){
            foreach ($targetedUser->children as &$child){
                $this->populateChildData($child, 1, $depth);
            }
        }

        $this->populateUplineData($userId, $uplines, 'sponsor');

        return array_merge([
            'member_data' =>[
                'details' =>[
                    'ibo_id' => $targetedUser->ibo_id,
                    'ibo_name' => $targetedUser->ibo_name,
                    'sales_activity_status' =>$targetedUser->sales_activity_status,
                    'joined_date' => $targetedUser->joined_date,
                    'expiry_date' => $targetedUser->expiry_date,
                    'membership_type' => $targetedUser->membership_type,
                    'highest_rank' =>  $targetedUser->highest_rank,
                    'effective_rank' => $targetedUser->effective_rank,
                    'personal_sales_cv' => $targetedUser->personal_sales_cv,
                    'personal_sales_cv_percentage' => $targetedUser->personal_sales_cv_percentage,
                    'country' => $targetedUser->country,
                ]
            ],
            'downlines' => [$targetedUser],
            'uplines' => array_reverse($uplines)
        ], $baDetails);
    }

    /**
     * Populate Sponsor Children Data
     *
     * @param $member
     * @param int $currentDepth
     * @param int $breakDepth
     */
    private function populateChildData(&$member, int $currentDepth, int $breakDepth)
    {
        if($currentDepth == $breakDepth){
            unset($member->children);
        }

        $member->ibo_name = $this->usersRecord->get($member->user_id)->name;

        $member->ibo_id = $this->usersRecord->get($member->user_id)->old_member_id;

        $member->joined_date = $this->membersRecord->get($member->user_id)->join_date;

        $member->expiry_date = $this->membersRecord->get($member->user_id)->expiry_date;

        $member->membership_type = $this->enrollmentRanksRecord->get(
            $this->membersRecord->get($member->user_id)->enrollment_rank_id);

        $member->highest_rank = $this->teamBonusRanksRecord->get(
            $this->membersRecord->get($member->user_id)->highest_rank_id);

        $member->effective_rank = $this->teamBonusRanksRecord->get(
            $this->membersRecord->get($member->user_id)->effective_rank_id);

        $member->sales_activity_status = $this->masterDataRecord->get(
            $this->membersRecord->get($member->user_id)->status_id);

        $member->personal_sales_cv = $this->membersRecord
            ->get($member->user_id)->personal_sales_cv;

        $member->personal_sales_cv_percentage = $this->membersRecord
            ->get($member->user_id)->personal_sales_cv_percentage;

        $member->current_cw_personal_sales_cv = optional($this->bonusMembersTreeRecord->get($member->user_id))->personal_sales_cv;

        $activeUntilCwId = $this->membersRecord->get($member->user_id)->active_until_cw_id;
        
        $member->active_until_date = optional($this->cwSchedulesRecord->get($activeUntilCwId))->date_to;
    
        $member->total_direct_downlines = 0;

        $member->total_downlines = 0;

        $member->total_active_left_downlines = 0;

        $member->total_active_right_downlines = 0;

        $member->depth = $currentDepth;

        $memberDetails = $this->bonusMembersTreeRecord->get($member->user_id);

        if ($memberDetails != null) {    
            $member->total_direct_downlines = $memberDetails->total_direct_downline;

            $member->total_downlines = $memberDetails->total_ba_right + $memberDetails->total_ba_left;

            $member->total_active_left_downlines = $memberDetails->total_active_ba_left;

            $member->total_active_right_downlines = $memberDetails->total_active_ba_right;
        }

        if(isset($member->children)){
            foreach ($member->children as &$child){
                $this->populateChildData($child, $currentDepth + 1, $breakDepth);
            }
        }

        return;
    }

    /**
     * get member's sponsor parent data
     *
     * @param int $userId
     * @param $memberUplines
     * @param string $networkType
     */
    private function populateUplineData(int $userId, &$memberUplines, string $networkType)
    {
        $memberTreeDetail = $this->membersTreeRecord->get($userId);

        $uplineUserId = ($networkType == 'sponsor') ?
            $memberTreeDetail->sponsor_parent_user_id :
                $memberTreeDetail->placement_parent_user_id;
                
        $memberUplines[] = [
                'id' => $memberTreeDetail->id,
                'user_id' => $memberTreeDetail->user_id,
                'sponsor_parent_user_id' => $memberTreeDetail->sponsor_parent_user_id,
                'sponsor_node_key' => $memberTreeDetail->sponsor_node_key,
                'sponsor_node_left' => $memberTreeDetail->sponsor_node_left,
                'sponsor_node_right' => $memberTreeDetail->sponsor_node_right,
                'placement_parent_user_id' => $memberTreeDetail->placement_parent_user_id,
                'placement_node_key' => $memberTreeDetail->placement_node_key,
                'placement_node_left' => $memberTreeDetail->placement_node_left,
                'placement_node_right' => $memberTreeDetail->placement_node_right,
                'placement_position' => $memberTreeDetail->placement_position,
                'sponsor_position' => $memberTreeDetail->sponsor_position,
                'ibo_id' => $this->usersRecord->get($userId)->old_member_id,
                'ibo_name' => $this->usersRecord->get($userId)->name,
                'status' => $this->masterDataRecord->get(
                    $this->membersRecord->get($userId)->status_id),
                'root' => empty($uplineUserId) ? true : false
            ];

        if($uplineUserId){
            $this->populateUplineData(
                $uplineUserId,
                $memberUplines,
                $networkType
            );
        }

        return;
    }

    /**
     * assign children data to parent by given parents data and children data
     *
     * @param MemberTree $parents
     * @param string $networkType
     * @param array $childrens
     * @param array $downlineInfos
     * @return array
     */
    private function assignChildrenData(MemberTree $parents, string $networkType, array $childrens, array $downlineInfos)
    {
        $downlineCount = array(
            'downlines' =>
                (isset($downlineInfos['downlines'][$parents->user_id]['count'])) ?
                    $downlineInfos['downlines'][$parents->user_id]['count'] : 0,
            'activeLeftDownlines' =>
                (isset($downlineInfos['activeLeftDownlines'][$parents->user_id]['count'])) ?
                    $downlineInfos['activeLeftDownlines'][$parents->user_id]['count'] : 0,
            'activeRightDownlines' =>
                (isset($downlineInfos['activeRightDownlines'][$parents->user_id]['count'])) ?
                    $downlineInfos['activeRightDownlines'][$parents->user_id]['count'] : 0
        );

        if(isset($childrens[$parents->user_id])){
            $parents->total_direct_downlines = $childrens[$parents->user_id]['direct_downline'];

            $parents->children = $childrens[$parents->user_id]['detail'];

            foreach($childrens[$parents->user_id]['detail'] as $childItem){

                $childDownline = $this->assignChildrenData($childItem, $networkType, $childrens, $downlineInfos);

                $downlineCount = array(
                    'downlines' => $downlineCount['downlines'] + $childDownline['downlines'],
                    'activeLeftDownlines' => $downlineCount['activeLeftDownlines'] + $childDownline['activeLeftDownlines'],
                    'activeRightDownlines' => $downlineCount['activeRightDownlines'] + $childDownline['activeRightDownlines']
                );
            }

            $parents->total_downlines = $downlineCount['downlines'];

            $parents->total_active_left_downlines = $downlineCount['activeLeftDownlines'];

            $parents->total_active_right_downlines = $downlineCount['activeRightDownlines'];

        } else {

            if($networkType == 'placement'){

                $node_left = $parents->placement_node_left;

                $node_right = $parents->placement_node_right;

            } else if($networkType == 'sponsor'){

                $node_left = $parents->sponsor_node_left;

                $node_right = $parents->sponsor_node_right;

            }

            if($node_right > $node_left + 1){

                $downlineCount = array(
                    'downlines' => $this->getTotalDownlineInfo($parents, $networkType, 'count', 'downlines'),
                    'activeLeftDownlines' => $this->getTotalDownlineInfo($parents, $networkType, 'count', 'activeLeftDownlines'),
                    'activeRightDownlines' => $this->getTotalDownlineInfo($parents, $networkType, 'count', 'activeRightDownlines')
                );

                $parents->total_direct_downlines = $this->getTotalDownlineInfo($parents, $networkType, 'count', 'directDownlines');

                $parents->total_downlines = $downlineCount['downlines'];

                $parents->total_active_left_downlines = $downlineCount['activeLeftDownlines'];

                $parents->total_active_right_downlines = $downlineCount['activeRightDownlines'];
            }
        }

        return $downlineCount;
    }

    /**
     * Get Or calculate total downline info by given network type and calculate type
     *
     * @param MemberTree $parents
     * @param string $networkType
     * @param string $action
     * @param string|NULL $calulateType
     * @return array|bool
     */
    private function getTotalDownlineInfo(MemberTree $parents, string $networkType, string $action, string $calulateType = NULL)
    {
        $query = $this->modelObj
            ->where(function ($q) use ($parents, $networkType, $calulateType) {
                if($networkType == 'placement'){

                    $q->where('placement_node_key', $parents->placement_node_key);

                    $q->where('placement_depth_level', '>', $parents->placement_depth_level);

                    $q->where('placement_node_left', '>', $parents->placement_node_left);

                    $q->where('placement_node_right', '<', $parents->placement_node_right);

                    if(in_array($calulateType, array('directDownlines'))){
                        $q->where('placement_depth_level', $parents->placement_depth_level + 1);
                    }

                } else if($networkType == 'sponsor'){

                    $q->where('sponsor_node_key', $parents->sponsor_node_key);

                    $q->where('sponsor_depth_level', '>', $parents->sponsor_depth_level);

                    $q->where('sponsor_node_left', '>', $parents->sponsor_node_left);

                    $q->where('sponsor_node_right', '<', $parents->sponsor_node_right);

                    if(in_array($calulateType, array('directDownlines'))){
                        $q->where('sponsor_depth_level', $parents->sponsor_depth_level + 1);
                    }
                }

                if($calulateType == 'activeLeftDownlines'){

                    $q->where('placement_position', 1);

                } else if($calulateType == 'activeRightDownlines'){

                    $q->where('placement_position', 2);

                }
            })
            ->with(['member' => function ($q) use ($calulateType) {
                    if(in_array($calulateType, array('activeLeftDownlines','activeRightDownlines'))){
                        $status = $this->masterObj
                            ->where("key", "member_status")
                            ->with(['masterData' => function ($q_status){
                                $q_status->where('title','ACTIVE');
                            }])
                            ->first();

                        $q->where('status_id', $status->masterData[0]->id);
                    }
                }
            ]);

        if($action == 'get'){

            $infos = $query->get();

            $downlineInfos = array();

            foreach($infos as $info){

                if($networkType == 'placement'){

                    $downlineInfos[$info->placement_parent_user_id]['detail'][] = $info;

                    (isset($downlineInfos[$info->placement_parent_user_id]['count'])) ?
                        $downlineInfos[$info->placement_parent_user_id]['count'] += 1 :
                            $downlineInfos[$info->placement_parent_user_id]['count'] = 1;

                } else if($networkType == 'sponsor'){

                    $downlineInfos[$info->sponsor_parent_user_id]['detail'][] = $info;

                    (isset($downlineInfos[$info->sponsor_parent_user_id]['count'])) ?
                        $downlineInfos[$info->sponsor_parent_user_id]['count'] += 1 :
                            $downlineInfos[$info->sponsor_parent_user_id]['count'] = 1;
                }
            }

            return $downlineInfos;

        } else if($action == 'count'){

            $total = $query->count();

            return $total;

        } else {
            return false;
        }
    }

    /**
     * get member's sponsor child with given member id and depth
     *
     * @param MemberTree $data
     * @param int $breakPoint
     */
    private function getSponsorChildren(MemberTree $data, int $breakPoint = 10)
    {
        $data->member = $data->member()->with(['country','status','enrollmentRank','effectiveRank','highestRank'])->get();

        $networkChilds = $this->modelObj
            ->where('sponsor_node_key', $data->sponsor_node_key)
            ->where('sponsor_depth_level', '>', $data->sponsor_depth_level)
            ->where('sponsor_depth_level', '<', $data->sponsor_depth_level + $breakPoint + 1)
            ->where('sponsor_node_left', '>', $data->sponsor_node_left)
            ->where('sponsor_node_right', '<', $data->sponsor_node_right)
            ->with('member.effectiveRank','user')
            ->get();

        $childrens = array();

        $totalDirectDownlineInfo = array();

        foreach($networkChilds as $networkChild){

            $networkChild->depth = $networkChild->sponsor_depth_level - $data->sponsor_depth_level;

            $networkChild->ibo_id = $networkChild->user->old_member_id;

            $networkChild->ibo_name = $networkChild->user->name;

            $networkChild->sales_activity_status = $networkChild->member->status;

            $networkChild->expiry_date = $networkChild->member->expiry_date;

            $networkChild->highest_rank = $networkChild->member->highestRank;

            $networkChild->effective_rank = $networkChild->member->effectiveRank;

            unset(
                $networkChild->member->user,
                $networkChild->user
            );

            $childrens[$networkChild->sponsor_parent_user_id]['detail'][] = $networkChild;

            (isset($childrens[$networkChild->sponsor_parent_user_id]['direct_downline'])) ?
                $childrens[$networkChild->sponsor_parent_user_id]['direct_downline'] += 1 :
                    $childrens[$networkChild->sponsor_parent_user_id]['direct_downline'] = 1;
        }

        //Get Downline Info
        $downlineInfo = array(
            'downlines' => $this->getTotalDownlineInfo($data, 'sponsor', 'get', 'downlines'),
            'activeLeftDownlines' => $this->getTotalDownlineInfo($data, 'sponsor', 'get', 'activeLeftDownlines'),
            'activeRightDownlines' => $this->getTotalDownlineInfo($data, 'sponsor', 'get', 'activeRightDownlines')
        );

        $childDownline = $this->assignChildrenData($data, 'sponsor', $childrens, $downlineInfo);

        $data->total_downlines = $childDownline['downlines'];

        $data->total_active_left_downlines = $childDownline['activeLeftDownlines'];

        $data->total_active_right_downlines = $childDownline['activeRightDownlines'];

        return;
    }

    /**
     * get member's sponsor parent with given member id
     *
     * @param $data
     */
    private function getSponsorParent($data)
    {
        if ($data instanceof MemberTree){
            $data->parent = $data->parent()->with('member.status')->get();
        }

        if (!empty($data->parent[0])){
            $data->parent[0]->ibo_id = $data->parent[0]->member->user->old_member_id;
            $data->parent[0]->ibo_name = $data->parent[0]->member->name;
            $data->parent[0]->status = $data->parent[0]->member->status;
            $data->parent[0]->root = empty($data->parent[0]->sponsor_parent_user_id) ? true : false;
			
            $this->getSponsorParent($data->parent[0]);
        }

        return;
    }

    /**
     * reformat the upline object with level
     *
     * @param $data
     * @param $level
     * @param $upline
     */
    private function formatParentObjectWithLevel($data, $level, &$upline)
    {
        if (!empty($data->parent[0])) {
            unset(
                $data->parent[0]->sponsor_depth_level,
                $data->parent[0]->placement_depth_level,
                $data->parent[0]->member);

            if (count($data->parent) > 0) {
                $upline[$level] = $data->parent[0];
            }
            $this->formatParentObjectWithLevel($data->parent[0], $level + 1, $upline);
        }
        return;
    }

    /**
     * remove the parent object for given model object
     *
     * @param $data
     */
    private function removeParentObject($data)
    {
        foreach ($data as $d){
            unset($d->parent);
        }
    }
	
    /**
     * restructure children object to add self detail data in downLines.
     *
     * @param $data
     * @param $children
     * @param $downLines
     */
    private function restructureChildrenObject($data, $children, &$downLines)
    {

        $downLines[] = (object)[
            'id' => $data->id,
            'user_id' => $data->user_id,
            'sponsor_parent_user_id' => $data->sponsor_parent_user_id,
            'placement_parent_user_id' => $data->placement_parent_user_id,
            'placement_position' => $data->placement_position,
            'sponsor_position' => $data->sponsor_position,
            'created_at' => $data->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $data->updated_at->format('Y-m-d H:i:s'),
            'depth' => -1,
            'ibo_id' => $data->user()->first()->old_member_id,
            'ibo_name' => $data->member[0]->name,
            'sales_activity_status' => $data->member[0]->status,
            'expiry_date' => $data->member[0]->expiry_date,
            'total_direct_downlines' => ($data->total_direct_downlines) ? $data->total_direct_downlines : 0,
            'total_downlines' => ($data->total_downlines) ? $data->total_downlines : 0,
            'total_active_left_downlines' => ($data->total_active_left_downlines) ? $data->total_active_left_downlines : 0,
            'total_active_right_downlines' => ($data->total_active_right_downlines) ? $data->total_active_right_downlines : 0,
            'highest_rank' => $data->member[0]->highestRank,
            'effective_rank' => $data->member[0]->effectiveRank,
            'member' => $data->member[0],
            'children' => $children
        ];
				
        return;
    }

    /**
     * retrieve all member tree info
     */
    private function populateMemberTreeData()
    {
        $this->usersRecord = Cache::remember('usersRecord', 10, function () {
            return DB::table('users')
                ->select(
                    'id',
                    'name',
                    'old_member_id'
                )
                ->get()
                ->keyBy('id');
        });

        $this->membersRecord = Cache::remember('membersRecord', 10, function () {
            return DB::table('members')
                ->select(
                    'user_id',
                    'active_until_cw_id',
                    'join_date',
                    'expiry_date',
                    'enrollment_rank_id',
                    'highest_rank_id',
                    'effective_rank_id',
                    'status_id',
                    'country_id',
                    'personal_sales_cv',
                    'personal_sales_cv_percentage'
                )
                ->get()
                ->keyBy('user_id');
        });

        $this->membersTreeRecord = Cache::remember('memberTreesResults', 10, function () {
            return DB::table('member_trees')
                ->select(
                    'id',
                    'user_id',
                    'sponsor_parent_user_id',
                    'sponsor_node_key',
                    'sponsor_depth_level',
                    'sponsor_node_left',
                    'sponsor_node_right',
                    'placement_parent_user_id',
                    'placement_node_key',
                    'placement_depth_level',
                    'placement_node_left',
                    'placement_node_right',
                    'placement_position',
                    'sponsor_position'
                )
                ->get()
                ->keyBy('user_id');
        });

        $this->masterDataRecord = Cache::remember('masterDataRecord', 10, function () {
            return $this->masterDataObj->all()->keyBy('id');
        });
    }

    /*
    * Search member from the same network
    */
    public function searchSponsorNetwork(Array $option)
    {
        if(!$option['user_id'] && $option['old_member_id']){
            $option['user_id'] = $this->userObj->where('old_member_id', $option['old_member_id'])->get()->first()->id;
        }

        if($this->isUser('member')){
            //if member,
            $validate = $this->verifySameMemberTreeNetwork(
                'sponsor',
                Auth::id() ,
                $option['user_id']
            )['result'];

            if(!$validate){
                return [
                    'error'=> trans('message.member.not-sponsor-child', ['userId' => $option['old_member_id']])
                ];
            }
        }

        return collect(
            ['data' => $this->userObj->where('id', $option['user_id'])->select('name', 'id AS user_id', 'old_member_id')->get()]
        );
    }

    /**
     * verify same member network with given tree type, first user id, and second user id
     *
     * @param string $treeType
     * @param int $firstUserId
     * @param int $secondUserId
     * @return array
     */
    public function verifySameMemberTreeNetwork(
        string $treeType,
        int $firstUserId,
        int $secondUserId = 0,
        $iboId = 0
    )
    {
        if($iboId){
            $secondUserId = $this->userObj->select('id')->where('old_member_id', $iboId)->get();
            if($secondUserId->count()){
                $secondUserId = $secondUserId->first()->id;
            }else{
                abort(404);
            }
        }

        $this->populateMemberTreeData();

        $this->populateUplineData($firstUserId, $firstUplines, $treeType);

        $this->populateUplineData($secondUserId, $secondUplines, $treeType);

        $validateFirstUserUpline = collect($firstUplines)->where('user_id', $secondUserId)->first();

        $validateSecondUserUpline = collect($secondUplines)->where('user_id', $firstUserId)->first();

        return ['result' => ($validateFirstUserUpline || $validateSecondUserUpline) ? true : false];
    }

    /**
     * verify member downline with given tree type, upline member id, and downline member id
     *
     * @param string $treeType
     * @param int $uplineUserId
     * @param int $downlineUserId
     * @return array
     */
    public function verifyMemberTreeDownline(string $treeType, int $uplineUserId, int $downlineUserId)
    {
        $this->populateMemberTreeData();

        $this->populateUplineData($downlineUserId, $uplines, $treeType);

        $validateUpline = collect($uplines)->where('user_id', $uplineUserId)->first();

        return ['result' => ($validateUpline) ? true : false];
    }

    /**
     * Lightweight get all sponsors child user id (unlimited sponsor tree level)
     *
     * @param int $userId
     * @param bool $associateLevel
     * @return array
     */
    public function getAllSponsorChildUserId(int $userId, bool $associateLevel)
    {
        $membersTree = collect();

        // initialize tree
        DB::table('member_trees')
            ->select('user_id', 'sponsor_parent_user_id')
            ->get()
            ->each(function($record) use($membersTree) {
                $record->directSponsorChilds = [];

                $membersTree->put($record->user_id, $record);
            });
        
        // build relationship
        $membersTree->each(function(&$member, $key) use($membersTree) {
                if ($member->sponsor_parent_user_id) {
                    $member->sponsorParent = $membersTree->get($member->sponsor_parent_user_id);
    
                    $member->sponsorParent->directSponsorChilds[] = $member;
                }
            });

        // traverse tree and get result
        $sponsorChildUserIds = [];

        if ($associateLevel) {
            $this->traverseSponsorTreePluckUserIdWithLevel($sponsorChildUserIds, $membersTree->get($userId)->directSponsorChilds, 1);
        }
        else {
            $this->traverseSponsorTreePluckUserId($sponsorChildUserIds, $membersTree->get($userId)->directSponsorChilds);
        }

        return $sponsorChildUserIds;
    }

    /**
     * this will allow the temporary tree table record to be inserted to actual member tree
     *
     * @param $uniqueId - id from temp enrollment tree table
     * @param int $userId
     * @return bool
     */
    public function insertToMemberTreeFromTemp($uniqueId = null, int $userId)
    {
        //please make sure all payments are received and we will apply this user into the actual member tree
        $enrolmentTempTable = $this->enrollmentTempTreeObj->where('unique_id', $uniqueId)->get()->first();

        if($enrolmentTempTable) {
            $status = $this->insertEnrollmentTempTree(
                [
                    'user_id' => $userId,
                    'sponsor_user_id' => $enrolmentTempTable->sponsor_user_id,
                    'placement_user_id' => $enrolmentTempTable->placement_user_id,
                    'position' => ($enrolmentTempTable->is_auto) ? 0 : $enrolmentTempTable->placement_position
                ]
            );
            return $status;
        }

        return false;
    }

    /**
     * @param array $information - user_id ,sponsor_user_id, placement_user_id, side
     * @return array
     */
    public function assignMemberTree(array $information)
    {
        return ['result' => $this->insertToMemberTree($information)];
    }

    /**
     * @param array $information - user_id ,sponsor_user_id, placement_user_id, side
     * @return bool
     */
    private function insertToMemberTree($information)
    {

        $userId = $information['user_id']; // person to be placed
        $sponsorUserId = $information['sponsor_user_id']; // the sponsor of the person
        $placementUserId = (isset($information['placement_user_id']) && $information['placement_user_id'])
            ? $information['placement_user_id'] : $sponsorUserId; // if empty, will be using sponsor id
        $placementPosition = $information['position']; // 1 = left, 2 = right, 0 = auto

        $sponsorMember = $this->modelObj->where('user_id', $sponsorUserId)->get()->first();

        //we will do an insertion into member_trees
        $memberTreeTable = new MemberTree;
        $memberTreeTable->user_id = $userId;
        $memberTreeTable->sponsor_parent_user_id = $sponsorUserId;

        $memberTreeTable->sponsor_depth_level = $sponsorMember->sponsor_depth_level + 1;
        $memberTreeTable->placement_depth_level = $sponsorMember->placement_depth_level + 1;

        // populate placement info
        if(!$placementPosition){ // 0 = auto placement
            //automatic placement, we will have to check for the previous CW results before assigning
            $cwId = $this->cwSchedulesRepositoryObj->getCwSchedulesList('current')->first();
            $previousCwId = ($cwId == 1) ? 1 : $cwId - 1;

            $bonusSummary = $this->bonusSummaryObj->select('id')
                ->where('user_id', $sponsorMember->user_id)
                ->where('cw_id', $previousCwId)
                ->get()->first();

            $bonusSummaryId = (isset($bonusSummary->id)) ? $bonusSummary->id : false;

            $payLegSide = 1;

            if($bonusSummaryId){
                $payLegSide = $this->bonusTeamBonusDetailsObj->select('gcv_bring_forward_position')
                    ->where('bonuses_summary_id', $bonusSummaryId)->where('gcv_leg_group', 'PAY')->get()->first();

                if(isset($payLegSide->gcv_bring_forward_position) && $payLegSide->gcv_bring_forward_position){
                    $payLegSide = $payLegSide->gcv_bring_forward_position;
                }
            }

            //we will assign the user to outer left/right based on $payLegSide
            $placementMember = $this->findOuterPlacement($sponsorMember->user_id, $payLegSide);
            $placementMember = $this->modelObj->where('user_id',
                $placementMember->user_id)->get()->first();

            $memberTreeTable->placement_depth_level = $placementMember->placement_depth_level + 1;
            $memberTreeTable->placement_parent_user_id = $placementMember->user_id;
            $memberTreeTable->placement_position = $payLegSide;
        }else{ // Selected side
            $placementMember = $this->modelObj->where('user_id', $placementUserId)->get()->first();
            $memberTreeTable->placement_depth_level = $placementMember->placement_depth_level + 1;
            $memberTreeTable->placement_parent_user_id = $placementUserId;
            $memberTreeTable->placement_position = $placementPosition;
        }

        //we must check so it wont be any duplicate in the placement.
        if(!$this->modelObj->where('placement_parent_user_id', $memberTreeTable->placement_user_id)
            ->where('placement_position', $memberTreeTable->placement_position)->exists()){
            // save the records once we confirm the placement isnt occupied yet
            return $memberTreeTable->save();
        }else{
            //if it has been occupied, assign it to the outer left/right automatically
            //searching available outer left/right
            $placementMember = $this->findOuterPlacement($memberTreeTable->placement_parent_user_id,
                $memberTreeTable->placement_position);
            $placementMember = $this->modelObj->where('user_id',
                $placementMember->user_id)->get()->first();

            $memberTreeTable->placement_depth_level = $placementMember->placement_depth_level + 1;
            $memberTreeTable->placement_parent_user_id = $placementMember->user_id;

            return $memberTreeTable->save();
        }
    }

    /**
     * This will look for the nearest placement location based on the pay/power leg on the previous CW.
     *
     * @param $memberId
     * @param $side
     * @return MemberTree
     */
    private function findOuterPlacement($memberId, $side)
    {
        $this->membersTreeRecord = DB::table('member_trees')
            ->select(
                'user_id',
                'placement_parent_user_id',
                'placement_position'
            )
            ->get()->keyBy('user_id');

        $this->membersTreeRecord->each(function($member){
            if($member->placement_parent_user_id){
                if($member->placement_position == 1){
                    $this->membersTreeRecord->get($member->placement_parent_user_id)->left = $member;
                } else {
                    //must be right
                    $this->membersTreeRecord->get($member->placement_parent_user_id)->right = $member;
                }
            }
        });

        return $this->recursiveOuterPlacement($this->membersTreeRecord->get($memberId), $side); // instance of membertree
    }

    /**
     * to be used with $this->findOuterPlacement function only
     * @param $member
     * @param $side
     * @return MemberTree instance
     */
    private function recursiveOuterPlacement($member, $side)
    {
        if($side == 1 && !isset($member->left)){
            return $member;
        }elseif($side == 2 && !isset($member->right)){
            return $member;
        }elseif($side == 1){
            return $this->recursiveOuterPlacement($member->left, $side);
        }else{
            return $this->recursiveOuterPlacement($member->right, $side);
        }
    }

    /**
     * Insert enrolment temp tree
     *
     * leave third and forth parameter empty if is-auto
     *
     * @param $uniqueId
     * @param $sponsorUserId
     * @param int $placementUserId
     * @param int $placementPosition
     * @return bool
     */
    public function insertEnrollmentTempTree(
        $uniqueId,
        int $sponsorUserId = null,
        int $placementUserId = null,
        int $placementPosition = null
    )
    {
        if($placementUserId === null){
            //this is an auto placement
            $enrolmentTempTable = $this->enrollmentTempTreeObj->firstOrNew(['unique_id' => $uniqueId]);

            $enrolmentTempTable->sponsor_user_id = $sponsorUserId;
            $enrolmentTempTable->placement_user_id = null;
            $enrolmentTempTable->placement_position = null;
            $enrolmentTempTable->is_auto = true;
            $enrolmentTempTable->save();
        }else{
            //this is not an auto placement, we need to validate one last time before putting in
            //$result = $this->validatePlacement($sponsorUserId, $placementUserId, $placementPosition);
            //if($result){
                $enrolmentTempTable = $this->enrollmentTempTreeObj->firstOrNew(['unique_id' => $uniqueId]);
                $enrolmentTempTable->sponsor_user_id = $sponsorUserId;
                $enrolmentTempTable->placement_user_id = $placementUserId;
                $enrolmentTempTable->placement_position = $placementPosition;
                $enrolmentTempTable->is_auto = false;
                $enrolmentTempTable->save();
            //}else{
                //@todo return error saying that this placement is already occupied
            //}
        }

        return true;
    }

    /**
     * To check if the new user is able to be stored in the placement or not
     *
     * @param int $sponsorUserId
     * @param int $placementOldMemberId
     * @param int $placementPosition
     * @return array|mixed
     */
    public function validatePlacement(int $sponsorUserId, int $placementOldMemberId, int $placementPosition)
    {
        $placementUser = $this->userObj->where('old_member_id', $placementOldMemberId)->first();

        //quickly get a list of member tree
        $this->membersTreeRecord = DB::table('member_trees')
            ->select(
                'user_id',
                'sponsor_parent_user_id',
                'placement_parent_user_id',
                'placement_position'
            )
            ->get()->keyBy('user_id');

        //we must make sure the placement user Id is actually a sponsor of the sponsor id
        $placementMember = $this->membersTreeRecord->get($placementUser->id);

        $isValidUpline = $this->isValidPlacement($sponsorUserId, $placementMember);

        if(!$isValidUpline){
            return [
                'status' => false,
                'message' => trans('message.member-placement-verify.is-not-valid-upline')
            ];
        }

        //check if the position of this user is still available
        $occupiedPlacement = DB::table('member_trees')
            ->where('placement_parent_user_id', $placementUser->id)
            ->where('placement_position', $placementPosition)
            ->exists();

        if($occupiedPlacement){
            return [
                'status' => false,
                'message' => trans('message.member-placement-verify.occupied')
            ];
        }

        //else this is free and can be used up
        return [
            'status' => true,
            'message' => trans('message.member-placement-verify.valid'),
            'user' => $placementUser
        ];
    }

    /**
     * Recursive method to find the existence of the placement parent
     *
     * @param $uplineMemberId
     * @param $searchMember
     * @return bool
     */
    private function isValidPlacement($uplineMemberId, $searchMember)
    {
        if($searchMember->placement_parent_user_id == $uplineMemberId){
            return true;
        }elseif($searchMember->placement_parent_user_id){
            return $this->isValidPlacement($uplineMemberId,
                $this->membersTreeRecord->get($searchMember->placement_parent_user_id));
        }

        return false;
    }

    /**
     * recursive function used by getAllSponsorChildUserId to traverse sponsor tree and pluck user_id
     *
     * @param array $sponsorChildUserIds
     * @param array $sponsorChilds
     */
    private function traverseSponsorTreePluckUserId(array &$sponsorChildUserIds, array $sponsorChilds)
    {
        collect($sponsorChilds)->each(function($memberTree) use (&$sponsorChildUserIds){
            array_push($sponsorChildUserIds, $memberTree->user_id);

            $this->traverseSponsorTreePluckUserId($sponsorChildUserIds, $memberTree->directSponsorChilds);
        });
    }

    /**
     * recursive function used by getAllSponsorChildUserId to traverse sponsor tree and pluck user_id with level
     *
     * @param array $sponsorChildUserIds
     * @param array $sponsorChilds
     * @param int $currentLevel
     */
    private function traverseSponsorTreePluckUserIdWithLevel(array &$sponsorChildUserIds, array $sponsorChilds, int $currentLevel)
    {
        collect($sponsorChilds)->each(function($memberTree) use (&$sponsorChildUserIds, $currentLevel){
            $sponsorChildUserIds[$memberTree->user_id] = $currentLevel;

            $this->traverseSponsorTreePluckUserIdWithLevel($sponsorChildUserIds, $memberTree->directSponsorChilds, $currentLevel + 1);
        });
    }

    /**
     * use to create member tree details record based on currenct cw
     */
    private function createLiteBonusMemberTreeDetailsRecord()
    {
        $systemSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('current_cw_id'));

        $currentCwId = $systemSettings['current_cw_id'][0]->value;

        $this->liteBonusMemberTreeDetailsRecord = Cache::remember('liteBonusMemberTreeDetailsRecord', 10, function() use($currentCwId) {
            return DB::table('bonus_member_tree_details')
                ->where('cw_id', $currentCwId)
                ->select(
                    'user_id',
                    'personal_sales_cv',
                    'total_direct_downline',
                    'total_downline',
                    'total_unique_line_left',
                    'total_unique_line_right',
                    'is_active_brand_ambassador',
                    'cw_id',
                    'left_gcv',
                    'right_gcv'
                )
                ->get()
                ->keyBy('user_id');
        });
    }

    /**
     * Lightweight sponsor network traversal (unlimited sponsor tree level)
     *
     * @param int $userId
     * @param int $depth
     * @return array
     */
    public function getSponsorDownlineListing(int $userId, int $depth)
    {
        $systemSettings = $this->settingRepositoryObj
            ->getSettingDataByKey(array('current_cw_id'));

        $currentCwId = $systemSettings['current_cw_id'][0]->value;

        $this->createLiteBonusMemberTreeDetailsRecord();

        $this->liteMasterDataRecord = Cache::remember('liteMasterDataRecord', 10, function() {
            return DB::table('master_data')
                ->select(
                    'id',
                    'title'
                )
                ->get()
                ->keyBy('id');
        });

        $this->liteTeamBonusRanksRecord = Cache::remember('liteTeamBonusRanksRecord', 10, function() {
            return DB::table('team_bonus_ranks')
                ->select(
                    'id',
                    'rank_name'
                )
                ->get()
                ->keyBy('id');
        });

        $this->liteEnrollmentRanksRecord = Cache::remember('liteEnrollmentRanksRecord', 10, function() {
            return DB::table('enrollment_ranks')
            ->select(
                'id',
                'rank_code'
            )
            ->get()
            ->keyBy('id');
        });

        $this->liteCwSchedulesRecord = Cache::remember('liteCwSchedulesRecord', 10, function() use($currentCwId) {
            $cwScheduleRecord = DB::table('cw_schedules')
                ->select(
                    'id',
                    'cw_name',
                    'date_to'
                )
                ->orderBy('date_to')
                ->get()
                ->keyBy('id');

            $activeExpiring = -1;

            foreach ($cwScheduleRecord as $record) {
                if ($record->id == $currentCwId || $activeExpiring != -1) {
                    $activeExpiring++;
                }
                $record->active_expiring = $activeExpiring;
            }

            return $cwScheduleRecord;
        });

        $this->liteMembersRecord = Cache::remember('liteMembersRecord', 10, function() {
            return DB::table('members')
                ->select(
                    'user_id',
                    'active_until_cw_id',
                    'join_date',
                    'expiry_date',
                    'enrollment_rank_id',
                    'highest_rank_id',
                    'effective_rank_id',
                    'status_id',
                    'country_id',
                    'personal_sales_cv',
                    'personal_sales_cv_percentage',
                    'updated_at'
                )
                ->get()
                ->keyBy('user_id');
        });

        $this->liteUsersRecord = Cache::remember('liteUsersRecord', 10, function() {
            return DB::table('users')
                ->select(
                    'id',
                    'name',
                    'old_member_id'
                )
                ->get()
                ->keyBy('id');
        });

        $this->liteMemberTreesRecord = Cache::remember('liteMemberTreesRecord', 10, function() {
            return DB::table('member_trees')
                ->select(
                    'user_id',
                    'sponsor_parent_user_id'
                )
                ->get()
                ->keyBy('user_id');
        });


        $this->liteMemberTreesRecord->each(function($member) {
            if($member->sponsor_parent_user_id){
                $this->liteMemberTreesRecord->get($member->sponsor_parent_user_id)->children[] = $member;
            }
        });

        $targetedUser = $this->liteMemberTreesRecord->get($userId);

        $this->populateSponsorDownlineListingData($targetedUser, 0);

        if (isset($targetedUser->children) && count($targetedUser->children) > 0) {
            foreach ($targetedUser->children as &$child) {
                $this->traverseSponsorDownlineListing($child, 1, $depth);
            }
        }

        $memberDetails = $this->liteMembersRecord->get($targetedUser->user_id);

        $this->populateSponsorDownlineListingData($memberDetails, 0);

        $countryRecord = $this->countryObj->find(optional($memberDetails)->country_id);

        $memberDetails->country = optional($countryRecord)->name;

        return [
            'member_data' => [
                'details' => $memberDetails
            ],
            'downlines' => [$targetedUser]
        ];
    }

    /**
     * Populate Sponsor Children Data
     *
     * @param $member
     * @param int $currentDepth
     */
    private function populateSponsorDownlineListingData(&$member, int $currentDepth)
    {
        $usersRecord = $this->liteUsersRecord->get($member->user_id);

        $membersRecord = $this->liteMembersRecord->get($member->user_id);

        $member->ibo_name = optional($usersRecord)->name;

        $member->ibo_id = optional($usersRecord)->old_member_id;

        $member->joined_date = optional($membersRecord)->join_date;

        $member->expiry_date = optional($membersRecord)->expiry_date;

        $member->enrollment_rank = optional($this->liteEnrollmentRanksRecord->get(
            optional($membersRecord)->enrollment_rank_id))->rank_code;

        $member->highest_rank = optional($this->liteTeamBonusRanksRecord->get(
            optional($membersRecord)->highest_rank_id))->rank_name;

        $member->effective_rank = optional($this->liteTeamBonusRanksRecord->get(
            optional($membersRecord)->effective_rank_id))->rank_name;

        $member->sales_activity_status = optional($this->liteMasterDataRecord->get(
            optional($membersRecord)->status_id))->title;

        $member->personal_sales_cv = optional($membersRecord)->personal_sales_cv;

        $member->personal_sales_cv_percentage = optional($membersRecord)->personal_sales_cv_percentage;

        $cwSchedulesRecord = $this->liteCwSchedulesRecord->get(optional($membersRecord)->active_until_cw_id);
        
        $member->active_until_cw = optional($cwSchedulesRecord)->cw_name;

        $member->active_expiring = optional($cwSchedulesRecord)->active_expiring;
    
        $member->current_cw_personal_sales_cv = 0;

        $member->total_direct_downlines = 0;

        $member->total_downlines = 0;

        $member->total_active_left_downlines = 0;

        $member->total_active_right_downlines = 0;

        $member->depth = $currentDepth;

        $bonusMemberTreeDetailsRecord = $this->liteBonusMemberTreeDetailsRecord->get($member->user_id);

        if ($bonusMemberTreeDetailsRecord != null) {  
            $member->current_cw_personal_sales_cv = $bonusMemberTreeDetailsRecord->personal_sales_cv;

            $member->total_direct_downlines = $bonusMemberTreeDetailsRecord->total_direct_downline;

            $member->total_downlines = $bonusMemberTreeDetailsRecord->total_downline;

            $member->total_active_left_downlines = $bonusMemberTreeDetailsRecord->total_unique_line_left;

            $member->total_active_right_downlines = $bonusMemberTreeDetailsRecord->total_unique_line_right;
        }
    }

    /**
     * Populate Sponsor Children Data
     *
     * @param $member
     * @param int $currentDepth
     * @param int $maxDepth
     */
    private function traverseSponsorDownlineListing(&$member, int $currentDepth, int $maxDepth)
    {
        $this->populateSponsorDownlineListingData($member, $currentDepth);

        if (isset($member->children)) {
            if ($maxDepth == 0 || $currentDepth < $maxDepth) {
                foreach ($member->children as &$child) {
                    $this->traverseSponsorDownlineListing($child, $currentDepth + 1, $maxDepth);
                }
            }
            else {
                unset($member->children);
            }
        }

    }
}