<?php
namespace App\Http\Controllers\V1\Integrations;

use App\{
    Interfaces\Integrations\YonyouInterface,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class YonyouController extends Controller
{
    private $obj;

    /**
     * YonyouController constructor.
     *
     * @param YonyouInterface $yonyouInterface
     */
    public function __construct(YonyouInterface $yonyouInterface)
    {
        $this->middleware('auth');

        $this->obj = $yonyouInterface;
    }

    //TODO implement RNP
    //This route should be accessible by root user only
    /**
     * get yonyou integration log listing
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getYonyouIntegrationLogList(Request $request)
    {
        request()->validate([
            'job_type' => 'sometimes|nullable|string',
            'mapping_model' => 'sometimes|nullable|string',
            'mapping_id' => 'sometimes|nullable|integer',
            'request_data' => 'sometimes|nullable|string',
            'response_data' => 'sometimes|nullable|string',
            'return_code' => 'sometimes|nullable|string',
            'create_date_from' => 'sometimes|nullable|date',
            'create_date_to' => 'sometimes|nullable|date',
            'exclude_json_data' => 'sometimes|nullable|integer|min:0|max:1',
            'limit' => 'sometimes|nullable|integer',
            'sort' => 'sometimes|nullable|string',
            'order' => 'sometimes|nullable|string',
            'offset' => 'sometimes|nullable|integer'
        ]);

        return response(
            $this->obj->getYonyouIntegrationLogsByFilters(
                ($request->has('job_type') ? $request->input('job_type') : ''),
                ($request->has('mapping_model') ? $request->input('mapping_model') : ''),
                ($request->has('mapping_id') ? $request->input('mapping_id') : 0),
                ($request->has('request_data') ? $request->input('request_data') : ''),
                ($request->has('response_data') ? $request->input('response_data') : ''),
                ($request->has('return_code') ? $request->input('return_code') : ''),
                ($request->has('create_date_from') ? $request->input('create_date_from') : ''),
                ($request->has('create_date_to') ? $request->input('create_date_to') : ''),
                ($request->has('exclude_json_data') ? $request->input('exclude_json_data') : 0),
                ($request->has('limit') ? $request->input('limit') : 0),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    //TODO implement RNP
    //This route should be accessible by root user only
    /**
     * retry failed yonyou integration
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function retryFailedYonyouIntegration(Request $request)
    {
        request()->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:yy_integration_logs,id'
        ]);
        
        return response(
            $this->obj->retryFailedYonyouIntegration($request->input('ids'))
        );        
    }
    
}