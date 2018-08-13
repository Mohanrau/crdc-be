<?php
namespace App\Repositories\EWallet;

use App\{Helpers\Classes\Sms,
    Helpers\Classes\Uploader,
    Helpers\Classes\OTPHelper,
    Helpers\Traits\AccessControl,
    Interfaces\Currency\CurrencyInterface,
    Interfaces\EWallet\EWalletInterface,
    Interfaces\General\CwSchedulesInterface,
    Interfaces\Members\MemberInterface,
    Interfaces\Settings\SettingsInterface,
    Interfaces\Uploader\UploaderInterface,
    Models\Currency\Currency,
    Models\EWallets\EWallet,
    Models\EWallets\EWalletAdjustment,
    Models\EWallets\EWalletGIROBankPayment,
    Models\EWallets\EWalletGIRORejectedPayment,
    Models\EWallets\EWalletTransaction,
    Models\Locations\Country,
    Models\Locations\CountryBank,
    Models\Masters\MasterData,
    Models\Members\Member,
    Models\Users\User,
    Repositories\BaseRepository};
use Carbon\Carbon;
use Illuminate\{
    Support\Facades\Auth, Support\Facades\Storage, Support\Facades\Validator
};
use PhpOffice\{
    PhpSpreadsheet\IOFactory, PhpSpreadsheet\Spreadsheet, PhpSpreadsheet\Writer\Csv
};

class EWalletRepository extends BaseRepository implements EWalletInterface
{
    use AccessControl;

    private $eWalletObj, $masterDataObj, $currencyModelObj,
        $currencyObj, $userObj,
        $countryObj, $countryBankObj, $smsObj,
        $memberObj, $memberModelObj, $settingsObj, $cwScheduleObj,
        $eWalletGIROBankPaymentObj, $eWalletGIRORejectedPaymentObj,
        $uploaderObj, $uploaderHelperObj, $eWalletAdjustmentObj, $otpHelperObj;

	/**
	 * EWalletRepository constructor.
	 *
	 * @param EWalletTransaction $model
	 * @param EWallet $eWalletObj
	 * @param MasterData $masterData
	 * @param Currency $currencyModel
	 * @param CurrencyInterface $currencyInterface
	 * @param User $user
	 * @param Country $country
	 * @param Member $member
	 * @param Sms $sms
	 * @param CountryBank $countryBank
	 * @param SettingsInterface $settingsInterface
	 * @param CwSchedulesInterface $cwScheduleInterface
	 * @param EWalletGIROBankPayment $eWalletGIROBankPayment
	 * @param EWalletGIRORejectedPayment $eWalletGIRORejectedPayment
	 * @param UploaderInterface $uploaderInterface
	 * @param Uploader $uploader
	 * @param EWalletAdjustment $eWalletAdjustment
	 * @param MemberInterface $memberInterface
	 * @param OTPHelper $OTPHelper
	 */
    public function __construct(
        EWalletTransaction $model,
        EWallet $eWalletObj,
        MasterData $masterData,
        Currency $currencyModel,
        CurrencyInterface $currencyInterface,
        User $user,
        Country $country,
        Member $member,
        Sms $sms,
        CountryBank $countryBank,
        SettingsInterface $settingsInterface,
        CwSchedulesInterface $cwScheduleInterface,
        EWalletGIROBankPayment $eWalletGIROBankPayment,
        EWalletGIRORejectedPayment $eWalletGIRORejectedPayment,
        UploaderInterface $uploaderInterface,
        Uploader $uploader,
        EWalletAdjustment $eWalletAdjustment,
        MemberInterface $memberInterface,
        OTPHelper $OTPHelper
    )
    {
        parent::__construct($model);

        $this->eWalletObj = $eWalletObj;

        $this->masterDataObj = $masterData;

        $this->currencyModelObj = $currencyModel;

        $this->currencyObj = $currencyInterface;

        $this->userObj = $user;

        $this->countryObj = $country;

        $this->memberModelObj = $member;

        $this->memberObj = $memberInterface;

        $this->smsObj = $sms;

        $this->countryBankObj = $countryBank;

        $this->settingsObj = $settingsInterface;

        $this->cwScheduleObj = $cwScheduleInterface;

        $this->eWalletGIROBankPaymentObj = $eWalletGIROBankPayment;

        $this->eWalletGIRORejectedPaymentObj = $eWalletGIRORejectedPayment;

        $this->uploaderObj = $uploaderInterface;

        $this->uploaderHelperObj = $uploader;

        $this->eWalletAdjustmentObj = $eWalletAdjustment;

        $this->otpHelperObj = $OTPHelper;

        $this->with = ['amountType', 'transferToUser', 'currency', 'transactionType', 'transactionStatus'];
    }

    /**
     * Get eWallet Obj of Authorized User
     *
     * @param int $userId
     * @return mixed
     */
    public function getEWallet(int $userId = 0)
    {
        $userId = ($userId > 0) ? $userId : Auth::id();

        if($userId == Auth::id() && $this->userObj->find($userId)->isUserType('member'))
        {
            $currencyId = $this->memberModelObj->where('user_id', $userId)->first()->country->default_currency_id;
            return $this->eWalletObj->firstOrCreate(['user_id' => $userId], ['default_currency_id' => $currencyId])->fresh();
        }

        return $this->eWalletObj->where(['user_id' => $userId])->first();
    }

    /**
     * Create EWallet record
     *
     * @param $inputs
     * @return mixed
     */
    public function createEWallet(array $inputs)
    {
        return $this->eWalletObj->create($inputs);
    }

    /**
     * Update EWallet record based on ewallet_id
     *
     * @param int $id
     * @param array $inputs
     * @return mixed
     */
    public function updateEWallet(int $id, array $inputs)
    {
        return $this->eWalletObj->find($id)->update($inputs);
    }

    /**
     * get all transaction history
     *
     * @param int $countryId
     * @param int $userId
     * @param string|null $fromDate
     * @param string|null $toDate
     * @param string|null $amountType
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getEWalletTransactions(
        int $countryId = 0,
        int $userId = 0,
        string $fromDate = null,
        string $toDate = null,
        string $amountType = null,
        int $paginate = 10,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $where = [];

        if($userId > 0)
        {
            $eWallet = $this->getEWallet($userId);

            if($eWallet)
            {
                $where = [
                    ['ewallet_id', $eWallet->id]
                ];
            }
        }

        if(!is_null($amountType) && $amountType != '')
        {
            $where[] = ['amount_type_id', '=', $this->masterDataObj->getIdByTitle( ucwords( strtolower($amountType) ), 'ewallet_amount_type')];
        }

        !is_null($fromDate) ? $where[] = [ "transaction_date", ">=", date( "Y-m-d H:i:s", strtotime($fromDate)) ] : null;
        !is_null($toDate) ? $where[] = [ "transaction_date", "<=", date( "Y-m-d H:i:s", strtotime($toDate)) ] : null;

        $data = $this->modelObj
            ->where($where);

        if($countryId > 0)
        {
            $data = $data->whereHas('ewallet.user.member', function($query) use ($countryId) {
                $query->where('country_id', $countryId);
            });
        }

        $totalRecords = collect(
            [
                'total' => $data
                    ->orderBy($orderBy, $orderMethod)
                    ->count()
            ]
        );

        $data = $data
            ->orderBy($orderBy, $orderMethod);

        //check if no relations required.
        if ($this->with != null) {
            $data = $data->with($this->with);
        }

        $data = ($paginate) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * get transaction by id
     *
     * @param int $id
     * @return mixed
     */
    public function getEWalletTransaction(int $id)
    {
        return $this->modelObj->find($id);
    }

