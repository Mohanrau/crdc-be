<?php
namespace App\Http\Controllers\V1\Locations;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Locations\ZoneRequest,
    Interfaces\Locations\ZoneInterface,
    Http\Controllers\Controller,
    Models\Locations\Zone
};
use Illuminate\Http\Request;
use Validator;

class ZoneController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel;

    /**
     * ZoneController constructor.
     *
     * @param ZoneInterface $zoneInterface
     * @param Zone $model
     */
    public function __construct(
        ZoneInterface $zoneInterface, 
        Zone $model
    )
    {
        $this->middleware('auth');

        $this->obj = $zoneInterface;

        $this->authorizedModel = $model;
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
        //TODO implement RNP
        //$this->authorize('view', $this->obj->show($id));

        return response($this->obj->show($id));
    }

    /**
     * Create or Update resource
     *
     * @param EsacVoucherTypeRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdate(ZoneRequest $request)
    {
        //TODO implement RNP
        //check if the action is create then apply create permission-----
        // if ($request->input('id') == null){
        //     $this->authorize('create', [$this->authorizedModel]);
        // }else{
        //     $this->authorize('update', [$this->authorizedModel]);
        // }

        return response($this->obj->createOrUpdate($request->all()));
    }

    /**
     * get zone listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getZoneList(Request $request)
    {
        request()->validate([
            'code' => 'sometimes|nullable|string',
            'name' => 'sometimes|nullable|string',
            'is_all_countries' => 'sometimes|nullable|integer|min:0|max:2',
            'is_all_states' => 'sometimes|nullable|integer|min:0|max:2',
            'is_all_cities' => 'sometimes|nullable|integer|min:0|max:2',
            'is_all_postcodes' => 'sometimes|nullable|integer|min:0|max:2',
            'limit' => 'sometimes|nullable|integer',
            'sort' => 'sometimes|nullable|string',
            'order' => 'sometimes|nullable|string',
            'offset' => 'sometimes|nullable|integer'
        ]);

        //TODO implement RNP
        //check the authorization
        //$this->applyListingOrSearchPermission($this->authorizedModel);

        return response(
            $this->obj->getZonesByFilters(
                ($request->has('code') ? $request->input('code') : null),
                ($request->has('name') ? $request->input('name') : null),
                (!empty($request->input('is_all_countries')) ? $request->input('is_all_countries') : 2),
                (!empty($request->input('is_all_states')) ? $request->input('is_all_states') : 2),
                (!empty($request->input('is_all_cities')) ? $request->input('is_all_cities') : 2),
                (!empty($request->input('is_all_postcodes')) ? $request->input('is_all_postcodes') : 2),
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
                    'bail', 'required', 'integer', 'exists:zones,id'
                ]
            ])->validate();

        //TODO implement RNP
        //$this->authorize('delete', $this->obj->show($id));

        $this->obj->delete($id);

        return response(['data' => trans('message.delete.success')]);
    }

    /**
     * get stock location based on country ~ state ~ city ~ postcode
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getStockLocation(Request $request)
    {
        request()->validate([
            'country_id' => 'sometimes|nullable|integer|exists:countries,id',
            'state_id' => 'sometimes|nullable|integer|exists:states,id',
            'city_id' => 'sometimes|nullable|integer|exists:cities,id',
            'postcode' => 'sometimes|nullable|string',
        ]);

        //TODO implement RNP
        //check the authorization
        //$this->applyListingOrSearchPermission($this->authorizedModel);

        return response(
            $this->obj->getStockLocation(
                (!empty($request->has('country_id')) ? $request->input('country_id') : 0),
                (!empty($request->has('state_id')) ? $request->input('state_id') : 0),
                (!empty($request->has('city_id')) ? $request->input('city_id') : 0),
                (!empty($request->has('postcode')) ? $request->input('postcode') : '')
            )
        );
    }
}