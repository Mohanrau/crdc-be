<?php

use Illuminate\Database\Seeder;
use App\Models\
{
    Members\MemberTree,
    Users\User
};

class MemberTreePyramidAlgoritmSeeder extends Seeder
{
    private $directChild = array();
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Sponsor Tree Node Update
        $sponsor_trees = MemberTree::
            whereNull('sponsor_parent_user_id')
            ->orWhere('sponsor_parent_user_id', '')
            ->get();

        foreach($sponsor_trees as $tree){

            $tree_id = $tree->id;

            $node_key = $tree->user_id;

            $items = MemberTree::where('sponsor_parent_user_id','<>',0)
                ->orWhere('user_id','=',$node_key)
                ->orderBy('user_id')
                ->get();

            if(!$items) {
                continue;
            }

            $this->directChild = array();

            foreach($items as $item) {
                $data = array();
                $parent_id = $item->sponsor_parent_user_id;
                $data['tree_id'] = $item->id;
                $data['user_id'] = $item->user_id;
                $this->directChild[$parent_id][] = $data;
            }

            $this->buildSponsorTree($tree_id, $node_key, 1, 1, $node_key);
        }

        //Placement Tree Node Update
        $placement_trees = MemberTree::whereNull('placement_parent_user_id') ->orWhere('placement_parent_user_id', '')->get();

        foreach($placement_trees as $tree){

            $tree_id = $tree->id;

            $node_key = $tree->user_id;

            $items = MemberTree::where('placement_parent_user_id','<>',0)
                ->orWhere('user_id','=',$node_key)
                ->orderBy('user_id')
                ->get();

            if(!$items) {
                continue;
            }

            $this->directChild = array();

            foreach($items as $item) {
                $data = array();
                $parent_id = $item->placement_parent_user_id;
                $data['tree_id'] = $item->id;
                $data['user_id'] = $item->user_id;
                $this->directChild[$parent_id][] = $data;
            }

            $this->buildPlacementTree($tree_id, $node_key, 1, 1, $node_key);
        }
    }

    /**
     * Update sponsor tree node value
     *
     * @param int $tree_id
     * @param int $parent_id
     * @param int $left
     * @param int $level
     * @param int $node_key
     * @return int|mixed
     */
    private function buildSponsorTree(int $tree_id, int $parent_id, int $left, int $level, int $node_key)
    {
        $right = $left + 1;

        $child = @(isset($this->directChild[$parent_id])) ? $this->directChild[$parent_id] : array();

        if(count($child)) {
            foreach($child as $key => $child_info) {
                $right = $this->buildSponsorTree($child_info['tree_id'], $child_info['user_id'], $right, $level + 1, $node_key);
            }
        }

        $networkTree = MemberTree::find($tree_id);
        $networkTree->sponsor_node_left = (int)$left;
        $networkTree->sponsor_node_right = (int)$right;
        $networkTree->sponsor_depth_level = (int)$level;
        $networkTree->sponsor_node_key = $node_key;
        $networkTree->save();

        return $right + 1;
    }

    /**
     * Update placement tree node value
     *
     * @param int $tree_id
     * @param int $parent_id
     * @param int $left
     * @param int $level
     * @param int $node_key
     * @return int|mixed
     */
    private function buildPlacementTree(int $tree_id, int $parent_id, int $left, int $level, int $node_key)
    {
        $right = $left + 1;

        $child = @(isset($this->directChild[$parent_id])) ? $this->directChild[$parent_id] : array();

        if(count($child)) {
            foreach($child as $key => $child_info) {
                $right = $this->buildPlacementTree($child_info['tree_id'], $child_info['user_id'], $right, $level + 1, $node_key);
            }
        }

        $networkTree = MemberTree::find($tree_id);
        $networkTree->placement_node_left = (int)$left;
        $networkTree->placement_node_right = (int)$right;
        $networkTree->placement_depth_level = (int)$level;
        $networkTree->placement_node_key = $node_key;
        $networkTree->save();

        return $right + 1;
    }
}
