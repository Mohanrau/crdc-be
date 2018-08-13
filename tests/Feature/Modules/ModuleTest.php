<?php
namespace Tests\Feature\Modules;

use App\Models\Modules\Module;
use App\Models\Users\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ModuleTest extends TestCase
{
    // test Module created successfully---------------------------------------------------------------------------------
    public function testModuleCreatedSuccessfully()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');

        $payload = [
            //'parent_id' => 0,
            'label' => 'Testing Module'. rand(1,255),
            'description' => 'testing module description',
            'operations' => [2,3],
            'active' => 1
        ];

        $this
            ->json('POST', 'api/v1/modules', $payload)
            ->assertStatus(200)
            ->assertJson(
                [
                    'label' => $payload['label'],
                    'description' => $payload['description']
                ]
            )
        ;
    }

    //test module updated successfully----------------------------------------------------------------------------------
    public function testModuleUpdatedSuccessfully()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');

        $module = factory(Module::class)->create([
            'label' => 'Testing Module'. rand(256,560),
            'description' => 'Testing Body',
        ]);

        $payload = [
            //'parent_id' => 0,
            'label' => 'Testing Module Change'. rand(256,560),
            'description' => 'testing module description changed',
            'operations' => [2,5],
            'active' => 1
        ];

        $this
            ->json('PUT', 'api/v1/modules/'.$module->id, $payload)
            ->assertStatus(200)
            ->assertJson(
                [
                    'label' => $payload['label'],
                    'description' => $payload['description']
                ]
            )
        ;
    }

    //test if module is listed correctly--------------------------------------------------------------------------------
    public function testModuleListedCorrectly()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user, 'api');

        factory(Module::class)->create([
            'label' => 'Testing Module'. rand(600,1000),
            'description' => 'Testing Body',
        ]);

        $response = $this->json('GET', '/api/v1/modules')
            ->assertStatus(200)
            ->assertJsonStructure(
                [ 'total', 'data' ]
            );
    }
}
