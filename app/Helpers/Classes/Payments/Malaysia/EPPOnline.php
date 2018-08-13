<?php
namespace App\Helpers\Classes\Payments\Malaysia;

use App\Helpers\Classes\Payments\Malaysia\Ipay88;
use App\Interfaces\Masters\MasterInterface;

/**
 * Important : EPP online can be used in several countries and on every country, it will be using different
 * kind of online payment gateway to support this. For example, in malaysia, we are using ipay88 for EPP online.
 *
 * Class EPPOnline
 * @package App\Helpers\Classes\Payments\Common
 */
class EPPOnline extends Ipay88
{

    /**
     * EPPOnline constructor.
     * @param MasterInterface $masterInterface
     */
    public function __construct(MasterInterface $masterInterface)
    {
        parent::__construct($masterInterface, true);// indicates that this is an EPP online
    }
}