<?php
namespace App\Helpers\Classes\Payments\Common;

use App\{
    Helpers\Classes\Payments\Payment as PaymentClass,
    Interfaces\EWallet\EWalletInterface,
    Models\Payments\Payment,
    Models\Users\User,
    Models\EWallets\EWallet,
    Models\Masters\MasterData
};

class EWalletCommon extends PaymentClass
{
    protected $eWalletRepositoryObj;
    protected $paymentObj;
    protected $userObj;
    protected $eWalletObj;
    protected $masterDataObj;

    /**
     * EWalletCommon constructor.
     *
     * @param EWalletInterface $eWalletInterface
     * @param Payment $payment
     * @param User $user
     * @param EWallet $eWallet
     * @param MasterData $masterData
     */
    public function __construct
    (
        EWalletInterface $eWalletInterface,
        Payment $payment,
        User $user,
        EWallet $eWallet,
        MasterData $masterData
    )
    {
        parent::__construct();

        $this->eWalletRepositoryObj = $eWalletInterface;

        $this->masterDataObj = $masterData;

        $this->paymentObj = $payment;

        $this->userObj = $user;

        $this->eWalletObj = $eWallet;

        $this->requiredInputs = config('payments.general.payment_common_required_inputs.ewallet');
    }

    /**
     * To process manual payment if needed
     *
     * @param $paymentId
     * @return boolean
     */
    public function processManualPayment($paymentId)
    {
        $ewalletTransactionTypes = config('mappings.ewallet_transaction_type');

        $paymentRecord = $this->paymentObj->find($paymentId);

        $paymentDetail = json_decode($paymentRecord->payment_detail, true);

        $fields = collect($paymentDetail['fields']);

        $iboId = $fields->where('name','ibo_id')->first()['value'];

        $eWalletDetail = $this->userObj->where('old_member_id', $iboId)->first()->eWallet;;
        
        $this->eWalletRepositoryObj->createNewTransaction([
            "ewallet_id" => $eWalletDetail->id,
            "currency_id" => $paymentRecord->currency_id,
            "transaction_type_id" => $this->masterDataObj->getIdByTitle($ewalletTransactionTypes['purchase'], "ewallet_transaction_type"),
            "amount_type_id" => $this->masterDataObj->getIdByTitle("Debit", "ewallet_amount_type"),
            "amount" => $paymentRecord->amount,
            "recipient_email" => $eWalletDetail->user->email,
            "recipient_reference" => "EWallet Purchase",
            "transaction_details" => "EWallet Purchase"
        ]);

        return true;
    }
}