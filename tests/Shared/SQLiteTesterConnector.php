<?php
namespace Tests\Shared;

use Illuminate\Database\SQLiteConnection as ParentSQLiteConnection;

class SQLiteTesterConnector extends ParentSQLiteConnection
{
    public function getSchemaBuilder() {
        $builder = parent::getSchemaBuilder();

        $builder->blueprintResolver(function($table, $callback){

            return new SQLliteTesterBlueprint($table, $callback);
        });

        return $builder;
    }
}