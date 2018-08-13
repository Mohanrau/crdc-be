<?php
namespace App\Http\Controllers\V1\Payments;

use App\{
    Interfaces\Payments\PaymentInterface,
    Interfaces\Masters\MasterInterface,
    Models\EWallets\EWallet,
    Models\Stockists\ConsignmentDepositRefund,
    Models\Payments\PaymentModeProvider,
    Models\Payments\PaymentModeSetting,
    Models\Payments\Payment,
    Models\Sales\Sale,
    Models\Sales\SaleExchange,
    Models\Users\User,
    Http\Controllers\Controller,
    Rules\Payments\AeonPaymentReleaseCheck,
    Rules\Payments\MakeFullSalePaymentValidation,
    Rules\Payments\MakePaymentValidation,
    Rules\Payments\PaymentInputFieldValidation,
    Rules\Payments\EWalletPaymentValidation,
    Rules\Payments\SinglePaymentValidation,
    Rules\Payments\EppMotoUpdateApproveCodeValidation,
    Rules\Payments\EppPaymentSaleCovertValidate,
    Rules\Payments\AeonUpdateAgreementNumberValidation,
    Rules\Payments\UpdateAeonAgreementNumberValidateApproveAmount,
    Rules\Payments\PaymentBatchCancelValidator,
    Rules\Payments\CreditCardNumberValidation,
    Rules\Payments\EppEligibilityAmountValidation,
    Rules\Payments\SharePaymentDetailValidation
};
use Illuminate\Http\Request;
use Validator;

class PaymentController extends Controller
{
    private $obj;

    /**
     * ProductController constructor.
     *
     * @param PaymentInterface $paymentRepository
     */
    public function __construct(PaymentInterface $paymentRepository)
    {
        $this->middleware('auth')->except(array('processCallback', 'redirectPayments', 'createPaymentExternal'));

        $this->obj = $paymentRepository;
    }

