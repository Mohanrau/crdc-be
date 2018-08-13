<?php
namespace App\Http\Controllers\V1\Locations;

use App\{
    Helpers\Traits\AccessControl,
    Http\Controllers\Controller,
    Helpers\Traits\ResourceController,
    Http\Requests\Locations\LocationRequest,
    Interfaces\Locations\LocationInterface,
    Models\Locations\Location
};
use Illuminate\Http\Request;
use Validator;

class LocationController extends Controller
{
    use AccessControl, ResourceController{
        index as oldIndex;
    }

    private
        $obj,
        $authorizedModel
    ;

    /**
     * LocationController constructor.
     *
     * @param LocationInterface $locationRepository
     * @param Location $location
     */
    public function __construct(
        LocationInterface $locationRepository,
        Location $location
    )
    {
        $this->middleware('auth');

        $this->obj = $locationRepository;

        $this->authorizedModel = $location;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request)
    {
        //check the authorization
        $this->authorize('listing', [$this->authorizedModel]);

        //check if user is stockist or back office has access to locations.list
        $this->locationListingAccessCheck();

        return response(
            $this->obj->getAll(
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param LocationRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(LocationRequest $request)
    {
        $this->authorize('create', [$this->authorizedModel]);

        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param LocationRequest $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(LocationRequest $request, $id)
    {
        $this->authorize('update', [$this->authorizedModel]);

        return response($this->obj->update($request->all(), $id));
    }

    /**
     * get location type listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterLocationsTypes(Request $request)
    {
        //TODO: Apply RnP

        return response(
            $this->obj->getLocationsTypesByFilters(
                ($request->has('code') ? $request->input('code') : ''),
                ($request->has('name') ? $request->input('name') : ''),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * get location address listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterLocationsAddresses(Request $request)
    {
        //TODO: Apply RnP

        return response(
            $this->obj->getLocationsAddressesByFilters(
                ($request->has('country_id') ? $request->input('country_id') : 0),
                ($request->has('state_id') ? $request->input('state_id') : 0),
                ($request->has('location_id') ? $request->input('location_id') : 0),
                ($request->has('location_type_codes') ? $request->input('location_type_codes') : []),
                ($request->has('relations') ? $request->input('relations') : []),
                ($request->has('stockists_ibs_online') ? $request->input('stockists_ibs_online') : false),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') : 'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') : 0)
            )
        );
    }

    /**
     * get stock locations by locationId
     *
     * @param $locationId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function stockLocationsByLocationId($locationId)
    {
        Validator::make(
                ['id' => $locationId],
                ['id' => 'required|integer|exists:locations,id']
            )
            ->validate();

        //check the authorization
        $this->authorize('listing', [$this->authorizedModel]);

        return response($this->obj->getStockLocationsByLocation($locationId));
    }
}
