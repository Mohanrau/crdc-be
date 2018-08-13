<?php
namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Hash;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    protected $make = true;

    public function createApplication()
    {

        $app = require __DIR__.'/../bootstrap/app.php';

        if ($this->make) {
            $this->make($app);
        }

        return $app;
    }

    public function make($app) {
        $app->make(Kernel::class)->bootstrap();
        Hash::setRounds(4);
    }

}
