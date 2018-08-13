<?php
namespace App\Helpers\Traits;

trait Member
{
    //General info
//    public $name = '';
//    public $id = '';
//    public $memberId = '';
//    public $country = 'default';
//    public $status = '';
//    public $level = '';
//    public $placementParentId;
//    public $placementPosition; // 1 = left, 2 = right
//    public $sponsorParentId;

    public $sponsorLevel = 0; // level in the sponsor tree
    public $placementLevel = 0; // level in the placement tree

    public $qualifiedRank = ''; //rank will be based on pay leg GCV
    public $highestRank = ''; // highest rank achieved by this user in the past

    public $isActive = false; // is this member active at current CW
    public $isActiveByMegaCv = false;
    public $isActiveByNormalCv = false;
    public $memberType = 0; // 1 = member, 2 = premium member, 3 = BA

    public $sponsorDepth;
    public $placementDepth;

    //generals
    public $currencyConversionRate = 1; //USD : this user currency

    /**
     * Placement info
     */
    public $placementParent;
    public $placementLeft;
    public $placementRight;
    public $totalLeftActiveBA = 0; // refers to sponsor tree
    public $totalRightActiveBA = 0;// refers to sponsor tree
    public $placementRankLeft = array(); // id(rank_id) => total, all the ranks that exists in the left placement
    public $placementRankRight = array(); // id(rank_id) => total

    public $totalUniqueLeftActiveBA = 0; // unique line that is active on the left in placement tree
    public $totalUniqueRightActiveBA = 0; //unique line that is active on the right in placement tree

    /**
     * Sponsor info
     */
    public $sponsorParent;
    public $firstLevelSponsorChild = array();
    public $totalActiveBA = 0;
    public $totalRanks = array(); //array(1,2,3,1) store all the available childrens' ranks
    public $achievedRankId = 0; // rank that achieved by this
    public $totalDirectDownlineActiveBA = 0;
    public $isNewBA = false; // is this a new ba? Based on purchase on registration pack
    public $totalDownlines = 0; // total sponsors under this member until unlimited level
    public $totalNewBA = 0;


    // in depth report, only looking at the placement table
    public $totalNewBALeft = 0;
    public $totalNewBARight = 0;
    public $totalBALeft = 0;
    public $totalBARight = 0;
    public $totalLeftActiveBAPlacement = 0;
    public $totalRightActiveBAPlacement = 0;


    //the rank that we would like to keep track at, and the name of the table as the key
    public $sponsorLineRankDetails = [
        'total_sponsor_unique_line_1_BA' => [2, 1, 0], // means rank id 2 must have at least 1, actual record
        'total_sponsor_unique_line_1_SD' => [7, 1, 0], // third one means 0 count
        'total_sponsor_unique_line_2_SD' => [7, 2, 0],
        'total_sponsor_unique_line_1_RD' => [8, 1, 0],
        'total_sponsor_unique_line_2_RD' => [8, 2, 0],
        'total_sponsor_unique_line_1_ED' => [9, 1, 0],
        'total_sponsor_unique_line_2_ED' => [9, 2, 0]
    ];

    //Mega placement
    public $leftMegaCv = 0; // gcv from mega contributed from the left child on current cw
    public $rightMegaCv = 0;


    public $firstLevelCompressedSponsorChild = array(); // compressed tree for mentor bonus

    public $campaignInfo = [];

    //self at level 0
    public function getSponsorChild($memberTree, $level = 10, &$info = array(), $currentLevel = 0)
    {
        $info[$currentLevel][] = array(
            'member_id' => $this->member_id,
            'sponsor_member_id' => $this->sponsor_member_id,
            'sponsor_depth_level' => $this->sponsor_depth_level,
            'placement_member_id' => $this->placement_member_id,
            'placement_depth_level' => $this->placement_depth_level,
            'placement_position' => $this->placement_position,
            'sponsor_position' => $this->sponsor_position
        );

        $level--;
        if(!$level || empty($this->firstLevelSponsorChild)){
            return;
        }

        $currentLevel++;
        foreach($this->firstLevelSponsorChild as $children){
            $this->getMember($memberTree, $children)->getSponsorChild($memberTree, $level, $info, $currentLevel);
        }
    }

    /**
     * To retrieve all the child information of current rank to the 10th(default) level below
     *
     * @param $memberTree
     * @param int $level
     * @param array $info
     * @param int $currentLevel
     */
    public function getPlacementChild($memberTree, $level = 10, &$info = array(), $currentLevel = 0)
    {
        $info[$currentLevel][] = array(
            'member_id' => $this->member_id,
            'sponsor_member_id' => $this->sponsor_member_id,
            'sponsor_depth_level' => $this->sponsor_depth_level,
            'placement_member_id' => $this->placement_member_id,
            'placement_depth_level' => $this->placement_depth_level,
            'placement_position' => $this->placement_position,
            'sponsor_position' => $this->sponsor_position
        );

        $level--;
        if(!$level || (!$this->placementLeft($memberTree) && !$this->placementRight($memberTree))){
            return;
        }

        $currentLevel++;

        if($this->placementLeft($memberTree)){
            $this->placementLeft($memberTree)->getPlacementChild($memberTree, $level, $info, $currentLevel);
        }

        if($this->placementRight($memberTree)){
            $this->placementRight($memberTree)->getPlacementChild($memberTree, $level, $info, $currentLevel);
        }

    }

    /*******************************************************************************************************************
     * Reference to the respective member without using object reference
     */

    /**
     * @param MemberTree $tree
     */
    public function placementLeft($tree)
    {
        return ($this->placementLeft) ? $tree[$this->placementLeft] : $this->placementLeft;
    }

    public function placementRight($tree)
    {
        return ($this->placementRight) ? $tree[$this->placementRight] : $this->placementRight;
    }

    public function placementParent($tree)
    {
        return ($this->placementParent) ? $tree[$this->placementParent] : $this->placementParent;
    }

    public function sponsorParent($tree)
    {
        return ($this->sponsorParent) ? $tree[$this->sponsorParent] : $this->sponsorParent;
    }

    public function getMember($tree, $memberId){
        return (isset($tree[$memberId])) ? $tree[$memberId] : false ;
    }

    public function setFirstLevelSponsorChild($member)
    {
        $this->firstLevelSponsorChild[] = $member;
    }
}