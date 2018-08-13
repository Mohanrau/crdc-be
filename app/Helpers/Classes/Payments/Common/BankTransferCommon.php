<?php
namespace App\Helpers\Classes\Payments\Common;

use App\Helpers\Classes\Payments\Payment;

/**
 * Readme : This class basically handles all the bank transfer which is manual, all they need is to key in
 * the text of the transaction number
 *
 * Class BankTransferCommon
 * @package App\Helpers\Classes\Payments\Common
 */
class BankTransferCommon extends Payment
{
    /**
     * BankTransferCommon constructor.
     */
    public function __construct()
    {
        $this->isThirdPartyRefund = true;
    }
}