<?php
namespace App\Exceptions;

class AppException extends \Exception
{
    protected $logException = false;

    /**
     * Whether or not to log the exception in the log
     *
     * @return bool
     */
    final public function logException() {
        return $this->logException;
    }
}