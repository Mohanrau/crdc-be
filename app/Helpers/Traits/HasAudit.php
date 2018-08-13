<?php
namespace App\Helpers\Traits;

use App\Models\Users\User;

trait HasAudit
{
    /**
     * return created by - user details for a given roleGroupObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * return update by - user info for a given roleGroupObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class,'updated_by');
    }

    /**
     * check if tax active
     *
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}