<?php

namespace Tests\Functional;

use Tests\Shared\CustomTestCase as UnitTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends UnitTestCase
{

    public function createApplication()
    {
        return parent::createApplication();
    }
}
