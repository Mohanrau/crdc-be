<?php
namespace App\Http\Controllers\V1\Locations;

use App\{
    Helpers\Traits\ResourceController,
    Http\Requests\Locations\CityRequest,
    Interfaces\Locations\CityInterface,
    Http\Controllers\Controller,
    Models\Locations\City
};
use Illuminate\Http\Request;

class CityController extends Controller
{
    use ResourceController;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * CityController constructor.
     *
     * @param CityInterface $cityInterface
     * @param City $model
     */
    public function __construct(CityInterface $cityInterface, City $model)
    {
        $this->middleware('auth');

        $this->obj = $cityInterface;

        $this->authorizedModel = $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Locations\CityRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CityRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Locations\CityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CityRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }

    /**
     * get stock locations by cityId
     *
     * @param int $cityId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function stockLocationsByCityId(int $cityId)
    {
        return response($this->obj->getStockLocationsByCity($cityId));
    }
}
