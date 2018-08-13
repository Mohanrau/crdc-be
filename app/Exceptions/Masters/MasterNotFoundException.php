<?php
namespace App\Exceptions\Masters;

use App\Exceptions\{
    Code,
    AppException
};

class MasterNotFoundException extends AppException
{
    protected
        $message = "message.master.master-not-found",
        $code = Code::MASTER_NOT_FOUND,
        $logException = false
    ;
}
