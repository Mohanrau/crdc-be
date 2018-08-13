<?php
namespace App\Http\Controllers\V1\Locations;

use App\{
    Helpers\Traits\ResourceController,
    Http\Requests\Locations\EntityRequest,
    Interfaces\Locations\EntityInterface,
    Http\Controllers\Controller,
    Models\Locations\Entity
};
use Illuminate\Http\Request;

class EntityController extends Controller
{
    use ResourceController;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * EntityController constructor.
     *
     * @param EntityInterface $entityRepository
     * @param Entity $model
     */
    public function __construct(EntityInterface $entityRepository ,Entity $model)
    {
        $this->middleware('auth');

        $this->obj = $entityRepository;

        $this->authorizedModel = $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Locations\EntityRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EntityRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Locations\EntityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EntityRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }
}
