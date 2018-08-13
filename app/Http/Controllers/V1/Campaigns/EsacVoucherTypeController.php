<?php
namespace App\Http\Controllers\V1\Campaigns;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Campaigns\EsacVoucherTypeRequest,
    Interfaces\Campaigns\EsacVoucherTypeInterface,
    Http\Controllers\Controller,
    Models\Campaigns\CampaignRule,
    Models\Campaigns\EsacVoucherType,
    Models\Campaigns\EsacVoucherSubType,
    Models\Campaigns\EsacPromotion,
    Rules\Campaign\EsacVoucherTypeEditDeleteCheck
};
use Illuminate\Http\Request;
use Validator;

class EsacVoucherTypeController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel, 
        $campaignRuleObj, 
        $esacVoucherSubTypeObj, 
        $esacPromotionObj
    ;

    /**
     * EsacVoucherTypeController constructor.
     *
     * @param EsacVoucherTypeInterface $esacVoucherTypeInterface
     * @param EsacVoucherType $model
     * @param CampaignRule $campaignRule
     * @param EsacVoucherSubType $esacVoucherSubType
     * @param EsacPromotion $esacPromotion
     */
    public function __construct(
        EsacVoucherTypeInterface $esacVoucherTypeInterface, 
        EsacVoucherType $model,
        CampaignRule $campaignRule,
        EsacVoucherSubType $esacVoucherSubType,
        EsacPromotion $esacPromotion
    )
    {
        $this->middleware('auth');

        $this->obj = $esacVoucherTypeInterface;

        $this->authorizedModel = $model;
        
        $this->campaignRuleObj = $campaignRule;

        $this->esacVoucherSubTypeObj = $esacVoucherSubType;
        
        $this->esacPromotionObj = $esacPromotion;
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
     * @param EsacVoucherTypeRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdate(EsacVoucherTypeRequest $request)
    {
        //check if the action is create then apply create permission-----
        if ($request->input('id') == null){
            $this->authorize('create', [$this->authorizedModel]);
        }else{
            $this->authorize('update', [$this->authorizedModel]);
        }

        return response($this->obj->createOrUpdate($request->all()));
    }

    /**
     * get campaign listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getEsacVoucherTypeList(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'name' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'active' => 'sometimes|nullable|integer',
            'limit' => 'sometimes|nullable|integer',
            'sort' => 'sometimes|nullable|string',
            'order' => 'sometimes|nullable|string',
            'offset' => 'sometimes|nullable|integer'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->authorizedModel);

        return response(
            $this->obj->getEsacVoucherTypesByFilters(
                $request->input('country_id'),
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
                    'bail', 'required', 'integer', 'exists:esac_voucher_types,id',
                    new EsacVoucherTypeEditDeleteCheck(
                        false,
                        $this->campaignRuleObj,
                        $this->authorizedModel,
                        $this->esacVoucherSubTypeObj,
                        $this->esacPromotionObj
                    )
                ]
            ])->validate();

        $this->authorize('delete', $this->obj->show($id));

        $this->obj->delete($id);

        return response(['data' => trans('message.delete.success')]);
    }
}