<?php
namespace App\Http\Controllers\V1\Dummy;

use App\{
    Helpers\Traits\AccessControl,
    Http\Requests\Dummy\DummyRequest,
    Interfaces\Dummy\DummyInterface,
    Models\Dummy\Dummy,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class DummyController extends Controller
{
    use AccessControl;

    private
        $obj,
        $authorizedModel
    ;

    /**
     * DummyController constructor.
     *
     * @param DummyInterface $dummyRepository
     * @param Dummy $model
     */
    public function __construct(DummyInterface $dummyRepository, Dummy $model)
    {
        $this->middleware('auth');

        $this->obj = $dummyRepository;

        $this->authorizedModel = $model;
    }

    /**
     * get dummy data filtered by countryId and dummy name or code (optional)
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function filterDummy(Request $request)
    {
        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
        ]);

        //check the authorization
        $this->applyListingOrSearchPermission($this->authorizedModel);

        return response(
            $this->obj->getDummyFilters(
                $request->input('country_id'),
                ($request->has('is_lingerie') ? $request->input('is_lingerie') : 2),
                ($request->has('dummy_data') ? $request->input('dummy_data') : ''),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * get dummy details for a given countryId and dummyId (optional)
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function dummyDetails(Request $request)
    {
        $this->authorize('view', [$this->authorizedModel]);

        request()->validate([
            'country_id' => 'required|integer|exists:countries,id',
            'dummy_id' => 'nullable|integer'
        ]);

        return response($this->obj->dummyDetails(
            $request->input('country_id'),
            ($request->has('dummy_id') ?
                ((($request->input('dummy_id') == null) ? 0 : $request->input('dummy_id'))):
                0)
        ));
    }

    /**
     * create or update DummyInterface
     *
     * @param DummyRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function createOrUpdateDummy(DummyRequest $request)
    {
        //check if the action is create then apply create permission-----
        if ($request->input('dummy_id') == null){
            $this->authorize('create', [$this->authorizedModel]);
        }else{
            $this->authorize('update', [$this->authorizedModel]);
        }

        return response($this->obj->createOrUpdate($request->all()));
    }
}
