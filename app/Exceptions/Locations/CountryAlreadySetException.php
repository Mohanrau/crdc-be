<?php
namespace App\Exceptions\Locations;

use App\Exceptions\{
    Code,
    AppException
};

class CountryAlreadySetException extends AppException
{
    protected
        $message = "message.country.already-set",
        $code = Code::COUNTRY_ALREADY_SET,
        $logException = true
    ;
}