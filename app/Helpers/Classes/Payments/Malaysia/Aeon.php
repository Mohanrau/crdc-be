<?php
namespace App\Helpers\Classes\Payments\Malaysia;

use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use App\Models\Payments\AeonTransaction;
use App\Helpers\Classes\Payments\Common\AeonCommon;

class Aeon extends AeonCommon
{
    /**
     * Aeon Malaysia constructor.
     */
    public function __construct(AeonTransaction $aeonTransaction)
    {
        parent::__construct($aeonTransaction);

        $this->requiredInputs = Config::get('payments.malaysia.aeon.required_inputs');
    }
}