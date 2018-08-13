<?php
namespace App\Http\Controllers\V1\Invoices;

use App\{
    Helpers\Traits\AccessControl,
    Interfaces\Invoices\InvoiceInterface,
    Interfaces\Payments\PaymentInterface,
    Http\Controllers\Controller,
    Models\Invoices\Invoice,
    Models\Sales\Sale,
    Rules\Invoices\LegacyInvoiceCheck,
    Http\Requests\Invoice\TaxInvoiceReportRequest
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel,
        $salesAuthorizedModel
    ;

    /**
     * InvoiceController constructor.
     *
     * @param InvoiceInterface $invoiceInterface
     * @param Invoice $invoice
     * @param Sale $sale
     */
    public function __construct(InvoiceInterface $invoiceInterface, Invoice $invoice, Sale $sale)
    {
        $this->middleware('auth');

        $this->obj = $invoiceInterface;

        $this->authorizedModel = $invoice;

        $this->salesAuthorizedModel = $sale;
    }

    /**
     * get invoice listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function filterInvoices(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'user_id' => 'integer|exists:members,user_id',
            'to_date' => 'sometimes|nullable|date|before_or_equal:today',
            'from_date' => 'sometimes|nullable|date|before_or_equal:to_date'
        ]);

        //check the authorization for sales listing
        $this->authorize('listing', [$this->salesAuthorizedModel]);

        //check if user is stockist or back office has access to locations.list
        $this->locationListingAccessCheck();

        return response(
            $this->obj->getInvoicesByFilters(
                $request->input('country_id'),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('from_date') ? $request->input('from_date') : ''),
                ($request->has('to_date') ? $request->input('to_date') : ''),
                ($request->has('user_id') ? $request->input('user_id') : NULL),
                ($request->has('is_sale_cancellation') ? $request->input('is_sale_cancellation') :  false),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     *  get invoice details for a given invoiceId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function invoiceDetails(Request $request)
    {
        request()->validate([
            'invoice_id' => 'required|integer|exists:invoices,id'
        ]);

        $sale = $this->obj->find($request->input('invoice_id'))->sale()->first();

        //check if user authorized to download invoice
        $this->authorize('salesViewByInvoice', $sale);

        return response($this->obj->invoiceDetails(
            $request->input('invoice_id')
        ));
    }

    /**
     * download invoice for a given invoiceId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Mpdf\MpdfException
     */
    public function downloadInvoice(Request $request)
    {
        $isLegacy = $request->input('is_legacy')? $request->input('is_legacy') : false;
        
        request()->validate([
            'invoice_id' => ['required', 'integer', new LegacyInvoiceCheck($isLegacy)]
        ]);

        //check if you are authorized to download the invoice
        $this->authorize('downloadInvoice', [$this->authorizedModel]);

        return response($this->obj->downloadPDF(
            $request->input('invoice_id'),
            $isLegacy
        ));
    }

    /**
     * get stockist daily invoice transaction list
     *
     * @param PaymentInterface $paymentInterface
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getStockistDailyInvoiceTransactionList(
        PaymentInterface $paymentInterface,
        Request $request
    )
    {
        request()->validate([
            'stockist_number' => 'required|integer|exists:stockists,stockist_number',
            'filter_date' => 'required|date|before_or_equal:yesterday'
        ]);

        //RNP check if user has the permission to access this api
        $this->authorize('stockistDailyTransactionListing', $this->authorizedModel);

        if ($this->isUser('stockist')) {
            if ($this->getStockistUser('stockist_number') != $request->input('stockist_number')){
                abort(403, trans('message.user.resource_not_belong'));
            }
        }

        return response(
            $this->obj->getStockistDailyInvoiceTransactionList(
                $paymentInterface,
                $request->input('stockist_number'),
                $request->input('filter_date'),
                ($request->has('stockist_daily_transaction_status_id') ?
                    $request->input('stockist_daily_transaction_status_id') : 0)
            )
        );
    }

    /**
     * batch release stockist invoice transaction
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function batchReleaseStockistDailyInvoiceTransaction(Request $request)
    {
        request()->validate([
            'stockist_invoices' => 'required|array',
            'stockist_invoices.*' => 'required|integer|exists:invoices,id'
        ]);

        $this->authorize('stockistDailyTransactionUpdate', $this->authorizedModel);

        return response(
            $this->obj->batchReleaseStockistDailyInvoiceTransaction($request->input('stockist_invoices'))
        );
    }

    /**
     * download Auto maintenance invoice for a given sale ID
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadAutoMaintenanceInvoice(Request $request)
    {
        $isLegacy = $request->input('is_legacy')? $request->input('is_legacy') : false;

        request()->validate([
            'invoice_id' => ['required', 'integer', new LegacyInvoiceCheck($isLegacy)]
        ]);

        return response($this->obj->downloadAutoMaintenanceInvoice(
            $request->input('invoice_id'),
            $isLegacy
        ));
    }

    /**
     * download tax invoice summary report in excel format
     *
     * @param TaxInvoiceReportRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function taxInvoiceSummaryReport(TaxInvoiceReportRequest $request)
    {
        $locationIds = empty($request->input('location_ids'))? [] : $request->input('location_ids');

        $iboIds = empty($request->input('ibo_ids'))? [] : $request->input('ibo_ids');

        $status = $request->has('status') ? $request->input('status') : 0;

        return response($this->obj->downloadTaxInvoiceSummaryReport(
            $request->input('country_id'),
            $locationIds,
            $request->input('from_date'),
            $request->input('to_date'),
            $request->input('from_cw'),
            $request->input('to_cw'),
            $iboIds,
            $status
        ));
    }

    /**
     * download tax invoice product details report in excel format 
     *
     * @param TaxInvoiceReportRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function taxInvoiceDetailsReport(TaxInvoiceReportRequest $request)
    {
        $locationIds = empty($request->input('location_ids'))? [] : $request->input('location_ids');

        $iboIds = empty($request->input('ibo_ids'))? [] : $request->input('ibo_ids');

        return response($this->obj->downloadTaxInvoiceDetailsReport(
            $request->input('country_id'),
            $locationIds,
            $request->input('from_date'),
            $request->input('to_date'),
            $request->input('from_cw'),
            $request->input('to_cw'),
            $iboIds
        ));
    }
}
