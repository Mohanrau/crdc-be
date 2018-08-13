<?php
namespace App\Interfaces\Workflows;

interface WorkflowInterface
{
    /**
     * get current workflow step by tracking Id
     *
     * @param int $trackingId
     * @return mixed
     */
    public function getTrackingWorkflowDetails(int $trackingId);

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
    public function copyWorkflows(int $mappingId, string $mappingTable, int $workflowMasterId, int $userId);

    /**
     * List out workflow steps by workflow code
     *
     * @param string $workflowCode
     * @return mixed
     */
    public function listWorkflowSteps(string $workflowCode);

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
    );
}