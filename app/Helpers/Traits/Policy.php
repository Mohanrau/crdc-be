<?php
namespace App\Helpers\Traits;

use Illuminate\Support\Facades\Gate;

trait Policy
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
        if (! Gate::allows($this->moduleName.'.list', $this->getCountryId())){
            return false;
        }

        return true;
    }

    /**
     * check the authorization for the listing page
     *
     * @return bool
     */
    public function search()
    {
        if (! Gate::allows($this->moduleName.'.search', $this->getCountryId())){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can create resource.
     *
     * @return mixed
     */
    public function create()
    {
        if (! Gate::allows($this->moduleName.'.create', $this->getCountryId('create'))){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the resource.
     *
     * @return mixed
     */
    public function view()
    {
        $model = $this->modelObj->find($this->requestObj[$this->modelId]);

        //check if user has access
        if (! Gate::allows($this->moduleName.'.view', $model->country_id)){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can update the resource.
     *
     * @return mixed
     */
    public function update()
    {
        if (! Gate::allows($this->moduleName.'.update', $this->getCountryId('update'))){
            return false;
        }

        return true;
    }
}