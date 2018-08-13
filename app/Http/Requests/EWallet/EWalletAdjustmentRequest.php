<?php
namespace App\Http\Requests\EWallet;

use App\{Interfaces\Masters\MasterInterface,
    Rules\EWallet\EWalletBalanceCheck,
    Rules\EWallet\EWalletCheck,
    Rules\General\MasterDataIdExists};
use Illuminate\Foundation\Http\FormRequest;

class EWalletAdjustmentRequest extends FormRequest
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
     * @return array
     */
    public function rules(
        MasterInterface $masterRepository
    )
    {
        return [
            "user_id" => [
                "required",
                "integer",
                "exists:users,id",
                new EWalletCheck('user')
            ],
            "country_id" => "required|integer|exists:countries,id",
            "debit_amount" => [
                "required_without:credit_amount",
                "regex:/^\d*(\.\d{1,2})?$/",
                new EWalletBalanceCheck()
            ],
            "credit_amount" => "required_without:debit_amount|regex:/^\d*(\.\d{1,2})?$/",
            "reason_id" => [
                "required",
                "integer",
                new MasterDataIdExists($masterRepository, 'ewallet_adjustment_reasons')
            ],
            "remarks" => "sometimes"
        ];
    }
}
