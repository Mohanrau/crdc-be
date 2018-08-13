<?php
namespace App\Http\Controllers\V1\Sales;

use App\Http\Requests\Sales\{
    SaleCancellationRequest,
    LegacySaleCancellationRequest
};
use App\Models\{
    Invoices\Invoice,
    Sales\SaleCancellation
};
use App\{
    Helpers\Traits\AccessControl,
    Http\Controllers\Controller,
    Interfaces\Sales\SaleInterface,
    Interfaces\Masters\MasterInterface,
    Rules\Invoices\InvoiceUserCheck,
    Rules\Sales\SalesCancellationsInvoiceCheck,
    Rules\Sales\SalesCancellationsInvoiceProductValidation,
    Rules\Sales\SaleCancellationBathRefund
};
use Illuminate\{
    Http\Request
};

class SaleCancellationController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * SaleCancellationController constructor.
     *
     * @param SaleInterface $saleInterface
     * @param SaleCancellation $model
     */
    public function __construct(SaleInterface $saleInterface, SaleCancellation $model)
    {
        $this->middleware('auth');

        $this->obj = $saleInterface;

        $this->authorizedModel = $model;
    }

    /**
     * get sales cancellation invoice details by given invoiceId
     *
     * @param Invoice $invoice
     * @param MasterInterface $masterInterface
     * @param SaleInterface $saleInterface
     * @param SaleCancellation $saleCancellation
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSalesCancellationInvoiceDetails(
        Invoice $invoice,
        MasterInterface $masterInterface,
        SaleInterface $saleInterface,
        SaleCancellation $saleCancellation,
        Request $request
    )
    {
        request()->validate([
            'invoice_id' => [
                'required_if:method,normal', 'integer','exists:invoices,id',
                new InvoiceUserCheck($invoice, $request->input('user_id')),
                new SalesCancellationsInvoiceCheck($masterInterface, $saleCancellation),
                new SalesCancellationsInvoiceProductValidation(
                    $saleInterface,
                    $request->input('user_id'),
                    ($request->has('method') ? $request->input('method') : 'normal')
                )
            ],
            'country_id' => 'required_if:method,legacy|integer|exists:countries,id',
            'user_id' => 'required|integer|exists:members,user_id',
            'method' => 'required|string|in:normal,legacy'
        ]);

        return response($this->obj->getSalesCancellationInvoiceDetails(
            $request->input('method'),
            $request->input('user_id'),
            ($request->has('invoice_id') ? $request->input('invoice_id') :  0),
            ($request->has('country_id') ? $request->input('country_id') :  0)
        ));
    }

    /**
     * get sales cancellation listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function filterSalesCancellation(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'to_date' => 'sometimes|nullable|date|before_or_equal:today',
            'from_date' => 'sometimes|nullable|date|before_or_equal:to_date'
        ]);

        //check the authorization
        $this->authorize('listing', [$this->authorizedModel]);

        //check if user is stockist or back office has access to locations.list
        $this->locationListingAccessCheck();

        return response(
            $this->obj->getSalesCancellationByFilters(
                $request->input('country_id'),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('from_date') ? $request->input('from_date') : ''),
                ($request->has('to_date') ? $request->input('to_date') : ''),
                ($request->has('sale_cancellation_status_id') ? $request->input('sale_cancellation_status_id') : 0),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * get sales cancellation details for a given salesCancellationId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function saleCancellationDetails(Request $request)
    {
        request()->validate([
            'sales_cancellation_id' => 'required|integer|exists:sales_cancellations,id'
        ]);

        //check the authorization
        $this->authorize('view', [$this->authorizedModel]);

        $saleCancellation = $this->authorizedModel->find($request->input('sales_cancellation_id'));

        //check if user backOffice or stockist and can view this sale cancellation details
        $this->resourceLocationAccessCheck($saleCancellation->transaction_location_id, $saleCancellation->user_id);

        return response($this->obj->saleCancellationDetail($request->input('sales_cancellation_id')));
    }

    /**
     * create new sales cancellation records
     *
     * @param SaleCancellationRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(SaleCancellationRequest $request)
    {
        $this->authorize('create', [$this->authorizedModel]);

        return response($this->obj->createSalesCancellation($request->all()));
    }

    /**
     * create new legacy sales cancellation records
     *
     * @param LegacySaleCancellationRequest $request
     * @return mixed
     */
    public function createLegacySalesCancellation(LegacySaleCancellationRequest $request)
    {
        return response($this->obj->createLegacySalesCancellation($request->all()));
    }

    /**
     * @param MasterInterface $masterInterface
     * @param SaleCancellation $saleCancellation
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function salesCancellationBatchRefund(
        MasterInterface $masterInterface,
        SaleCancellation $saleCancellation,
        Request $request
    )
    {
        request()->validate([
            'sales_cancellation_ids' => 'required|array',
            'sales_cancellation_ids.*' => [
                'required',
                'integer',
                'exists:sales_cancellations,id',
                new SaleCancellationBathRefund($masterInterface, $saleCancellation)
            ]
        ]);

        return response(
            $this->obj->salesCancellationBatchRefund(
                $request->input('sales_cancellation_ids'),
                ($request->has('remark') ? $request->input('remark') : '')
            )
        );
    }
}
