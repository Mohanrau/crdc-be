<?php
namespace App\Models\Authorizations;

use App\Helpers\Traits\HasAudit;
use App\Models\Locations\Country;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasAudit;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'label',
        'active',
    ];

    /**
     * get the roleGroups for a given roleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roleGroups()
    {
        return $this->belongsToMany(RoleGroup::class, 'role_group_roles');
    }

    /**
     * return permissions for a given roleObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * Attach permissions for roleObj
     *
     * @param Permission $permission
     * @return array
     */
    public function attachPermission(Permission $permission)
    {
        return $this->permissions()->sync($permission);
    }

    /**
     * get countries roles for a given role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function countries()
    {
        return $this->belongsToMany(Country::class, 'country_roles')
            ->withTimestamps();
    }
}
