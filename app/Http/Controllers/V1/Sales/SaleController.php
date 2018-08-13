<?php
namespace App\Http\Controllers\V1\Sales;

use App\Http\Requests\Sales\{
    SaleExpressRequest,
    SaleRequest,
    SaleCancellationRequest,
    LegacySaleCancellationRequest
};
use App\Models\{
    Invoices\Invoice,
    Sales\Sale,
    Sales\SaleCancellation
};
use App\{
    Helpers\Traits\AccessControl,
    Http\Controllers\Controller,
    Interfaces\Sales\SaleInterface,
    Interfaces\Masters\MasterInterface,
    Rules\General\MasterDataIdExists,
    Rules\Sales\SalesStatusUpdateValidate
};
use Illuminate\{
    Http\Request,
    Validation\Rule
};

class SaleController extends Controller
{
    use AccessControl;

    private
        $masterRepositoryObj,
        $obj,
        $authorizedModel
    ;

    /**
     * SaleController constructor.
     *
     * @param MasterInterface $masterInterface
     * @param SaleInterface $saleInterface
     * @param Sale $model
     */
    public function __construct(
        MasterInterface $masterInterface, 
        SaleInterface $saleInterface, 
        Sale $model
    )
    {
        $this->middleware('auth')->except(['createExpressSales']);

        $this->masterRepositoryObj = $masterInterface;

        $this->obj = $saleInterface;

        $this->authorizedModel = $model;
    }

