<?php
namespace App\Http\Requests\Languages;

use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class LanguageRequest extends FormRequest
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
            'key' => 'required|min:2|max:2',
            'name' => ['required','min:3','max:191', Rule::unique('languages', 'name')->ignore($id),],
            'active' => 'required|boolean'
        ];
    }
}
