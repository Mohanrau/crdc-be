<?php
namespace Tests\Intergrated;

use Tests\Shared\SQLiteTesterServiceProvider;
use Tests\TestCase as UnitTestCase;

abstract class TestCase extends UnitTestCase
{
    protected $make = false;

    public function createApplication()
    {
        $app = parent::createApplication();
        $app->register(new SQLiteTesterServiceProvider($app));
        $this->make($app);
        return $app;
    }


    public function setUp()
    {
        parent::setUp();
        // TODO: do migration on start of test suite
//        Artisan::call('migrate');
    }

    public function tearDown()
    {
        // TODO: reset migration end of test suite
//        Artisan::call('migrate:reset');
    }
}
