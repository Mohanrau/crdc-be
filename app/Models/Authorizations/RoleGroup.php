<?php
namespace App\Models\Authorizations;

use App\{
    Helpers\Traits\HasAudit,
    Models\Users\UserType
};
use Illuminate\Database\Eloquent\Model;

class RoleGroup extends Model
{
    use HasAudit;

    protected $table = 'role_groups';

    protected $fillable = [
        'user_type_id',
        'title',
        'active',
        'created_by_id',
        'updated_by_id',
        'expiry_date'
    ];

    protected $dates = ['expiry_date'];

    /**0
     * return userType info for a given RoleGroupObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    /**
     * get the roleGroup Roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_group_roles');
    }
}
