<?php
namespace App\Http\Controllers\V1\ApiAuth;

use App\{
    Helpers\Classes\Sms,
    Helpers\Classes\OTPHelper,
    Http\Controllers\Controller,
    Http\Requests\Users\ForgotPasswordRequest,
    Models\Locations\Country,
    Models\Masters\MasterData,
    Models\Users\User,
    Models\Users\UserOTP
};
use Illuminate\{
    Foundation\Auth\SendsPasswordResetEmails, Support\Facades\Password
};

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    protected $userObj, $countryObj, $smsObj, $userOTPObj, $masterDataObj, $otpHelperObj;

    /**
     * ForgotPasswordController constructor.
     *
     * @param User $user
     * @param Country $country
     * @param Sms $sms
     * @param UserOTP $userOTP
     * @param MasterData $masterData
     * @param OTPHelper $OTPHelper
     */
    public function __construct(
        User $user,
        Country $country,
        Sms $sms,
        UserOTP $userOTP,
        MasterData $masterData,
        OTPHelper $OTPHelper)
    {
        $this->middleware('guest');

        $this->userObj = $user;

        $this->countryObj = $country;

        $this->smsObj = $sms;

        $this->userOTPObj = $userOTP;

        $this->masterDataObj = $masterData;

        $this->otpHelperObj = $OTPHelper;
    }

    /**
     * Send a reset link to the given user.
     *
     * @param ForgotPasswordRequest $request
     * @return array
     * @throws \Exception
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $otpCodeTypeConfig = config('mappings.otp_code_type');

        $preferredContactConfig = config('mappings.preferred_contact');

        $user = $this->userObj->getUser($request->all());

        $language = app()->getLocale();

        //check if user is member
        $isMember = $user->userType()->where('name', 'member')->count();

        //get preferred contact if user is member
        if ($isMember && !is_null($user->member->contactInfo)) {
            $preferredContactId = $user->member->contactInfo->preferred_contact_id;

            if (isset($preferredContactId)) {
                $preferredContact = $this->masterDataObj->find($preferredContactId);
            }

            if(!is_null($user->member->personalData) && !is_null($user->member->personalData->language))
            {
                $language = strtolower($user->member->personalData->language->key);
            }
        }

        // send otp via email or sms based on preferred contact
        if (isset($preferredContact) && $preferredContact->title == $preferredContactConfig['phone'])
        {
            $callCodeId = $user->member->contactInfo->mobile_1_country_code_id;

            $mobile = $this->countryObj->find($callCodeId)->call_code . $user->member->contactInfo->mobile_1_num;

            $codeTypeId = $this->masterDataObj->getIdByTitle($otpCodeTypeConfig['phone'], 'otp_code_type');

            $userOtp = $this->userOTPObj->where([
                'user_id' => $user->id,
                'contact' => $mobile,
                'code_type_id' => $codeTypeId,
                'expired' => 0
            ])->orderBy('id', 'desc')->first();

            if (!is_null($userOtp)) {
                //check timestamp if less than 5 minutes using Carbon php lib
                if ($userOtp->updated_at->diffInMinutes(now(config('app.timezone'))) < 5) {
                    return response([
                        'mobile_country_code_id' => $user->member->contactInfo->mobile_1_country_code_id,
                        'mobile_num' => $user->member->contactInfo->mobile_1_num,
                        'status' => trans('message.mobile.already_sent')
                    ], 422);
                }
            }

            $this->userOTPObj->where([
                'user_id' => $user->id,
                'contact' => $mobile,
                'code_type_id' => $codeTypeId
            ])->where('send_count', '>', 3)->update(['expired' => 1]);

            $userOtp = $this->otpHelperObj->generateOTPCode(
                $mobile,
                $otpCodeTypeConfig['phone'],
                $user->id
            );

            $userOtp->send_count = $userOtp->send_count + 1;

            $userOtp->save();

            $message = __('passwords.reset-password.sms', [
                'otp' => $userOtp->code,
                'url' => config('app.member_url')
            ], $language);

            if (empty($message)) {
                $message = __('passwords.reset-password.sms', [
                    'otp' => $userOtp->code,
                    'url' => config('app.member_url')
                ], config('app.fallback_locale'));
            }

            $smsResponse = $this->smsObj->sendSMS($mobile, $message);

            return $smsResponse['response_msg'] == config('sms.api_success_response')
                ? [
                    'mobile_country_code_id' => $user->member->contactInfo->mobile_1_country_code_id,
                    'mobile_num' => $user->member->contactInfo->mobile_1_num,
                    'status' => config('sms.api_success_response')
                ]
                : ['error' => trans($smsResponse['response_msg'])];
        }
        else
        {
            $codeTypeId = $this->masterDataObj->getIdByTitle($otpCodeTypeConfig['email'], 'otp_code_type');

            $userOtp = $this->userOTPObj->where([
                'user_id' => $user->id,
                'contact' => $user->email,
                'code_type_id' => $codeTypeId,
                'expired' => 0
            ])->orderBy('id', 'desc')->first();

            if (!is_null($userOtp)) {
                //check timestamp if less than 5 minutes using Carbon php lib
                if ($userOtp->updated_at->diffInMinutes(now(config('app.timezone'))) < 5) {
                    return response([
                        'email' => $user->email,
                        'status' => trans('message.email.already_sent')
                    ], 422);
                }
            }

            $this->userOTPObj->where([
                'user_id' => $user->id,
                'contact' => $user->email,
                'code_type_id' => $codeTypeId
            ])->where('send_count', '>', 3)->update(['expired' => 1]);

            if ($user) {
                request()->request->set('email', $user->email);
            }

            $this->validateEmail($request);

            $userOtp = $this->otpHelperObj->generateOTPCode(
                $user->email,
                $otpCodeTypeConfig['email'],
                $user->id
            );

            $userOtp->send_count = $userOtp->send_count + 1;

            $userOtp->save();

            // We will send the password reset link to this user. Once we have attempted
            // to send the link, we will examine the response then see the message we
            // need to show to the user. Finally, we'll send out a proper response.
            $response = $this->sendResetLink($user, $userOtp->code, $isMember, $language);

            return $response == Password::RESET_LINK_SENT
                ? [
                    'email' => $user->email,
                    'status' => trans($response)
                ]
                : ['error' => trans($response)];
        }
    }

    /**
     * Sent Reset Link
     *
     * @param User $user
     * @param string $otpCode
     * @param bool $isMember
     * @param string|null $language
     * @return string
     */
    public function sendResetLink(User $user, string $otpCode, bool $isMember = false, string $language = null)
    {
        $user->sendPasswordResetEmail($otpCode, $isMember, $language);

        return Password::RESET_LINK_SENT;
    }
}
