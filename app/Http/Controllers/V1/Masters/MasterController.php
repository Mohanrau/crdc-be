<?php
namespace App\Http\Controllers\V1\Masters;

use App\{
    Helpers\Traits\ResourceController,
    Http\Requests\Masters\MasterRequest,
    Interfaces\Masters\MasterInterface,
    Http\Controllers\Controller,
    Models\Masters\Master
};
use Illuminate\Http\Request;

class MasterController extends Controller
{
    use ResourceController;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * MasterController constructor.
     *
     * @param MasterInterface $masterRepository
     * @param Master $model
     */
    public function __construct(MasterInterface $masterRepository, Master $model)
    {
        $this->middleware('auth');

        $this->obj = $masterRepository;

        $this->authorizedModel = $model;
    }

    /**
     * get master Data By Master Keys
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getMasterDataByKeys(Request $request)
    {
        if ($request->has('country_id')){
            request()->validate([
                'country_id' => 'required|integer|exists:countries,id'
            ]);
        }

        request()->validate([
            'keys' => 'present|array'
        ]);

        return response($this->obj->getMasterDataByKey(
            $request->input('keys'),
            ($request->has('country_id') ? $request->input('country_id') : null)
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Masters\MasterRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(MasterRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Masters\MasterRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MasterRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }
}
