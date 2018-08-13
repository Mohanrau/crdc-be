<?php
namespace App\Http\Controllers\V1\Locations;

use App\{Helpers\Traits\ResourceController,
    Http\Requests\Locations\CountryRequest,
    Interfaces\Locations\CountryInterface,
    Http\Controllers\Controller,
    Models\Locations\Country,
    Rules\Locations\CountryRelationRule};
use Illuminate\Http\Request;

class CountryController extends Controller
{
    use ResourceController{
        index as OldIndex;
    }

    private
        $obj,
        $authorizedModel
    ;

    /**
     * CountryController constructor.
     *
     * @param CountryInterface $countryInterface
     * @param Country $model
     */
    public function __construct(CountryInterface $countryInterface, Country $model)
    {
        $this->middleware('auth')->except('index');

        $this->obj = $countryInterface;

        $this->authorizedModel = $model;
    }

     /**
    * Display a listing of the resource.
    *
    * @param Request $request
    * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
    */
    public function index(Request $request)
    {
        return response(
            $this->obj->countriesList(
                ($request->has('active') ? $request->input('active') : 2),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0),
                ($request->has('location_code') ? $request->input('location_code') : null)
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Locations\CountryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CountryRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Locations\CountryRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CountryRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }

    /**
     * get country with relations
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCountryWithRelation(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'relations' => [
                'required',
                'array',
                new CountryRelationRule($this->authorizedModel)
            ],
            'criterias' => 'nullable|array'
        ]);

        return response($this->obj->getCountryWithRelations(
            $request->input('country_id'),
            $request->input('relations'),
            ($request->has('criterias') ? $request->input('criterias') : [])
        ));
    }
}
