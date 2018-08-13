<?php
namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class StaffRequest extends FormRequest
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
        return [
            'country_id' => 'required|integer|exists:countries,id',
            'stockist_user_id' => 'sometimes|nullable|integer|exists:users,id',
            'email' => 'required|string|email|max:255|unique:users,email',
            'name' => 'required|min:3',
            'position' => 'sometimes|nullable|min:4',

            //validate roles and role groups ids
            'role_ids.*' => 'integer|exists:roles,id',
            'role_groups_ids.*' => 'integer|exists:role_groups,id',
        ];
    }
}
