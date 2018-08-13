<?php

namespace Tests\Browser\Auth;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AuthTest extends DuskTestCase
{

    private $email = 'mohammedjalala1@gmail.com';

    /**
     * Test User Registration
     *
     * @group auth
     * @return void
     */
    public function testRegister()
    {
        $this->browse(function ($browser) {
            $browser->visit('/register')
                ->type('name', 'Mohammed Jalala')
                ->type('email', $this->email)
                ->type('password', 'Secret2017')
                ->type('password_confirmation', 'Secret2017')
                ->press('Sign up')
                ->assertPathIs('/admin');
        });
    }

    /**
     * Test User Login
     */
    public function testLogin()
    {
        $this->browse(function($logout,$browser) {

            $logout->visit('/logout')
                ->assertSee('Laravel');

           $browser->visit('/login')
            ->type('email', $this->email)
            ->type('password', 'Secret2017')
            ->press('Sign in')
            ->assertPathIs('/admin');
        });
    }
}
