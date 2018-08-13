<?php
namespace App\Http\Controllers\V1\General;

use App\{
    Helpers\Traits\ResourceController,
    Interfaces\General\CwSchedulesInterface,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class CwSchedulesController extends Controller
{
    private $obj;

    /**
     * CwSchedulesController constructor.
     *
     * @param CwSchedulesInterface $cwSchedulesInterface
     */
    public function __construct(CwSchedulesInterface $cwSchedulesInterface)
    {
        $this->middleware('auth');

        $this->obj = $cwSchedulesInterface;
    }

    /**
     * Retrieve CW Schedule Data List by filter type
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function cwSchedulesList(Request $request)
    {
        $this->validate($request, [
            'filter_type' => '
            required|string|
            in:current_past,past,current,current_back_date,next,future,current_future,custom,custom_past,custom_current_past,custom_future,custom_current_future,all',
        ]);

        return response(
            $this->obj->getCwSchedulesList(
                $request->input('filter_type'),
                array(
                    'custom_cw_name' => ($request->has('custom_cw_name') ? $request->input('custom_cw_name') : null),
                    'limit' => ($request->has('limit') ? $request->input('limit') : 0),
                    'sort' => ($request->has('sort') ? $request->input('sort') :  'cw_name'),
                    'order' => ($request->has('order') ? $request->input('order') : 'asc'),
                    'offset' => ($request->has('offset') ? $request->input('offset') :  0)
                )
            )
        );
    }

    /**
     * Retrieve enrollment rank Data List by filter type
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function enrollmentRanksList(Request $request)
    {
        return response(
            $this->obj->getEnrollmentRanksList(
                array(
                    'limit' => ($request->has('limit') ? $request->input('limit') : 0),
                    'sort' => ($request->has('sort') ? $request->input('sort') :  'id'),
                    'order' => ($request->has('order') ? $request->input('order') : 'desc'),
                    'offset' => ($request->has('offset') ? $request->input('offset') :  0)
                )
            )
        );
    }

    /**
     * Retrieve team bonus rank Data List by filter type
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function teamBonusRanksList(Request $request)
    {
        return response(
            $this->obj->getTeamBonusRanksList(
                array(
                    'limit' => ($request->has('limit') ? $request->input('limit') : 0),
                    'sort' => ($request->has('sort') ? $request->input('sort') :  'id'),
                    'order' => ($request->has('order') ? $request->input('order') : 'desc'),
                    'offset' => ($request->has('offset') ? $request->input('offset') :  0)
                )
            )
        );
    }
}
