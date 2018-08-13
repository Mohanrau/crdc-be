<?php
namespace App\Http\Requests\EWallet;

use App\{
    Models\Locations\Country,
    Models\Users\User,
    Models\Users\UserOTP,
    Rules\General\OTPCodeValid,
    Rules\Users\MobileExistCheck
};
use Illuminate\{
    Foundation\Http\FormRequest,
    Support\Facades\Auth
};

class EWalletMobileNumberRequest extends FormRequest
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
     * @param User $userObj
     * @param Country $countryObj
     * @return array
     */
    public function rules(UserOTP $userOTPObj, User $userObj, Country $countryObj)
    {
        $mobileNumber = "";

        if($this->has('mobile_1_country_code_id'))
        {
            $country = $countryObj->find(request()->input('mobile_1_country_code_id'));

            $mobileNumber = $country->call_code . request()->input('mobile_1_num');
        }

        return [
            "mobile_1_country_code_id" => "required|integer|exists:countries,id",
            "mobile_1_num" => [
                "required",
                "numeric",
                "regex:/^((?!(0))[0-9]*)$/",
                "unique_with:member_contact_info,mobile_1_country_code_id," . Auth::id() . " = user_id",
                new MobileExistCheck($userObj, $countryObj, request()->input('mobile_1_country_code_id'), Auth::id())
            ],
            "code" => [
                "sometimes",
                "required",
                "numeric",
                "digits:6",
                new OTPCodeValid($userOTPObj, $mobileNumber, Auth::id())
            ],
            "request_type" => "required_with:code|in:activate,validate",
        ];
    }
}
