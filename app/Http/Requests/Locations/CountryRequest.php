<?php
namespace App\Http\Requests\Locations;

use App\Helpers\Traits\ValidationErrorFormat;
use Illuminate\{
    Contracts\Validation\Validator, Foundation\Http\FormRequest, Validation\Rule
};

class CountryRequest extends FormRequest
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
    public function rules()
    {
        //Todo clear this part to use request
        $id = isset($this->segments()[3])? $this->segments()[3] : '';

        return [
            'name' => ['required','min:3','max:255', Rule::unique('countries', 'name')->ignore($id),],
            'code' => 'sometimes|required|min:3|max:3',
            'call_code' =>'required|min:2|max:5',
            'code_iso_2' => 'sometimes|min:2|max:2',
            'default_currency_id' => 'required||integer|exists:currencies,id',
            'tax_desc' => 'required|min:2|max:3',
            'active' => 'sometimes|required|boolean'
        ];
    }
}
