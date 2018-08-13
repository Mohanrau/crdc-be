<?php
namespace App\Http\Controllers\V1\Campaigns;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Campaigns\EsacVoucherSubTypeRequest,
    Interfaces\Campaigns\EsacVoucherSubTypeInterface,
    Http\Controllers\Controller,
    Models\Campaigns\CampaignRule,
    Models\Campaigns\EsacVoucherSubType,
    Models\Campaigns\EsacPromotionVoucherSubType,
    Rules\Campaign\EsacVoucherSubTypeEditDeleteCheck
};
use Illuminate\{
    Http\Request,
    Support\Facades\Validator
};

class EsacVoucherSubTypeController extends Controller
{
    use AccessControl;

    private
        $obj,
        $campaignRuleObj,
        $esacVoucherSubTypeObj,
        $esacPromotionVoucherSubTypeObj;

    /**
     * EsacVoucherSubTypeController constructor.
     *
     * @param EsacVoucherSubTypeInterface $esacVoucherSubTypeInterface
     * @param EsacVoucherSubType $esacVoucherSubType
     * @param CampaignRule $campaignRule
     * @param EsacPromotionVoucherSubType $esacPromotionVoucherSubType
     */
    public function __construct(
        EsacVoucherSubTypeInterface $esacVoucherSubTypeInterface,
        EsacVoucherSubType $esacVoucherSubType,
        CampaignRule $campaignRule,
        EsacPromotionVoucherSubType $esacPromotionVoucherSubType
    )
    {
        $this->middleware('auth');

        $this->obj = $esacVoucherSubTypeInterface;

        $this->esacVoucherSubTypeObj = $esacVoucherSubType;

        $this->campaignRuleObj = $campaignRule;

        $this->esacPromotionVoucherSubTypeObj = $esacPromotionVoucherSubType;
    }

    /**
     * get campaign listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getEsacVoucherSubTypeList(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'voucher_type_id' => 'sometimes|nullable|integer|exists:esac_voucher_types,id',
            'name' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'active' => 'sometimes|nullable|integer',
            'limit' => 'sometimes|nullable|integer',
            'sort' => 'sometimes|nullable|string',
            'order' => 'sometimes|nullable|string',
            'offset' => 'sometimes|nullable|integer'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->esacVoucherSubTypeObj);

        return response(
            $this->obj->getEsacVoucherSubTypesByFilters(
                $request->input('country_id'),
                ($request->has('voucher_type_id') ? $request->input('voucher_type_id') : null),
                ($request->has('name') ? $request->input('name') : null),
                ($request->has('description') ? $request->input('description') : null),
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
        $this->authorize('view', $this->esacVoucherSubTypeObj->find($id));

        return response($this->obj->show($id));
    }

    /**
     * Create or Update resource
     *
     * @param EsacVoucherSubTypeRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdate(EsacVoucherSubTypeRequest $request)
    {
        //check if the action is create then apply create permission-----
        if ($request->input('id') == null){
            $this->authorize('create', [$this->esacVoucherSubTypeObj]);
        }else{
            $this->authorize('update', [$this->esacVoucherSubTypeObj]);
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
            [
                'id' => $id
            ],
            [
                'id' => [
                    'bail', 'required', 'integer', 'exists:esac_voucher_sub_types,id',
                    new EsacVoucherSubTypeEditDeleteCheck(
                        false,
                        $this->campaignRuleObj,
                        $this->esacVoucherSubTypeObj,
                        $this->esacPromotionVoucherSubTypeObj
                    )
                ]
            ])->validate();

        $this->authorize('delete', $this->obj->show($id));

        $this->obj->delete($id);

        return response(['data' => trans('message.delete.success')]);
    }
}