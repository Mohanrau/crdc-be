<?php
namespace App\Http\Controllers\V1\Authorizations;

use App\{
    Http\Controllers\Controller,
    Helpers\Traits\ResourceController,
    Http\Requests\Authorizations\RoleRequest,
    Interfaces\Authorizations\RoleInterface,
    Models\Authorizations\Role
};
use Illuminate\{
    Http\Request
};

class RoleController extends Controller
{
    use ResourceController {
        show as oldShow;
    }

    private
        $obj,
        $authorizedModel
    ;

    /**
     * RoleController constructor.
     *
     * @param RoleInterface $roleRepository
     * @param Role $model
     */
    public function __construct(RoleInterface $roleRepository, Role $model)
    {
        $this->middleware('auth');

        $this->obj = $roleRepository;

        $this->authorizedModel = $model;
    }

    /**
     * @param RoleRequest $request
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(RoleRequest $request)
    {
        $this->authorize('create', [$this->authorizedModel]);

        return response($this->obj->createOrUpdate($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show($id)
    {
        $this->authorize('view', $this->authorizedModel);

        return response($this->obj->roleDetails($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RoleRequest $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(RoleRequest $request, $id)
    {
        $this->authorize('update', [$this->authorizedModel]);

        return response(
            $this->obj->createOrUpdate($request->all(), $id)
        );
    }
}
