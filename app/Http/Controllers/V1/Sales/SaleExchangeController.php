<?php
namespace App\Http\Controllers\V1\Sales;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Sales\SaleExchangeRequest,
    Interfaces\Sales\SaleExchangeInterface,
    Http\Controllers\Controller,
    Models\Sales\SaleExchange
};
use Illuminate\Http\Request;

class SaleExchangeController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * SaleExchangeController constructor.
     * 
     * @param SaleExchangeInterface $saleExchange
     * @param SaleExchange $model
     */
    public function __construct(SaleExchangeInterface $saleExchange, SaleExchange $model)
    {
        $this->middleware('auth');

        $this->obj = $saleExchange;

        $this->authorizedModel = $model;
    }

    /**
     *  get the sales exchange by filters
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function filterSalesExchange(Request $request)
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
            $this->obj->getSalesExchangeByFilters(
                $request->input('country_id'),
                ($request->has('search_text') ? $request->input('search_text') : ''),
                ($request->has('from_date') ? $request->input('from_date') : ''),
                ($request->has('to_date') ? $request->input('to_date') : ''),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * create new salesExchange
     *
     * @param SaleExchangeRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(SaleExchangeRequest $request)
    {
        $this->authorize('create', [$this->authorizedModel]);

        return response($this->obj->createSaleExchange($request->all()));
    }

    /**
     * get sales details for a given salesExchangeId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function salesExchangeDetails(Request $request)
    {
        request()->validate([
            'sale_exchange_id' => 'required|integer|exists:sales_exchanges,id'
        ]);

        $this->authorize('view', [$this->authorizedModel]);

        $saleExchange = $this->obj->find($request->input('sale_exchange_id'));

        //check if user backOffice or stockist and can view this sale exchange details
        $this->resourceLocationAccessCheck($saleExchange->transaction_location_id, $saleExchange->user_id);

        return response($this->obj->saleExchangeDetails($request->input('sale_exchange_id')));
    }

    /**
     * download exchange note for a given sales_exchange_bills ID
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Mpdf\MpdfException
     */
    public function getExchangeBill(Request $request)
    {
        request()->validate([
            'sales_exchange_bill_id' => 'required|integer|exists:sales_exchange_bills,id'
        ]);

        return response($this->obj->downloadExchangeBill(
            $request->input('sales_exchange_bill_id')
        ));
    }
}
