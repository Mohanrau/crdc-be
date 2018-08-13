<?php
namespace App\Http\Controllers\V1\Modules;

use App\{
    Http\Controllers\Controller,
    Models\Modules\Operation
};
use Illuminate\Http\Request;

class OperationController extends Controller
{
    private $modelObj;

    /**
     * OperationController constructor.
     *
     * @param Operation $operation
     */
    public function __construct(Operation $operation)
    {
        $this->middleware('auth');

        $this->modelObj = $operation;
    }

    /**
     * get all operations lists
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return response(['data' => $this->modelObj->all()]);
    }
}
