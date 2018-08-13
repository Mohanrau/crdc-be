<?php
namespace App\Models\Workflows;

use Illuminate\Database\Eloquent\Model;
use App\Models\Workflows\WorkflowMasterStep;
use App\Helpers\Traits\HasAudit;

class WorkflowMaster extends Model
{
    use HasAudit;

    protected $table = 'workflow_master';
    
    protected $fillable = [
        'name',
        'code',
        'active'
    ];

    /**
     * get workflow steps
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function steps()
    {
        return $this->hasMany(WorkflowMasterStep::class, 'master_id', 'id');
    }

    /**
     * Scope a query to only include active users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}
