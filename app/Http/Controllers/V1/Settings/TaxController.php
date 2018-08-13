<?php
namespace App\Http\Controllers\V1\Settings;

use App\{
    Helpers\Traits\ResourceController,
    Http\Requests\Settings\TaxRequest,
    Models\Settings\Tax,
    Http\Controllers\Controller
};
use Illuminate\{
    Http\Request,
    Support\Facades\Auth
};

class TaxController extends Controller
{
    use ResourceController{
        index as resourceIndex;
    }

    private
        $with= null,
        $obj,
        $authorizedModel
    ;

    /**
     * TaxController constructor.
     *
     * @param Tax $tax
     */
    public function __construct(Tax $tax)
    {
        $this->middleware('auth');

        $this->authorizedModel = $tax; //this object for rnp

        $this->obj = $tax; //we need this object for resourceController trail
    }

    /**
     * get all records
     *
     * @param Request $request
     * @return static
     */
    public function index(Request $request)
    {
        $paginate = ($request->has('limit') ? $request->input('limit') : 0);

        $orderBy = ($request->has('sort') ? $request->input('sort') :  'id');

        $orderMethod = ($request->has('order') ? $request->input('order') : 'desc');

        $offset = ($request->has('offset') ? $request->input('offset') :  0);

        $totalRecords = collect(
            [
                'total' => $this->authorizedModel->orderBy($orderBy, $orderMethod)->count()
            ]
        );

        $data = $this->authorizedModel
            ->orderBy($orderBy, $orderMethod);

        //check if no relations required.
        if ($this->with != null) {
            $data = $data->with($this->with);
        }

        $data = ($paginate) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Settings\TaxRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TaxRequest $request)
    {
        return response(Auth::user()->createdBy($this->authorizedModel)->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Settings\TaxRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TaxRequest $request, $id)
    {
        $data = $this->authorizedModel->findOrFail($id);

        $data->update($request->all());

        return response($data);
    }
}
