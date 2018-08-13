<?php
namespace App\Http\Requests\Currency;

use App\{
    Models\Currency\CurrencyConversion,
    Rules\Currency\CurrencyConvertEffectiveCw
};
use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class CurrencyConversionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(CurrencyConversion $currencyConversion)
    {
        return [
            'from_currency_id' => 'required|exists:currencies,id|different:to_currency_id',
            'to_currency_id' => 'required|exists:currencies,id|different:from_currency_id',
            'rate' => 'required|numeric|min:0',
            'cw_id' => [
                'required', 'integer', 'exists:cw_schedules,id',
                new CurrencyConvertEffectiveCw(
                    $currencyConversion,
                    $this->input('from_currency_id'),
                    $this->input('to_currency_id')
                )
            ]
        ];
    }
}
