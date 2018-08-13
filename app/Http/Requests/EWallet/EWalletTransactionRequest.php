<?php
namespace App\Http\Requests\EWallet;

use App\{Interfaces\Currency\CurrencyInterface,
    Interfaces\Masters\MasterInterface,
    Models\Currency\Currency,
    Models\EWallets\EWallet,
    Models\Masters\MasterData,
    Models\Users\User,
    Rules\EWallet\EWalletBalanceCheck,
    Rules\EWallet\EWalletCheck,
    Rules\EWallet\SecurityPinCheck,
    Rules\EWallet\WithdrawalAmountCheck,
    Rules\General\MasterDataIdExists};
use Illuminate\Foundation\Http\FormRequest;

class EWalletTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param MasterInterface $masterRepository
     * @param MasterData $masterDataObj
     * @param CurrencyInterface $currencyInterface
     * @param Currency $currency
     * @param User $user
     * @param EWallet $eWallet
     * @return array
     */
    public function rules(
        MasterInterface $masterRepository,
        MasterData $masterDataObj,
        CurrencyInterface $currencyInterface,
        Currency $currency,
        User $user,
        EWallet $eWallet
    )
    {
        return [
            "user_id" => [
                "sometimes",
                "required_without:ewallet_id",
                "integer",
                "exists:users,id",
                new EWalletCheck("user")
            ],
            "ewallet_id" => [
                "sometimes",
                "required_without:user_id",
                "integer",
                "exists:user_ewallets,id",
                new EWalletCheck("ewallet")
            ],
            "currency_id" => "required|integer|exists:currencies,id",
            "transaction_type_id" => [
                "sometimes",
                "required",
                "integer",
                new MasterDataIdExists($masterRepository, 'ewallet_transaction_type')
            ],
            "amount_type_id" => [
                "required_without:transaction_type_id",
                new MasterDataIdExists($masterRepository, 'ewallet_amount_type')
            ],
            "amount" => [
                "required",
                "regex:/^\d*(\.\d{1,2})?$/",
                new EWalletBalanceCheck(),
                new WithdrawalAmountCheck($currencyInterface, $currency, $masterDataObj, $user, $eWallet)
            ],
            "transfer_to_user_id" => [
                'required_if:transaction_type_id,' . $masterDataObj->getIdByTitle("Transfer", "ewallet_transaction_type"),
                'integer',
                'exists:users,id',
                new EWalletCheck("user", true)
            ],
            "recipient_email" => "sometimes|email",
            "recipient_reference" => "sometimes",
            "transaction_details" => "required_without:transaction_type_id",
            "security_pin" => [
                "required_with:transaction_type_id",
                "numeric",
                "digits:6",
                new SecurityPinCheck($user, $eWallet)
            ]
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            "amount.regex" => trans('validation.eWallet_amount_regex'),
            "security_pin.numeric" => trans('validation.security_pin_value'),
        ];
    }
}