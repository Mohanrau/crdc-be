<?php
namespace App\Http\Controllers\V1\ApiAuth;

use App\{
    Http\Controllers\Controller,
    Models\Locations\Country,
    Models\Users\User,
    Models\Users\UserOTP,
    Rules\General\OTPCodeValid,
    Rules\Members\MemberMobileExistCheck,
    Rules\Users\UserExistCheck
};
use Illuminate\{
    Auth\Events\PasswordReset,
    Foundation\Auth\ResetsPasswords,
    Support\Facades\Auth,
    Http\Request,
    Support\Facades\Password,
    Support\Facades\Hash,
    Support\Str
};

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |.
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords {
        reset as oldReset;
        resetPassword as oldResetPassword;
    }

    protected $userObj, $userOTPObj, $countryObj;

    /**
     * ResetPasswordController constructor.
     *
     * @param User $user
     * @param UserOTP $userOTP
     * @param Country $country
     */
    public function __construct(User $user, UserOTP $userOTP, Country $country)
    {
        $this->middleware('guest');

        $this->userObj = $user;

        $this->userOTPObj = $userOTP;

        $this->countryObj = $country;
    }

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/admin';

    /**
     * override the default guard
     *
     * @return mixed
     */
    protected function guard()
    {
        return Auth::guard('api');
    }

    /**
     * Reset the given user's password.
     *
     * @param Request $request
     * @return array
     */
    public function reset(Request $request)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        if(request()->input('request_type') == 'validate')
        {
            return ['success' => __('message.member.otp-validated')];
        }

        $userDetail = $this->userObj->getUser($request->all());

        request()->request->set('email', $userDetail->email);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->resetPassword($userDetail, $request->input('password'), $request->input('otp'));

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? ['success' => trans($response)]
            : ['error' => trans($response)];
    }

    /**
     * Reset the given user's password.
     *
     * @param User $user
     * @param  string $password
     * @param string $otpCode
     * @return string
     */
    protected function resetPassword(User $user, string $password, string $otpCode)
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        if($user->login_count == 1)
        {
            $user->login_count = 2;
        }

        $user->save();

        $this->userOTPObj->where('user_id', $user->id)
            ->where('code', $otpCode)
            ->update([
                'verified' => 1,
                'expired' => 1
            ]);

        event(new PasswordReset($user));

        return Password::PASSWORD_RESET;
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        $userId = $this->userObj->getUser(request()->all())->id;

        if (request()->has('mobile_country_code_id'))
        {
            $callCodeId = request()->input('mobile_country_code_id');

            $contact = $this->countryObj->find($callCodeId)->call_code . request()->input('mobile_num');
        }
        elseif (request()->has('email'))
        {
            $contact = request()->input('email');
        }

        return [
            'email' => [
                'required_without_all:mobile_country_code_id,mobile_num',
                new UserExistCheck($this->userObj)
            ],
            'mobile_country_code_id' => [
                'required_without_all:email',
                'exists:countries,id'
            ],
            'mobile_num' => [
                'required_without_all:email',
                new MemberMobileExistCheck($this->userObj, $this->countryObj, request()->input('mobile_country_code_id'))
            ],
            'otp' => [
                'required',
                new OTPCodeValid($this->userOTPObj, $contact, $userId)
            ],
            'request_type' => 'required|in:validate,reset-password',
            'password' => 'required_if:request_type,reset-password|confirmed|min:6',
        ];
    }
}
