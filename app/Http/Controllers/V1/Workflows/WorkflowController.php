<?php
namespace App\Http\Controllers\V1\Workflows;

use App\{
    Interfaces\Workflows\WorkflowInterface,
    Http\Controllers\Controller
};
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    private $obj;

    /**
     * WorkflowController constructor.
     *
     * @param WorkflowsInterface $workflowInterface
     */
    public function __construct(WorkflowInterface $workflowInterface)
    {
        $this->middleware('auth');

        $this->obj = $workflowInterface;
    }

    /**
     * get tracking workflow details
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getTrackingWorkflowDetails(Request $request)
    {
        request()->validate([
            'workflow_tracking_id' => 'required|exists:workflow_tracking,id'
        ]);

        return response(
            $this->obj->getTrackingWorkflowDetails(
                $request->input('workflow_tracking_id')
            )
        );
    }

    /**
     * update workflow tracking step
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function updateWorkflowTrackingStep(Request $request)
    {
        request()->validate([
            'workflow_tracking_step_id' => 'required|exists:workflow_tracking_step,id'
        ]);

        return response(
            $this->obj->updateWorkflowTrackingStep(
                $request->input('workflow_tracking_step_id'),
                $request->has('tracking_step_input')? $request->input('tracking_step_input') : '',
                $request->has('workflow_remark')? $request->input('workflow_remark') : ''
            )
        );
    }

}