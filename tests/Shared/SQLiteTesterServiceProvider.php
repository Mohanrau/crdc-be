<?php
namespace Tests\Shared;

use Illuminate\{
    Support\ServiceProvider,
    Database\Connection
};

class SQLiteTesterServiceProvider extends ServiceProvider
{
    public function register()
    {
        Connection::resolverFor('sqlite', function ($connection, $database, $prefix, $config) {
            return new SQLiteTesterConnector($connection, $database, $prefix, $config);
        });
    }
}