    /**
     * create new transaction
     *
     * $input Array key usage
     * ======================
     * ewallet_id - wallet id recipient of the e-wallet money (optional)
     * transfer_to_user_id - self-explainatory (optional but either this or ewallet should be filled)
     * transaction_type_id - default to General
     * recipient_reference - 255 max char for reference
     * currency_id - currency of the amount
     * amount - total amount
     * recipient_email
     *
     * @param array $inputs
     * @return array
     */
    public function createNewTransaction(array $inputs)
    {
        $inputCollection = collect($inputs);

        $ewalletAmountTypes = config('mappings.ewallet_amount_type');

        $ewalletTransactionTypes = config('mappings.ewallet_transaction_type');

        $ewalletTransactionStatus = config('mappings.ewallet_transaction_status');

        $now = Carbon::now(config('app.timezone'));

        if ($inputCollection->has('ewallet_id'))
        {
            $eWallet = $this->eWalletObj->find($inputCollection->get('ewallet_id'));

            $transactingUser = $eWallet->user;
        }
        elseif ($inputCollection->has('user_id'))
        {
            $transactingUser = $this->userObj->find($inputCollection->get('user_id'));

            $eWallet = $transactingUser->eWallet;
        }

        if($this->isUser('member'))
        {
            $transactingUser = $this->userObj->find(Auth::id());

            $eWallet = $transactingUser->eWallet;

            $eWalletSupportedCountries = config('ewallet.supported_countries');

            if($transactingUser->member)
            {
                $userCountry = $transactingUser->member->country->code_iso_2;

                if(in_array($userCountry, $eWalletSupportedCountries))
                {
                    if (!$eWallet->active) {
                        return collect([
                            "error_response" => [
                                "error" => trans("message.e-wallet.blocked")
                            ],
                            "error_code" => 422
                        ]);
                    }
                }
            }
        }

        $transactionNumber = $this->settingsObj->getRunningNumber('ewallet_transaction_number', $transactingUser->member->country->id);

        $recipientReference = $inputCollection->has('recipient_reference') ? $inputCollection->get('recipient_reference') : "";

        if( $inputCollection->has('transaction_type_id') )
        {
            if( $inputCollection->get('transaction_type_id') == $this->masterDataObj->getIdByTitle($ewalletTransactionTypes['withdraw'], "ewallet_transaction_type") )
            {
                $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                $rate = $this->currencyObj->getCurrenciesConversionsRate($inputCollection->get('currency_id'), $currencyUSDId);

                $memberPaymentInfo = $this->memberModelObj->where('user_id', $eWallet->user_id)->whereHas('payment')->first()->payment->payment_data;

                if (($inputCollection->get('amount') * $rate) < 10) {
                    return collect([
                        "error_response" => [
                            "amount" => [ trans("message.e-wallet.withdraw-amount-check") ]
                        ],
                        "error_code" => 422
                    ]);
                }

                $data = [
                    "ewallet_id" => $eWallet->id,
                    "currency_id" => $inputCollection->get('currency_id'),
                    "transaction_type_id" => $inputCollection->get('transaction_type_id'),
                    "transaction_date" => $now->toDateTimeString(),
                    "transaction_number" => $transactionNumber,
                    "amount_type_id" => $this->masterDataObj->getIdByTitle($ewalletAmountTypes['debit'], "ewallet_amount_type"),  //Debit
                    "amount" => $inputCollection->get('amount'),
                    "before_balance" => $eWallet->balance,
                    "after_balance" => $eWallet->balance - $inputCollection->get('amount'),
                    "transaction_details" => trans('message.e-wallet.withdraw'),
                    "member_payment_info" => $memberPaymentInfo,
                    "recipient_email" => $inputCollection->has('recipient_email') ? $inputCollection->get('recipient_email') : "",
                    "recipient_reference" => $recipientReference,
                    "transaction_status_id" => $this->masterDataObj->getIdByTitle($ewalletTransactionStatus['pending'], "ewallet_transaction_status")
                ];

                $transaction = Auth::user()->createdBy($this->modelObj)->create($data)->fresh();

                $this->eWalletObj->find($data['ewallet_id'])->update(["balance" => $data['after_balance']]);
            }
            elseif( $inputCollection->get('transaction_type_id') == $this->masterDataObj->getIdByTitle($ewalletTransactionTypes['purchase'], "ewallet_transaction_type") )
            {

                $data = [
                    "ewallet_id" => $eWallet->id,
                    "currency_id" => $inputCollection->get('currency_id'),
                    "transaction_type_id" => $inputCollection->get('transaction_type_id'),
                    "transaction_date" => $now->toDateTimeString(),
                    "transaction_number" => $transactionNumber,
                    "amount_type_id" => $this->masterDataObj->getIdByTitle($ewalletAmountTypes['debit'], "ewallet_amount_type"),  //Debit
                    "amount" => $inputCollection->get('amount'),
                    "before_balance" => $eWallet->balance,
                    "after_balance" => $eWallet->balance - $inputCollection->get('amount'),
                    "transaction_details" => $inputCollection->get('transaction_details'),
                    "recipient_email" => $inputCollection->has('recipient_email') ? $inputCollection->get('recipient_email') : "",
                    "recipient_reference" => $recipientReference,
                    "transaction_status_id" => $this->masterDataObj->getIdByTitle($ewalletTransactionStatus['successful'], "ewallet_transaction_status")
                ];

                $transaction = Auth::user()->createdBy($this->modelObj)->create($data)->fresh();

                $this->eWalletObj->find($data['ewallet_id'])->update(["balance" => $data['after_balance']]);
            }
            elseif( $inputCollection->get('transaction_type_id') == $this->masterDataObj->getIdByTitle($ewalletTransactionTypes['transfer'], "ewallet_transaction_type") )
            {
                $transferToUser = $this->userObj->find($inputCollection->get('transfer_to_user_id'));

                $recipientReference2 = ($recipientReference == "") ? "" : " - " . $recipientReference;

                $data = [
                    [
                        "ewallet_id" => $eWallet->id,
                        "currency_id" => $inputCollection->get('currency_id'),
                        "transaction_type_id" => $inputCollection->get('transaction_type_id'),
                        "transfer_to_user_id" => $inputCollection->get('transfer_to_user_id'),
                        "transaction_date" => $now->toDateTimeString(),
                        "transaction_number" => $transactionNumber,
                        "amount_type_id" => $this->masterDataObj->getIdByTitle($ewalletAmountTypes['debit'], "ewallet_amount_type"),  //Debit
                        "amount" => $inputCollection->get('amount'),
                        "before_balance" => $eWallet->balance,
                        "after_balance" => $eWallet->balance - $inputCollection->get('amount'),
                        "transaction_details" => trans('message.e-wallet.transfer-to', [
                            "details" => $transferToUser->member->country->code . $transferToUser->old_member_id . " " . $transferToUser->name . $recipientReference2
                        ]),
                        "recipient_email" => $inputCollection->has('recipient_email') ? $inputCollection->get('recipient_email') : "",
                        "recipient_reference" => $recipientReference,
                        "transaction_status_id" => $this->masterDataObj->getIdByTitle($ewalletTransactionStatus['successful'], "ewallet_transaction_status")
                    ],
                    [
                        "ewallet_id" => $transferToUser->eWallet->id,
                        "currency_id" => $inputCollection->get('currency_id'),
                        "transaction_type_id" => $inputCollection->get('transaction_type_id'),
                        "transaction_date" => $now->toDateTimeString(),
                        "transaction_number" => $transactionNumber,
                        "amount_type_id" => $this->masterDataObj->getIdByTitle($ewalletAmountTypes['credit'], "ewallet_amount_type"),  //Credit
                        "amount" => $inputCollection->get('amount'),
                        "before_balance" => $transferToUser->eWallet->balance,
                        "after_balance" => $transferToUser->eWallet->balance + $inputCollection->get('amount'),
                        "transaction_details" => trans('message.e-wallet.received-from', [
                            'details' => $transactingUser->member->country->code . $transactingUser->old_member_id . " " . $transactingUser->name . $recipientReference2
                        ]),
                        "recipient_email" => $inputCollection->has('recipient_email') ? $inputCollection->get('recipient_email') : "",
                        "recipient_reference" => $recipientReference,
                        "transaction_status_id" => $this->masterDataObj->getIdByTitle($ewalletTransactionStatus['successful'], "ewallet_transaction_status")
                    ]
                ];

                foreach ($data as $item)
                {
                    $transaction[] = Auth::user()->createdBy($this->modelObj)->create($item)->fresh();

                    $this->eWalletObj->find($item['ewallet_id'])->update(["balance" => $item['after_balance']]);
                }

                $transaction = $transaction[0];
            }
            elseif( $inputCollection->get('transaction_type_id') == $this->masterDataObj->getIdByTitle($ewalletTransactionTypes['welcome-bonus'], "ewallet_transaction_type") )
            {

                $data = [
                    "ewallet_id" => $eWallet->id,
                    "currency_id" => $inputCollection->get('currency_id'),
                    "transaction_type_id" => $inputCollection->get('transaction_type_id'),
                    "transaction_date" => $now->toDateTimeString(),
                    "transaction_number" => $transactionNumber,
                    "amount_type_id" => $this->masterDataObj->getIdByTitle($ewalletAmountTypes['credit'], "ewallet_amount_type"),  //Debit
                    "amount" => $inputCollection->get('amount'),
                    "before_balance" => $eWallet->balance,
                    "after_balance" => $eWallet->balance + $inputCollection->get('amount'),
                    "transaction_details" => $inputCollection->get('transaction_details'),
                    "recipient_email" => $inputCollection->has('recipient_email') ? $inputCollection->get('recipient_email') : "",
                    "recipient_reference" => $recipientReference,
                    "transaction_status_id" => $this->masterDataObj->getIdByTitle($ewalletTransactionStatus['successful'], "ewallet_transaction_status")
                ];

                $transaction = Auth::user()->createdBy($this->modelObj)->create($data)->fresh();

                $this->eWalletObj->find($data['ewallet_id'])->update(["balance" => $data['after_balance']]);
            }
        }
        else
        {
            $data = [
                "ewallet_id" => $eWallet->id,
                "currency_id" => $inputCollection->get('currency_id'),
                "transaction_date" => $now->toDateTimeString(),
                "transaction_number" => $transactionNumber,
                "transaction_type_id" => $this->masterDataObj->getIdByTitle($ewalletTransactionTypes['general'], "ewallet_transaction_type"),
                "amount_type_id" => $inputCollection->get('amount_type_id'),
                "amount" => $inputCollection->get('amount'),
                "before_balance" => $eWallet->balance,
                "transaction_details" => $inputCollection->get('transaction_details'),
                "recipient_email" => $inputCollection->has('recipient_email') ? $inputCollection->get('recipient_email') : "",
                "recipient_reference" => $recipientReference,
                "transaction_status_id" => $this->masterDataObj->getIdByTitle($ewalletTransactionStatus['successful'], "ewallet_transaction_status")
            ];

            if($inputCollection->get('amount_type_id') == $this->masterDataObj->getIdByTitle($ewalletAmountTypes['debit'], "ewallet_amount_type"))
            {
                $data = array_merge($data, [
                    "after_balance" => $eWallet->balance - $inputCollection->get('amount'),
                ]);
            }
            elseif($inputCollection->get('amount_type_id') == $this->masterDataObj->getIdByTitle($ewalletAmountTypes['credit'], "ewallet_amount_type"))
            {
                $data = array_merge($data, [
                    "after_balance" => $eWallet->balance + $inputCollection->get('amount'),
                ]);
            }

            $transaction = (Auth::id()) ?
                Auth::user()->createdBy($this->modelObj)->create($data)->fresh() :
                    $this->modelObj->create($data)->fresh();

            $this->eWalletObj->find($data['ewallet_id'])->update(["balance" => $data['after_balance']]);
        }

        return collect($transaction);
    }

