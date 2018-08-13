<?php
namespace App\Http\Controllers\V1\Users;

use App\{
    Helpers\Classes\OTPHelper,
    Http\Requests\Users\EmailVerificationRequest,
    Interfaces\Masters\MasterInterface,
    Mail\VerificationEmail,
    Models\Locations\Country,
    Http\Controllers\Controller,
    Rules\General\MasterDataTitleExists
};
use Illuminate\Http\Request;
use Mail;

class EmailVerificationController extends Controller
{
    private
        $otpHelper,
        $masterObj;

    /**
     * EmailVerificationController constructor.
     *
     * @param OTPHelper $OTPHelper
     * @param MasterInterface $masterInterface
     * @param Country $country
     */
    public function __construct(
        OTPHelper $OTPHelper,
        MasterInterface $masterInterface,
        Country $country
    )
    {
        $this->middleware('auth');

        $this->otpHelper = $OTPHelper;

        $this->masterObj = $masterInterface;
    }

    /**
     * get email code to verify the given email
     *
     * @param EmailVerificationRequest $request
     * @return mixed|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function getCode(EmailVerificationRequest $request)
    {
        $email = $request->input('verification_email');

        $emailData = $this->otpHelper->generateOTPCode($email, config('mappings.otp_code_type.guest-email'));

        //check if email verified--------------------------------------
        if ($emailData->verified)
        {
            return response([
                'status' => __('message.email.verified', ['email' => $emailData->contact]),
                'code' => $emailData->code,
                'verified' => $emailData->verified
            ]);
        }

        //check for the max tries
        if ($emailData->send_count > 3 and $emailData->updated_at->diffInMinutes(now()) <= 1440)
        {
            return response([
                'status' => trans('message.email.max_send_count')
            ], 422);

        }
        else
        {
            //send email
            try {
                $message = trans('message.member.otp-code', ['otp' => $emailData->code]);

                Mail::to($email)->queue(new VerificationEmail($message));

                //increment the tries
                $emailData->send_count = $emailData->send_count + 1;

                $emailData->save();

            }
            catch (\Exception $exception)
            {
                return response(['error' => trans('message.email.email_failed')], 422);
            }
        }

        return $emailData;
    }

    /**
     * verify the given email by the given code
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function verifyEmail(Request $request)
    {
        request()->request->set('otp_type', config('mappings.otp_code_type.guest-email'));

        request()->validate([
            'verification_email' => 'required|exists:user_otps,contact',
            'verification_code' => 'required|exists:user_otps,code',
            'otp_type' => [
                'required',
                new MasterDataTitleExists($this->masterObj, 'otp_code_type')
            ]
        ]);

        $emailData = $this->otpHelper->getOTPCode(
            $request->input('verification_email'),
            $request->input('otp_type'),
            $request->input('verification_code')
        );

        if ($emailData)
        {
            $emailData->verified = 1;

            $emailData->save();

            return response([
                'status' => __('message.email.verified', ['email' => $emailData->contact]),
                'verified' => 1
            ]);
        }
        else
        {
            //TODO check the tries............
            $emailData->tries = $emailData->tries + 1;

            $emailData->save();

            return response(['status' => trans('message.email.wrong_code')]);
        }
    }
}
