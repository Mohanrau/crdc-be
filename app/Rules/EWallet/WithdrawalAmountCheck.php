<?php
namespace App\Rules\EWallet;

use App\{
    Interfaces\Currency\CurrencyInterface,
    Models\Currency\Currency,
    Models\EWallets\EWallet,
    Models\Masters\MasterData,
    Models\Users\User
};
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class WithdrawalAmountCheck implements Rule
{
    protected $currencyObj, $currencyModelObj, $masterDataObj, $userObj, $ewalletObj;

    /**
     * Create a new rule instance.
     *
     * @param CurrencyInterface $currencyInterface
     * @param Currency $currency
     * @param MasterData $masterData
     * @param User $user
     * @param EWallet $eWallet
     */
    public function __construct(
        CurrencyInterface $currencyInterface,
        Currency $currency,
        MasterData $masterData,
        User $user,
        EWallet $eWallet
    )
    {
        $this->currencyObj = $currencyInterface;

        $this->currencyModelObj = $currency;

        $this->masterDataObj = $masterData;

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
        $eWalletTransactionTypeId = $this->masterDataObj->getIdByTitle(config('mappings.ewallet_transaction_type.withdraw'), 'ewallet_transaction_type');

        if ($eWalletTransactionTypeId == request()->get('transaction_type_id'))
        {
            if(Auth::user()->isUserType(config('mappings.user_types.member')))
            {
                $user = Auth::user();
                if ($user && $user->eWallet->active && !$user->eWallet->blocked)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate(request()->input('currency_id'), $currencyUSDId);

                    if (($value * $rate) < 10)
                    {
                        return false;
                    }
                }
            }
            elseif(Auth::user()->isUserType(config('mappings.user_types.back_office')))
            {
                if (request()->has('ewallet_id'))
                {
                    $eWallet = $this->ewalletObj->find(request()->input('ewallet_id'));
                    if ($eWallet && $eWallet->active && !$eWallet->blocked)
                    {
                        $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                        $rate = $this->currencyObj->getCurrenciesConversionsRate(request()->input('currency_id'), $currencyUSDId);

                        if (($value * $rate) < 10)
                        {
                            return false;
                        }
                    }
                }
                elseif(request()->has('user_id'))
                {
                    $user = $this->userObj->has('eWallet')->find(request()->input('user_id'));
                    if ($user && $user->eWallet->active && !$user->eWallet->blocked)
                    {
                        $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                        $rate = $this->currencyObj->getCurrenciesConversionsRate(request()->input('currency_id'), $currencyUSDId);

                        if (($value * $rate) < 10)
                        {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans("message.e-wallet.withdraw-amount-check");
    }
}