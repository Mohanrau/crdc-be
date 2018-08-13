<?php
namespace Tests\Feature\Authorizations;

use App\Models\Authorizations\RoleGroup;
use App\Models\Users\UserType;
use App\Models\Users\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleGroupTest extends TestCase
{
    //test RoleGroup created successfully-------------------------------------------------------------------------------
    public function testRoleGroupCreatedSuccessfully()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api');

        $payload = [
            "user_type_id" => UserType::first()->id,
	        "title" => "Managers Role Group". rand(1,255),
	        "active" => 1
        ];

        $this
            ->json('POST', 'api/v1/role-groups', $payload)
            ->assertStatus(200)
            ->assertJson(
                [
                    'title' => $payload['title'],
                    'active' => $payload['active']
                ]
            );
    }

    //test if RoleGroup updated successfully----------------------------------------------------------------------------
    public function testRoleGroupUpdatedSuccessfully()
    {
        $user  = factory(User::class)->create();

        $this->actingAs($user, 'api');

        $roleGroup = RoleGroup::create([
            'user_type_id' => UserType::first()->id,
            'title' => 'Role Group Test'. rand(256,560),
            'active' => 1
        ]);

        $payload = [
            'user_type_id' => UserType::first()->id,
            'title' => 'Role Group Test'. rand(256,560),
            'active' => 1
        ];

        $this
            ->json('PUT', 'api/v1/role-groups/'.$roleGroup->id, $payload)
            ->assertStatus(200)
            ->assertJson(
                [
                    'user_type_id' => $payload['user_type_id'],
                    'title' => $payload['title']
                ]
            )
        ;
    }

    //test if RoleGroup is listed correctly-----------------------------------------------------------------------------
    public function testModuleListedCorrectly()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');

        RoleGroup::create([
            'user_type_id' => UserType::first()->id,
            'title' => 'Role Group Test'. rand(256,560),
            'active' => 1
        ]);

        $response = $this->json('GET', '/api/v1/role-groups')
            ->assertStatus(200)
            ->assertJsonStructure(
                [ 'total', 'data' ]
            )
        ;
    }

}