    /**
     * Get Sale Supported payment mode by location id and country id
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSupportedSalePayments(Request $request)
    {
        request()->validate([
            'location_id' => 'required|integer|exists:locations,id',
            'country_id' => 'required|integer|exists:countries,id',
            'exclude_payment_mode' => 'array',
            'exclude_payment_provider' => 'array'
        ]);

        return response($this->obj->getSupportedPayments(
            $request->input('country_id'),
            $request->input('location_id'),
            ($request->has('exclude_payment_mode') ? $request->input('exclude_payment_mode') : []),
            ($request->has('exclude_payment_provider') ? $request->input('exclude_payment_provider') : [])
        ));
    }

    /**
     * Make Payment
     *
     * @param MasterInterface $masterRepository
     * @param PaymentInterface $paymentRepository
     * @param Sale $sale
     * @param SaleExchange $saleExchange
     * @param ConsignmentDepositRefund $consignmentDepositRefund
     * @param PaymentModeProvider $paymentModeProvider
     * @param PaymentModeSetting $paymentModeSetting
     * @param EWallet $eWallet
     * @param Payment $payment
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function makePayment(
        MasterInterface $masterRepository,
        PaymentInterface $paymentRepository,
        Sale $sale,
        SaleExchange $saleExchange,
        ConsignmentDepositRefund $consignmentDepositRefund,
        PaymentModeProvider $paymentModeProvider,
        PaymentModeSetting $paymentModeSetting,
        EWallet $eWallet,
        Payment $payment,
        User $user,
        Request $request
    )
    {
        request()->validate([
            'pay_type' => 'required|string|in:sales,consignment_deposit,user_ewallets',
            'is_share' => 'boolean',
            'fields' => 'required',
            'sale_id' => [
                'required_if:pay_type,==,sales',
                'integer',
                'exists:sales,id',
                new MakeFullSalePaymentValidation(
                    $sale, $payment, $request->input('fields'), $request->input('pay_type')),
                new MakePaymentValidation(
                    $consignmentDepositRefund, $sale, $saleExchange, $payment,
                        $request->input('fields'), $request->input('pay_type'))
            ],
            'consignment_deposit_id' => [
                'required_if:pay_type,==,consignment_deposit',
                'integer',
                'exists:consignments_deposits_refunds,id',
                new MakePaymentValidation(
                    $consignmentDepositRefund, $sale, $saleExchange, $payment,
                        $request->input('fields'), $request->input('pay_type'))
            ],
            'ewallet_id' => 'required_if:pay_type,==,user_ewallets|
                integer|exists:user_ewallets,id',
            'payment_mode_id' => [
                'required',
                'integer',
                'exists:payments_modes_settings,id',
                new PaymentInputFieldValidation(
                    $paymentRepository,
                    $paymentModeSetting,
                    $request->input('fields')
                ),
                new EWalletPaymentValidation(
                    $masterRepository,
                    $paymentModeProvider,
                    $paymentModeSetting,
                    $user,
                    $eWallet,
                    $request->input('fields')
                ),
                new CreditCardNumberValidation(
                    $request->input('fields')
                ),
                new EppEligibilityAmountValidation(
                    $masterRepository,
                    $paymentModeProvider,
                    $paymentModeSetting,
                    $request->input('fields')
                )
            ],
            'single_payment' => [
                'nullable',
                'boolean',
                new SinglePaymentValidation(
                    $payment, $request->input('pay_type'))
             ]
        ]);

        return response($this->obj->makePayment(
            $request->input('pay_type'),
            $request->input('payment_mode_id'),
            $request->input('fields'),
            ($request->has('sale_id') ? $request->input('sale_id') : 0),
            ($request->has('consignment_deposit_id') ? $request->input('consignment_deposit_id') : 0),
            ($request->has('ewallet_id') ? $request->input('ewallet_id') : 0),
            ($request->has('is_share') ? $request->input('is_share') : false)
        ));
    }

    /**
     * Get Third Party Share Payment Post Data
     *
     * @param MasterInterface $masterRepository
     * @param PaymentModeProvider $paymentModeProvider
     * @param Payment $payment
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function sharePaymentDetail
    (
        MasterInterface $masterRepository,
        PaymentModeProvider $paymentModeProvider,
        Payment $payment,
        Request $request
    )
    {
        request()->validate([
            'payment_id' => [
                'required',
                'integer',
                'exists:payments,id',
                new SharePaymentDetailValidation(
                    $masterRepository, $paymentModeProvider, $payment)
            ]
        ]);

        return response($this->obj->sharePaymentDetail($request->input('payment_id')));
    }

    /**
     * To check what's the status of the payment, 0 = fail, 1 = passed, 2 = awaiting response
     * @param $paymentId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPaymentStatus($paymentId)
    {
        Validator::make(
            [
                'payment_id' => $paymentId
            ],
            [
                'payment_id' => "required|integer|exists:payments,id"
            ]
        )->validate();

        return response(['status' => $this->obj->getPaymentStatus($paymentId)]);
    }


    /**
     * To process callback from the automatic payment system (payment gateway)
     *
     * @param $salePaymentId
     * @param $isBackendCall
     * @param Request $request
     */
    public function processCallback($salePaymentId, $isBackendCall = false, Request $request)
    {
        //first thing to validate is to load the sales payment record exists
        $data = ['sale_payment_id' => $salePaymentId];
        $validator = Validator::make($data, [
            'sale_payment_id' => 'integer|exists:payments,id'
        ]);
        $validator->validate();

        //second level validation should be in the respective payment class itself
        echo $this->obj->processCallback($salePaymentId, $isBackendCall, $request);
    }

    /**
     * get epp payment method listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function eppPaymentListing(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'to_date' => 'sometimes|nullable|date|before_or_equal:today',
            'from_date' => 'sometimes|nullable|date|before_or_equal:to_date'
        ]);

        return response(
            $this->obj->eppPaymentListing(
                $request->input('country_id'),
                ($request->has('from_date') ? $request->input('from_date') : ''),
                ($request->has('to_date') ? $request->input('to_date') : ''),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('location_type_id') ? $request->input('location_type_id') : 0),
                ($request->has('epp_mode_id') ? $request->input('epp_mode_id') : 0),
                ($request->has('approval_status_id') ? $request->input('approval_status_id') : 0),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    //TODO implement RNP
    /**
     * update epp moto approve code
     *
     * @param MasterInterface $masterInterface
     * @param Payment $payment
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function updateEppMotoApproveCode
    (
        MasterInterface $masterInterface,
        Payment $payment,
        Request $request
    )
    {
        request()->validate([
            'payment_id' => [
                'required',
                'integer',
                'exists:payments,id',
                new EppMotoUpdateApproveCodeValidation($masterInterface, $payment)
            ],
            'approve_code' => 'required'
        ]);

        return response(
            $this->obj->updateEppMotoApproveCode(
                $request->input('payment_id'),
                $request->input('approve_code')
            )
        );
    }

    /**
     * batch covert epp payment to valid sales order
     *
     * @param MasterInterface $masterInterface
     * @param Payment $payment
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function eppPaymentSaleConvert(
        MasterInterface $masterInterface,
        Payment $payment,
        Request $request
    )
    {
        request()->validate([
            'sales_payments_ids' => 'required|array',
            'sales_payments_ids.*' => [
                'required', 'integer', 'exists:payments,id',
                new EppPaymentSaleCovertValidate($masterInterface, $payment)
            ],
        ]);

        return response(
            $this->obj->eppPaymentSaleConvert(
                $request->input('sales_payments_ids')
            )
        );
    }

    /**
     * get aeon payment method listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function aeonPaymentListing(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'to_date' => 'sometimes|nullable|date|before_or_equal:today',
            'from_date' => 'sometimes|nullable|date|before_or_equal:to_date'
        ]);

        return response(
            $this->obj->aeonPaymentListing(
                $request->input('country_id'),
                ($request->has('from_date') ? $request->input('from_date') : ''),
                ($request->has('to_date') ? $request->input('to_date') : ''),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('location_type_id') ? $request->input('location_type_id') : 0),
                ($request->has('approval_status_id') ? $request->input('approval_status_id') : 0),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * This is used to do redirect a form for a payment
     *
     * @param $salePaymentId
     * @param Request $request
     */
    public function redirectPayments($salePaymentId, Request $request)
    {
        //validate salePaymentId
        $data = ['sale_payment_id' => $salePaymentId];
        $validator = Validator::make($data, [
            'sale_payment_id' => 'integer|exists:payments,id'
        ]);
        $validator->validate();

        echo $this->obj->redirectPayments($salePaymentId, $request);
    }

