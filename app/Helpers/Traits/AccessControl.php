<?php
namespace App\Helpers\Traits;

use App\Models\{
    Locations\Country,
    Locations\Location,
    Users\User
};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

trait AccessControl
{
    /**
     * check location listing access check
     *
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    private function locationListingAccessCheck()
    {
        if ($this->isUser('back_office') or $this->isUser('stockist') or $this->isUser('stockist_staff'))
        {
            if (Auth::user()->cant('listing', [Location::class])){
                abort(403, trans('message.user.locations_access_forbidden'));
            }
        }

        return true;
    }

    /**
     * check if user has permission to do listing or search
     *
     * @param $model
     * @return bool
     */
    private function applyListingOrSearchPermission($model)
    {
        if (Auth::user()->can('listing', $model) or Auth::user()->can('search', $model)){
            return true;
        }else{
            abort(403, trans('message.user.un_authorized'));
        }
    }

    /**
     * check if user has permission to do search or view
     *
     * @param Model $model
     * @return bool
     */
    private function applySearchOrViewPermission($model)
    {
        if (Auth::user()->can('view', $model) or Auth::user()->can('search', $model)){
            return true;
        }else{
            abort(403, trans('message.user.un_authorized'));
        }
    }

    /**
     * check if user can access to a given resource for a given location id
     *
     * @param int $locationId
     * @param int|null $downLineMemberId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response|void
     */
    private function resourceLocationAccessCheck(int $locationId, int $downLineMemberId = null)
    {
        if ($this->isUser('back_office') or $this->isUser('stockist') or $this->isUser('stockist_staff'))
        {
            if(! in_array($locationId, $this->getUserGrantedLocations()->toArray()))
            {
                abort(403, trans('message.user.locations_access_forbidden'));
            }
        }
        elseif ($this->isUser('member'))
        {
            if(Auth::user()->id !== $downLineMemberId)
            {
                abort(403, trans('message.user.resource_not_belong'));
            }
        }

        return;
    }

    /**
     * apply location
     *
     * @param $query
     * @param int $countryId
     * @param string $locationColumn
     */
    private function applyLocationQuery($query, int $countryId, string $locationColumn)
    {
        if ($this->isUser('back_office') or $this->isUser('stockist') or $this->isUser('stockist_staff'))
        {
            return $query
                ->whereIn($locationColumn, $this->getUserLocations($countryId));
        }

        return;
    }

    /**
     * check if this resource belong to the given user
     *
     * @param int $userId
     * @param string $type
     * @return bool
     */
    private function resourceBelongToMe(int $userId, string $type = 'member')
    {
        if ($this->isUser($type))
        {
            if(Auth::id() !== $userId)
            {
                abort(403, trans('message.user.resource_not_belong'));
            }
        }

        return true;
    }

    /**
     * Check if this resource belongs to the logged in users down line
     *
     * This method requires App\Interface\MemberTreeInterface to be injected and available in $this->memberTreeRepository,
     * it will use the repository method verifyMemberTreeDownline to verify down line.
     * By default this check only tests user type 'member'
     *
     * @param int $downLineUserId
     * @param string $type
     * @return bool
     */
    private function resourceBelongToMyDownLine(int $downLineUserId, string $type = 'member')
    {
        if ($this->isUser($type))
        {
            if
            (
                Auth::id() !== $downLineUserId
                and
                !$this->memberTreeRepository->verifyMemberTreeDownline("sponsor", Auth::id(), $downLineUserId)['result']
            )
            {
                abort(403, trans('message.user.resource_not_belong'));
            }
        }

        return true;
    }

    /**
     * get Auth user countries based on RNP
     *
     * @return mixed
     */
    private function getAuthorizedCountries()
    {
        $roles = Auth::user()
            ->roles()
            ->with('countries')
            ->get();

        $countriesIds = [];

        foreach ($roles as $role){
            $countriesIds[] = $role->countries()->pluck('id');
        }

        return array_flatten($countriesIds);
    }

    /**
     * get locations list based on user type
     *
     * @param Country $country
     * @param int $countryId
     * @return mixed
     */
    private function getLocationsByCountry(Country $country, int $countryId)
    {
        if ($this->isUser('back_office') or $this->isUser('stockist') or $this->isUser('stockist_staff'))
        {
            $locations = $country
                ->entity()
                ->first()
                ->locationsRnp($countryId)
                ->get();
        }else{
            $locations = $country
                ->entity()
                ->first()
                ->locations()
                ->get();
        }

        return $locations;
    }

    /**
     * get auth user locations based on RNP
     *
     * @param int|null $countryId
     * @param string $column
     * @return mixed
     */
    private function getUserLocations(int $countryId = null, string $column = 'id')
    {
        if (is_null($countryId)){
            return $this->getUserGrantedLocations();
        }

        return
            Country::findOrFail($countryId)
            ->entity()
            ->first()
            ->locations()
            ->whereIn('id', $this->getUserGrantedLocations())
            ->pluck($column);
    }

    /**
     * get the auth user granted locations by rnp
     *
     * @return mixed
     */
    private function getUserGrantedLocations()
    {
        return Auth::user()->userLocations()->pluck('id');
    }

    /**
     * check if user match the give type
     *
     * @param string $type
     * @return bool
     */
    private function isUser(string $type) : bool
    {
        if (Auth::check()){
            return Auth::user()->isUserType(config('mappings.user_types.'.$type));
        }

        return false;
    }

    /**
     * get stockist user data
     *
     * @param string|null $column
     * @return mixed
     */
    private function getStockistUser(string $column = null)
    {
        $stockist = Auth::user()->stockist()->first();

        if (!is_null($column)){
            return $stockist->$column;
        }

        return $stockist;
    }

    /**
     * get stockist parent user if auth user is stockist staff
     *
     * @return mixed
     */
    private function getStockistParentUser()
    {
        return User::find(Auth::user()->staff()->first()->stockist_user_id)->id;
    }

    /**
     * get parent stockist location
     *
     * @return mixed
     */
    private function getStockistParentLocation()
    {
        return User::find(Auth::user()->staff()->first()->stockist_user_id)
            ->stockist()
            ->first()
            ->stockistLocation()
            ->pluck('id')
            ->toArray();
    }

    /**
     * get countries has the given permission
     *
     * @param string $permission
     * @return array
     */
    private function getUserRolesCountriesForPermission(string $permission) : array
    {
        $roles = Auth::user()
            ->roles()
            ->whereHas('permissions', function($query) use($permission){
                $query->where('name', $permission);
            })
            ->get();

        $countries = [];

        $roles->each(function ($item) use(&$countries) {
            $countries[] = $item->countries()->pluck('id');
        });

        return $countries;
    }

    /**
     * check user self resource access to skip country check if resource belong to this user type
     *
     * @param string $userType
     * @param string $permission
     * @param null $model
     * @return bool
     */
    private function checkUserTypeSelfResource(string $userType, string $permission, $model = null)
    {
        //check if user is member
        if ($this->isUser($userType)){
            //check if user has access
            if (! Gate::allows($this->moduleName.'.'.$permission)){
                return false;
            }
        }else{
            if (is_null($model)){
                $countryId = $this->getCountryId($permission);
            }else{
                $countryId = $model->country_id;
            }

            //check if user has access
            if (! Gate::allows($this->moduleName.'.'.$permission, $countryId)){
                return false;
            }
        }

        return true;
    }
}