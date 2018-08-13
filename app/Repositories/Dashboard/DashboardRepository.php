<?php

namespace App\Repositories\Dashboard;

use App\Helpers\Classes\MemberNetworkTree;
use App\Interfaces\Dashboard\DashboardInterface;
use App\Interfaces\General\CwSchedulesInterface;
use App\Models\Bonus\BonusMemberTreeDetails;
use App\Models\Bonus\TeamBonusRank;
use App\Models\Members\MemberSnapshot;

class DashboardRepository implements DashboardInterface
{
    protected $bonusMemberTreeDetailsObj, $cwScheduleRepository, $memberNetworkTreeHelper, $memberSnapshotObj,
                $teamBonusRankObj;
    private $data, $teamBonusRanks, $teamBonusRanksById;

    public function __construct(
        BonusMemberTreeDetails $bonusMemberTreeDetails,
        CwSchedulesInterface $cwSchedules,
        MemberNetworkTree $memberNetworkTree,
        MemberSnapshot $memberSnapshot,
        TeamBonusRank $teamBonusRank
    )
    {
        $this->bonusMemberTreeDetailsObj = $bonusMemberTreeDetails;
        $this->cwScheduleRepository = $cwSchedules;
        $this->memberNetworkTreeHelper = $memberNetworkTree;
        $this->memberSnapshotObj = $memberSnapshot;
        $this->teamBonusRankObj = $teamBonusRank;

        $this->teamBonusRanksById = $this->teamBonusRankObj->select('id','rank_code')->get()->keyBy('id');
        $this->teamBonusRanks = $this->teamBonusRankObj->select('id')->get()->mapWithKeys(function($rank){
            return [$rank->id => 0];
        })->toArray();

        $this->data = collect();
    }

    public function updateMemberSnapshot()
    {
        ini_set('memory_limit', -1);
        //get current cw
        $cwId = $this->cwScheduleRepository->getCwSchedulesList('current')['data']->first()->id;

        //
        $memberTree = $this->memberNetworkTreeHelper
            ->initiateMemberTree($cwId, 'bonus_member_tree_details')
            ->getMemberTree();

        //As for now, we only need to put in people who are active on the dashboard.
        $memberTree->each(function($member){
            if($member->sponsor_parent_user_id){
                $this->pushToUplines($member->sponsorParent,
                        [
                            'status' => $member->is_active_brand_ambassador,
                            'effective_rank_id' => $member->effective_rank_id,
                            'enrollment_rank_id' => $member->enrollment_rank_id
                        ]
                );
            }
        });

        // after populating, insert the information
        $this->data->each(function($data, $userId) use($cwId){
            $snapshot = $this->memberSnapshotObj->firstOrNew(['cw_id' => $cwId, 'user_id' => $userId]);

            $data['team_bonus_ranks'] = collect($data['team_bonus_ranks'])->map(function($data, $key){
                return ["id" => $key, 'total' => $data, 'rank_code' => $this->teamBonusRanksById->get($key)->rank_code];
            })->values()->toArray();

            $snapshot->data = collect($data)->toJson();

            $snapshot->save();
        });
    }

    /**
     * Push all the information rank to uplines to get the count
     * @param $upline
     * @param $details - active, effective_rank_id, enrollment_rank_id (null = member, 1 = premier, > 2 = BA)
     */
    private function pushToUplines($upline, $details)
    {

        $data = $this->data->get($upline->user_id, [
            'member' => 0,
            'premier_member' => 0,
            'brand_ambassador' => [
                'total_active' => 0,
                'total_inactive' => 0
            ],
            'team_bonus_ranks' => $this->teamBonusRanks
        ]);

        //@todo once jalala added enrolment_type id, we only take active BA
        if($details['status']){
            //1 = active, 2 = inactive
            $data['brand_ambassador']['total_active']++;
        }else{
            $data['brand_ambassador']['total_inactive']++;
        }

        if($details['effective_rank_id']){
            $data['team_bonus_ranks'][$details['effective_rank_id']]++;
        }

        switch($details['enrollment_rank_id']){
            case 'null':
            case '':
            case 6 :
                $data['member']++;
                break;
            case 5:
                $data['premier_member']++;
                break;
            default:
                // this should be a BA but there's no such information is needed in dashboard
        }

        $this->data->put($upline->user_id, $data);

        if(isset($upline->sponsorParent)){
            $this->pushToUplines($upline->sponsorParent, $details);
        }
    }
}