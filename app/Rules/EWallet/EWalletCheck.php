<?php
namespace App\Rules\EWallet;

use App\Models\EWallets\EWallet;
use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class EWalletCheck implements Rule
{
    private $checkType, $recipient;

	/**
	 * Create a new rule instance.
	 *
	 * @param string $type
	 * @param bool $recipient
	 */
    public function __construct(string $type, bool $recipient = false)
    {
        $this->checkType = $type;

        $this->recipient = $recipient;
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
        if ($this->checkType == "user") {
            $user = User::has('eWallet')->find($value);
            if ($user && $user->eWallet->active && !$user->eWallet->blocked) {
                return $value;
            }
        } elseif ($this->checkType == "ewallet") {
            $eWallet = EWallet::find($value);
            if ($eWallet && $eWallet->active && !$eWallet->blocked) {
                return $value;
            }
        } else {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->recipient ? trans('validation.eWallet_recipient_check') : trans('validation.eWallet_check');
    }
}
