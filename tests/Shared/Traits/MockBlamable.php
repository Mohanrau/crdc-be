<?php
namespace Tests\Shared\Traits;

use App\Models\Users\User;
use App\Models\Members\Member;

trait MockBlamable
{

    use MockAuth;

    public function mockBlamableSave($class, $input, $methods = [], $user = [ 'id' => 1 ]) {

        $additionalMethos = implode(', ',$methods);
        $additionalMethos = (!empty($additionalMethos) ? ", " . $additionalMethos : "");
        $model = \Mockery::mock($class .'[create'.$additionalMethos.']', []);
        $model->shouldReceive('create')
            ->atLeast()
            ->times(1)
            ->andReturn($input);

        $user = $this->mockUser($user, '[createdBy, first]');
        $user->shouldReceive('createdBy')->andReturn($model);
        $user->shouldReceive('first')->andReturn($user);
        // Invoke laravel facade
        \Auth::shouldReceive('user')->andReturn($user);
        return $model;
    }

    public function mockBlamableMemberSave($class, $input, $user = [ 'id' => 1 ]) {

        $model = \Mockery::mock($class .'[create]', []);
        $model->shouldReceive('create')
            ->atLeast()
            ->times(1)
            ->with($input)
            ->andReturn($input);

        $member = \Mockery::mock(Member::class . "[first]", $user);
        $member->shouldReceive('first')->andReturn((object) $user);

        $user = \Mockery::mock(User::class.'[createdBy, member]', $user);
        $user->shouldReceive('createdBy')->andReturn($model);
        $user->shouldReceive('member')->andReturn($member);
        $this->be($user);

        // Invoke laravel facade
        \Auth::shouldReceive('user')->andReturn($user);
    }
}