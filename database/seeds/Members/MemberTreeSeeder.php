<?php

use Illuminate\Database\Seeder;
use App\Models\
{
    Members\MemberTree,
    Users\User
};

class MemberTreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = json_decode(file_get_contents('database/seeding/'."member_tree_for_user_table.txt"));

        foreach ($data as $item)
        {
            User::updateOrCreate(
                ["old_member_id" => $item->member],
                [
                    "name" => $item->member_name,
                    "old_member_id" => $item->member,
                    "email" => str_random(10),
                    "password" => $item->password,
                    "active" => $item->active,
                    "created_by" => $item->created_by,
                    "update_by" => $item->update_by
                ]);
        }

        foreach ($data as $item) {

            $user = User::where('old_member_id', '=', $item->member)->first();
            $placement = User::where('old_member_id', '=', $item->placement_code)->first();
            $sponsor = User::where('old_member_id', '=', $item->sponsor_code)->first();

            if (is_null($item->placement_code)){
                $placement = new User(['id'=>NULL]);
            }

            if (is_null($item->sponsor_code)){
                $sponsor = new User(['id'=>NULL]);
            }

            MemberTree::updateOrCreate(
                [
                    "user_id" => $user->id,
                    "sponsor_parent_user_id" => $sponsor->id,
                    "placement_parent_user_id" => $placement->id,
                    "placement_position" => $item->placement_position
                ]);
        }
    }
}
