<?php
namespace App\Http\Controllers\V1\Authorizations;

use App\{
    Helpers\Traits\ResourceController,
    Http\Controllers\Controller,
    Http\Requests\Authorizations\RoleGroupRequest,
    Interfaces\Authorizations\RoleGroupInterface,
    Models\Authorizations\RoleGroup
};
use Illuminate\{
    Http\Request, Support\Facades\Auth, Support\Facades\Config
};

class RoleGroupController extends Controller
{
    use ResourceController;

    private
        $obj,
        $authorizedModel,
        $recordsCount;

    /**
     * RoleGroupController constructor.
     *
     * @param RoleGroupInterface $roleGroupInterface
     * @param RoleGroup $model
     */
    public function __construct(RoleGroupInterface $roleGroupInterface, RoleGroup $model)
    {
        $this->middleware('auth');

        $this->obj = $roleGroupInterface;

        $this->recordsCount = Config::get('setting.records');

        $this->authorizedModel = $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RoleGroupRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(RoleGroupRequest $request)
    {
        $this->authorize('create', [$this->authorizedModel]);

        return response($this->obj->create($request->all()));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RoleGroupRequest $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(RoleGroupRequest $request, $id)
    {
        $this->authorize('update', [$this->authorizedModel]);

        return response(
            $this->obj->update($request->all(), $id)
        );
    }

    /**
     * attach or revoke roles from roleGroup
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function attachRolesToRoleGroup(Request $request)
    {
        $this->authorize('update', [$this->authorizedModel]);

        request()->validate([
            'role_group_id' => 'required|integer|exists:role_groups,id',
            'role_ids' => 'array|nullable|exists:roles,id'
        ]);

        $roleGroupId = $request->input('role_group_id');

        $roles = $request->input('role_ids');

        return response($this->obj->attachRoles($roles,$roleGroupId));
    }
}
