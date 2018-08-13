<?php
namespace App\Http\Controllers\V1\Stockists;

use App\{
    Helpers\Traits\AccessControl,
    Interfaces\Masters\MasterInterface,
    Interfaces\Stockists\StockistInterface,
    Http\Controllers\Controller,
    Http\Requests\Stockists\StockistRequest,
    Http\Requests\Stockists\ConsignmentOrderReturnCreateRequest,
    Models\Stockists\ConsignmentDepositRefund,
    Models\Stockists\ConsignmentOrderReturn,
    Models\Stockists\Stockist,
    Models\Stockists\StockistSalePayment,
    Rules\Stockists\ConsignmentDepositRefundAmount,
    Rules\Stockists\ConsignmentDepositReturnUpdateValidate,
    Rules\Stockists\ValidateGenerateConsignmentRefund,
    Rules\Stockists\StockistOutstandingAdjustmentAmount
};
use Illuminate\Http\Request;

class StockistController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * StockistController constructor.
     *
     * @param StockistInterface $stockist
     * @param Stockist $model
     */
    public function __construct(StockistInterface $stockist, Stockist $model)
    {
        $this->middleware('auth');

        $this->obj = $stockist;

        $this->authorizedModel = $model;
    }

    /**
     * get stockist filtered by a given vars
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterStockists(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->authorizedModel);

        return response(
            $this->obj->getStockistsByFilters(
                $request->input('country_id'),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('stockist_type_id') ? $request->input('stockist_type_id') : 0),
                ($request->has('stockist_status_id') ? $request->input('stockist_status_id') : 0),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * create or update stockist records
     *
     * @param StockistRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdateStockist(StockistRequest $request)
    {
        //check if the action is create then apply create permission-----
        if (!$request->has('stockist_data.details.id')){
            $this->authorize('create', [$this->authorizedModel]);
        }else{
            $this->authorize('update', [$this->authorizedModel]);
        }

        return response($this->obj->createOrUpdateStockist($request->all()));
    }

    /**
     * get stockist details for a given stockistId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function stockistDetails(Request $request)
    {
        request()->validate([
            'stockist_user_id' => 'required|integer|exists:stockists,stockist_user_id'
        ]);

        //check if user has access to the resource
        $this->applySearchOrViewPermission($this->authorizedModel
            ->where('stockist_user_id', $request->input('stockist_user_id'))
            ->first());

        return response($this->obj->stockistDetails(
            ($request->input('stockist_user_id'))
        ));
    }

    /**
     * get consignment deposit refund setting by giving stockist user id
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function consignmentDepositRefundValidate(Request $request)
    {
        request()->validate([
            'stockist_user_id' => 'required|integer|exists:stockists,stockist_user_id',
            'type' => 'required|string|in:deposit,refund'
        ]);

        //TODO implement rnp

        return response($this->obj->validatesConsignmentDepositsRefunds(
            ($request->input('stockist_user_id')),
            ($request->input('type'))
        ));
    }

    /**
     * get consignment deposit refund filtered by a given vars
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterConsignmentDepositRefund(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'to_date' => 'sometimes|nullable|date|before_or_equal:today',
            'from_date' => 'sometimes|nullable|date|before_or_equal:to_date'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission(ConsignmentDepositRefund::class);

        return response(
            $this->obj->getConsignmentDepositRefundByFilters(
                $request->input('country_id'),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('from_date') ? $request->input('from_date') : ''),
                ($request->has('to_date') ? $request->input('to_date') : ''),
                ($request->has('type_id') ? $request->input('type_id') : 0),
                ($request->has('status_id') ? $request->input('status_id') : 0),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * get consignment deposit refund details for a given consignmentDepositRefundId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function consignmentDepositRefundDetails(Request $request)
    {
        request()->validate([
            'id' => 'required|integer|exists:consignments_deposits_refunds,id'
        ]);

        //check if user has access to the resource
        $this->authorize('view', ConsignmentDepositRefund::find($request->input('id')));

        return response($this->obj->consignmentDepositsRefundsDetails(
            ($request->input('id'))
        ));
    }

    /**
     * create consignment deposit records
     *
     * @param StockistInterface $stockistInterface
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createConsignmentDeposit
    (
        StockistInterface $stockistInterface,
        Request $request
    )
    {
        request()->validate([
            'consignment_deposit_refund.stockist_id' => 'required|integer|exists:stockists,id',
            'consignment_deposit_refund.stockist_user_id' => 'required|integer|exists:stockists,stockist_user_id',
            'consignment_deposit_refund.amount' => [
                'required',
                'numeric',
                'regex:/^\d*(\.\d{1,2})?$/',
                new ConsignmentDepositRefundAmount(
                    $stockistInterface, $request->input('consignment_deposit_refund.stockist_user_id'), 'deposit')
            ]
        ]);

        //check if user has access to the resource
        $this->authorize('create', [
            ConsignmentDepositRefund::class,
            $request->input('consignment_deposit_refund.stockist_id')
        ]);

        return response($this->obj->createConsignmentDeposit($request->all()));
    }

    /**
     * create consignment refund records
     *
     * @param StockistInterface $stockistInterface
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createConsignmentRefund
    (
        StockistInterface $stockistInterface,
        Request $request
    )
    {
        request()->validate([
            'consignment_deposit_refund.stockist_id' => [
                'required',
                'integer',
                'exists:stockists,id',
                new ValidateGenerateConsignmentRefund($stockistInterface)
            ],
            'consignment_deposit_refund.stockist_user_id' => 'required|integer|exists:stockists,stockist_user_id',
            'consignment_deposit_refund.amount' => [
                'required',
                'numeric',
                'regex:/^\d*(\.\d{1,2})?$/',
                new ConsignmentDepositRefundAmount(
                    $stockistInterface, $request->input('consignment_deposit_refund.stockist_user_id'), 'refund')
            ]
        ]);

        //check if user has access to the resource
        $this->authorize('create', [
            ConsignmentDepositRefund::class,
            $request->input('consignment_deposit_refund.stockist_id')
        ]);

        return response($this->obj->createConsignmentRefund($request->all()));
    }

    /**
     * update consignment deposit return records
     *
     * @param Request $request
     * @param MasterInterface $masterInterface
     * @param ConsignmentDepositRefund $consignmentDepositRefund
     * @param int $consignmentDepositReturnId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateConsignmentDepositReturn
    (
        Request $request,
        MasterInterface $masterInterface,
        ConsignmentDepositRefund $consignmentDepositRefund,
        int $consignmentDepositReturnId
    )
    {
        request()->validate([
            'consignment_deposit_refund.update_type' => 'required|string|in:cancel_deposit',
            'consignment_deposit_refund.id' => [
                'required',
                'integer',
                'exists:consignments_orders_returns,id',
                new ConsignmentDepositReturnUpdateValidate(
                    $masterInterface, $consignmentDepositRefund, $consignmentDepositReturnId,
                        $request->input('consignment_deposit_refund.update_type'))
            ]
        ]);

        $this->authorize('update', ConsignmentDepositRefund::find($consignmentDepositReturnId));

        return response($this->obj->updateConsignmentDepositReturn($request->all(), $consignmentDepositReturnId));
    }

    /**
     * validates no pending consignment return before create new return by giving stockist user id
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function consignmentReturnValidate(Request $request)
    {
        request()->validate([
            'stockist_user_id' => 'required|integer|exists:stockists,stockist_user_id'
        ]);

        //TODO implement rnp

        return response($this->obj->validatesConsignmentReturn(
            ($request->input('stockist_user_id'))
        ));
    }

    /**
     * get consignment order return filtered by a given vars
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getConsignmentOrderReturnByFilters(Request $request)
    {
        request()->validate([
            'type' => 'required|string|in:order,return',
            'country_id' => 'required|integer|exists:countries,id',
            'to_date' => 'sometimes|nullable|date|before_or_equal:today',
            'from_date' => 'sometimes|nullable|date|before_or_equal:to_date'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission(ConsignmentOrderReturn::class);

        return response(
            $this->obj->getConsignmentOrderReturnByFilters(
                $request->input('type'),
                $request->input('country_id'),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('from_date') ? $request->input('from_date') : ''),
                ($request->has('to_date') ? $request->input('to_date') : ''),
                ($request->has('status_id') ? $request->input('status_id') : 0),
                ($request->has('warehouse_receiving_id') ? $request->input('warehouse_receiving_id') : 0),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * get consignment order return details for a given consignmentDepositRefundId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function consignmentOrderReturnDetails(Request $request)
    {
        request()->validate([
            'id' => 'required|integer|exists:consignments_orders_returns,id'
        ]);

        //check if user has access to the resource
        $this->authorize('view', ConsignmentOrderReturn::find($request->input('id')));

        return response($this->obj->consignmentOrderReturnDetails(
            ($request->input('id'))
        ));
    }

    /**
     * create consignment return records
     *
     * @param ConsignmentOrderReturnCreateRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createConsignmentOrderReturn(ConsignmentOrderReturnCreateRequest $request)
    {
        $this->authorize('create', ConsignmentOrderReturn::class);

        return response($this->obj->createConsignmentOrderReturn($request->all()));
    }

    /**
     * validates total product quantity that can be return by giving stockistId and productId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function consignmentReturnProductValidate(Request $request)
    {
        request()->validate([
            'stockist_id' => 'required|integer|exists:stockists,id',
            'product_id' => 'required|integer|exists:products,id'
        ]);

        return response($this->obj->validatesConsignmentReturnProduct(
            ($request->input('stockist_id')),
            ($request->input('product_id'))
        ));
    }

    /**
     * download consignment note for a given invoiceId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Mpdf\MpdfException
     */
    public function downloadConsignmentNote(Request $request)
    {
        request()->validate([
            'consignment_order_return_id' => 'required|integer|exists:consignments_orders_returns,id'
        ]);

        //check if you are authorized to download the Note
        $this->authorize('downloadConsignmentNote', ConsignmentOrderReturn::find($request->input('consignment_order_return_id')));

        return response($this->obj->downloadConsignmentNote(
            $request->input('consignment_order_return_id')
        ));
    }

    /**
     * get stockist sales daily payment verification list
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSalesDailyPaymentVerificationLists(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'to_date' => 'sometimes|nullable|date|before_or_equal:yesterday',
            'from_date' => 'sometimes|nullable|date|before_or_equal:to_date',
            'selected_stockist_ids.*' => 'sometimes|integer|exists:stockists,id'
        ]);

        //TODO check is part
        //apply rnp for daily payment verification
        //$this->authorize('list', StockistSalePayment::class);

        return response(
            $this->obj->getSalesDailyPaymentVerificationLists(
                $request->input('country_id'),
                ($request->has('from_date') ? $request->input('from_date') : ''),
                ($request->has('to_date') ? $request->input('to_date') : ''),
                ($request->has('exclude_zero_balance') ? $request->input('exclude_zero_balance') : false),
                ($request->has('selected_stockist_ids') ? $request->input('selected_stockist_ids') : [])
            )
        );
    }

    /**
     * batch update stockist outstanding and ar payment balance
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function batchUpdateStockistOutstandingPayment(Request $request)
    {
        request()->validate([
            'stockists_sales_payments' => [
                'required',
                'array',
                new StockistOutstandingAdjustmentAmount()
            ],
            'stockists_sales_payments.*.id' => 'required|integer|exists:stockists_sales_payments,id',
            'stockists_sales_payments.*.pay_amount' => 'required|regex:/^\d*(\.\d{1,2})?$/', // for 2 decimals or without decimals,
            'stockists_sales_payments.*.adjustment_amount' => 'required|regex:/^-?\d*(\.\d{1,2})?$/', // for 2 decimals or without decimals,
        ]);

        return response($this->obj->batchUpdateStockistOutstandingPayment($request->all()));
    }

     /**
     * get stockist outstanding summary by a given vars
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getStockistOutstandingSummary(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id'
        ]);

        return response(
            $this->obj->getStockistOutstandingSummary(
                $request->input('country_id'),
                ($request->has('exclude_zero_balance') ? $request->input('exclude_zero_balance') : true),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * Download Stockist Daily Collection Report
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadDailyCollectionReport(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'location_ids' => 'array',
            'location_ids.*.location_id' => 'integer|exists:locations,id',
            'collection_from_date' => 'required|date|before_or_equal:collection_to_date',
            'collection_to_date' => 'required|date|before_or_equal:today',
            'user_id' => 'integer|exists:users,id'
        ],[],
        [
            'country_id' => 'country',
            'collection_from_date' => 'payment collection date from',
            'collection_to_date' => 'payment collection date to',
            'user_ids' => 'user',
        ]);

        if (empty($request->input('location_ids')))
        {
            $locationIds = [];
        }
        else
        {
            $locationIds = $request->input('location_ids');
        }

        if (empty($request->input('user_id')))
        {
            $userId = 0;
        }
        else
        {
            $userId = $request->input('user_id');
        }

        return response(
            $this->obj->downloadDailyCollectionReport(
                $request->input('country_id'),
                $locationIds,
                $request->input('collection_from_date'),
                $request->input('collection_to_date'),
                $userId
            )
        );
    }

    /**
     * Download Consignment Deposit Receipt Note
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadDepositReceipt(Request $request)
    {
        request()->validate([
            'consignment_deposit_refund_id' => 'required|integer|exists:consignments_deposits_refunds,id'
        ],[],
        [
            'consignment_deposit_refund_id' => 'consignment deposit receipt'
        ]);

        return response(
            $this->obj->downloadDepositReceipt(
                $request->input('consignment_deposit_refund_id')
            )
        );
    }

    /**
     * Download Stockist Consignment Stock Report in Excel format
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadStockistConsignmentStockReport(Request $request)
    {
        request()->validate([
            'stockist_id' => 'required|integer|exists:stockists_consignments_products,stockist_id'
        ],[],
        [
            'stockist_id' => 'Stockist'
        ]);

        return response(
            $this->obj->downloadConsignmentProduct(
                $request->input('stockist_id')
            )
        );
    }
}
