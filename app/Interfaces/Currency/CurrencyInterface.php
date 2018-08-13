<?php
namespace App\Interfaces\Currency;

use App\Interfaces\BaseInterface;

interface CurrencyInterface extends BaseInterface
{
    /**
     * Store a newly created currencies conversion resource
     *
     * @param array $data
     * @return mixed
     */
    public function currenciesConversionsStore(array $data);

     /**
     * get currency conversion filtered by the following parameters
     *
     * @param int|NULL $fromCurrencyId
     * @param int|NULL $toCurrencyId
     * @param int|NULL $cwId
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed|static
     */
    public function getCurrenciesConversionsByFilters(
        int $fromCurrencyId = NULL,
        int $toCurrencyId = NULL,
        int $cwId = NULL,
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * get currency conversion rate by fromCurrencyId and toCurrencyId
     *
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     * @return decimal
     */
    public function getCurrenciesConversionsRate(int $fromCurrencyId, int $toCurrencyId);
}