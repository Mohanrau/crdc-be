<?php
namespace App\Http\Controllers\V1\Users;

use App\{
    Models\Users\UserType,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class UsersTypeController extends Controller
{
    private $modelObj;

    /**
     * UsersTypeController constructor.
     *
     * @param UserType $userType
     */
    public function __construct(UserType $userType)
    {
        $this->middleware('auth');

        $this->modelObj = $userType;
    }

    /**
     * get all user types list
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return response($this->modelObj->all());
    }
}
