<?php
namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use App\Models\Workflows\WorkflowTracking;
use App\Models\Users\User;
use App\Helpers\Traits\HasAudit;

class WorkflowTrackingStep extends Model
{
    use HasAudit;

    protected $table = 'workflow_tracking_step';
    
    protected $fillable = [
        'workflow_tracking_id',
        'sequence',
        'step',
        'name',
        'last_step',
        'process_result',
        'remark',
        'updated_by',
        'step_data'
    ];

    /**
     * get workflow tracking detail by given workflowTrackingStepObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowTracking()
    {
        return $this->belongsTo(WorkflowTracking::class, 'workflow_tracking_id', 'id');
    }
}
