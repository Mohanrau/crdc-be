<?php
namespace App\Helpers\Traits;

use App\Models\Locations\Location;
use Illuminate\Http\Request;

trait ResourceController
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $this->authorize('listing', [$this->authorizedModel]);

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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = $this->obj->find($id);

        $this->authorize('view', $model);

        return response($model);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('delete', [$this->authorizedModel]);

        $this->obj->delete($id);

        return response(['data' => trans('message.delete.success')]);
    }
}