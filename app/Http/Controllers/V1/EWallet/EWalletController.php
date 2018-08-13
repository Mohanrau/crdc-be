<?php
namespace App\Http\Controllers\V1\EWallet;

use App\{Helpers\Classes\OTPHelper,
    Helpers\Traits\AccessControl,
    Http\Controllers\Controller,
    Http\Requests\EWallet\EWalletAdjustmentRequest,
    Http\Requests\EWallet\EWalletMobileNumberRequest,
    Http\Requests\EWallet\EWalletTransactionRequest,
    Interfaces\EWallet\EWalletInterface,
    Interfaces\General\CwSchedulesInterface,
    Interfaces\Masters\MasterInterface,
    Interfaces\Settings\SettingsInterface,
    Interfaces\Uploader\UploaderInterface,
    Models\EWallets\EWallet,
    Models\EWallets\EWalletAdjustment,
    Models\EWallets\EWalletGIROBankPayment,
    Models\EWallets\EWalletGIRORejectedPayment,
    Models\EWallets\EWalletTransaction,
    Models\Locations\Country,
    Models\Masters\MasterData,
    Models\Members\MemberContactInfo,
    Models\Users\User,
    Rules\EWallet\EWalletBankGIROExists,
    Rules\EWallet\EWalletCheck,
    Rules\EWallet\EWalletCheckGIROType,
    Rules\EWallet\SecurityPinCheck,
    Rules\General\MasterDataTitleExists};
use Facades\App\Helpers\Classes\Uploader;
use Illuminate\{
    Http\Request, Support\Facades\Auth, Support\Facades\Validator
};

class EWalletController extends Controller
{
    use AccessControl;

    private $obj,
        $authorizedModel,
        $eWalletObj,
        $settingsObj,
        $eWalletGIROBankPaymentObj,
        $eWalletGIRORejectedPaymentObj,
        $eWalletAdjustmentObj,
        $cwScheduleObj,
        $uploaderObj,
        $memberContactInfoObj,
        $masterRepository,
        $otpHelperObj,
        $masterDataObj,
        $countryObj,
        $userObj;

    /**
     * EWalletController constructor.
     *
     * @param EWallet $eWallet
     * @param EWalletTransaction $eWalletTransaction
     * @param EWalletInterface $eWalletInterface
     * @param SettingsInterface $settings
     * @param EWalletGIROBankPayment $eWalletGIROBankPayment
     * @param EWalletGIRORejectedPayment $eWalletGIRORejectedPayment
     * @param EWalletAdjustment $eWalletAdjustment
     * @param CwSchedulesInterface $cwScheduleInterface
     * @param UploaderInterface $uploader
     * @param MemberContactInfo $memberContactInfo
     * @param MasterInterface $master
     * @param OTPHelper $OTPHelper
     * @param Country $country
     * @param MasterData $masterData
     * @param User $user
     */
    public function __construct(
        EWallet $eWallet,
        EWalletTransaction $eWalletTransaction,
        EWalletInterface $eWalletInterface,
        SettingsInterface $settings,
        EWalletGIROBankPayment $eWalletGIROBankPayment,
        EWalletGIRORejectedPayment $eWalletGIRORejectedPayment,
        EWalletAdjustment $eWalletAdjustment,
        CwSchedulesInterface $cwScheduleInterface,
        UploaderInterface $uploader,
        MemberContactInfo $memberContactInfo,
        MasterInterface $master,
        OTPHelper $OTPHelper,
        Country $country,
        MasterData $masterData,
        User $user
    )
    {
        $this->middleware('auth');

        $this->obj = $eWalletInterface;

        $this->authorizedModel = $eWalletTransaction;

        $this->eWalletObj = $eWallet;

        $this->settingsObj = $settings;

        $this->eWalletGIROBankPaymentObj = $eWalletGIROBankPayment;

        $this->eWalletGIRORejectedPaymentObj = $eWalletGIRORejectedPayment;

        $this->eWalletAdjustmentObj = $eWalletAdjustment;

        $this->uploaderObj = $uploader;

        $this->cwScheduleObj = $cwScheduleInterface;

        $this->memberContactInfoObj = $memberContactInfo;

        $this->masterRepository = $master;

        $this->otpHelperObj = $OTPHelper;

        $this->countryObj = $country;

        $this->masterDataObj = $masterData;

        $this->userObj = $user;
    }

