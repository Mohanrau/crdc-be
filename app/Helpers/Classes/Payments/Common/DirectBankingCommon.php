<?php
namespace App\Helpers\Classes\Payments\Common;

use App\Helpers\Classes\Payments\Payment;

class DirectBankingCommon extends Payment
{
    /**
     * DirectBankingCommon constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->requiredInputs = config('payments.general.payment_common_required_inputs.direct_banking');
    }
}