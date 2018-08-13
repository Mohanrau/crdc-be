<?php
namespace App\Rules\General;

use App\Models\Locations\Country;
use App\Models\Users\UserOTP;
use Illuminate\Contracts\Validation\Rule;

class OTPMobileNumberCheck implements Rule
{
    protected $countryObj, $userOTPObj, $countryId;

    /**
     * Create a new rule instance.
     *
     * @param Country $country
     * @param UserOTP $userOTP
     * @param int $countryId
     */
    public function __construct(Country $country, UserOTP $userOTP, int $countryId)
    {
        $this->countryObj = $country;

        $this->userOTPObj = $userOTP;

        $this->countryId = $countryId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $country = $this->countryObj->find($this->countryId);

        $mobileNumber = $country->call_code . $value;

        $userOTP = $this->userOTPObj->where('contact', $mobileNumber)->orderBy('id', 'desc')->first();

        if($userOTP)
        {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.exists');
    }
}