    /**
     * send OTP Code
     *
     * @param string $mobile
     * @return array
     * @throws \Exception
     */
    public function sendOTPCode(string $mobile)
    {
        $otpType = config('mappings.otp_code_type.phone');

        $currentUserOtp = $this->otpHelperObj->getOTPCode(
            $mobile,
            $otpType,
            0,
            Auth::id(),
            false
        );

        if(!is_null($currentUserOtp))
        {
            //check timestamp if less than 5 minutes using Carbon php lib
            if ($currentUserOtp->updated_at->diffInMinutes(now(config('app.timezone'))) < 5)
            {
                return [
                    'response_code' => 0,
                    'response_msg' => __('message.mobile.already_sent')
                ];
            }
        }

        $userOtp = $this->otpHelperObj->generateOTPCode($mobile,
            $otpType,
            Auth::id());

        $smsResponse = $this->smsObj->sendSMS($mobile, __('message.e-wallet.otp-code', ['otp' => $userOtp->code]));

        return $smsResponse;
    }

    /**
     * Activate e-Wallet
     *
     * @param array $inputs
     * @return mixed
     */
    public function activateEWallet(array $inputs)
    {
        $this->updateEWallet($this->getEWallet()->id, ['security_pin' => bcrypt($inputs['security_pin']), 'active' => 1]);

        return $this->getEWallet();
    }

    /**
     * Change Auto Withdrawal Status
     *
     * @param array $inputs
     * @return mixed
     */
    public function changeEWalletAutoWithdrawal(array $inputs)
    {
        $eWallet = $this->getEWallet();

        $this->updateEWallet($eWallet->id, [ 'auto_withdrawal' => $inputs['auto_withdrawal'] ]);

        return $this->getEWallet();
    }

