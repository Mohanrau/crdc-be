<?php
namespace App\Rules\Payments;

use App\{
    Interfaces\Masters\MasterInterface,
    Models\Payments\PaymentModeProvider,
    Models\Payments\PaymentModeSetting,
    Models\Users\User,
    Models\EWallets\EWallet
};
use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Auth,
    Support\Facades\Hash
};

class EWalletPaymentValidation implements Rule
{
    private 
        $masterRepositoryObj,
        $paymentModeProviderObj,
        $paymentModeSettingObj,
        $userObj,
        $eWalletObj,
        $fields,
        $paymentModeConfigCodes,
        $errorType,
        $walletBalance;

    /**
     * EWalletPaymentValidation constructor.
     *
     * @param MasterInterface $masterInterface
     * @param PaymentModeProvider $paymentModeProvider
     * @param PaymentModeSetting $paymentModeSetting
     * @param User $user
     * @param EWallet $eWallet
     * @param fields
     */
    public function __construct(
        MasterInterface $masterInterface,
        PaymentModeProvider $paymentModeProvider,
        PaymentModeSetting $paymentModeSetting,
        User $user,
        EWallet $eWallet,
        $fields
    )
    {
        $this->masterRepositoryObj = $masterInterface;

        $this->userObj = $user;

        $this->paymentModeProviderObj = $paymentModeProvider;

        $this->paymentModeSettingObj = $paymentModeSetting;

        $this->eWalletObj = $eWallet;

        $this->fields = $fields;

        $this->paymentModeConfigCodes = config('mappings.payment_mode');
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
        $result = true;

        //Get Mater Data Detail
        $settingsData = $this->masterRepositoryObj->getMasterDataByKey(array('payment_mode'));

        //Get E-Wallet Payment Mode ID
        $paymentMode = array_change_key_case($settingsData['payment_mode']->pluck('id','title')->toArray());

        $eWalletPaymentId = $paymentMode[$this->paymentModeConfigCodes['e-wallet']];

        $eWalletPaymentProviderIds = $this->paymentModeProviderObj
            ->where('master_data_id', $eWalletPaymentId)
            ->pluck('id');

        $paymentSettingDetail = $this->paymentModeSettingObj
            ->where('id', $value)
            ->whereIn('payment_mode_provider_id', $eWalletPaymentProviderIds)
            ->first();

        if($paymentSettingDetail){

            $fields = collect($this->fields);

            $amount = $fields->where('name','amount')->first()['value'];

            $iboId = $fields->where('name','ibo_id')->first()['value'];

            $pinNumber = $fields->where('name','pin_number')->first()['value'];

            //Validate IBO ID
            $userDetail = $this->userObj->where('old_member_id', $iboId)->first();

            if($userDetail){

                //Validate Wallet Pin Number
                $eWallet = $userDetail->eWallet;

                if($eWallet){

                    if (Hash::check($pinNumber, $eWallet->security_pin)) {

                        $this->errorType = "insufficientBalance";

                        $this->walletBalance = $eWallet->balance;

                        $result = ($eWallet->balance >= $amount) ? true : false;

                    } else {

                        $this->errorType = "invalidPinNo";

                        $result = false;
                    }

                } else {

                    $this->errorType = "invalidIboId";

                    $result = false;

                }

            } else {

                $this->errorType = "invalidIboId";

                $result = false;
            }
        }

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        switch ($this->errorType) {
            case "invalidIboId":
                $msg = trans('message.make-payment.ewallet-invalid-ibo-id');
                break;

            case "invalidPinNo":
                $msg = trans('message.make-payment.ewallet-invalid-pin-no');
                break;

            case "insufficientBalance":
                $msg = trans('message.make-payment.ewallet-insufficient-balance', [
                    'balance' => $this->walletBalance
                ]);
                break;

            default :
                $msg = trans('message.make-payment.ewallet-invalid-ibo-id');
                break;
        };

        return $msg;
    }
}
