<?php
namespace App\Repositories\Workflows;

use App\{
    Interfaces\Workflows\WorkflowInterface,
    Models\Workflows\WorkflowTracking,
    Models\Workflows\WorkflowMaster,
    Models\Workflows\WorkflowTrackingStep,
    Models\Workflows\WorkflowMasterStep,
    Repositories\BaseRepository
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Event;

class WorkflowRepository extends BaseRepository implements WorkflowInterface
{

    private $workflowMasterStepObj, $workflowTrackingObj, $workflowTrackingStepObj;

    /**
     * WorkflowRepository constructor.
     *
     * @param WorkflowMaster $model
     * @param WorkflowMasterStep $workflowMasterStep
     * @param WorkflowTracking $workflowTracking
     * @param WorkflowTrackingStep $workflowTrackingStep
     */
    public function __construct(
        WorkflowMaster $model,
        WorkflowMasterStep $workflowMasterStep,
        WorkflowTracking $workflowTracking,
        WorkflowTrackingStep $workflowTrackingStep
    )
    {
        parent::__construct($model);

        $this->workflowMasterStepObj = $workflowMasterStep;

        $this->workflowTrackingObj = $workflowTracking;

        $this->workflowTrackingStepObj = $workflowTrackingStep;
    }

    /**
     * get current workflow step by tracking Id
     *
     * @param int $trackingId
     * @return mixed
     */
    public function getTrackingWorkflowDetails(int $trackingId)
    {
        $tracking = $this->workflowTrackingObj
            ->whereId($trackingId)
            ->with('steps.updatedBy', 'steps.createdBy',
                'user', 'createdBy', 'updatedBy')
            ->first();

        collect($tracking->steps)
            ->map(function ($stepDetail){
                $stepDetail->step_data = json_decode($stepDetail->step_data, true);

                return $stepDetail;
            });

        $step = $this->getCurrentStep($trackingId, $tracking->is_complete);

        $step->step_data = json_decode($step->step_data, true);

        return [
            'workflow' =>
            [
                'current_step' => $step,
                'name' => $tracking->name,
                'code' => $tracking->code,
                'workflow_master_id' => $tracking->workflow_master_id,
                'workflow_tracking_id' => $tracking->id,
                'user_id' => $tracking->user_id,
                'mapping_id' => $tracking->mapping_id,
                'mapping_table' => $tracking->mapping_table,
                'created_at' => $tracking->created_at,
                'created_by' => $tracking->createdBy,
                'completed_at' => $tracking->is_complete? $tracking->updated_at : '',
                'updated_by' => $tracking->updatedBy,
                'completion_status' => $tracking->is_complete,
                'rejected_status' => $tracking->is_rejected,
                'rejected_reason' => $tracking->rejected_reason,
                'steps' => $tracking->steps
            ]
        ];
    }

    /**
     * get workflow tracking current steps based on workflow tracking id and completion status
     *
     * @param int $trackingId
     * @param int $isComplete
     * @return mixed
     */
    private function getCurrentStep(int $trackingId, int $isComplete)
    {
        if ($isComplete){
            return $this->workflowTrackingStepObj
                ->whereWorkflowTrackingId($trackingId)
                ->whereLastStep(1)
                ->first();
        }

        return $this->workflowTrackingStepObj
            ->whereWorkflowTrackingId($trackingId)
            ->whereNull('process_result')
            ->orderBy('sequence')
            ->first();
    }

    /**
     * Copy workflow steps from workflow master table for tracking purpose
     * This method does not update any workflow tracking record.
     *
     * @param int $mappingId
     * @param string $mappingTable
     * @param int $workflowMasterId
     * @param int $userId
     * @return mixed
     */
    public function copyWorkflows(int $mappingId, string $mappingTable, int $workflowMasterId, int $userId)
    {
        $workflow =  $this->modelObj
            ->whereId($workflowMasterId)
            ->active()
            ->with('steps')
            ->first();

        $trackingMaster = [
            'workflow_master_id' => $workflow->id,
            'name' => $workflow->name,
            'code' => $workflow->code,
            'mapping_id' => $mappingId,
            'mapping_table' => $mappingTable,
            'user_id' => $userId
        ];

        $tracking = Auth::user()->createdBy($this->workflowTrackingObj)->create($trackingMaster);

        foreach($workflow->steps as $step){
            $data = [
                'workflow_tracking_id' => $tracking->id,
                'sequence' => $step->sequence,
                'step' => $step->step,
                'name' => $step->name,
                'last_step' => $step->last_step,
                'step_data' => $step->step_data
            ];

            Auth::user()->createdBy($this->workflowTrackingStepObj)->create($data);
        }

        return ['workflow' => ['workflow_tracking_id' => $tracking->id] ];
    }

    /**
     * List out workflow steps by workflow code
     *
     * @param string $workflowCode
     * @return mixed
     */
    public function listWorkflowSteps(string $workflowCode)
    {
        $workflow = $this->modelObj
            ->whereCode($workflowCode)
            ->active()
            ->with('steps')
            ->first();

        collect($workflow->steps)
            ->map(function ($stepDetail){
                $stepDetail->step_data = json_decode($stepDetail->step_data, true);

                return $stepDetail;
            });

        return $workflow;
    }

    /**
     * Update workflow
     *
     * @param int $trackingStepId
     * @param array $trackingStepInput
     * @param string $workflowRemark
     * @return mixed
     */
    public function updateWorkflowTrackingStep(
        int $trackingStepId,
        array $trackingStepInput = array(),
        string $workflowRemark = NULL
    )
    {
        //Get Tracking Step Information
        $trackingStepDetail = $this->workflowTrackingStepObj->find($trackingStepId);

        //Get Tracking Information
        $trackingDetail = $this->workflowTrackingObj->find($trackingStepDetail->workflow_tracking_id);

        //Verify tracking step process result
        $processResult = $trackingStepDetail->process_result;

        if(empty($processResult) && !$trackingDetail->is_complete && !$trackingDetail->is_rejected){

            //Update step to process
            $trackingStepDetail->update(
                array(
                    'remark' => $workflowRemark,
                    'process_result' => 1,
                    'updated_by' => Auth::id()
                )
            );

            //Call event
            $stepDatas = json_decode($trackingStepDetail->step_data);

            $eventClassName = $stepDatas->eventClass;

            $eventResult = Event::fire(new $eventClassName($trackingDetail->mapping_id, $trackingStepInput));

            //Set Process Result
            (isset($eventResult[0]['errors'])) ? $eventUpdateResult = 2 : $eventUpdateResult = 3;

            //Step Revert
            $stepProcessRevert = false;

            if(isset($eventResult[0]['step_pending'])){
                if($eventResult[0]['step_pending'] == true){
                    $eventUpdateResult = NULL;
                    $stepProcessRevert = true;
                }
            }

            //Update step to status
            $trackingStepDetail->update(
                array(
                    'process_result' => $eventUpdateResult,
                    'updated_by' => Auth::id()
                )
            );

            if(!$stepProcessRevert){

                $completeWorkflow = false;

                //Set workflow to complete if result was fails
                if($eventUpdateResult == 2){
                    $trakingComplete = true;
                }

                //Verify is it next step is last step
                $nextTrackingStepDetails = $this->workflowTrackingStepObj
                    ->whereWorkflowTrackingId($trackingStepDetail->workflow_tracking_id)
                    ->whereNull('process_result')
                    ->orderBy('sequence')
                    ->first();

                if($nextTrackingStepDetails->last_step == 1){

                    //Set workflow to complete
                    $completeWorkflow = true;

                    //Update last step to complete
                    $lastStep = $this->workflowTrackingStepObj->find($nextTrackingStepDetails->id);

                    $lastStep->update(
                        array(
                            'process_result' => 3,
                            'updated_by' => Auth::id()
                        )
                    );
                }

                if(isset($trackingStepInput['end_workflow'])){
                    if($trackingStepInput['end_workflow']){

                        //Set workflow to complete
                        $completeWorkflow = true;

                        //Update last step to complete
                        $lastTrackingStepDetails = $this->workflowTrackingStepObj
                            ->whereWorkflowTrackingId($trackingStepDetail->workflow_tracking_id)
                            ->whereNull('process_result')
                            ->orderBy('sequence', 'desc')
                            ->first();

                        if(!empty($lastTrackingStepDetails)){
                            $lastStep = $this->workflowTrackingStepObj->find($lastTrackingStepDetails->id);

                            $lastStep->update(
                                array(
                                    'process_result' => 3,
                                    'updated_by' => Auth::id()
                                )
                            );
                        }
                    }
                }

                //Update master tracking to complete
                if($completeWorkflow){
                    $workflowData = $this->workflowTrackingObj->find($trackingStepDetail->workflow_tracking_id);

                    $workflowTrackingUpdateData = [
                        'is_complete' => 1,
                        'updated_by' => Auth::id()
                    ];

                    //Update rejected in tracking workflow
                    if(isset($trackingStepInput['is_rejection_step'])){
                        if($trackingStepInput['is_rejection_step']){
                            $workflowTrackingUpdateData['is_rejected'] = 1;

                            $workflowTrackingUpdateData['rejected_reason'] = $trackingStepInput['rejection_reason'];
                        }
                    }

                    $workflowData->update($workflowTrackingUpdateData);
                }

                if($eventResult[0]['workflow']){
                    $eventResult[0]['workflow'] = $this->getTrackingWorkflowDetails(
                        $trackingStepDetail->workflow_tracking_id
                    );
                }

                return collect([
                    'step_status' => true,
                    'even_result' => $eventResult
                ]);
            }
        }

        return collect(['step_status' => false]);
    }
}