    //TODO implement RNP
    /**
     * update aeon application agreement number
     *
     * @param MasterInterface $masterInterface
     * @param Payment $payment
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAeonAgreementNumber
    (
        MasterInterface $masterInterface,
        Payment $payment,
        Request $request
    )
    {
        request()->validate([
            'payment_id' => [
                'required',
                'integer',
                'exists:payments,id',
                new AeonUpdateAgreementNumberValidation($masterInterface, $payment)
            ],
            'agreement_number' => 'required',
            'approved_amount' =>  [
                'required',
                'regex:/^-?\d*(\.\d{1,2})?$/', // for 2 decimals or without decimals
                new UpdateAeonAgreementNumberValidateApproveAmount(
                    $payment, $request->input('payment_id'))
            ],
        ]);

        return response(
            $this->obj->updateAeonAgreementNumber(
                $request->input('payment_id'),
                $request->input('agreement_number'),
                $request->input('approved_amount')
            )
        );
    }

    /**
     * aeon payment cooling off release update
     *
     * @param MasterInterface $masterInterface
     * @param Payment $payment
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function aeonPaymentCoolingOffRelease(
        MasterInterface $masterInterface,
        Payment $payment,
        Request $request
    )
    {
        request()->validate([
            'sales_payments_ids' => 'required|array',
            'sales_payments_ids.*' => [
                'required', 'integer', 'exists:payments,id',
                new AeonPaymentReleaseCheck($masterInterface, $payment)
            ],
        ]);

        return response(
            $this->obj->aeonPaymentCoolingOffRelease(
                $request->input('sales_payments_ids')
            )
        );
    }

    //TODO implement RNP
    /**
     * payment batch cancel
     *
     * @param MasterInterface $masterInterface
     * @param Payment $payment
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function paymentBatchCancel(
        MasterInterface $masterInterface,
        Payment $payment,
        Request $request
    )
    {
        request()->validate([
            'payment_mode' => 'required|string|in:epp_moto,aeon',
            'sales_payments_ids' => 'required|array',
            'sales_payments_ids.*' => [
                'required', 'integer', 'exists:payments,id',
                new PaymentBatchCancelValidator($masterInterface, $payment, $request->input('payment_mode'))
            ],
        ]);

        return response(
            $this->obj->paymentBatchCancel(
                $request->input('payment_mode'),
                $request->input('sales_payments_ids')
            )
        );
    }

    /**
     * create payment (external)
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function createPaymentExternal(Request $request)
    {
        request()->validate([
            "sale_id" => "required|integer|exists:sales,id",
            "payment_mode" => "required|in:cash,ipay88",
            "currency" => "required|exists:currencies,code",
            "amount" => "required|regex:/^\d*(\.\d{1,2})?$/"
        ]);

        return response( $this->obj->externalPayment( $request->all() ) );
    }

    /**
     * get payment document requirement details
     *
     * @param Request $request
     * @return mixed
     */
    public function paymentDocumentDetails(Request $request)
    {
        request()->validate([
            'country_id' => "required|integer|exists:countries,id",
            'payment_mode_provider_id' => 'required|integer|exists:payments_modes_providers,id',
        ]);

        return $this->obj->getPaymentModeDocumentDetails($request->input('country_id'), $request->input('payment_mode_provider_id'));
    }
}
