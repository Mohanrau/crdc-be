<?php
namespace App\Helpers\Traits;

use Illuminate\Support\Facades\Gate;

trait GeneralPolicy
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
        if (! Gate::allows($this->moduleName.'.list')){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can create sales.
     *
     * @return mixed
     */
    public function create()
    {
        if (! Gate::allows($this->moduleName.'.create')){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @return bool
     */
    public function view()
    {
        //check if user has access
        if (! Gate::allows($this->moduleName.'.view')){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the sale.
     *
     * @return mixed
     */
    public function update()
    {
        if (! Gate::allows($this->moduleName.'.update')){
            return false;
        }

        return true;
    }
}