<?php
namespace Tests\Shared;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\Grammar;

class MySqlTesterBlueprint extends Blueprint
{

    public function addColumn($type, $name, array $parameters = [])
    {

        if (in_array($type, ["text", "blob", "longText", "json"])) {
            $column = parent::char( $name, 255);
        } else {
            $column = parent::addColumn($type, $name, $parameters);
        }
        return $column;
    }


}