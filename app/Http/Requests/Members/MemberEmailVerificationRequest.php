<?php
namespace App\Http\Requests\Members;

use App\{
    Models\Members\MemberContactInfo,
    Models\Users\UserOTP,
    Rules\General\OTPCodeValid,
    Rules\Members\MemberEmailAlreadyVerified
};
use Illuminate\{
    Foundation\Http\FormRequest,
    Support\Facades\Auth,
    Validation\Rule
};

class MemberEmailVerificationRequest extends FormRequest
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
     * @param UserOTP $userOTPObj
     * @param MemberContactInfo $memberContactInfo
     * @return array
     */
    public function rules(UserOTP $userOTPObj, MemberContactInfo $memberContactInfo)
    {
        return [
            "email" => [
                "required",
                "email",
                Rule::unique('member_contact_info', 'email')->ignore(Auth::id(), 'user_id'),
                Rule::unique('users', 'email')->ignore(Auth::id(), 'id'),
                new MemberEmailAlreadyVerified($memberContactInfo)
            ],
            "code" => [
                "sometimes",
                "required",
                "numeric",
                "digits:6",
                new OTPCodeValid($userOTPObj, $this->input('email'), Auth::id())
            ]
        ];
    }
}
