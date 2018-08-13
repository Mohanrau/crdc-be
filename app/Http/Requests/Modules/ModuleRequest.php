<?php
namespace App\Http\Requests\Modules;

use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class ModuleRequest extends FormRequest
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
           // 'parent_id' => 'sometimes|required|integer|exists:modules,id',
            'label' => ['required','min:3','max:255', Rule::unique('modules', 'label')->ignore($id),],
            'operations' => 'required',
            'active' => 'sometimes|required|boolean'
        ];
    }
}
