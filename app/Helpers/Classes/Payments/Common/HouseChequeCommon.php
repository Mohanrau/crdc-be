<?php
namespace App\Helpers\Classes\Payments\Common;

use App\Helpers\Classes\Payments\Payment;

class HouseChequeCommon extends Payment
{
    /**
     * HouseChequeCommon constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->requiredInputs = config('payments.general.payment_common_required_inputs.house_cheque');
    }
}