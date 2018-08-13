<?php
namespace App\Models\Modules;

use App\Helpers\Traits\HasAudit;
use App\Models\Authorizations\Permission;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasAudit;

    protected $table = 'modules';

    protected $fillable = [
        'parent_id',
        'label',
        'name',
        'alias',
        'description',
        'active'
    ];

    /**
     * get the permissions for a given moduleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * get the modules operations for a given module object
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function operations()
    {
        return $this->belongsToMany(Operation::class,'module_operations')
            ->withTimestamps();
    }

    /**
     * get the module parent info for a given module
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Module::class,'parent_id');
    }

    /**
     * get the current module childs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function childs()
    {
        return $this->hasMany(Module::class, 'parent_id');
    }
}
