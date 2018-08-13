<?php
namespace App\Http\Requests\Locations;

use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class EntityRequest extends FormRequest
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
            'country_id' => 'required|integer|exists:countries,id',
            'name' => ['required','min:2','max:255', Rule::unique('entities', 'name')->ignore($id),],
            'active' => 'required|boolean'
        ];
    }
}
