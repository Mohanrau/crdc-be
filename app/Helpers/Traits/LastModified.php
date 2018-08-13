<?php
namespace App\Helpers\Traits;

use App\Models\Users\User;

trait LastModified
{
    /**
     * get last modified by for given model
     *
     * @return mixed
     */
    public function getLastModifiedByAttribute()
    {
        if ($this->updated_by != null)
        {
            return optional(User::find($this->updated_by))->name;
        }
        else if ($this->created_by != null) {
            return optional(User::find($this->created_by))->name;
        }
        else {
            return null;
        }
    }

    /**
     * get last modified at for given model
     *
     * @return mixed
     */
    public function getLastModifiedAtAttribute()
    {
        if ($this->attributes['updated_at'] != null)
        {
            return $this->attributes['updated_at'];
        }
        else {
            return $this->attributes['created_at'];
        }
    }
}