    /**
     * Get Bank Payment Listing
     *
     * @param int $registeredCountryId
     * @param string $giroType
     * @param bool $generate
     * @return array|mixed
     */
    public function getBankPaymentRecords(
        int $registeredCountryId,
        string $giroType,
        bool $generate = false
    )
    {
        $totalAmount = 0;
        $now = Carbon::now(config('app.timezone'));
        $currentDate = $now->toDateString();

        $currentCW = $this->cwScheduleObj->getCwSchedulesList('past', [
            'sort' => 'id',
            'order' => 'desc',
            'limit' => 1,
            'offset' => 0
        ])->get('data')[0];

        $allGiroTypes = config('ewallet.giro_types');

        $cwName = explode('-', $currentCW->cw_name);

        $country = $this->countryObj->find($registeredCountryId);

        $countryGiroTypes = collect($allGiroTypes[$country->code_iso_2]);

        $members = $this->memberModelObj
            ->has('payment')
            ->has('user.ewallet')
            ->where('country_id', $registeredCountryId)
            ->get();

        $records = [
            'registered_country' => $country->name,
            'giro_type' => $giroType,
            'total_amount' => 0,
            'column_names' => [],
            'data' => []
        ];

        // Malaysia
        if(strtoupper($giroType) == $countryGiroTypes->get("my"))
        {
            $records['column_names'] = [
                'Payment Mode',
                'Beneficiary Name',
                'Beneficiary Account',
                'Beneficiary Bank Code',
                'Amount',
                'Payment Description',
                'Payment Reference',
                'Beneficiary New IC No',
                'Beneficiary Old IC No',
                'Beneficiary Business Registration',
                'Beneficiary Others',
                'Country Code'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($amount == 0)
                {
                    continue;
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                if( strtolower($bankData->title) == "bank" )
                {
                    $totalAmount += $amount;

                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    if(!count($bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')))
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A/C Holder Name')->pluck('value')[0];
                    }
                    else
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')[0];
                    }

                    $records['data'][] = [
                        "payment_mode" => "",
                        "beneficiary_name" => $beneficiaryName,
                        "beneficiary_account" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "beneficiary_bank_code" => $bank->swift_code,
                        "amount" => $amount,
                        "payment_description" => $country->code_iso_2 . '_e-Wallet withdrawal_'. $currentDate,
                        "payment_reference" => "i-Commission W".date('y/m', strtotime($currentCW->cw_name)),
                        "beneficiary_new_ic_no" => $member->ic_passport_number,
                        "beneficiary_old_ic_no" => "",
                        "beneficiary_business_registration" => "",
                        "beneficiary_others" => "",
                        "country_code" => $country->code_iso_2
                    ];
                }
            }
        }
        elseif(strtoupper($giroType) == $countryGiroTypes->get("mpay"))
        {
            $records['column_names'] = [
                'No.',
                'Card Number First 6',
                'Card Number Last 4',
                'Card Expiry',
                'Name',
                'ID/Passport',
                'Amount',
                'Reference number 1',
                'Reference number 2',
                'Remark'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $transactions = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') );

                $amount = $transactions->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                if( strtoupper($bankData->title) == "MPAY" )
                {
                    $reference = $this->modelObj->whereIn('id', $transactions->pluck('id'))->orderBy('id', 'desc')->first()->transaction_number;

                    $totalAmount += $amount;

                    $bankFields = collect($bankData->fields);

                    $records['data'][] = [
                        "no" => $member->user->id,
                        "card_no_first_six" => substr($bankFields->where('label', '=', 'Card Number')->pluck('value')[0], 0, 6),
                        "card_no_last_four" => substr($bankFields->where('label', '=', 'Card Number')->pluck('value')[0], -4),
                        "card_expiry" => $bankFields->where('label', '=', 'Card Expiry Date')->pluck('value')[0] . $bankFields->where('label', '=', 'Card Expiry Year')->pluck('value')[0],
                        "name" => $bankFields->where('label', '=', 'Card Holder Name')->pluck('value')[0],
                        "id_or_passport" => $member->ic_passport_number,
                        "amount" => $amount,
                        "reference_number_1" => $reference,
                        "reference_number_2" => $reference,
                        "remarks" => $country->code_iso_2 . '_e-Wallet withdrawal_'. $currentDate
                    ];
                }
            }
        }
        // Singapore
        elseif(strtoupper($giroType) == $countryGiroTypes->get("sg"))
        {
            $records['column_names'] = [
                'Bank Code',
                'Branch Code',
                'A/C No.',
                'Name',
                'Payment Code',
                'Description',
                'Reference',
                'Amount'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $transactions = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') );

                $amount = $transactions->sum('amount');

                $reference = $transactions->orderBy('id', 'desc')->first()->transaction_number;

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    $bank_code = $branch_code = "";

                    if(str_contains('-', $bank->swift_code))
                    {
                        $bank_code = explode('-', $bank->swift_code)[1];
                        $branch_code = explode('-', $bank->swift_code)[2];
                    }

                    if(!count($bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')))
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A/C Holder Name')->pluck('value')[0];
                    }
                    else
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')[0];
                    }

                    $records['data'][] = [
                        "bank_code" => $bank_code,
                        "branch_code" => $branch_code,
                        "account_no" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "name" => $beneficiaryName,
                        "payment_code" => 20,
                        "description" => $country->code_iso_2 . '_e-Wallet withdrawal_'. $currentDate,
                        "reference" => $reference,
                        "amount" => $amount
                    ];
                }
            }
        }
        // Taiwan
        elseif(strtoupper($giroType) == $countryGiroTypes->get("tw"))
        {
            $records['column_names'] = [
                'Beneficiary Name',
                'Second Party ID',
                'A/C No.',
                'Payment Amount',
                'Corresponding Bank'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    $bank_code = $branch_code = "";

                    if(str_contains('-', $bank->swift_code))
                    {
                        $bank_code = explode('-', $bank->swift_code)[0];
                        $branch_code = explode('-', $bank->swift_code)[1];
                    }

                    if(!count($bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')))
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A/C Holder Name')->pluck('value')[0];
                    }
                    else
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')[0];
                    }

                    $records['data'][] = [
                        "beneficiary_name" => $beneficiaryName,
                        "second_party_id" => $member->user->old_member_id,
                        "account_no" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "amount" => $amount,
                        "corresponding_bank" => $bank_code.$branch_code
                    ];
                }
            }
        }
        // Hong Kong
        elseif(strtoupper($giroType) == $countryGiroTypes->get("hk"))
        {
            $records['column_names'] = [
                'Name',
                'Code',
                'Payment Currency',
                'Net Payment Amount (HKD)',
                'Remarks'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    if(!count($bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')))
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A/C Holder Name')->pluck('value')[0];
                    }
                    else
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')[0];
                    }

                    $records['data'][] = [
                        "name" => $beneficiaryName,
                        "code" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "currency" => $country->currency->code,
                        "amount" => $amount,
                        "remarks" => $country->code_iso_2 . '_e-Wallet withdrawal_'. $currentDate
                    ];
                }
            }
        }
        // Thailand
        elseif(strtoupper($giroType) == $countryGiroTypes->get("hsbc"))
        {
            $records['column_names'] = [
                'Member ID',
                'Member Name',
                '',
                '',
                '',
                'Bank Code',
                'Branch Code',
                'Bank Account',
                'Net Amount (THB)'
            ];
            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    $bank_code = "";

                    if(str_contains('-', $bank->swift_code))
                    {
                        $bank_code = explode('-', $bank->swift_code)[1];
                    }

                    if(!count($bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')))
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A/C Holder Name')->pluck('value')[0];
                    }
                    else
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')[0];
                    }

                    $records['data'][] = [
                        "member_id" => $member->user->old_ibs_user_id,
                        "member_name" => $beneficiaryName,
                        "dot_1" => ".",
                        "dot_2" => ".",
                        "dot_3" => ".",
                        "bank_code" => $bank_code,
                        "branch_code" => "0".substr($bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0], 0, 3),
                        "bank_account" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "currency" => $country->currency->code,
                        "amount" => $amount
                    ];
                }
            }
        }
        // Brunei
        elseif(strtoupper($giroType) == $countryGiroTypes->get("bn"))
        {
            $records['column_names'] = [
                'EMP_NO',
                'EMP_NAME',
                'BANK_NAME',
                'ACC_NO',
                'AMOUNT'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    if(!count($bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')))
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A/C Holder Name')->pluck('value')[0];
                    }
                    else
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')[0];
                    }

                    $records['data'][] = [
                        "emp_no" => $member->user->old_member_id,
                        "emp_name" => $beneficiaryName,
                        "bank_name" => $bank->name,
                        "account_no" => "'" . $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "amount" => $amount
                    ];
                }
            }
        }
        //TODO:: need to complete Cambodia, Brunei, Philippines and Indonesia because currently Payment info format not in database
        // Cambodia
        elseif(strtoupper($giroType) == $countryGiroTypes->get("anz_online"))
        {
            $records['column_names'] = [
                'Code',
                'Name',
                'Beneficiary Bank Name',
                'Beneficiary Bank Code / Swift Code',
                'Beneficiary A.C / IBAN No.',
                'Net Payment Amount (USD)',
                'Remarks'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    if(!count($bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')))
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A/C Holder Name')->pluck('value')[0];
                    }
                    else
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')[0];
                    }

                    $records['data'][] = [
                        "code" => $member->user->old_ibs_user_id,
                        "name" => $beneficiaryName,
                        "bank_name" => $bank->name,
                        "bank_swift_code" => $bank->swift_code,
                        "bank_account_no" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "amount" => $amount,
                        "remarks" => $country->code_iso_2 . '_e-Wallet withdrawal_'. $currentDate
                    ];
                }
            }
        }
        elseif(strtoupper($giroType) == $countryGiroTypes->get("wings"))
        {
            $records['column_names'] = [
                'Wing Number',
                'Payee ID',
                'Name',
                'Net Payment Amount (USD)',
                'Remarks'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtoupper($bankData->title) == "WINGS" )
                {
                    $bankFields = collect($bankData->fields);

                    $records['data'][] = [
                        "wing_number" => "",
                        "payee_id" => $member->user->old_ibs_user_id,
                        "name" => $member->user->name,
                        "amount" => $amount,
                        "remarks" => $country->code_iso_2 . '_e-Wallet withdrawal_'. $currentDate
                    ];
                }
            }
        }
        // Philippines
        elseif(strtoupper($giroType) == $countryGiroTypes->get("bdo_online"))
        {
            $records['column_names'] = [
                'No.',
                'Account Number',
                'Reference Number',
                'Client Transaction Number',
                'Amount',
                'Particulars',
                'Remarks',
                'Member ID',
                'Member Name',
                'TIN No',
                'Bank Code',
            ];

            $count = 1;

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    $records['data'][] = [
                        's_no' => $count,
                        "account_no" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "reference_number" => "",
                        "client_transaction_number" => "",
                        "amount" => $amount,
                        "particulars" => "COMM CW".$currentCW->id,
                        "remarks" => $country->code_iso_2 . '_e-Wallet withdrawal_'. $currentDate,
                        "member_id" => $member->user->old_member_id,
                        "beneficiary_name" => $member->user->name,
                        "tin_number" => $member->tin_no_philippines,
                        "bank_code" => $bank->swift_code
                    ];

                    $count++;
                }
            }
        }
        elseif(strtoupper($giroType) == $countryGiroTypes->get("bdo_cash_card"))
        {
            $records['column_names'] = [
                'No.',
                'Account #',
                'Amount',
                'Name',
                'Remarks',
                'Particulars',
                'Member ID',
                'TIN No',
                'Bank Code'
            ];

            $count = 1;

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    $records['data'][] = [
                        "s_no" => $count,
                        "account_no" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "amount" => $amount,
                        "name" => $member->user->name,
                        "remarks" => $country->code_iso_2 . '_e-Wallet withdrawal_'. $currentDate,
                        "particulars" => "COMM CW".$currentCW->id,
                        "member_id" => $member->user->old_member_id,
                        "tin_number" => $member->tin_no_philippines,
                        "bank_code" => $bank->swift_code
                    ];

                    $count++;
                }
            }
        }
        elseif(strtoupper($giroType) == $countryGiroTypes->get("dragonpay"))
        {
            $records['column_names'] = [
                'Account',
                'Type',
                'Name',
                'Amount',
                'MM/DD/YYYY',
                'Particulars',
                'Member ID',
                'TIN No',
                'Bank Code'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    $records['data'][] = [
                        "account_no" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "type" => "",
                        "name" => $member->user->name,
                        "amount" => $amount,
                        "current_date" => date('m/d/Y', now(config('app.timezone'))),
                        "particulars" => "COMM CW".$currentCW->id,
                        "member_id" => $member->user->old_member_id,
                        "tin_number" => $member->tin_no_philippines,
                        "bank_code" => $bank->swift_code
                    ];
                }
            }
        }
        elseif(strtoupper($giroType) == $countryGiroTypes->get("hsbc_online"))
        {
            $records['column_names'] = [
                'No.',
                'File Reference',
                'Value Date',
                'Customer Reference',
                'Bank Account No',
                'Bank Account Name',
                'Amount',
                'Bank Swift Code',
                'Member ID',
                'TIN No'
            ];

            $count = 1;

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    $records['data'][] = [
                        "s_no" => $count,
                        "file_reference" => "PH.HSBC-CW".$currentCW->id,
                        "value_date" => date('d/m/Y', now(config('app.timezone'))),
                        "customer_reference" => "COMM CW".$currentCW->id,
                        "account_no" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "name" => $member->user->name,
                        "amount" => $amount,
                        "bank_swift_code" => $bank->swift_code,
                        "member_id" => $member->user->old_member_id,
                        "tin_number" => $member->tin_no_philippines,
                    ];

                    $count++;
                }
            }
        }
        // Indonesia
        elseif(strtoupper($giroType) == $countryGiroTypes->get("cimb"))
        {
            $records['column_names'] = [
                'Bank Account Number',
                'Name',
                'Currency',
                'Amount',
                'Description',
                'Total Quantity',
                'Date',
                'Email Address',
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                $count = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->count();

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    if(!count($bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')))
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A/C Holder Name')->pluck('value')[0];
                    }
                    else
                    {
                        $beneficiaryName = $bankFields->where('label', '=', 'A\/C Holder Name')->pluck('value')[0];
                    }

                    $records['data'][] = [
                        "account_no" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "name" => $beneficiaryName,
                        "currency" => $country->currency->name,
                        "amount" => $amount,
                        "description" => $country->code_iso_2 . '_e-Wallet withdrawal_'. $currentDate,
                        "total_qty" => $count,
                        "date" => date('Ymd', now(config('app.timezone'))),
                        "email_address" => $member->user->email
                    ];
                }
            }
        }
        elseif(strtoupper($giroType) == $countryGiroTypes->get("bca"))
        {
            $records['column_names'] = [
                'Account No.',
                'Transaction Amount',
                'Member Number',
                'Member Name',
                'Department',
                'Transaction Date',
                '',
                'Juml. Rec',
                'Total'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $records['data'][] = [
                        "account_number" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "amount" => $amount,
                        "member_id" => $member->user->old_member_id,
                        "name" => $member->user->name,
                        "department" => "",
                        "date" => date('m/d/Y', now(config('app.timezone'))),
                    ];
                }
            }
        }
        elseif(strtoupper($giroType) == $countryGiroTypes->get("hsbc"))
        {
            $records['column_names'] = [
                'Beneficiary Name',
                'Amount',
                'Beneficiary Bank Code',
                'Beneficiary Account Number',
                'Beneficiary Reference',
                'Email Advice Recipient 1',
                'Email Advice Recipient 2',
                'Email Advice Recipient 3',
                'Email Advice Recipient 4',
                'Email Advice Recipient 5',
                'Email Advice Recipient 6',
                'Free Text',
                'Regulatory Reporting 1',
                'Regulatory Reporting 2',
                'Regulatory Reporting 3',
                'Currency',
                'Customer Reference'
            ];

            foreach($members as $member)
            {
                $payment = collect(json_decode($member->payment->payment_data));
                $selectedId = $payment->get('selected');
                $bankData = collect($payment->get('bank_data'))->where('id', $selectedId);

                if (count($bankData)) {
                    $bankData = $bankData[$selectedId - 1];
                } else {
                    continue;
                }

                $amount = $member->user->ewallet->transactions
                    ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                    ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                    ->sum('amount');

                if($member->user->ewallet->auto_withdrawal)
                {
                    $currencyUSDId = $this->currencyModelObj->where('code', 'USD')->first()->id;

                    $rate = $this->currencyObj->getCurrenciesConversionsRate($country->default_currency_id, $currencyUSDId);

                    if (($member->user->ewallet->balance * $rate) >= 10) {
                        $amount += $member->user->ewallet->balance;

                        if($generate)
                        {
                            $this->createNewTransaction([
                                "ewallet_id" => $member->user->ewallet->id,
                                "currency_id" => $country->default_currency_id,
                                "transaction_type_id" => $this->masterDataObj->getIdByTitle("Withdraw", "ewallet_transaction_type"),
                                "amount" => $member->user->ewallet->balance,
                                "recipient_email" => $member->user->email,
                                "recipient_reference" => "Auto Withdrawal"
                            ]);
                        }
                    }
                }

                if($generate)
                {
                    $member->user->ewallet->transactions()
                        ->where("transaction_type_id", $this->masterDataObj->getIdByTitle('Withdraw', 'ewallet_transaction_type') )
                        ->where("transaction_status_id", $this->masterDataObj->getIdByTitle('In Process', 'ewallet_transaction_status') )
                        ->update([
                            "member_payment_info" => $member->payment->payment_data,
                            "transaction_status_id" => $this->masterDataObj->getIdByTitle('Successful', 'ewallet_transaction_status')
                        ]);
                }

                $totalAmount += $amount;

                if( strtolower($bankData->title) == "bank" )
                {
                    $bankFields = collect($bankData->fields);

                    $bankId = $bankFields->where("identifier", '=', "countries_bank")->pluck('value')[0];

                    $bank = $this->countryBankObj->find($bankId);

                    $records['data'][] = [
                        "name" => $member->user->name,
                        "amount" => $amount,
                        "bank_swift_code" => $bank->swift_code,
                        "account_no" => $bankFields->where('label', '=', 'Bank Account No')->pluck('value')[0],
                        "reference" => "BONUSSV" . $cwName[1] . date('y', strtotime($cwName[0]) ),
                        "email_advice_recipient_1" => "",
                        "email_advice_recipient_2" => "",
                        "email_advice_recipient_3" => "",
                        "email_advice_recipient_4" => "",
                        "email_advice_recipient_5" => "",
                        "email_advice_recipient_6" => "",
                        "free_text" => "",
                        "regulatory_reporting_1" => "",
                        "regulatory_reporting_2" => "/SKN/11",
                        "regulatory_reporting_3" => "",
                        "currency" => "",
                        "customer_reference" => $member->user->old_member_id
                    ];
                }
            }
        }

        $records['total_amount'] = $totalAmount;

        if($generate)
        {
            if(count($records['data']))
            {
                return Auth::user()->createdBy($this->eWalletGIROBankPaymentObj)->create([
                    "batch_id" => $this->eWalletGIROBankPaymentObj->generateBatchId(),
                    "cw_id" => $currentCW->id,
                    "registered_country_id" => $registeredCountryId,
                    "giro_type" => $giroType,
                    "total_amount" => $totalAmount,
                    "data" => json_encode([
                        "column_names" => $records['column_names'],
                        "data" => $records['data']
                    ])
                ]);
            }
        }

        return $records;
    }

    /**
     * Generate Bank Payment File
     *
     * @param array $inputs
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function generateBankPaymentFile(array $inputs)
    {
        $giroTypes = [];
        $inputCollection = collect($inputs);

        if($inputCollection->has("batch_id"))
        {
            $bankGIRO = $this->eWalletGIROBankPaymentObj->where("batch_id", $inputCollection->get("batch_id"))->first();
        }
        else
        {
            $currentCW = $this->cwScheduleObj->getCwSchedulesList('past', [
                'sort' => 'id',
                'order' => 'desc',
                'limit' => 1,
                'offset' => 0
            ])->get('data')[0];

            $bankGIRO = $this->eWalletGIROBankPaymentObj->where([
                "cw_id" => $currentCW->id,
                "registered_country_id" => $inputCollection->get('registered_country_id'),
                "giro_type" => $inputCollection->get('giro_type')
            ])->first();
        }

        if($bankGIRO && $bankGIRO->count())
        {
            $response = $bankGIRO;
        }
        else
        {
            $response = $bankGIRO = $this->getBankPaymentRecords($inputCollection->get('registered_country_id'), $inputCollection->get('giro_type'), true);
        }

        $allGiroTypeSettings = array_values(collect(json_decode($this->settingsObj->getSettingDataByKey(['giro_type'])['giro_type'][0]->value))->toArray());

        foreach ($allGiroTypeSettings as $giroTypeSetting)
        {
            $giroTypes = array_merge($giroTypes, $giroTypeSetting);
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->createSheet();

        if( in_array($bankGIRO->giro_type, $giroTypes) )
        {
            $records = json_decode($response->data, true);

            $col = "A";

            $row = 1;

            // Indonesia
            if($bankGIRO->giro_type == "HSBC")
            {
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("A1", "Debit Account Number");
                $spreadsheet->getActiveSheet()->getColumnDimension("A1")->setAutoSize(true);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("B1", "050048446071");
                $spreadsheet->getActiveSheet()->getColumnDimension("B1")->setAutoSize(true);

                $spreadsheet->setActiveSheetIndex(0)->setCellValue("A2", "Value Date");
                $spreadsheet->getActiveSheet()->getColumnDimension("A2")->setAutoSize(true);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("B2", date('Ymd', strtotime($response->created_at)));
                $spreadsheet->getActiveSheet()->getColumnDimension("B2")->setAutoSize(true);

                $spreadsheet->setActiveSheetIndex(0)->setCellValue("A3", "Payment Set Code");
                $spreadsheet->getActiveSheet()->getColumnDimension("A3")->setAutoSize(true);
                $spreadsheet->setActiveSheetIndex(0)->setCellValue("B3", "HN4");
                $spreadsheet->getActiveSheet()->getColumnDimension("B3")->setAutoSize(true);

                $row+=3;
            }

            foreach ($records['column_names'] as $value)
            {
                $cell = $col.$row;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $value);

                $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

                $col++;
            }

            $row++;

            foreach ($records['data'] as $data)
            {
                $col = "A";

                foreach ($data as $datum)
                {
                    $cell = $col.$row;

                    settype($datum, "String");

                    $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $datum);

                    $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

                    $col++;
                }

                $row++;
            }

            // Philippines
            if( in_array($bankGIRO->giro_type, config('ewallet.giro_types.PH')) )
            {
                $col = "A";
                $row += 2;
                $col = chr(ord($col) + array_search('Amount', $records['column_names']));
                $cell = $col.$row;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $response->total_amount);

                $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
            }
            // Indonesia
            elseif($bankGIRO->giro_type == "BCA")
            {
                $col = "A";
                $row = 2;
                $cell = chr(ord($col) + array_search('Juml. Rec', $records['column_names'])).$row;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, count($records['data']));

                $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

                $cell = chr(ord($col) + array_search('Total', $records['column_names'])).$row;

                $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $response->total_amount);

                $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            }
        }


        // Output excel file

        $outputPath = config('filesystems.subpath.giro_payment.storage_path');

        $absoluteUrlPath = config('filesystems.subpath.giro_payment.absolute_url_path');

        $filename = $bankGIRO->giro_type . '_e-Wallet withdrawal_'. date('Y-m-d', strtotime($bankGIRO->created_at)) . '.csv';

        $fileDisk = config('setting.uploader-temp-disk');
        $filePath = config('setting.uploader-temp-path');
        $workPath = config('setting.uploader.ewallet_giro_payment_file.file_path');

        $saveFolder = $this->uploaderHelperObj->getUploaderPath($filePath) . $this->uploaderHelperObj->getUploaderFolder($workPath);

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Csv($spreadsheet);
        $writer->setEnclosure('');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $writer->save($outputPath.$filename);

        Storage::disk($fileDisk)->put($saveFolder.'/'.$filename, file_get_contents($outputPath.$filename), "public");

        Storage::disk('public')->delete($absoluteUrlPath.$filename);

        $result = collect([]);

        $result->push(['download_link' => Storage::disk('public')->url($absoluteUrlPath.$filename)]);

        return $result;
    }

    /**
     * Get Bank Payment History Listing
     *
     * @param array $inputs
     * @return mixed
     */
    public function getBankPaymentHistory(array $inputs)
    {
        $inputCollection = collect($inputs);

        $eWalletGIROBankPayment = $this->eWalletGIROBankPaymentObj->with(['cwSchedule', 'country', 'createdBy'])
            ->where('registered_country_id', $inputCollection->get('registered_country_id'))
            ->where('giro_type', $inputCollection->get('giro_type'));

        if($inputCollection->has('batch_id_from') && $inputCollection->has('batch_id_to'))
        {
            $data = $eWalletGIROBankPayment->whereBetween('batch_id', [$inputCollection->get('batch_id_from'), $inputCollection->get('batch_id_to')])->get();
        }
        else
        {
            $data = $eWalletGIROBankPayment->get();
        }

        return $data;
    }

    /**
     * get ewallet to bank(remittance) with pending integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationRemittance()
    {
        $data = $this->modelObj
            ->where('user_ewallet_transactions.yy_remittance_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('user_ewallet_transactions.transaction_status_id', $this->masterDataObj->getIdByTitle("Successful", "ewallet_transaction_status"))
            ->select('user_ewallet_transactions.id')
            ->distinct()
            ->get();

        return $data;
    }

    /**
     * get ewallet(credit) with pending integration
     *
     * @return mixed
     */
    public function getYonyouIntegrationEwallet()
    {
        $data = $this->modelObj
            ->where('user_ewallet_transactions.yy_integration_status', config('integrations.yonyou.yy_integration_status.new'))
            ->where('user_ewallet_transactions.transaction_status_id', $this->masterDataObj->getIdByTitle("Successful", "ewallet_transaction_status"))
            ->select('user_ewallet_transactions.id')
            ->distinct()
            ->get();

        return $data;
    }

    /*
     * Upload and Read GIRO Rejected Payment File
     *
     * @param array $inputs
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function readRejectedPaymentFile(array $inputs)
    {
        $result = collect([]);

        $inputCollection = collect($inputs['file']);

        $fileLink = $inputCollection->get('link');

        $fileName = $inputCollection->get('name');

        $this->uploaderHelperObj->downloadFile($fileLink, 'storage/' . $fileName);

        $publicFileName = './storage/' . $fileName;

        $reader = IOFactory::createReader( IOFactory::identify($publicFileName) );

        $fileData = $reader->load($publicFileName);

        $spreadsheet = $fileData->getActiveSheet();

        $fileNo = $this->eWalletGIRORejectedPaymentObj->generateFileNo();

        $rowCount = $spreadsheet->getHighestRow();

        $lastColumn = $spreadsheet->getHighestColumn();

        $columns = range("A", $lastColumn);

        $spreadsheetData = $spreadsheet->toArray();

        $columnNames = $spreadsheetData[0];

        unset($spreadsheetData[0]);

        if( !in_array( config('ewallet.giro_rejected_file_columns'), array($columnNames), true ) )
        {
            return collect([
                "error_response" => [
                    "error" => trans("message.e-wallet.rejected_payment_file_no_error")
                ],
                "error_code" => 422
            ]);
        }

        $keys = str_replace(' ', '_', $columnNames);

        $spreadsheetData = collect( array_combine(range(0, count($spreadsheetData)-1), $spreadsheetData) )
            ->transform(function($item, $key) use($keys){
            if( !empty( array_filter( $item, function ($a) { return $a !== null; } ) ) )
            {
                $item = array_combine($keys, $item);
                return array_change_key_case($item);
            }
        });

        $data = $spreadsheetData->each(function($item, $key) use($spreadsheetData){
            if( is_null($item) )
            {
                $spreadsheetData->forget($key);
            }
        });

        $validator = Validator::make( array_combine( range(1, count( $data->toArray() ) ), array_values( $data->toArray() ) ), [
            "*.ibo_id" => "required|numeric|exists:users,old_member_id",
            "*.ibo_name" => "required",
            "*.rejected_amount" => "required|regex:/^\d*(\.\d{1,2})?$/",
            "*.registered_country_currency" => "required|alpha",
            "*.registered_country_total" => "required|regex:/^\d*(\.\d{1,2})?$/",
            "*.remarks" => "required"
        ]);

        $result->push([
            'file_name' => $inputCollection->get('name'),
            'file_url' => $fileLink,
            'file_no' => $fileNo,
            'data' => $data,
            'errors' => $validator->errors()->toArray()
        ]);

        return $result;
    }

    /**
     * Submit GIRO Rejected Payment Records
     *
     * @param array $inputs
     * @return \Illuminate\Support\Collection
     */
    public function submitRejectedPaymentFile(array $inputs)
    {
        $today = Carbon::now(config('app.timezone'))->toDateString();

        $inputCollection = collect($inputs);

        $country_id = $inputCollection->get('country_id');

        $file = [
            'name' => $this->uploaderHelperObj->getFileName($inputCollection->get('file_url')),
            'link' => $inputCollection->get('file_url')
        ];

        $readFile = $this->readRejectedPaymentFile(['file' => $file]);

        if(array_key_exists("error_response", $readFile->toArray()))
        {
            return $readFile;
        }
        else
        {
            $readFile = $readFile[0];
        }

        if(count($readFile['errors']))
        {
            return collect([
                "error_response" => [
                    "error" => trans("message.e-wallet.rejected_payment_submit_error")
                ],
                "error_code" => 422
            ]);
        }

        $rejectedPaymentRecords = collect($readFile['data']);

        foreach ($rejectedPaymentRecords as $rejectedPaymentRecord)
        {
            $rejectedPaymentRecord = collect($rejectedPaymentRecord);

            $currency = $this->currencyModelObj->where('code', $rejectedPaymentRecord->get('registered_country_currency'))->first();

            $user = $this->userObj->where('old_member_id', $rejectedPaymentRecord->get("ibo_id"));

            if($user->count())
            {
                $user_id = $user->first()->id;

                Auth::user()->createdBy($this->eWalletGIRORejectedPaymentObj)->create([
                    "user_id" => $user_id,
                    "country_id" => $country_id,
                    "currency_id" => $currency->id,
                    "file_no" => $readFile['file_no'],
                    "rejected_amount" => $rejectedPaymentRecord->get('rejected_amount'),
                    "total_amount" => $rejectedPaymentRecord->get('registered_country_total'),
                    "remarks" => $rejectedPaymentRecord->get('remarks')
                ]);
            }

        }

        return collect(
            $this->eWalletGIRORejectedPaymentObj
                ->where('country_id', $country_id)
                ->whereDate('created_at', $today)
                ->get()
                ->toArray()
        );
    }

    /**
     * Get Rejected Payment Sample File
     *
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getRejectedPaymentSampleFile()
    {
        $col = "A";
        $row = 1;

        $spreadsheet = new Spreadsheet();

        $spreadsheet->createSheet();

        $data = config('ewallet.giro_rejected_file_columns');

        foreach ($data as $datum)
        {
            $cell = $col.$row;

            settype($datum, "String");

            $spreadsheet->setActiveSheetIndex(0)->setCellValue($cell, $datum);

            $spreadsheet->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);

            $col++;
        }

        // Output excel file

        $outputPath = config('filesystems.subpath.rejected_payment.storage_path');

        $absoluteUrlPath = config('filesystems.subpath.rejected_payment.absolute_url_path');

        $filename = 'Sample_Rejected_Payment_File.csv';

        $fileDisk = config('setting.uploader-temp-disk');
        $filePath = config('setting.uploader-temp-path');
        $workPath = config('setting.uploader.ewallet_rejected_payment_file.file_path');

        $saveFolder = $this->uploaderHelperObj->getUploaderPath($filePath) . $this->uploaderHelperObj->getUploaderFolder($workPath);

        if(!Storage::disk('public')->has($absoluteUrlPath))
        {
            Storage::disk('public')->makeDirectory($absoluteUrlPath);
        }

        $writer = new Csv($spreadsheet);
        $writer->save($outputPath.$filename);

        Storage::disk($fileDisk)->put($saveFolder.'/'.$filename, file_get_contents($outputPath.$filename), "public");

        Storage::disk('public')->delete($absoluteUrlPath.$filename);

        $result = collect([]);

        $result->push(['download_link' => Storage::disk($fileDisk)->url($saveFolder.'/'.$filename)]);

        return $result;
    }

    /**
     * Rejected Payment Listing
     *
     * @param array $filters
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function rejectedPaymentListing(
        array $filters,
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $where = [];

        $filters = collect($filters);

        if($filters->has('user_id') && !empty($filters->get('user_id')) )
        {
            $where[] = ['user_id', $filters->get('user_id')];
        }

        if($filters->has('country_id') && !empty($filters->get('country_id')) )
        {
            $where[] = [ 'country_id', $filters->get('country_id') ];
        }

        if($filters->has('level_one_status') && !empty($filters->get('level_one_status')) )
        {
            $where[] = [ 'level_one_status', $filters->get('level_one_status') ];
        }

        if($filters->has('level_two_status') && !empty($filters->get('level_two_status')) )
        {
            $where[] = [ 'level_two_status', $filters->get('level_two_status') ];
        }

        if($filters->has('year') && !empty($filters->get('year')) )
        {
            $whereYear = $filters->get('year');

            $rejectedPaymentObj = $this->eWalletGIRORejectedPaymentObj
                ->where($where)
                ->whereYear("created_at", $whereYear)
                ->orderBy($orderBy, $orderMethod);
        }
        else
        {
            $rejectedPaymentObj = $this->eWalletGIRORejectedPaymentObj
                ->where($where)
                ->orderBy($orderBy, $orderMethod);
        }

        $totalRecords = collect(
            [
                'total' => $rejectedPaymentObj
                    ->count()
            ]
        );

        $rejectedPaymentObj = ($paginate) ?
            $rejectedPaymentObj->offset($offset)->limit($paginate)->get() :
            $rejectedPaymentObj->get();

        return $totalRecords -> merge(['data' => $rejectedPaymentObj]);
    }

    /**
     * Update Rejected Payment Records
     *
     * @param array $inputs
     * @param bool $levelTwo
     * @return mixed
     */
    public function rejectedPaymentUpdate(array $inputs, bool $levelTwo = false)
    {
        $now = Carbon::now(config('app.timezone'))->toDateTimeString();
        $data = collect($inputs['data']);

        unset($inputs['data']);

        $data->each(function($item, $key) use ($inputs, $now, $levelTwo) {
            $rejectedPayment = $this->eWalletGIRORejectedPaymentObj->find($item['id']);

            unset($item['id']);

            if(isset($item['level_one_status']))
            {
                $item['level_one_approval_at'] = $now;

                if($item['level_one_status'])
                {
                    $item['level_one_reason'] = "";
                }
            }
            elseif (isset($item['level_two_status']))
            {
                $item['level_two_approval_at'] = $now;

                if($item['level_two_status'])
                {
                    $item['level_two_reason'] = "";
                }
            }

            $rejectedPayment->update(array_merge($item, $inputs));

            $rejectedPayment->fresh();

            if($levelTwo && $item['level_two_status'])
            {
                $user = $this->userObj->find($rejectedPayment->user_id);

                $this->createNewTransaction([
                    "ewallet_id" => $user->eWallet->id,
                    "currency_id" => $user->member->country->currency->id,
                    "amount_type_id" => $this->masterDataObj->getIdByTitle("Credit", "ewallet_amount_type"),
                    "amount" => $rejectedPayment->rejected_amount,
                    "recipient_email" => $user->email,
                    "recipient_reference" => "",
                    "transaction_details" => trans('message.e-wallet.giro-rejected', ['details' => $rejectedPayment->remarks] )
                ]);
            }
        });

        return collect(
                $this->eWalletGIRORejectedPaymentObj->with([
                    'country',
                    'user',
                    'currency',
                    'levelOneUser',
                    'levelTwoUser',
                    'createdBy'
                ])
                ->whereIn('id', $data->pluck('id')->toArray())
                ->get()
                ->toArray()
        );
    }

    /**
     * eWallet Adjustment Listing
     *
     * @param array $filters
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function eWalletAdjustmentListing(
        array $filters,
        int $paginate = 0,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $where = [];

        $filters = collect($filters);

        if($filters->has('user_id') && !empty($filters->get('user_id')) )
        {
            $where[] = ['user_id', $filters->get('user_id')];
        }

        if($filters->has('country_id') && !empty($filters->get('country_id')) )
        {
            $where[] = [ 'country_id', $filters->get('country_id') ];
        }

        if($filters->has('level_one_status') && !empty($filters->get('level_one_status')) )
        {
            $where[] = [ 'level_one_status', $filters->get('level_one_status') ];
        }

        if($filters->has('level_two_status') && !empty($filters->get('level_two_status')) )
        {
            $where[] = [ 'level_two_status', $filters->get('level_two_status') ];
        }

        if($filters->has('date') && !empty($filters->get('date')) )
        {
            $where[] = [ 'created_at', $filters->get('date') ];
        }

        $eWalletAdjustmentObj = $this->eWalletAdjustmentObj
            ->where($where)
            ->orderBy($orderBy, $orderMethod);

        $totalRecords = collect(
            [
                'total' => $eWalletAdjustmentObj
                    ->count()
            ]
        );

        $eWalletAdjustmentObj = ($paginate) ?
            $eWalletAdjustmentObj->offset($offset)->limit($paginate)->get() :
            $eWalletAdjustmentObj->get();

        return $totalRecords -> merge(['data' => $eWalletAdjustmentObj]);
    }

    /**
     * Get eWallet Adjustment Record Details
     *
     * @param int $id
     * @param bool $memberData
     * @return mixed
     */
    public function eWalletAdjustmentRecord(int $id, bool $memberData = false)
    {
        $adjustment = $this->eWalletAdjustmentObj->find($id);

        $adjustmentCollection = collect($adjustment->toArray());

        if($memberData)
        {
            $adjustmentCollection->put('member_details',
                $this->memberObj->memberDetails($adjustment->user_id)['member_data']
            );
        }

        return $adjustmentCollection;
    }

    /**
     * add new record in eWallet Adjustment
     *
     * @param array $inputs
     * @return mixed
     */
    public function eWalletAdjustmentInsert(array $inputs)
    {
        $user = $this->userObj->find($inputs['user_id']);

        if (isset($inputs['debit_amount']) && $inputs['debit_amount'] != 0)
        {
            $amountTypeId = $this->masterDataObj->getIdByTitle('Debit', 'ewallet_amount_type');
            $amount = $inputs['debit_amount'];

            $inputs['amount_type_id'] = $amountTypeId;
            $inputs['amount'] = $amount;
        }
        elseif (isset($inputs['credit_amount']) && $inputs['credit_amount'] != 0)
        {
            $amountTypeId = $this->masterDataObj->getIdByTitle('Credit', 'ewallet_amount_type');
            $amount = $inputs['credit_amount'];

            $inputs['amount_type_id'] = $amountTypeId;
            $inputs['amount'] = $amount;
        }

        $reason = $this->masterDataObj->find($inputs['reason_id']);

        $transaction = $this->createNewTransaction([
            "ewallet_id" => $user->eWallet->id,
            "currency_id" => $user->member->country->currency->id,
            "amount_type_id" => $amountTypeId,
            "amount" => $amount,
            "recipient_email" => $user->email,
            "recipient_reference" => "",
            "transaction_details" => trans('message.e-wallet.adjustment', ['details' => $reason->title . " - " . $inputs['remarks']] )
        ]);

        $inputs['transaction_id'] = $transaction->get('id');

        $adjustment = Auth::user()->createdBy($this->eWalletAdjustmentObj)->create( $inputs );

        return $adjustment->fresh();
    }

    /**
     * eWallet Adjustment
     *
     * @param int $id
     * @param array $inputs
     * @param bool $levelTwo
     * @return mixed
     */
    public function eWalletAdjustmentUpdate(int $id, array $inputs, bool $levelTwo = false)
    {
        $now = Carbon::now(config('app.timezone'))->toDateTimeString();
        $eWalletAdjustmentObj = $this->eWalletAdjustmentObj->find($id);

        if(isset($inputs['level_one_status']))
        {
            $inputs['level_one_approval_at'] = $now;

            if($inputs['level_one_status'])
            {
                $inputs['level_one_reason'] = "";
            }
        }
        elseif (isset($inputs['level_two_status']))
        {
            $inputs['level_two_approval_at'] = $now;

            if($inputs['level_two_status'])
            {
                $inputs['level_two_reason'] = "";
            }
        }

        if($levelTwo && $inputs['level_two_status'])
        {
            $user = $this->userObj->find($eWalletAdjustmentObj->user_id);

            if ($this->masterDataObj->getIdByTitle('Debit', 'ewallet_amount_type') == $eWalletAdjustmentObj->amount_type_id
                && $eWalletAdjustmentObj->amount > $user->eWallet->balance)
            {
                return collect([
                    "error_response" => [
                        "error" => trans("message.e-wallet.adjustment-amount-check")
                    ],
                    "error_code" => 422
                ]);
            }

            $this->createNewTransaction([
                "ewallet_id" => $user->eWallet->id,
                "currency_id" => $user->member->country->currency->id,
                "amount_type_id" => $eWalletAdjustmentObj->amount_type_id,
                "amount" => $eWalletAdjustmentObj->amount,
                "recipient_email" => $user->email,
                "recipient_reference" => "",
                "transaction_details" => trans('message.e-wallet.adjustment', ['details' => $eWalletAdjustmentObj->reason->title. " - " .$eWalletAdjustmentObj->remarks] )
            ]);
        }

        $eWalletAdjustmentObj->update($inputs);

        return collect($eWalletAdjustmentObj->fresh());
    }
}