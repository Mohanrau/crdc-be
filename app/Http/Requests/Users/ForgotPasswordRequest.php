<?php
namespace App\Http\Requests\Users;

use App\{
    Models\Locations\Country,
    Models\Users\User,
    Rules\Members\MemberMobileExistCheck,
    Rules\Users\UserExistCheck
};
use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
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
            'email' => [
                'required_without_all:mobile_country_code_id,mobile_num',
                new UserExistCheck(new User())
            ],
            'mobile_country_code_id' => [
                'required_without:email',
                'exists:countries,id'
            ],
            'mobile_num' => [
                'required_without:email',
                new MemberMobileExistCheck(new User(), new Country(), request()->input('mobile_country_code_id'))
            ]
        ];
    }
}
