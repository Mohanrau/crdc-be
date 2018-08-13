<?php
namespace App\Interfaces\Integrations;

interface CimbMposInterface
{
    /**
     *
     * Query for settled payments.
     * @param null $dateFrom
     * @param null $dateTo
     * @return array|bool
     */
    public function queryMpos($dateFrom = null, $dateTo = null);
}