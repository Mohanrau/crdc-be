<?php
namespace App\Helpers\Classes\Payments\Thailand;

use Illuminate\Support\Facades\Config;
use App\Helpers\Classes\Payments\Common\DirectBankingCommon;

class DirectBanking extends DirectBankingCommon
{
    /**
     * DirectBanking constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }
}