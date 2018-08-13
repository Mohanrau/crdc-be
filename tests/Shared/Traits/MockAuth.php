<?php
namespace Tests\Shared\Traits;

use App\Models\Users\User;
use App\Models\Members\Member;

trait MockAuth
{
    public function mockUser($user = [ 'id' => 1 ], $methods = "") {


        $userMock = \Mockery::mock(User::class . $methods, $user);
        $userMock->shouldReceive('first')->andReturn((object) $user);
        $this->be($userMock);

        // Invoke laravel facade
        \Auth::shouldReceive('user')->andReturn($userMock);
        \Auth::shouldReceive('id')->andReturn($user['id']);
        return $userMock;
    }

    public function mockMember($user = [ 'id' => 1 ]) {

        $member = \Mockery::mock(Member::class . "[first]", $user);
        $member->shouldReceive('first')->andReturn((object) $user);

        $user = \Mockery::mock(User::class.'[member]', $user);
        $user->shouldReceive('member')->andReturn($member);
        $this->be($user);

        // Invoke laravel facade
        \Auth::shouldReceive('user')->andReturn($user);
        \Auth::shouldReceive('id')->andReturn($user['id']);
    }
}