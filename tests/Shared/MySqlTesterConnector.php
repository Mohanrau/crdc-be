<?php
namespace Tests\Shared;

use Illuminate\Database\MySqlConnection as ParentSQLiteConnection;

class MySqlTesterConnector extends ParentSQLiteConnection
{
    public function getSchemaBuilder() {
        $builder = parent::getSchemaBuilder();

        $builder->blueprintResolver(function($table, $callback){

            return new MySqlTesterBlueprint($table, $callback);
        });

        return $builder;
    }
}