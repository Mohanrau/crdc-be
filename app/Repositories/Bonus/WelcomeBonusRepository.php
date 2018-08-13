<?php

namespace App\Repositories\Bonus;


use App\Interfaces\Bonus\WelcomeBonusInterface;
use App\Interfaces\EWallet\EWalletInterface;
use App\Interfaces\General\CwSchedulesInterface;
use App\Models\Bonus\BonusWelcomeBonusDetails;
use App\Models\Currency\CurrencyConversion;
use App\Models\Masters\MasterData;
use App\Models\Members\Member;
use App\Repositories\BaseRepository;

class WelcomeBonusRepository extends BaseRepository implements WelcomeBonusInterface
{
    protected $welcomeBonusDetailsObj, $cwScheduleRepository, $eWalletRepository, $memberObj, $currencyConversionObj,
                $masterDataObj;

    public function __construct(
        BonusWelcomeBonusDetails $welcomeBonusDetails,
        CwSchedulesInterface $cwScheduleInterface,
        EWalletInterface $eWalletInterface,
        Member $member,
        CurrencyConversion $currencyConversion,
        MasterData $masterData
    )
    {
        $this->welcomeBonusDetailsObj = $welcomeBonusDetails;
        $this->cwScheduleRepository = $cwScheduleInterface;
        $this->eWalletRepository = $eWalletInterface;
        $this->memberObj = $member;
        $this->currencyConversionObj = $currencyConversion;
        $this->masterDataObj = $masterData;
    }

    public function runDailyPayout( $cwId = 0 )
    {
        // first we will see what are the payout that hasn't been made in this CW
        $cwId = ($cwId) ? $cwId :
            $this->cwScheduleRepository->getCwSchedulesList('current')['data']->first()->id;

        //look for all the welcome bonus that is not redeemed yet for the CW
        $welcomeBonusRecords = $this->welcomeBonusDetailsObj->whereHas('bonuses', function($query) use($cwId){
            $query->where('cw_id', $cwId);
        })->where('is_redeemed', 0)->get();

        //get the conversion rate for the CW
        $conversionRate = $this->currencyConversionObj->where('cw_id', $cwId)->get();

        //payout type
        $welcomeBonusTransactionTypeId = $this->masterDataObj->getIdByTitle('Welcome Bonus','ewallet_transaction_type');

        // if there is no conversion rate applied, we shouldnt go ahead with the payment
        if(!$conversionRate->count()){
            return false;
        }

        $welcomeBonusRecords->each(function($record) use($welcomeBonusTransactionTypeId){
            $payoutCurrencyId = $record->bonuses->currency->id;
           //we will pump in the payment into the user's e-wallet
            $transactionInfo = [
                'user_id' => $record->bonuses->user->id,
                'currency_id' => $payoutCurrencyId,
                'amount' => $record->total_local_amount,
                'recipient_email' => $record->bonuses->user->email,
                'transaction_type_id' => $welcomeBonusTransactionTypeId
            ];

            $this->eWalletRepository->createNewTransaction($transactionInfo);

            $record->is_redeemed = 1; // indicates that this welcome bonus has been paid
            $record->save();
        });
    }

    /**
     * We should clawback the amount that is payout to the uplines if the sales has been cancelled
     * This should be run at the CW closing
     */
    public function welcomeBonusClawback( $cwId = 0 )
    {
        // look for the sales type
        $welcomePackTransactionIds = MasterData::whereIn('title', [
                'Registration',
                'Member Upgrade',
                'BA Upgrade'
            ]
        )->get(['id'])->pluck('id')->toArray();


    }


}