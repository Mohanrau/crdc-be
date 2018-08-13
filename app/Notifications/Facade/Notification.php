<?php
namespace App\Notifications\Facade;

use Illuminate\Support\Facades\Facade;

class Notification extends Facade{
    protected static function getFacadeAccessor()
    {
        return 'general-notification';
    }
}