<?php
namespace App\Http\Requests\Currency;

use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class CurrencyRequest extends FormRequest
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
        $id = isset($this->segments()[3])? $this->segments()[3] : '';

        return [
            'name' => ['required','min:3','max:255', Rule::unique('countries', 'name')->ignore($id),],
            'code' => 'sometimes|min:2|max:3',
            'active' => 'sometimes|required|boolean'
        ];
    }
}
