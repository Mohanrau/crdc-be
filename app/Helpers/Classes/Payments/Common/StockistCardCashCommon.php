<?php
namespace App\Helpers\Classes\Payments\Common;

use App\Helpers\Classes\Payments\Payment;

class StockistCardCashCommon extends Payment
{
    /**
     * StockistCardCashCommon constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->requiredInputs = config('payments.general.payment_common_required_inputs.stockist_card_cash');
    }
}