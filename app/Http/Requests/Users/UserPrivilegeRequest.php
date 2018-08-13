<?php
namespace App\Http\Requests\Users;

use App\Interfaces\Users\UserInterface;
use App\Rules\Users\UserLocationAccess;
use Illuminate\Foundation\Http\FormRequest;

class UserPrivilegeRequest extends FormRequest
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
     * @param UserInterface $userInterface
     * @return array
     */
    public function rules(UserInterface $userInterface)
    {
        return [
            'user_id' => 'required|integer|exists:users,id',

            //validate roles ids
            'role_ids' => 'array',
            'role_ids.*' => 'integer|exists:roles,id',

            //validate role groups ids
            'role_groups_ids' => 'array',
            'role_groups_ids.*' => 'integer|exists:role_groups,id',

            //validate locations ids
            'location_ids' => [
                'sometimes',
                'nullable',
                'array',
                new UserLocationAccess(
                    $userInterface,
                    $this->input('user_id'))
            ],
            'location_ids.*' => 'integer|exists:locations,id',
            'active' => 'required|boolean',
        ];
    }
}
