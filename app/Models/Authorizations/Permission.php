<?php
namespace App\Models\Authorizations;

use App\{
    Helpers\Traits\HasAudit, Models\Modules\Module, Models\Modules\Operation
};
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Permission extends Model
{
    use HasAudit;

    protected $table = 'permissions';

    protected $fillable = [
        'module_id',
        'operation_id',
        'name',
        'label',
        'alias'
    ];

    /**
     * return module info for a given permissionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * return roles for a given permissionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * get the operation details for a given permissionObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function operation()
    {
        return $this->belongsTo(Operation::class);
    }

    /**
     * create or update permission based on the module
     *
     * @param Module $module
     * @param Operation $operation
     * @return mixed
     */
    public function createPermission(Module $module, Operation $operation)
    {
        return
            Auth::user()
            ->createdBy($this)
            ->updateOrCreate([
                'module_id' => $module->id,
                'operation_id' => $operation->id,
                'name' => strtolower(trim(str_replace(' ','_',$operation->name .' '. $module->label))),
                'label' => ucwords($operation->name .' '. $module->label)
            ]);
    }
}