    /**
     * Get e-wallet info
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function eWalletInfo()
    {
        $model = $this->eWalletObj->where('user_id', Auth::id())->first();

        $this->authorize('view', $model);

        return response($this->obj->getEWallet());
    }

    /**
     * generate and verify otp for received mobile number
     *
     * @param EWalletMobileNumberRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function eWalletValidateMobileNumber(EWalletMobileNumberRequest $request)
    {
        $countryCallCode = $this->countryObj->find($request->input('mobile_1_country_code_id'))->call_code;

        $mobile = $countryCallCode . $request->input('mobile_1_num');

        $otpType = config('mappings.otp_code_type.phone');

        if ($request->has('code'))
        {
            $userOtp = $this->otpHelperObj->getOTPCode(
                $mobile,
                $otpType,
                $request->input('code'),
                Auth::id()
            );

            $userOtp->expired = 1;

            $userOtp->save();

            if ($request->has('request_type') && strtolower($request->input('request_type')) == 'activate')
            {
                $this->obj->getEWallet();

                $this->memberContactInfoObj
                    ->updateOrCreate(
                        [
                            'user_id' => Auth::id()
                        ],
                        [
                            'user_id' => Auth::id(),
                            'mobile_1_country_code_id' => $request->input('mobile_1_country_code_id'),
                            'mobile_1_num' => $request->input('mobile_1_num'),
                            'mobile_1_activated' => 1
                        ]
                    );

                $this->userObj->where('id', Auth::id())->update([
                    'mobile' => $mobile
                ]);

                $userOtp->verified = 1;

                $userOtp->expired = 1;

                $userOtp->save();

                return response(['code' => trans('message.e-wallet.otp-code-valid')]);
            }
            elseif ($request->has('request_type') && strtolower($request->input('request_type')) == 'validate')
            {
                return response(['message' => trans('message.e-wallet.number_validated')]);
            }
        }
        else
        {
            return response($this->obj->sendOTPCode($mobile));
        }
    }

    /**
     * Validate eWallet Security Pin
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function eWalletValidateSecurityPin(Request $request)
    {
        request()->validate([
            "security_pin" => [
                "required",
                "numeric",
                "digits:6",
                new SecurityPinCheck($this->userObj, $this->eWalletObj)
            ]
        ]);

        return response( [ "response" => true ] );
    }

    /**
     * Set e-Wallet Security Pin and Activate e-Wallet
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function eWalletActivation(Request $request)
    {
        $model = $this->eWalletObj->where('user_id', Auth::id())->first();

        $this->authorize('update', $model);

        request()->validate([
            "security_pin" => "required|confirmed|numeric|digits:6"
        ]);

        return response($this->obj->activateEWallet( $request->all() ));
    }

    /**
     * Update Security Number
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function eWalletSetNewSecurityNumber(Request $request)
    {
        $model = $this->eWalletObj->where('user_id', Auth::id())->first();

        $this->authorize('update', $model);

        request()->validate([
            "security_pin" => "required|confirmed|numeric|digits:6"
        ]);

        $eWallet = $this->obj->getEWallet();

        $this->obj->updateEWallet($eWallet->id, [ 'security_pin' => bcrypt( $request->input('security_pin') ) ]);

        return response($this->obj->getEWallet());
    }

    /**
     * Change Auto Withdrawal Status
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function eWalletAutoWithdrawal(Request $request)
    {
        $model = $this->eWalletObj->where('user_id', Auth::id())->first();

        $this->authorize('update', $model);

        request()->validate([
            "auto_withdrawal" => "required|boolean"
        ]);

        return response( $this->obj->changeEWalletAutoWithdrawal($request->all()));
    }

    /**
     * Get EWallet Transaction by ID
     *
     * @param int $id
     * @return mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTransaction(int $id)
    {
        request()->request->set('ewallet_transaction_id', $id);

        request()->validate([
            'ewallet_transaction_id' => "required|integer|exists:user_ewallet_transactions,id"
        ]);

        $this->authorize('view', $this->authorizedModel);

        return $this->obj->getEWalletTransaction($id);
    }

    /**
     * List of Transaction History
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function transactionListing(Request $request)
    {
        $this->authorize('listing', $this->authorizedModel);

        if($this->isUser('member'))
        {
            request()->validate([
                'from_date' => 'sometimes|nullable|date',
                'to_date' => 'sometimes|nullable|date',
                'amount_type' => [
                    'sometimes',
                    'nullable',
                    new MasterDataTitleExists($this->masterRepository, 'ewallet_amount_type')
                ]
            ]);

            $eWallet = $this->obj->getEWallet();

            if($eWallet)
            {
                if(empty($eWallet->security_pin))
                {
                    return response([
                        "error" => trans('message.e-wallet.not-activated')
                    ], 422);
                }

                if($eWallet->blocked)
                {
                    return response([
                        "error" => trans('message.e-wallet.blocked')
                    ], 422);
                }

                if(!$eWallet->active)
                {
                    return response([
                        "error" => trans('message.e-wallet.inactive')
                    ], 422);
                }
            }
            else
            {
                return response([
                    "error" => trans('message.e-wallet.not-activated')
                ], 422);
            }

            $countryId = 0;
            $userId = Auth::id();
        }
        else
        {
            request()->validate([
                'country_id' => 'required|integer|exists:countries,id',
                'user_id' => [
                    'sometimes',
                    'required',
                    'integer',
                    'exists:users,id',
                    new EWalletCheck('user', true)
                ],
                'from_date' => 'sometimes|nullable|date',
                'to_date' => 'sometimes|nullable|date',
                'amount_type' => [
                    'sometimes',
                    'nullable',
                    new MasterDataTitleExists($this->masterRepository, 'ewallet_amount_type')
                ]
            ]);

            $countryId = $request->input('country_id');
            $userId = $request->has('user_id') ? $request->input('user_id') : 0;

            if($request->has('limit'))
            {
                request()->request->set('paginate', $request->input('limit'));
            }
        }

        return response(
            $this->obj->getEWalletTransactions(
                $countryId,
                $userId,
                ($request->has('from_date') ? $request->input('from_date') : null),
                ($request->has('to_date') ? $request->input('to_date') : null),
                ($request->has('amount_type') ? $request->input('amount_type') : null),
                ($request->has('paginate') ? $request->input('paginate') :  10),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * Create transaction
     *
     * @param EWalletTransactionRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createTransaction(EWalletTransactionRequest $request)
    {
        $countryId = 0;

        if($this->isUser('member'))
        {
            $countryId = Auth::user()->member->country_id;
        }
        else
        {
            if($request->has('user_id'))
            {
                $countryId = $this->userObj->find($request->input('user_id'))->member->country_id;
            }
            elseif($request->has('ewallet_id'))
            {
                $countryId = $this->eWalletObj->find($request->input('ewallet_id'))->user->member->country_id;
            }
        }

        $this->authorize('create', [$this->authorizedModel, $countryId]);

        $data = $this->obj->createNewTransaction($request->all());

        if($data->has('error_code'))
        {
            return response($data->get('error_response'), 422);
        }

        return response($data);
    }

    /**
     * List Bank Payment Records
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function bankGIRO(Request $request)
    {
        $this->authorize('listing', $this->eWalletGIROBankPaymentObj);

        request()->validate([
            "registered_country_id" => "required|integer|exists:countries,id",
            "giro_type" => [
                "required",
                new EWalletCheckGIROType($this->settingsObj),
                new EWalletBankGIROExists($this->eWalletGIROBankPaymentObj, $this->cwScheduleObj)
            ]
        ]);

        return response(
            $this->obj->getBankPaymentRecords(
                $request->input('registered_country_id'),
                $request->input('giro_type')
            )
        );
    }

    /**
     * Generate Bank GIRO Payment File
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function generateBankGIROFile(Request $request)
    {
        $this->authorize('download', $this->eWalletGIROBankPaymentObj);

        request()->validate([
            "batch_id" => "sometimes|required|exists:user_ewallet_giro_bank_payments,batch_id",
            "registered_country_id" => "required_without:batch_id|integer|exists:countries,id",
            "giro_type" => [
                "required_without:batch_id",
                new EWalletCheckGIROType($this->settingsObj)]
        ]);

        return response(
            $this->obj->generateBankPaymentFile($request->all())
        );
    }

    /**
     * Bank GIRO History
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function bankGIROHistory(Request $request)
    {
        $this->authorize('listing', $this->eWalletGIROBankPaymentObj);

        request()->validate([
            "registered_country_id" => "required|integer|exists:countries,id",
            "giro_type" => [
                "required",
                new EWalletCheckGIROType($this->settingsObj)
            ],
            "batch_id_from" => "sometimes|exists:user_ewallet_giro_bank_payments,batch_id",
            "batch_id_to" => "sometimes|exists:user_ewallet_giro_bank_payments,batch_id"
        ]);

        return response($this->obj->getBankPaymentHistory($request->all()));
    }

    /**
     * Get Rejected Payment Listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function listRejectedPaymentRecords(Request $request)
    {
        $this->authorize('listing', $this->eWalletGIRORejectedPaymentObj);

        request()->validate([
            "country_id" => "required|integer|exists:countries,id",
            "user_id" => "sometimes|integer|exists:users,id",
            "year" => "sometimes|date_format:Y",
            "level_one_status" => "sometimes|in:-1,1,0|nullable",
            "level_two_status" => "sometimes|in:-1,1,0|nullable"
        ]);

        return response( $this->obj->rejectedPaymentListing(
            $request->all(),
            ($request->has('paginate') ? $request->input('paginate') :  0),
            ($request->has('sort') ? $request->input('sort') :  'id'),
            ($request->has('order') ? $request->input('order') : 'desc'),
            ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * Read Rejected GIRO Payments File
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function readRejectedGIROFile(Request $request)
    {
        Validator::make([
            'file' => $request->file('file'),
            'extension' => '.' . strtolower($request->file('file')->getClientOriginalExtension())
        ],
        [
            'file' => config('setting.uploader.ewallet_rejected_payment_file.server_validate'),
            'extension' => 'required|in:'.config('setting.uploader.ewallet_rejected_payment_file.client_validate')
        ])->validate();

        $setting = Uploader::getUploaderSetting(true);

        $fileType = 'ewallet_rejected_payment_file';

        $file = $this->uploaderObj->processUploadFile($request, $fileType, $setting[$fileType]);

        $response = $this->obj->readRejectedPaymentFile(['file' => $file]);

        if($response->has('error_code') && $response->get('error_code') == 422)
        {
            return response($response->get('error_response'), $response->get('error_code'));
        }

        return response($response);
    }

    /**
     * Submit Rejected GIRO Payment Records
     *
     * @param Request $request
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function submitRejectedGIRORecords(Request $request)
    {
        $this->authorize('create', $this->eWalletGIRORejectedPaymentObj);

        request()->validate([
            "country_id" => "required|integer|exists:countries,id",
            "file_url" => "required|url"
        ]);

        $response = $this->obj->submitRejectedPaymentFile($request->all());

        if($response->has('error_code') && $response->get('error_code') == 422)
        {
            return response($response->get('error_response'), $response->get('error_code'));
        }

        return response($response);
    }

    /**
     * Get Rejected Payment Sample File
     *
     * @return \Illuminate\Support\Collection
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function downloadRejectedPaymentSampleFile()
    {
        $this->authorize('download', $this->eWalletGIRORejectedPaymentObj);

        return $this->obj->getRejectedPaymentSampleFile();
    }

    /**
     * Set Rejected Payment Level One Approval
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function rejectedPaymentLevelOneApproval(Request $request)
    {
        $this->authorize('update', $this->eWalletGIRORejectedPaymentObj);

        $request->merge([
            'level_one_by' => Auth::id()
        ]);

        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:user_ewallet_giro_rejected_payments,id',
            'data.*.level_one_status' => 'required|boolean',
            'data.*.level_one_reason' => 'required_if:data.*.level_one_status,false',
            'level_one_by' => 'required|exists:users,id'
        ]);

        return response($this->obj->rejectedPaymentUpdate($request->all()));
    }

    /**
     * Set Rejected Payment Level Two Approval
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function rejectedPaymentLevelTwoApproval(Request $request)
    {
        $this->authorize('update', $this->eWalletGIRORejectedPaymentObj);

        $request->merge([
            'level_two_by' => Auth::id()
        ]);

        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:user_ewallet_giro_rejected_payments,id',
            'data.*.level_two_status' => 'required|boolean',
            'data.*.level_two_reason' => 'required_if:data.*.level_two_status,false',
            'level_two_by' => 'required|exists:users,id'
        ]);

        $response = $this->obj->rejectedPaymentUpdate($request->all(), true);

        if($response->has('error_code') && $response->get('error_code') == 422)
        {
            return response($response->get('error_response'), $response->get('error_code'));
        }

        return response($response);
    }

    /**
     * eWallet Adjustment Listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function eWalletAdjustmentListing(Request $request)
    {
        $this->authorize('listing', $this->eWalletAdjustmentObj);

        request()->validate([
            "country_id" => "required|integer|exists:countries,id",
            "user_id" => "sometimes|integer|exists:users,id",
            "date" => "sometimes|date",
            "level_one_status" => "sometimes|in:-1,1,0|nullable",
            "level_two_status" => "sometimes|in:-1,1,0|nullable"
        ]);

        return response( $this->obj->eWalletAdjustmentListing(
            $request->all(),
            ($request->has('paginate') ? $request->input('paginate') :  0),
            ($request->has('sort') ? $request->input('sort') :  'id'),
            ($request->has('order') ? $request->input('order') : 'desc'),
            ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * eWallet Adjustment Details
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function eWalletAdjustmentDetails(Request $request)
    {
        request()->request->set('adjustment_id', $request->input('id'));

        $this->authorize('view', $this->eWalletAdjustmentObj);

        request()->validate([
            "id" => "required|integer|exists:user_ewallet_adjustments,id",
            "member_data" => "sometimes|boolean"
        ]);

        return response($this->obj->eWalletAdjustmentRecord(
            $request->input('id'),
            $request->has('member_data') ? $request->input('member_data') : false
            ));
    }

    /**
     * eWallet Adjustment Create
     *
     * @param EWalletAdjustmentRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function eWalletAdjustmentCreate(EWalletAdjustmentRequest $request)
    {
        $this->authorize('create', $this->eWalletAdjustmentObj);

        return response($this->obj->eWalletAdjustmentInsert($request->all()));
    }

    /**
     * eWallet Adjustment Level One Approval
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function eWalletAdjustmentLevelOneApproval(Request $request, int $id)
    {
        $model = $this->eWalletAdjustmentObj->find($id);

        $this->authorize('update', [$this->eWalletAdjustmentObj, $model->country_id]);

        $request->merge([
            'level_one_status' => request()->input('level_one_status') ? 1 : 0,
            'level_one_by' => Auth::id()
        ]);

        request()->validate([
            'level_one_status' => 'required|boolean',
            'level_one_reason' => 'required_if:level_one_status,0',
            'level_one_by' => 'required|integer|exists:users,id'
        ]);

        return response($this->obj->eWalletAdjustmentUpdate(
            $id,
            $request->all()
        ));
    }

    /**
     * eWallet Adjustment Level Two Approval
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function eWalletAdjustmentLevelTwoApproval(Request $request, int $id)
    {
        $model = $this->eWalletAdjustmentObj->find($id);

        $this->authorize('update', [$this->eWalletAdjustmentObj, $model->country_id]);

        $request->merge([
            'level_two_status' => request()->input('level_two_status') ? 1 : 0,
            'level_two_by' => Auth::id()
        ]);

        request()->validate([
            'level_two_status' => 'required|boolean',
            'level_two_reason' => 'required_if:level_two_status,false',
            'level_two_by' => 'required|integer|exists:users,id'
        ]);

        $response = $this->obj->eWalletAdjustmentUpdate(
            $id,
            $request->all(),
            true
        );

        if($response->has('error_code') && $response->get('error_code') == 422)
        {
            return response($response->get('error_response'), $response->get('error_code'));
        }

        return response($response);
    }
}
