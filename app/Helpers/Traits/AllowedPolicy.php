<?php
namespace App\Helpers\Traits;

trait AllowedPolicy
{
    /**
     * check if the user is superAdmin then no need to check the authorization
     *
     * @param $user
     * @param $ability
     * @return bool
     */
    public function before($user, $ability)
    {
        if ($user->isRootUser()) {
            return true;
        }
    }

    /**
     * check the authorization for the listing page
     *
     * @return bool
     */
    public function listing()
    {
        return true;
    }

    /**
     * Determine whether the user can update the resource.
     *
     * @return mixed
     */
    public function update()
    {
        return true;
    }

    /**
     * Determine whether the user can delete the resource.
     *
     * @return mixed
     */
    public function delete()
    {
        return true;
    }
}