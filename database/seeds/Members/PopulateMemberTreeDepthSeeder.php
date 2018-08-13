<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PopulateMemberTreeDepthSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rootId = 1;
        //
        ini_set('memory_limit', -1);
        $this->membersTreeRecord = Db::table('member_trees')->select('id', 'user_id', 'sponsor_parent_user_id',
            'placement_parent_user_id', 'placement_position')->get()->keyBy('user_id');

        $this->membersTreeRecord->each(function($member){
            if($member->placement_parent_user_id){
                if($member->placement_position == 1){
                    $this->membersTreeRecord->get($member->placement_parent_user_id)->childrenLeft = $member;
                } else {
                    //must be right
                    $this->membersTreeRecord->get($member->placement_parent_user_id)->childrenRight = $member;
                }
            }

            if($member->sponsor_parent_user_id){
                $this->membersTreeRecord->get($member->sponsor_parent_user_id)->children[] = $member;
            }
        });

        $this->updatePlacementDepth($this->membersTreeRecord->get($rootId), 1);
        $this->updateSponsorDepth($this->membersTreeRecord->get($rootId), 1);

        $this->membersTreeRecord->each(function($member){
            DB::table('member_trees')
                ->where('id', $member->id)
                ->update(
                    [
                        'placement_depth_level' => isset($member->placement_depth) ? $member->placement_depth : null,
                        'sponsor_depth_level' => isset($member->sponsor_depth) ? $member->sponsor_depth : null
                    ]);
        });
    }

    private function updatePlacementDepth($member, $depth)
    {
        $member->placement_depth = $depth;

        if(isset($member->childrenLeft)){
            $this->updatePlacementDepth($member->childrenLeft, $depth + 1);
        }

        if(isset($member->childrenRight)){
            $this->updatePlacementDepth($member->childrenRight, $depth + 1);
        }

        return;
    }

    private function updateSponsorDepth($member, $depth)
    {
        $member->sponsor_depth = $depth;

        if(isset($member->children)){
            foreach($member->children as $child){
                $this->updateSponsorDepth($child, $depth + 1);
            }
        }

    }
}
