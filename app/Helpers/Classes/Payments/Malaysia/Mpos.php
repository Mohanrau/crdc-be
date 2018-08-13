<?php
namespace App\Helpers\Classes\Payments\Malaysia;

use App\Helpers\Classes\Payments\Payment;
use App\Interfaces\Integrations\CimbMposInterface;
use App\Interfaces\Masters\MasterInterface;
use App\Interfaces\Settings\SettingsInterface;
use App\Models\Payments\MposTransaction;

class Mpos extends Payment
{

    protected $cimbMposRepository;
    /**
     * Key configurations from config
     */
    public function __construct(CimbMposInterface $cimbMpos)
    {
        parent::__construct();

        $this->isThirdPartyRefund = true;

        $this->cimbMposRepository = $cimbMpos;

        $this->isCreationGeneratePaymentDetail = true;

        $this->requiredInputs = config('payments.general.payment_common_required_inputs.mpos');

    }

    public function validateManualPayment()
    {
        //we must make sure that this exists in the db
        return $this->cimbMposRepository->checkRedeemable($this->params);
    }

    /**
     * To process manual payment if needed
     *
     * @param $paymentId
     * @return boolean
     */
    public function processManualPayment($paymentId)
    {
        return $this->cimbMposRepository->redeem($this->params, $paymentId);
    }

    public function generatePaymentDetailJson(
        SettingsInterface $settingsRepositoryObj,
        MasterInterface $masterRepositoryObj,
        array $mappingObject
    )
    {
        $mposTransaction = $this->cimbMposRepository->queryExistence($this->params);

        $paymentDetail = [];

        if(isset($mposTransaction->id)){

            $paymentDetail = [
                'merchant_id' => $mposTransaction->merchant_id,
                'terminal_id' => $mposTransaction->terminal_id,
                'approval_code' => $mposTransaction->approval_code,
                'amount' => $mposTransaction->amount,
                'settlement_date' => date('Y-m-d', strtotime($mposTransaction->created_at))
            ];
        }

        return $paymentDetail;
    }
}