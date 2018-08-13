<?php
namespace App\Exceptions\Locations;

use App\Exceptions\{
    Code,
    AppException
};

class LocationAlreadySetException extends AppException
{
    protected
        $message = "message.location.already-set",
        $code = Code::LOCATION_ALREADY_SET,
        $logException = true
    ;
}
