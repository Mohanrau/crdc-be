<?php
namespace App\Rules\Currency;

use App\Models\Currency\CurrencyConversion;
use Illuminate\Contracts\Validation\Rule;

class CurrencyConvertEffectiveCw implements Rule
{
    private $currencyConversionObj, $fromCurrencyId, $toCurrencyId;

    /**
     * CurrencyConvertEffectiveCw constructor.
     *
     * @param CurrencyConversion $currencyConversion
     * @param int $fromCurrencyId
     * @param int $toCurrencyId
     */
    public function __construct(
        CurrencyConversion $currencyConversion,
        int $fromCurrencyId,
        int $toCurrencyId
    )
    {
        $this->currencyConversionObj = $currencyConversion;

        $this->fromCurrencyId = $fromCurrencyId;

        $this->toCurrencyId = $toCurrencyId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $conversionDetail = $this->currencyConversionObj
            ->where('from_currency_id', $this->fromCurrencyId)
            ->where('to_currency_id', $this->toCurrencyId)
            ->where('cw_id', $value)
            ->first();

        return (empty($conversionDetail)) ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.currency-conversion.invalid-cw-id');
    }
}
