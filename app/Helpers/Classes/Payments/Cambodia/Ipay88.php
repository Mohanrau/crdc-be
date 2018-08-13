<?php
namespace App\Helpers\Classes\Payments\Cambodia;

use App\Interfaces\Masters\MasterInterface;
use App\Helpers\Classes\Payments\Malaysia\Ipay88 as MalaysiaIpay88;

class Ipay88 extends MalaysiaIpay88
{
    /**
     * Since cambodia is using Malaysia ipay88 providers, we will just link back to Malaysia
     * Ipay88 Cambodia constructor.
     */
    public function __construct(MasterInterface $masterInterface)
    {
        parent::__construct($masterInterface);
    }
}