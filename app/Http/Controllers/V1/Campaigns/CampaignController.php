<?php
namespace App\Http\Controllers\V1\Campaigns;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Campaigns\CampaignRequest,
    Interfaces\Campaigns\CampaignInterface,
    Http\Controllers\Controller,
    Models\Campaigns\Campaign,
    Models\Campaigns\CampaignPayoutPoint,
    Models\Campaigns\EsacPromotion,
    Rules\Campaign\CampaignEditDeleteCheck
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class CampaignController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel,
        $campaignPayoutPointObj,
        $esacPromotionObj
    ;

    /**
     * CampaignController constructor.
     *
     * @param CampaignInterface $campaignInterface
     * @param Campaign $model
     * @param CampaignPayoutPoint $campaignPayoutPoint
     * @param EsacPromotion $esacPromotion
     */
    public function __construct(
        CampaignInterface $campaignInterface, 
        Campaign $model, 
        CampaignPayoutPoint $campaignPayoutPoint,
        EsacPromotion $esacPromotion
    )
    {
        $this->middleware('auth');

        $this->obj = $campaignInterface;

        $this->authorizedModel = $model;

        $this->campaignPayoutPointObj = $campaignPayoutPoint;

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
     * @param CampaignRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdate(CampaignRequest $request)
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
    public function getCampaignList(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'from_cw_schedule_id' => 'sometimes|nullable|integer|exists:cw_schedules,id',
            'to_cw_schedule_id' => 'sometimes|nullable|integer|exists:cw_schedules,id'
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->authorizedModel);

        return response(
            $this->obj->getCampaignsByFilters(
                $request->input('country_id'),
                ($request->has('name') ? $request->input('name') : null),
                ($request->has('report_group') ? $request->input('report_group') : null),
                ($request->has('from_cw_schedule_id') ? $request->input('from_cw_schedule_id') : null),
                ($request->has('to_cw_schedule_id') ? $request->input('to_cw_schedule_id') : null),
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
     * @param mixed $id
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
                    'bail', 'required', 'integer', 'exists:campaigns,id',
                    new CampaignEditDeleteCheck(
                        false,
                        $this->authorizedModel, 
                        $this->campaignPayoutPointObj,
                        $this->esacPromotionObj
                    )
                ]
            ])->validate();

        $this->authorize('delete', $this->obj->show($id));

        $this->obj->delete($id);

        return response(['data' => trans('message.delete.success')]);
    }

}