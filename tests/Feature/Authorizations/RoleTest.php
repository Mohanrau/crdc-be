<?php

namespace Tests\Feature\Authorizations;

use App\Models\Authorizations\Role;
use App\Models\Users\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class RoleTest extends TestCase
{
    //test Role created successfully------------------------------------------------------------------------------------
    public function testRoleCreatedSuccessfully()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api');

        $payload = [
            "label" => "Managers Role". rand(1,255),
            "name" => "Managers Role". rand(1,255),
            "active" => 1
        ];

        $this
            ->json('POST', 'api/v1/roles', $payload)
            ->assertStatus(200)
            ->assertJson(
                [
                    'label' => $payload['label'],
                    'name' => $payload['name'],
                    'active' => $payload['active']
                ]
            );
    }

    //test module updated successfully----------------------------------------------------------------------------------
    public function testRoleUpdatedSuccessfully()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api');

        $role = Role::create([
            'label' => 'Admin Role Label'. rand(256,560),
            'name' => 'Admin Role'. rand(256,560),
            'active' => 1,
        ]);

        $payload = [
            'label' => 'Admin Role Label'. rand(256,560),
            'name' => 'Admin Role'. rand(256,560),
            'active' => 1,
        ];

        $this
            ->json('PUT', 'api/v1/roles/'.$role->id, $payload)
            ->assertStatus(200)
            ->assertJson(
                [
                    'label' => $payload['label'],
                    'name' => $payload['name'],
                    'active' => $payload['active']
                ]
            );

        $role->delete();
    }

    //test if module is listed correctly--------------------------------------------------------------------------------
    public function testRoleListedCorrectly()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api');

        $role = Role::create([
            'label' => 'Testing Role Label'. rand(256,560),
            'name' => 'Testing Role'. rand(256,560),
            'active' => 1,
        ]);

        $response = $this->json('GET', '/api/v1/roles')
            ->assertStatus(200)
            ->assertJsonStructure(
                [ 'total', 'data' ]
            );

        $role->delete();
    }
}
