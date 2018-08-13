<?php
namespace App\Http\Controllers\V1\Staff;

use App\{
    Http\Requests\Staff\StaffRequest,
    Interfaces\Staff\StaffInterface,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class StaffController extends Controller
{
    private $obj;

    /**
     * StaffController constructor.
     *
     * @param StaffInterface $staffInterface
     */
    public function __construct(StaffInterface $staffInterface)
    {
        $this->middleware('auth');

        $this->obj = $staffInterface;
    }

    /**
     * register new staff
     *
     * @param StaffRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function store(StaffRequest $request)
    {
        return response($this->obj->registerStaff($request->all()));
    }

    /**
     * get staff details for a given id
     *
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function show(int $id)
    {
        return response($this->obj->staffDetails($id));
    }
}
