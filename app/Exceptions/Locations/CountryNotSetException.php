<?php
namespace App\Exceptions\Locations;

use App\Exceptions\{
    Code,
    AppException
};

class CountryNotSetException extends AppException
{
    protected
        $message = "message.country.not-set",
        $code = Code::COUNTRY_NOT_SET,
        $logException = true
    ;
}
