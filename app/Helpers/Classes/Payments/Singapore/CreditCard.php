<?php
namespace App\Helpers\Classes\Payments\Singapore;

use Illuminate\Support\Facades\Config;
use App\Helpers\Classes\Payments\Common\CreditCardCommon;

class CreditCard extends CreditCardCommon
{
    /**
     * CreditCard constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}