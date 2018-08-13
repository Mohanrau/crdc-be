<?php
namespace App\Rules\EWallet;

use App\Models\EWallets\EWallet;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class EWalletBalanceCheck implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
        if(request()->has('ewallet_id'))
        {
            $eWallet = EWallet::find(request()->input('ewallet_id'));
            if ($eWallet && $eWallet->active && !$eWallet->blocked) {
                if ($value <= $eWallet->balance) {
                    return $value;
                } else {
                    return false;
                }
            }
        }
        elseif (request()->has('user_id'))
        {
            $user = User::has('eWallet')->find(request()->input('user_id'));
            if ($user && $user->eWallet->active && !$user->eWallet->blocked) {
                if ($value <= $user->eWallet->balance) {
                    return $value;
                } else {
                    return false;
                }
            }
        }
        else
        {
            $user = User::has('eWallet')->find(Auth::id());
            if ($user && $user->eWallet->active && !$user->eWallet->blocked) {
                if ($value <= $user->eWallet->balance) {
                    return $value;
                } else {
                    return false;
                }
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
        return trans('validation.balance_check');
    }
}
