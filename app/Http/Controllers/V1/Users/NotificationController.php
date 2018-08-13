<?php
namespace App\Http\Controllers\V1\Users;

use App\{
    Models\Notifications\Tracking,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private $trackingObj;

    /**
     * NotificationController constructor.
     *
     * @param Tracking $tracking
     */
    public function __construct(Tracking $tracking)
    {
        $this->middleware('auth');

        $this->trackingObj = $tracking;
    }

    /**
     * get all notifications mails
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $paginate = $request->has('limit') ? $request->input('limit') : 0 ;

        $orderBy = $request->has('sort') ? $request->input('sort') :  'id';

        $orderMethod = $request->has('order') ? $request->input('order') : 'desc';

        $offset = $request->has('offset') ? $request->input('offset') :  0;

        $totalRecords = collect(
            [
                'total' => $this->trackingObj->orderBy($orderBy, $orderMethod)->count()
            ]
        );

        $data = $this->trackingObj
            ->with('user')
            ->orderBy($orderBy, $orderMethod);

        $data = ($paginate) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        return response($totalRecords -> merge(['data' => $data]));

    }
}
