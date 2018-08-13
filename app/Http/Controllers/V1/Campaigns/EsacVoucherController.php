<?php
namespace App\Http\Controllers\V1\Campaigns;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Campaigns\EsacVoucherRequest,
    Interfaces\Campaigns\EsacVoucherInterface,
    Interfaces\Masters\MasterInterface,
    Http\Controllers\Controller,
    Models\Campaigns\EsacVoucher,
    Models\Sales\Sale,
    Models\Sales\SaleEsacVouchersClone,
    Rules\General\MasterDataIdExists,
    Rules\Campaign\EsacVoucherEditDeleteCheck
};
use Illuminate\Http\Request;
use Validator;

class EsacVoucherController extends Controller
{
    use AccessControl;

    private $obj, 
        $esacVoucherObj, 
        $masterRepositoryObj, 
        $saleObj,
        $saleEsacVouchersCloneObj;

    /**
     * EsacVoucherController constructor.
     *
     * @param EsacVoucherInterface $esacVoucherInterface
     * @param EsacVoucher $esacVoucher
     * @param MasterInterface $masterInterface
     * @param Sale $sale
     * @param SaleEsacVouchersClone $saleEsacVouchersClone
     */
    public function __construct(
        EsacVoucherInterface $esacVoucherInterface,
        EsacVoucher $esacVoucher,
        MasterInterface $masterInterface,
        Sale $sale,
        SaleEsacVouchersClone $saleEsacVouchersClone
    )
    {
        $this->middleware('auth');

        $this->obj = $esacVoucherInterface;

        $this->esacVoucherObj = $esacVoucher;

        $this->masterRepositoryObj = $masterInterface; 

        $this->saleObj = $sale; 

        $this->saleEsacVouchersCloneObj = $saleEsacVouchersClone;
    }

    /**
     * get esac voucher listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getEsacVoucherList(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'campaign_id' => 'sometimes|nullable|integer|exists:campaigns,id',
            'promotion_id' => 'sometimes|nullable|integer|exists:esac_promotions,id',
            'voucher_type_id' => 'sometimes|nullable|integer|exists:esac_voucher_types,id',
            'voucher_sub_type_id' => 'sometimes|nullable|integer|exists:esac_voucher_sub_types,id',
            'voucher_number' => 'sometimes|nullable|string',
            'voucher_status' => 'sometimes|nullable|string|in:N,P,V',
            'voucher_remarks' => 'sometimes|nullable|string',
            'voucher_period_id' => [
                'sometimes', 'nullable', 'integer',
                new MasterDataIdExists($this->masterRepositoryObj, 'voucher_period')
            ],
            'member_user_id' => 'sometimes|nullable|integer|exists:users,id',
            'sponsor_id' => 'sometimes|nullable|integer|exists:users,id',
            'issued_date' => 'sometimes|nullable|date',
            'expiry_date' => 'sometimes|nullable|date',
            'from_campaign_cw_schedule_id' => 'sometimes|nullable|integer|exists:cw_schedules,id',
            'to_campaign_cw_schedule_id' => 'sometimes|nullable|integer|exists:cw_schedules,id',
            'from_created_at' => 'sometimes|nullable|date|before_or_equal:to_created_at',
            'to_created_at' => 'sometimes|nullable|date|before_or_equal:today',
            'for_redemption' => 'sometimes|nullable|boolean',
            'active' => 'sometimes|nullable|integer',
            'limit' => 'sometimes|nullable|integer',
            'sort' => 'sometimes|nullable|string',
            'order' => 'sometimes|nullable|string',
            'offset' => 'sometimes|nullable|integer'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->esacVoucherObj);

        return response(
            $this->obj->getEsacVouchersByFilters(
                $request->input('country_id'),
                ($request->has('campaign_id') ? $request->input('campaign_id') : null),
                ($request->has('promotion_id') ? $request->input('promotion_id') : null),
                ($request->has('voucher_type_id') ? $request->input('voucher_type_id') : null),
                ($request->has('voucher_sub_type_id') ? $request->input('voucher_sub_type_id') : null),
                ($request->has('voucher_number') ? $request->input('voucher_number') : null),
                ($request->has('voucher_status') ? $request->input('voucher_status') : null),
                ($request->has('voucher_remarks') ? $request->input('voucher_remarks') : null),
                ($request->has('voucher_period_id') ? $request->input('voucher_period_id') : null),
                ($request->has('member_user_id') ? $request->input('member_user_id') : null),
                ($request->has('sponsor_id') ? $request->input('sponsor_id') : null),
                ($request->has('issued_date') ? $request->input('issued_date') : null),
                ($request->has('expiry_date') ? $request->input('expiry_date') : null),
                ($request->has('from_campaign_cw_schedule_id') ? $request->input('from_campaign_cw_schedule_id') : null),
                ($request->has('to_campaign_cw_schedule_id') ? $request->input('to_campaign_cw_schedule_id') : null),
                ($request->has('from_created_at') ? $request->input('from_created_at') : null),
                ($request->has('to_created_at') ? $request->input('to_created_at') : null),
                ($request->has('for_redemption') ? $request->input('for_redemption') : null),
                ($request->has('active') ? $request->input('active') : null),
                ($request->has('search') ? $request->input('search') : null),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * Get the specified resource in storage.
     *
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('view', $this->obj->show($id));

        return response($this->obj->show($id));
    }

    /**
     * Create or Update resource
     *
     * @param EsacVoucherRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdate(EsacVoucherRequest $request)
    {
        //check if the action is create then apply create permission-----
        if ($request->input('id') == null){
            $this->authorize('create', [$this->esacVoucherObj]);
        }else{
            $this->authorize('update', [$this->esacVoucherObj]);
        }

        return response($this->obj->createOrUpdate($request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($id)
    {
        Validator::make(
            ['id' => $id],
            [
                'id' => [
                    'bail', 'required', 'integer', 'exists:esac_vouchers,id',
                    new EsacVoucherEditDeleteCheck(
                        false,
                        $this->esacVoucherObj, 
                        $this->masterRepositoryObj,
                        $this->saleObj,
                        $this->saleEsacVouchersCloneObj
                    )
                ]
            ])->validate();

        $this->authorize('delete', $this->obj->show($id));

        $this->obj->delete($id);

        return response(['data' => trans('message.delete.success')]);
    }
}