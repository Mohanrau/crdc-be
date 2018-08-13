<?php
namespace App\Rules\General;

use App\Models\Users\UserOTP;
use Illuminate\Contracts\Validation\Rule;

class OTPCodeValid implements Rule
{
    private $userOtpObj, $contact, $userId;

    /**
     * Create a new rule instance.
     *
     * @param UserOTP $userOTP
     * @param string $contact
     * @param null $userId
     */
    public function __construct(UserOTP $userOTP, string $contact, $userId = null)
    {
        $this->userOtpObj = $userOTP;

        $this->contact = $contact;

        $this->userId = $userId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $userId = is_null($this->userId) ? null : $this->userId;

        $userOtp = $this->userOtpObj::where([
            'user_id' => $userId,
            'contact' => $this->contact,
            'code' => $value,
            'expired' => 0
        ])->orderBy('id', 'desc')->first();

        if (!is_null($userOtp))
        {
            //check timestamp if less than 5 minutes using Carbon php lib
            if ($userOtp->updated_at->diffInMinutes(now(config('app.timezone'))) < 5)
            {
                return $value;
            }
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
        return trans('validation.user_otp_check');
    }
}
