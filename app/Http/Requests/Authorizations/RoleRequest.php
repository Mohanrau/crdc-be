<?php
namespace App\Http\Requests\Authorizations;

use App\Helpers\Traits\ValidationErrorFormat;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
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
            'label' => ['required','min:3','max:191', Rule::unique('roles', 'label')->ignore($id),],
            'name' => 'required|min:3|max:191',
            'active' => 'required|boolean',

            'country_id' => 'required|integer|exists:countries,id',

            //validate role groups ---------------------------------------
            'role_group_ids' => 'array',
            'role_group_ids.*' => 'integer|exists:role_groups,id',

            //validate permissions --------------------------------------
            'permissions.ids' => 'required|array|min:1',
            'permissions.ids.*' => 'required|integer:exists:permissions,id'
        ];
    }
}
