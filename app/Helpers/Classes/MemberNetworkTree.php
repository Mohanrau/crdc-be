<?php
namespace App\Helpers\Classes;

use App\{
    Models\Bonus\BonusMemberTreeDetails, Models\Members\Member, Models\Members\MemberTree
};
use Illuminate\{
    Support\Collection, Support\Facades\DB, Support\Facades\Session, Support\Facades\Cache
};

class MemberNetworkTree
{
    public $membersTree, $memberSponsorRootIds, $memberPlacementRootIds;
    protected $bonusMemberTreeDetailsObj, $memberTreeObj;

    public function __construct(BonusMemberTreeDetails $bonusMemberTreeDetails, MemberTree $memberTree)
    {
        $this->bonusMemberTreeDetailsObj = $bonusMemberTreeDetails;
        $this->memberTreeObj = $memberTree;
    }

    /**
     * @param $cwId - only needed for bonys-member-tree-details
     * @param String $treeType
     * @return $this
     */
    public function initiateMemberTree($cwId = '', String $treeType = '')
    {
        $this->membersTree = collect();

        if($treeType == 'bonus_member_tree_details'){
            $this->bonusMemberTreeDetailsObj->select('bonus_member_tree_details.user_id',
                'bonus_member_tree_details.sponsor_parent_user_id',
                'bonus_member_tree_details.sponsor_depth_level',
                'bonus_member_tree_details.placement_parent_user_id',
                'bonus_member_tree_details.placement_depth_level',
                'bonus_member_tree_details.is_active_brand_ambassador',
                'members.enrollment_rank_id',
                'members.user_id',
                'members.country_id',
                'members.effective_rank_id',
                'members.highest_rank_id',
                'members.name',
                'members.join_date',
                'members.cw',
                'countries.default_currency_id'
            )
                ->leftJoin('members', 'bonus_member_tree_details.user_id', 'members.user_id')
                ->leftJoin('countries', 'members.country_id', 'countries.id')
                ->leftJoin('bonuses_summary', function($join) use($cwId){
                    $join->on('bonus_member_tree_details.user_id', 'bonuses_summary.user_id')
                        ->where('bonus_member_tree_details.cw_id', 'bonuses_summary.cw_id');
                })
                ->where('bonus_member_tree_details.cw_id', $cwId)
                ->get()->each(function($record){
                    $this->membersTree->put($record->user_id, $record);
                });
        }else{
            $this->memberTreeObj->select(
                'member_trees.user_id',
                'member_trees.sponsor_parent_user_id',
                'member_trees.sponsor_depth_level',
                'member_trees.placement_parent_user_id',
                'member_trees.placement_depth_level',
                'member_trees.placement_position',
                'members.enrollment_rank_id',
                'members.user_id',
                'members.country_id',
                'members.effective_rank_id',
                'members.highest_rank_id',
                'members.name',
                'members.join_date',
                'members.cw',
                'countries.default_currency_id')
                ->leftJoin('members', 'member_trees.user_id', 'members.user_id')
                ->leftJoin('countries', 'members.country_id', 'countries.id')
                ->get()->each(function($record){
                    $this->membersTree->put($record->user_id, $record);
                });
        }

        $this->membersTree->each(function(&$member, $key) use($treeType){
            $this->connectSponsor($member);
            $this->connectPlacement($member);
        });

        return $this;
    }

    /**
     * Get the instance of membertree
     *
     * @return mixed
     */
    public function getMemberTree()
    {
        return $this->membersTree;
    }

    /**
     * given user id, return the collections of membertree
     *
     * @param $userId
     * @return bool|Collection
     */
    public function getAllSponsorDescendant($userId)
    {
        if(!($this->membersTree)->has($userId)){
            return false;
        }

        $descendantList = collect();
        $this->traverseRecursive($descendantList, $this->membersTree->get($userId)->firstLevelSponsorChild);

        return $descendantList;
    }


    /**
     * Construct Sponsors Tree View
     *
     * @param MemberTree $member
     */
    private function connectSponsor($member)
    {

        // set the parent reference
        if($member->sponsor_parent_user_id){
            $member->sponsorParent = $this->membersTree->get($member->sponsor_parent_user_id);

            // set the reference to all the child
            $member->sponsorParent->setFirstLevelSponsorChild($member);
        }
    }

    /**
     * Construct Placement Tree View
     *
     * @param MemberTree $member
     */
    private function connectPlacement($member)
    {
        //to ensure the placement parent exists before assigning the reference
        if($member->placement_parent_user_id && $this->membersTree->has($member->placement_parent_user_id)){ //not zero and there is such member
            if($member->placement_position == 1){
                // 1 = left
                $this->membersTree->get($member->placement_parent_user_id)->placementLeft = $member;
            }elseif($member->placement_position == 2){
                // 2 = right
                $this->membersTree->get($member->placement_parent_user_id)->placementRight = $member;
            }

            $member->placementParent = $this->membersTree->get($member->placement_parent_user_id);
        }
    }

    /**
     * @param Collection $descendantList
     * @param $sponsorChildren
     */
    private function traverseRecursive(Collection &$descendantList, $sponsorChildren){
        if(empty($sponsorChildren)){
            return;
        }

        collect($sponsorChildren)->each(function($memberTree) use (&$descendantList){
            $descendantList->push($memberTree);
            $this->traverseRecursive($descendantList, $memberTree->firstLevelSponsorChild);
        });
    }
}