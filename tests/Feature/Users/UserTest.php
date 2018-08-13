<?php

namespace Tests\Feature\Users;

use App\Models\Users\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }


    /**
     * Login Api Test - required fields login---------------------------------------------------------------------------
     */
    public function testRequiredFieldsOnLogin()
    {
        $this->withoutMiddleware();

        $this->json('POST','api/v1/login')
            ->assertStatus(422)
            ->assertExactJson([
                "email" => [ 0 => "The email field is required."],
                "password" => [ 0 => "The password field is required."]
            ]);
    }

    /**
     * Test login successfully api--------------------------------------------------------------------------------------
     */
    public function testUserLoginSuccessfully()
    {
        $user = factory(User::class)->create([
            'email' => 'testlogin@user.com',
            'password' => bcrypt('testUSer1479')
        ]);

        $payload = ['email' => 'testlogin@user.com', 'password' => 'testUSer1479'];

        $this->json('POST', 'api/v1/login', $payload)
            ->assertStatus(200)
            ->assertJsonStructure(
                [
                    'token_type',
                    'expires_in',
                    'access_token',
                    'refresh_token'
                ]
            );

        $user->delete();
    }
}
