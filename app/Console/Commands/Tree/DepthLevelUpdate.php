<?php

namespace App\Console\Commands\Tree;

use App\Models\Members\MemberTree;
use Illuminate\Console\Command;

class DepthLevelUpdate extends Command
{
    protected $memberTreeObj, $rootUserId;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tree:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To update the tree depth level';

    /**
     * DepthLevelUpdate constructor.
     * @param MemberTree $memberTree
     */
    public function __construct(MemberTree $memberTree)
    {
        $this->rootUserId = 1;
        $this->memberTreeObj = $memberTree;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        //
        $memberTree = $this->memberTreeObj->all()->keyBy('user_id');

        //connect placement
        $memberTree->each(function($member) use($memberTree){
            if($member->placement_parent_user_id){
                if($member->placement_position == 1){
                    $memberTree->get($member->placement_parent_user_id)->placementLeft = $member;
                } else {
                    //must be right
                    $memberTree->get($member->placement_parent_user_id)->placementRight = $member;
                }
            }

            if($member->sponsor_parent_user_id){
                $memberTree->get($member->sponsor_parent_user_id)->firstLevelSponsorChild[] = $member;
            }
        });

        $this->updateDepth($memberTree);
    }

    private function updateDepth($memberTree)
    {
        $member = $memberTree->get($this->rootUserId);
        //$this->recursiveUpdateSponsorDepth($member);
        $this->recursiveUpdatePlacementDepth($member);
    }

    private function recursiveUpdateSponsorDepth($member, $level = 1)
    {
        $member->sponsor_depth_level = $level;
        $member->save();

        foreach ($member->firstLevelSponsorChild as $child){
            $this->recursiveUpdateSponsorDepth($child, $level + 1);
        }
    }

    private function recursiveUpdatePlacementDepth($member, $level = 1)
    {
        $member->placement_depth_level = $level;
        $member->save();

        if(isset($member->placementLeft)){
            $this->recursiveUpdatePlacementDepth($member->placementLeft, $level + 1);
        }

        if(isset($member->placementRight)){
            $this->recursiveUpdatePlacementDepth($member->placementRight, $level + 1);
        }
    }
}
