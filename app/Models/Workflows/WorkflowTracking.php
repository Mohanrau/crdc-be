<?php
namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use App\Models\{
    Workflows\WorkflowMaster,
    Workflows\WorkflowTrackingStep,
    Users\User
};
use App\Helpers\Traits\HasAudit;

class WorkflowTracking extends Model
{
    use HasAudit;

    protected $table = 'workflow_tracking';
    
    protected $fillable = [
        'workflow_master_id',
        'name',
        'code',
        'mapping_id',
        'mapping_table',
        'user_id',
        'is_complete',
        'is_rejected',
        'rejected_reason',
        'updated_by'
    ];

    /**
     * get user detail by given workflowTrackingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * get workflow master detail by given workflowTrackingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function workflowMaster()
    {
        return $this->belongsTo(WorkflowMaster::class, 'workflow_master_id', 'id');
    }

    /**
     * get tracking steps detail by given workflowTrackingObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function steps()
    {
        return $this->hasMany(WorkflowTrackingStep::class, 'workflow_tracking_id', 'id')
            ->orderBy('sequence');
    }
}
