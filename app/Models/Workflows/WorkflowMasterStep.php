<?php
namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use App\Models\Workflows\WorkflowMaster;
use App\Helpers\Traits\HasAudit;

class WorkflowMasterStep extends Model
{
    use HasAudit;

    protected $table = 'workflow_master_step';
    
    protected $fillable = [
        'master_id',
        'sequence',
        'step',
        'name',
        'last_step',
        'step_data'
    ];

    /**
     * get workflow master model
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowMaster(){
        return $this->belongsTo(WorkflowMaster::class, 'master_id', 'id');
    }
}
