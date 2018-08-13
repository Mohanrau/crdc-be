<?php
namespace App\Http\Controllers\V1\Locations;

use App\{
    Helpers\Traits\ResourceController,
    Http\Requests\Locations\StateRequest,
    Interfaces\Locations\StateInterface,
    Http\Controllers\Controller,
    Models\Locations\State
};
use Illuminate\Http\Request;

class StateController extends Controller
{
    use ResourceController;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * StateController constructor.
     *
     * @param StateInterface $stateInterface
     * @param State $model
     */
    public function __construct(StateInterface $stateInterface, State $model)
    {
        $this->middleware('auth');

        $this->obj = $stateInterface;

        $this->authorizedModel = $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Locations\StateRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StateRequest $request)
    {
        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Locations\StateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StateRequest $request, $id)
    {
        return response($this->obj->update($request->all(), $id));
    }
    
     /**
     * filter states
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterStates(Request $request)
    {
        //TODO implement RNP

        request()->validate([
            'country_id' => 'sometimes|integer|exists:countries,id',
            'active_stockist' => 'sometimes|boolean',
            'limit' => 'sometimes|integer|min:0',
            'offset' => 'sometimes|integer|min:0'
        ]);

        return response(
            $this->obj->getStatesByFilters(
                ($request->has('country_id') ? $request->input('country_id') : 0),
                ($request->has('active_stockist') ? $request->input('active_stockist') : false),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') : 'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') : 0)
            )
        );
    }
}
