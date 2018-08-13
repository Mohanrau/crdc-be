<?php
namespace App\Http\Controllers\V1\Campaigns;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Campaigns\EsacPromotionRequest,
    Interfaces\Campaigns\EsacPromotionInterface,
    Http\Controllers\Controller,
    Models\Campaigns\EsacPromotion,
    Models\Campaigns\EsacVoucher,
    Rules\Campaign\EsacPromotionEditDeleteCheck
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class EsacPromotionController extends Controller
{
    use AccessControl;

    private
        $obj,
        $esacPromotionObj,
        $esacVoucherObj;

    /**
     * EsacPromotionController constructor.
     *
     * @param EsacPromotionInterface $esacPromotionInterface
     * @param EsacPromotion $esacPromotion
     * @param EsacVoucher $esacVoucher
     */
    public function __construct(
        EsacPromotionInterface $esacPromotionInterface,
        EsacPromotion $esacPromotion,
        EsacVoucher $esacVoucher
    )
    {
        $this->middleware('auth');

        $this->obj = $esacPromotionInterface;

        $this->esacPromotionObj = $esacPromotion;

        $this->esacVoucherObj = $esacVoucher;
    }

    /**
     * get campaign listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getEsacPromotionList(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'campaign_id' => 'sometimes|nullable|integer|exists:campaigns,id',
            'taxable' => 'sometimes|nullable|integer',
            'voucher_type_id' => 'sometimes|nullable|integer|exists:esac_voucher_types,id',
            'entitled_by' => 'sometimes|nullable|string|in:C,P',
            'max_purchase_qty' => 'sometimes|nullable|integer|min:0',
            'active' => 'sometimes|nullable|integer',
            'limit' => 'sometimes|nullable|integer',
            'sort' => 'sometimes|nullable|string',
            'order' => 'sometimes|nullable|string',
            'offset' => 'sometimes|nullable|integer'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->esacPromotionObj);

        return response(
            $this->obj->getEsacPromotionsByFilters(
                $request->input('country_id'),
                ($request->has('campaign_id') ? $request->input('campaign_id') : null),
                ($request->has('taxable') ? $request->input('taxable') : null),
                ($request->has('voucher_type_id') ? $request->input('voucher_type_id') : null),
                ($request->has('entitled_by') ? $request->input('entitled_by') : null),
                ($request->has('max_purchase_qty') ? $request->input('max_purchase_qty') : null),
                ($request->has('search') ? $request->input('search') : null),
                ($request->has('active') ? $request->input('active') : null),
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
     * @param EsacPromotionRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdate(EsacPromotionRequest $request)
    {
        //check if the action is create then apply create permission-----
        if ($request->input('id') == null){
            $this->authorize('create', [$this->esacPromotionObj]);
        }else{
            $this->authorize('update', [$this->esacPromotionObj]);
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
                    'bail', 'required', 'integer', 'exists:esac_promotions,id',
                    new EsacPromotionEditDeleteCheck(
                        false,
                        $this->esacPromotionObj, 
                        $this->esacVoucherObj
                    )
                ]
            ])->validate();

        $this->authorize('delete', $this->obj->show($id));

        $this->obj->delete($id);

        return response(['data' => trans('message.delete.success')]);
    }
}