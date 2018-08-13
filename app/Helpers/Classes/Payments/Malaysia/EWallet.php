<?php
namespace App\Helpers\Classes\Payments\Malaysia;

use App\{
    Helpers\Classes\Payments\Payment as PaymentClass,
    Helpers\Classes\Payments\Common\EWalletCommon,
    Interfaces\EWallet\EWalletInterface,
    Models\Payments\Payment,
    Models\Users\User,
    Models\EWallets\EWallet as EWalletModel,
    Models\Masters\MasterData
};

class EWallet extends EWalletCommon
{
    /**
     * EWallet constructor.
     *
     * @param EWalletInterface $eWalletInterface
     * @param Payment $payment
     * @param User $user
     * @param EWalletModel $eWallet
     * @param MasterData $masterData
     */
    public function __construct
    (
        EWalletInterface $eWalletInterface,
        Payment $payment,
        User $user,
        EWalletModel $eWallet,
        MasterData $masterData
    )
    {
        parent::__construct(
            $eWalletInterface,
            $payment,
            $user,
            $eWallet,
            $masterData
        );
    }
}