<?php
namespace App\Rules\EWallet;

use App\{
    Models\Users\User,
    Models\EWallets\EWallet
};
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Auth,
    Support\Facades\Hash
};

class SecurityPinCheck implements Rule
{
    protected $userObj, $ewalletObj;

    /**
     * Create a new rule instance.
     *
     * @param User $user
     * @param EWallet $eWallet
     */
    public function __construct(User $user, EWallet $eWallet)
    {
        $this->userObj = $user;

        $this->ewalletObj = $eWallet;
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
        if (request()->has("ewallet_id")) {
            $eWallet = $this->ewalletObj->find(request()->input('ewallet_id'));
        } elseif (request()->has("user_id")) {
            $eWallet = $this->userObj->find(request()->input('user_id'))->eWallet;
        } else {
            $eWallet = Auth::user()->eWallet;
        }

        if (isset($eWallet) and Hash::check($value, $eWallet->security_pin)) {
            return $value;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.security_pin_check');
    }
}
