<?php
namespace App\Http\Controllers\V1\Users;

use App\{
    Helpers\Classes\Sms,
    Helpers\Classes\OTPHelper,
    Http\Controllers\Controller,
    Interfaces\Masters\MasterInterface,
    Models\Locations\Country,
    Models\Users\User,
    Models\Users\UserOTP,
    Rules\General\MasterDataTitleExists,
    Rules\General\OTPMobileNumberCheck,
    Rules\Users\MobileExistCheck
};
use Illuminate\Http\Request;

class MobileVerificationController extends Controller
{
    private
        $countryObj,
        $smsObj,
        $otpHelper,
        $masterObj,
        $userOTPObj;

    /**
     * MobileVerificationController constructor.
     *
     * @param Sms $sms
     * @param Country $country
     * @param OTPHelper $OTPHelper
     * @param MasterInterface $masterInterface
     * @param UserOTP $userOTP
     */
    public function __construct(
        Sms $sms,
        Country $country,
        OTPHelper $OTPHelper,
        MasterInterface $masterInterface,
        UserOTP $userOTP
    )
    {
        $this->middleware('auth');

        $this->smsObj = $sms;

        $this->countryObj = $country;

        $this->otpHelper = $OTPHelper;

        $this->masterObj = $masterInterface;

        $this->userOTPObj = $userOTP;
    }

    /**
     * get code for the given mobile
     *
     * @param Request $request
     * @return mixed|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function getCode(Request $request)
    {
        request()->validate([
            'call_code_id' => 'required|integer|exists:countries,id',
            'verification_mobile' => [
                'required',
                new MobileExistCheck(new User(), new Country(), $request->input('call_code_id'))
            ]
        ]);

        $country = $this->countryObj->find($request->input('call_code_id'));

        $mobileNumber = $country->call_code . $request->input('verification_mobile');

        $mobileData = $this->otpHelper->generateOTPCode($mobileNumber, config('mappings.otp_code_type.guest-phone'));

        //check if verified--------------------------------------
        if ($mobileData->verified)
        {
            return response([
                'status' => __('message.mobile.verified', ['number' => $mobileData->contact]),
                'code' => $mobileData->code,
                'verified' => $mobileData->verified
            ]);
        }

        //check for the max tries
        if ($mobileData->send_count > 3 and $mobileData->updated_at->diffInMinutes(now()) <= 1440)
        {
            return response([
                'status' => trans('message.mobile.max_send_count')
            ], 422);
        }
        else
        {
            //send sms
            $sendResponse = $this->smsObj->sendSMS(
                $mobileNumber,
                __('message.mobile.code', [
                    'code' => $mobileData->code
                ])
            );

            if ($sendResponse['response_code'] === 0)
            {
                //increment the tries
                $mobileData->send_count = $mobileData->send_count + 1;

                $mobileData->save();
            }
            else
            {
                return response(['error' => trans('message.mobile.sms_failed')], 422);
            }
        }

        return $mobileData;
    }

    /**
     * verify guest mobile number
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function verifyMobile(Request $request)
    {
        request()->request->set('otp_type', config('mappings.otp_code_type.guest-phone'));

        request()->validate([
            'call_code_id' => 'required|integer|exists:countries,id',
            'verification_mobile' => [
                'required',
                new OTPMobileNumberCheck($this->countryObj, $this->userOTPObj, request()->input('call_code_id'))
            ],
            'verification_code' => 'required|exists:user_otps,code',
            'otp_type' => [
                'required',
                new MasterDataTitleExists($this->masterObj, 'otp_code_type')
            ]
        ]);

        $country = $this->countryObj->find($request->input('call_code_id'));

        $mobileNumber = $country->call_code . $request->input('verification_mobile');

        $mobileData = $this->otpHelper->getOTPCode(
            $mobileNumber,
            $request->input('otp_type'),
            $request->input('verification_code')
        );

        if ($mobileData->code == $request->input('verification_code'))
        {
            $mobileData->verified = 1;

            $mobileData->save();

            return response([
                'status' => __('message.mobile.verified', ['number' => $mobileData->mobile]),
                'verified' => 1
            ]);
        }
        else
        {
            //TODO check the tries............
            $mobileData->tries = $mobileData->tries + 1;

            $mobileData->save();

            return response(['status' => trans('message.mobile.wrong_code')]);
        }
    }
}
