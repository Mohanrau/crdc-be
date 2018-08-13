<?php
namespace App\Http\Controllers\V1\Masters;

use App\{
    Helpers\Traits\ResourceController,
    Http\Requests\Masters\MasterDataRequest,
    Interfaces\Masters\MasterDataInterface,
    Http\Controllers\Controller,
    Models\Masters\MasterData
};
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    use ResourceController;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * MasterDataController constructor.
     *
     * @param MasterDataInterface $masterDataRepository
     * @param MasterData $model
     */
    public function __construct(MasterDataInterface $masterDataRepository, MasterData $model)
    {
        $this->middleware('auth');

        $this->obj = $masterDataRepository;

        $this->authorizedModel = $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Masters\MasterDataRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MasterDataRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Masters\MasterDataRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MasterDataRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }
}
