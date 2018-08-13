<?php
namespace Tests\Shared\Traits;

use Illuminate\Contracts\Validation\Rule;

trait DisableValidator
{
    public function disableValidator() {

        // Pass Though Validator
        $validator = \Validator::shouldReceive("passes");
        $validator->andReturn(false);
        \Validator::shouldReceive('validate')->andReturn($validator);

        \Validator::shouldReceive('make')
            ->andReturn(\Mockery::mock(['passes' => 'false', 'validate' => 'true']));}
}