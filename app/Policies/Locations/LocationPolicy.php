<?php
namespace App\Policies\Locations;

use App\{
    Models\Locations\Location,
    Models\Users\User
};
use Illuminate\{
    Auth\Access\HandlesAuthorization,
    Support\Facades\Gate,
    Support\Facades\Request
};

class LocationPolicy
{
    use HandlesAuthorization;

    private
        $modelObj,
        $requestObj,
        $moduleName,
        $modelId
    ;

    /**
     * LocationPolicy constructor.
     *
     * @param Location $location
     */
    public function __construct(Location $location)
    {
        $this->modelObj = $location;

        $this->requestObj = Request::all();

        $this->moduleName = 'locations';

        $this->modelId = 'id';
    }

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
     * Determine whether the user can create resource.
     *
     * @return mixed
     */
    public function create()
    {
        if (! Gate::allows($this->moduleName.'.create', $this->getCountryId())){
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param Location $location
     * @return bool
     */
    public function view(User $user, Location $location)
    {
        //ckeck if user has access
        if (! Gate::allows($this->moduleName.'.view', $location->entity->country->id)){
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

    /**
     * get country id
     *
     * @param string $section
     * @return mixed
     */
    private function getCountryId(string $section = null)
    {
        return $this->requestObj['country_id'];
    }
}
