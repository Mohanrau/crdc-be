<?php
namespace Tests\Shared;

use Tests\TestCase;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;

class CustomTestCase extends TestCase
{
    protected $make = false;
    protected $disableExceptionHandling = true;

    public function createApplication()
    {
        $app = parent::createApplication();
        $this->disableExceptionHandling($app);
        $app->register(new SQLiteTesterServiceProvider($app));
        $app->register(new MySqlTesterServiceProvider($app));
        $this->make($app);
        return $app;
    }

    protected function enableExeptionHandling() {
        $this->disableExceptionHandling = false;
    }

    protected function disableExceptionHandling($app)
    {
        $this->disableExceptionHandling = true;
        $app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}

            public function report(\Exception $e)
            {
            }

            public function render($request, \Exception $e) {
                throw $e;
            }
        });
    }
}