<?php
namespace Tests\Shared;

use Illuminate\{
    Support\ServiceProvider,
    Database\Connection
};

class MySqlTesterServiceProvider extends ServiceProvider
{
    public function register()
    {
        Connection::resolverFor('mysql', function ($connection, $database, $prefix, $config) {
            return new MySqlTesterConnector($connection, $database, $prefix, $config);
        });
    }
}