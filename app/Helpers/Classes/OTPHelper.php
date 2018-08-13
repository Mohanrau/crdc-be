<?php
namespace App\Helpers\Classes;

use App\{
    Models\Masters\MasterData,
    Models\Users\User,
    Models\Users\UserOTP
};

class OTPHelper
{
    protected $userOtpObj, $userObj, $masterDataObj;

    /**
     * OTPHelper constructor.
     *
     * @param User $user
     * @param UserOTP $userOTP
     * @param MasterData $masterData
     */
    public function __construct(
        User $user,
        UserOTP $userOTP,
        MasterData $masterData
    )
    {
        $this->userObj = $user;

        $this->userOtpObj = $userOTP;

        $this->masterDataObj = $masterData;
    }

    /**
     * Generate OTP Code
     *
     * @param string $contact
     * @param string $otpType
     * @param int $userId
     * @return object
     * @throws \Exception
     */
    public function generateOTPCode(
        string $contact,
        string $otpType,
        int $userId = 0
    )
    {
        $code = random_int(100000, 999999);

        $codeTypeId = $this->masterDataObj->getIdByTitle($otpType, 'otp_code_type');

        $userOtp = $this->userOtpObj->where([
            'contact' => $contact,
            'code_type_id' => $codeTypeId,
            'expired' => 0
        ]);

        if ($userId > 0) {
            $userOtp = $userOtp->where('user_id', $userId);
        }

        $userOtp = $userOtp->orderBy('id', 'desc')->first();

        if (!is_null($userOtp) and $userOtp->updated_at->diffInMinutes(now()) <= 1440)
        {
            if (!($userOtp->send_count > 3))
            {
                $userOtp->code = $code;

                $userOtp->save();
            }

            return $userOtp;
        }
        else
        {
            if (isset($userOtp))
            {
                $userOtp->expired = 1;

                $userOtp->save();
            }

            return $this->userOtpObj->create([
                'user_id' => ($userId == 0) ? null : $userId,
                'contact' => $contact,
                'code_type_id' => $codeTypeId,
                'code' => $code
            ]);
        }
    }

    /**
     * Get OTP Code
     *
     * @param string $contact
     * @param string $otpType
     * @param int $otpCode
     * @param int $userId
     * @param bool $expired
     * @return mixed
     */
    public function getOTPCode(string $contact, string $otpType, int $otpCode = 0, int $userId = 0, bool $expired = null)
    {
        $codeTypeId = $this->masterDataObj->getIdByTitle($otpType, 'otp_code_type');

        $userOtp = $this->userOtpObj->where([
            'contact' => $contact,
            'code_type_id' => $codeTypeId
        ]);

        if ($otpCode > 0)
        {
            $userOtp = $userOtp->where('code', $otpCode);
        }

        if ($userId > 0)
        {
            $userOtp = $userOtp->where('user_id', $userId);
        }

        if (!is_null($expired))
        {
            $userOtp = $userOtp->where('expired', $expired);
        }

        return $userOtp->orderBy('id', 'desc')->first();
    }
}