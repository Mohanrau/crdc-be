<?php
namespace App\Http\Requests\Masters;

use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class MasterRequest extends FormRequest
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
            'title' => ['required','min:3','max:255', Rule::unique('masters', 'title')->ignore($id),],
            'active' => 'sometimes|required|boolean'
        ];
    }
}