    /**
     * get sales listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterSales(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'to_date' => 'sometimes|nullable|date|before_or_equal:today',
            'from_date' => 'sometimes|nullable|date|before_or_equal:to_date'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->authorizedModel);

        //check if user is stockist or back office has access to locations.list
        $this->locationListingAccessCheck();

        return response(
              $this->obj->getSalesByFilters(
                  $request->input('country_id'),
                  ($request->has('search_text') ? $request->input('search_text') : ''),
                  ($request->has('from_date') ? $request->input('from_date') : ''),
                  ($request->has('to_date') ? $request->input('to_date') : ''),
                  ($request->has('channel_id') ? $request->input('channel_id') : 0),
                  ($request->has('delivery_method_id') ? $request->input('delivery_method_id') : 0),
                  ($request->has('delivery_status_id') ? $request->input('delivery_status_id') : 0),
                  ($request->has('order_status_id') ? $request->input('order_status_id') : 0),
                  ($request->has('is_esac_redemption') ? $request->input('is_esac_redemption') : -1),
                  ($request->has('is_corporate_sales') ? $request->input('is_corporate_sales') : -1),
                  ($request->has('is_rental_sale_order') ? $request->input('is_rental_sale_order') : -1),
                  ($request->has('with_trashed') ? $request->input('with_trashed') : 0),
                  ($request->has('limit') ? $request->input('limit') : 0),
                  ($request->has('sort') ? $request->input('sort') :  'id'),
                  ($request->has('order') ? $request->input('order') : 'desc'),
                  ($request->has('offset') ? $request->input('offset') :  0)
              )
        );
    }

    /**
     * get sales details for a given salesId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function salesDetails(Request $request)
    {
        request()->validate([
            'sale_id' => 'required|integer|exists:sales,id'
        ]);

        $this->authorize('view', [$this->authorizedModel]);

        $sale = $this->obj->find($request->input('sale_id'));

        //check if user backOffice or stockist and can view this sale
        $this->resourceLocationAccessCheck($sale->transaction_location_id, $sale->user_id);

        return response($this->obj->saleDetails($request->input('sale_id')));
    }

    /**
     * get sales product, kitting product, PWP and FOC detail
     *
     * @param MasterInterface $masterInterface
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getSalesProductDetails(MasterInterface $masterInterface, Request $request)
    {
        request()->validate([
            'function_type' => 'required|string|in:all,promo,admin_fees,delivery_fees,promo_price,other_fees',
            'downline_member_id' => 'required|integer|exists:members,user_id',
            'country_id' => 'required|integer|exists:countries,id',
            'location_id' => 'required|integer|exists:locations,id',
            'products' => 'array',
            'products.*.product_id' => 'required_with:products|integer|exists:products,id',
            'products.*.transaction_type' => [
                'required_with:products',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'sale_types')
             ],
            'products.*.quantity' => 'required_with:products|integer',
            'kittings' => 'array',
            'kittings.*.kitting_id' => 'required_with:kittings|integer|exists:kitting,id',
            'kittings.*.transaction_type' => [
                'required_with:kittings',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'sale_types')
            ],
            'kittings.*.quantity' => 'required_with:kittings|integer'
        ]);

        if($request->input('function_type') == 'promo'){

            $results = $this->obj->eligibleSalesPromo(
                $request->input('downline_member_id'),
                $request->input('country_id'),
                $request->input('location_id'),
                ($request->has('products') ? $request->input('products') :  array()),
                ($request->has('kittings') ? $request->input('kittings') :  array()),
                $request->input()
            );

        } else if($request->input('function_type') == 'promo_price'){

            $results = $this->obj->calculatePromoPrice(
                $request->has('selected.promotions') ? $request->input('selected.promotions') :  array(),
                $request->input()
            );

        } else if($request->input('function_type') == 'admin_fees'){

            $results = $this->obj->calculateSalesAdminFees(
                $request->input('downline_member_id'),
                $request->input()
            );

        } else if($request->input('function_type') == 'delivery_fees'){

            $results = $this->obj->calculateSalesDeliveryFees(
                $request->input('downline_member_id'),
                $request->input()
            );

        } else if($request->input('function_type') == 'other_fees'){

            $results = $this->obj->calculateOtherFees(
                $request->input()
            );

        } else if($request->input('function_type') == 'all'){

            $results = $this->obj->eligibleSalesPromo(
                $request->input('downline_member_id'),
                $request->input('country_id'),
                $request->input('location_id'),
                ($request->has('products') ? $request->input('products') :  array()),
                ($request->has('kittings') ? $request->input('kittings') :  array()),
                $request->input()
            );

            $results = $this->obj->calculatePromoPrice(
                $request->has('selected.promotions') ? $request->input('selected.promotions') :  array(),
                $results
            );

            $results = $this->obj->calculateSalesAdminFees(
                $request->input('downline_member_id'),
                $results
            );

            $results = $this->obj->calculateSalesDeliveryFees(
                $request->input('downline_member_id'),
                $results
            );
            
            $results = $this->obj->calculateOtherFees(
                $results
            );
        }

        return response($results);
    }

    /**
     * create new sale records
     *
     * @param SaleRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create(SaleRequest $request)
    {
        $this->authorize('create', [$this->authorizedModel]);

        return response($this->obj->createSale($request->all()));
    }

    /**
     * update sale records
     *
     * @param MasterInterface $masterInterface
     * @param Sale $sale
     * @param Request $request
     * @param int $saleId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(
        MasterInterface $masterInterface,
        Sale $sale,
        Request $request,
        int $saleId
    )
    {
        request()->validate([
            'sales_data.status' => 'required|string|in:cancel,save,cancelPreOrder',
            'sales_data.downline_member_id' => 'required|integer|exists:members,user_id',
            'sales_data' => [
                'required',
                'array',
                new SalesStatusUpdateValidate($masterInterface, $sale, $saleId)
            ]
        ]);

        $this->authorize('update', [$this->authorizedModel]);

        return response($this->obj->updateSale($request->all(), $saleId));
    }

    /**
     *  download credit note for a given CN_ID
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadCreditNote(Request $request)
    {
        request()->validate([
            'credit_note_id' => 'required|integer|exists:credit_notes,id',
            'section' => ['required', Rule::in(['sales_exchange', 'sales_cancellation'])],
        ]);

        //TODO implement RNP

        return response($this->obj->downloadCreditNote(
            $request->input('credit_note_id'),
            $request->input('section')
        ));
    }

    /**
     * create new sale (express)
     *
     * @param SaleExpressRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function createExpressSales(SaleExpressRequest $request)
    {
        return response( $this->obj->createSaleExpress( $request->all() ) );
    }

    /**
     * Download Sales Daily Receipt or Transaction Report
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function downloadSaleDailyReport(Request $request)
    {
        request()->validate([
            'type' => 'required|string|in:receipt,transaction',
            'country_id' => 'required|integer|exists:countries,id',
            'location_ids' => 'array',
            'location_ids.*.location_id' => 'integer|exists:locations,id',
            'from_date' => 'required|date|before_or_equal:to_date',
            'to_date' => 'required|date',
            'user_ids' => 'array',
            'user_ids.*.user_id' => 'integer|exists:users,id'
        ],[],
        [
            'country_id' => 'country',
            'from_date' => 'transaction date from',
            'to_date' => 'transaction date to'
        ]);

        //check rnp if user has access to this api
        $this->authorize('saleDailyReport', $this->authorizedModel);

        //assign default location if stockist user
        if ($this->isUser('stockist')){
            $locationArray = [$this->getStockistUser()->stockistLocation()->first()->id]; //todo clean that part, make it simple
        } elseif ($this->isUser('stockist_staff')){
            $locationArray = $this->getStockistParentLocation();
        } else {
            $locationArray = ($request->has('location_ids') ? $request->input('location_ids') : []);
        }

        if($request->input('type') == 'transaction'){
            return response(
                $this->obj->downloadSalesDailyTransactionReport(
                    $request->input('country_id'),
                    $locationArray,
                    $request->input('from_date'),
                    $request->input('to_date'),
                    ($request->has('user_ids') ? $request->input('user_ids') : [])
                )
            );

        } else {

            return response(
                $this->obj->downloadSaleDailyReceiptReport(
                    $request->input('country_id'),
                    $locationArray,
                    $request->input('from_date'),
                    $request->input('to_date'),
                    ($request->has('user_ids') ? $request->input('user_ids') : [])
                )
            );
        }
    }

    /**
     * Download Sales MPOS Report
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function downloadSaleMposReport(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'location_ids' => 'array',
            'location_ids.*.location_id' => 'integer|exists:locations,id',
            'from_date' => 'required|date|before_or_equal:to_date',
            'to_date' => 'required|date'
        ]);

        //check rnp if user has access to this api
        $this->authorize('saleMposReport', $this->authorizedModel);

        //assign default location if stockist user
        if ($this->isUser('stockist')){
            $locationArray = [$this->getStockistUser()->stockistLocation()->first()->id]; //todo clean that part, make it simple
        } else if($this->isUser('stockist_staff')){
            $locationArray = $this->getStockistParentLocation();
        } else {
            $locationArray = ($request->has('location_ids') ? $request->input('location_ids') : []);
        }

        return response(
            $this->obj->downloadSaleMposReport(
                $request->input('country_id'),
                $locationArray,
                $request->input('from_date'),
                $request->input('to_date')
            )
        );
    }

    /**
     * Download pre-order note
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadPreOrderNote(Request $request)
    {
        request()->validate([
            'sale_id' => 'required|integer|exists:sales,id'
        ],[],
        [
            'sale_id' => 'sale ID'
        ]);

        return response(
            $this->obj->downloadPreOrderNote($request->input('sale_id'))
        );
    }

    /**
     * Download Itemised Sales Report
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function downloadSaleProductReport(Request $request)
    {
        request()->validate([
            'country_ids' => 'required|array',
            'country_ids.*.country_id' => 'integer|exists:countries,id',
            'location_ids' => 'present|array',
            'location_ids.*.location_id' => 'integer|exists:locations,id',
            'from_date' => 'required|date|before_or_equal:to_date',
            'to_date' => 'required|date',
            'from_cw' => 'nullable|integer|exists:cw_schedules,id',
            'to_cw' => 'nullable|integer|exists:cw_schedules,id|gte:from_cw',
            'broad_categories' => 'present|array',
            'broad_categories.*.category_id' => 'integer|exists:product_categories:id',
            'sub_categories' => 'present|array',
            'sub_categories.*.category_id' => 'integer|exists:product_categories:id',
            'minor_categories' => 'present|array',
            'minor_categories.*.category_id' => 'integer|exists:product_categories:id',
        ],[],
        [
            'country_ids' => 'country',
            'location_ids' => 'location',
            'from_date' => 'transaction date from',
            'to_date' => 'transaction date to',
            'from_cw' => 'commission week from',
            'to_cw' => 'commission week to',
            'broad_categories' => 'broad category',
            'sub_categories' => 'sub-category',
            'minor_categories' => 'minor category',
        ]);

        $countryIds = empty($request->input('country_ids'))? [] : $request->input('country_ids');

        $locationIds = empty($request->input('location_ids'))? [] : $request->input('location_ids');

        $broadCategories = empty($request->input('broad_categories'))? [] : $request->input('broad_categories');

        $subCategories = empty($request->input('sub_categories'))? [] : $request->input('sub_categories');

        $minorCategories = empty($request->input('minor_categories'))? [] : $request->input('minor_categories');

        $fromCw = empty($request->input('from_cw'))? 0 : $request->input('from_cw');

        $toCw = empty($request->input('to_cw'))? 0 : $request->input('to_cw');

        return response(
            $this->obj->downloadSaleProductReport(
              $countryIds,
              $locationIds,
              $request->input('from_date'),
              $request->input('to_date'),
              $fromCw,
              $toCw,
              $broadCategories,
              $subCategories,
              $minorCategories
            )
        );
    }
}
