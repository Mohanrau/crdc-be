<?php
namespace App\Exceptions\Masters;

use App\Exceptions\{
    Code,
    AppException
};

class InvalidSaleTypeIdException extends AppException
{
    protected
        $message = "message.master.invalid-sale-type",
        $code = Code::INVALID_SALE_TYPE,
        $logException = false
    ;
}
