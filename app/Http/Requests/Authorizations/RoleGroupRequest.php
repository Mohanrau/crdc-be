<?php
namespace App\Http\Requests\Authorizations;

use App\Helpers\Traits\ValidationErrorFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleGroupRequest extends FormRequest
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
            'user_type_id' => 'required|integer|exists:user_types,id',
            'title' => ['required','min:3','max:255', Rule::unique('role_groups', 'title')->ignore($id),],
            'active' => 'sometimes|required|boolean'
        ];
    }
}
