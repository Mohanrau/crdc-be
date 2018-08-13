<?php
namespace Tests\Unit;

use Tests\TestCase as UnitTestCase;

abstract class TestCase extends UnitTestCase
{
    public function createApplication()
    {
        return parent::createApplication();
    }
}
