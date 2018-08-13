<?php
namespace App\Http\Controllers\V1\Modules;

use App\{
    Helpers\Traits\ResourceController,
    Http\Controllers\Controller,
    Http\Requests\Modules\ModuleRequest,
    Interfaces\Modules\ModuleInterface,
    Models\Modules\Module
};

class ModuleController extends Controller
{
    use ResourceController;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * ModuleController constructor.
     *
     * @param ModuleInterface $moduleRepository
     * @param Module $model
     */
    public function __construct(ModuleInterface $moduleRepository, Module $model)
    {
        $this->middleware('auth');

        $this->obj = $moduleRepository;

        $this->authorizedModel = $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Modules\ModuleRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ModuleRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Modules\ModuleRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ModuleRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }
}
