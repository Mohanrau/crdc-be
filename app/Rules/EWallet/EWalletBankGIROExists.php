<?php
namespace App\Rules\EWallet;

use App\{
    Interfaces\General\CwSchedulesInterface, Models\EWallets\EWalletGIROBankPayment
};
use Illuminate\Contracts\Validation\Rule;

class EWalletBankGIROExists implements Rule
{
    private $eWalletGIROBankPaymentObj, $cwScheduleObj;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(EWalletGIROBankPayment $eWalletGIROBankPayment, CwSchedulesInterface $cwSchedule)
    {
        $this->eWalletGIROBankPaymentObj = $eWalletGIROBankPayment;

        $this->cwScheduleObj = $cwSchedule;
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
        $currentCW = $this->cwScheduleObj->getCwSchedulesList('past', [
            'sort' => 'id',
            'order' => 'desc',
            'limit' => 1,
            'offset' => 0
        ])->get('data')[0];

        $bankGIRO = $this->eWalletGIROBankPaymentObj->where([
            "cw_id" => $currentCW->id,
            "registered_country_id" => request()->input('registered_country_id'),
            "giro_type" => $value
        ])->get();

        if(!$bankGIRO->count())
        {
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
        return trans('validation.giro_exists');
    }
}
