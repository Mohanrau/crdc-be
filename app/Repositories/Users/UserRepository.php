<?php
namespace App\Repositories\Users;

use App\{
    Helpers\Traits\ResourceRepository,
    Interfaces\General\CwSchedulesInterface,
    Interfaces\Users\UserInterface,
    Models\Authorizations\Role,
    Models\General\CWSchedule,
    Models\Locations\Country,
    Models\Users\Guest,
    Models\Locations\Entity,
    Models\Users\User,
    Models\Users\UserType,
    Notifications\Users\WelcomeNotification,
    Repositories\BaseRepository
};
use Carbon\Carbon;
use Illuminate\{
    Http\Request,
    Support\Facades\Auth,
    Support\Facades\Hash,
    Support\Facades\Route,
    Support\Facades\DB,
    Support\Facades\Config};
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\Constraint\Count;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Response;
use League\OAuth2\Server\AuthorizationServer;

class UserRepository extends BaseRepository implements UserInterface
{
    use ResourceRepository{
        create as baseCreate;
    }

    /**
     * @var User
     */
    protected
        $roleObj,
        $userTypeObj,
        $countryObj,
        $with = null,
        $authorizationServer,
        $guestModel,
        $entityObj,
        $saleObj,
        $cwScheduleModelObj,
        $cwScheduleObj,
        $stockistCommissionObj
    ;

    /**
     * UserRepository constructor.
     *
     * @param User $model
     * @param Role $role
     * @param UserType $userType
     * @param Country $country
     * @param AuthorizationServer $server
     * @param Guest $guestModel
     * @param Entity $entity
     * @param CWSchedule $cwSchedule
     * @param CwSchedulesInterface $cwScheduleInterface
     * @param StockistCommission $stockistCommission
     */
    public function __construct(
        User $model,
        Role $role,
        UserType $userType,
        Country $country,
        AuthorizationServer $server,
        Guest $guestModel,
        Entity $entity,
        CWSchedule $cwSchedule,
        CwSchedulesInterface $cwScheduleInterface,
        StockistCommission $stockistCommission
    )
    {
        parent::__construct($model);

        $this->roleObj = $role;

        $this->userTypeObj = $userType;

        $this->countryObj = $country;

        $this->authorizationServer = $server;

        $this->guestModel = $guestModel;

        $this->entityObj = $entity;

        $this->cwScheduleModelObj = $cwSchedule;

        $this->cwScheduleObj = $cwScheduleInterface;

        $this->stockistCommissionObj = $stockistCommission;

    }

    /**
     * store new role Group
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->modelObj->create($data);

        //TODO remove the bellow code
        // $user->notify(new WelcomeNotification($user));
        //Notification::send($user, new WelcomeNotification($user));
    }

    /**
     * verify user access info and send back token
     *
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $client = DB::table('oauth_clients')
            ->where('password_client',1)
            ->first();

        if(filter_var($request->input('email'), FILTER_VALIDATE_EMAIL))
        {
            $userDetail = $this->modelObj
                ->where('email', $request->input('email'))
                ->first();
        }
        else
        {
            $userDetail = $this->modelObj
                ->where('old_member_id', $request->input('email'))
                ->where('old_member_id', '>', 0)
                ->first();
        }

        $email = ($userDetail) ? $userDetail->email :
            $request->input('email');

        $this->validatePassword($userDetail, $request->input('password'));

        $http = new \GuzzleHttp\Client;

        $response = $http->post(Config('app.url').'/oauth/token', [
            'form_params' => [
                'username' => $email,
                'password' => $request->input('password'),
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'scope' => '*'
            ],
        ]);

        if($response->getStatusCode() == 200)
        {
            if(($userDetail->login_count < 1) or ($userDetail->login_count >= 2))
            {
                $userDetail->login_count = $userDetail->login_count + 1;
            }

            $userDetail->save();
        }

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * Validate and convert old ibs password to nibs password
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function validatePassword(User $user, string $password)
    {
        $hashedValue = $user->getAuthPassword();

        if(Hash::needsRehash($hashedValue) && $hashedValue === md5( md5($password).$user->salt ) )
        {
            $user->password = bcrypt($password);
            $user->salt = null;
            $user->save();
        }

        return Hash::check($password, $user->getAuthPassword());
    }

    /**
     * Change Password
     *
     * @param int $userId
     * @param string $password
     * @return mixed
     */
    public function updatePassword(int $userId, string $password)
    {
        $this->modelObj
            ->where('id', $userId)
            ->update([
                'password' => bcrypt($password),
                'login_count' => 2
            ]);

        return $this->modelObj->find($userId);
    }

    /**
     * refresh user token
     *
     * @param Request $request
     * @return mixed
     */
    public function refreshToken(Request $request)
    {
        //get the oauth client for a password grant.
        $client = DB::table('oauth_clients')
            ->where('password_client',1)
            ->first();

        $request->request->add([
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->input('refresh_token'),
            'client_id' => $client->id,
            'client_secret' => $client->secret,
        ]);

        $proxy = Request::create('/oauth/token', 'POST');

        return Route::dispatch($proxy);
    }

    /**
     * get one user by id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * list all user types
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function listUserTypes()
    {
        return $this->userTypeObj->all();
    }

    /**
     * get users listing by filters
     *
     * @param string|null $search
     * @param int $userTypeId
     * @param array $locationIds
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return \Illuminate\Support\Collection|mixed
     */
    public function getUsersByFilters(
        string $search = null,
        int $userTypeId = 0,
        array $locationIds = [],
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    )
    {
        $data = $this->modelObj
            ->orderBy($this->modelObj->getTable().'.'.$orderBy, $orderMethod);

        if (!is_null($search)){
            $data =  $data
                ->where(function ($query) use ($search) {
                    $query
                        ->where('users.name', 'like', '%' . $search . '%')
                        ->orWhere('users.email', 'like', '%' . $search . '%');
                });
        }
        //check the user type -------------------------------------
        if ($userTypeId > 0) {
            //get the user type data
            $data = $data
                ->leftJoin('user_type','users.id', '=', 'user_type.user_id')
                ->where('user_type.user_type_id', $userTypeId);
        }

        //check if locationIds set--------------------------------
        if (!empty($locationIds)) {
            $data = $data
                ->leftJoin('user_locations','users.id', '=', 'user_locations.user_id')
                ->whereIn('user_locations.location_id', $locationIds);
        }

        $totalRecords = collect(
            [
                'total' => $data->get()->count()
            ]
        );

        $data =  ($paginate > 0) ?
            $data ->offset($offset)->limit($paginate)->get() :
            $data->get();

        //attach type to each user
        collect($data)->each(function ($user){
            $user->user_type = $user->userType()->first();
        });

        return $totalRecords -> merge(['data' => $data]);
    }

    /**
     * update the user the privileges
     *
     * @param int $userId
     * @param array $roles
     * @param array $roleGroups
     * @param array $locationIds
     * @param $active
     * @return array|mixed
     */
    public function updateUserPrivileges(
        int $userId,
        array $roles,
        $active,
        array $roleGroups = [],
        array $locationIds = []
    )
    {
        $user = $this->find($userId);

        //attach role groups to the given user------
        $user->syncRoleGroups($roleGroups);

        //attach roles to the given user-----------
        $user->syncRoles($roles);

        $this->revokeLocationAccess($user, $roles, $locationIds);

        //TODO revoke all locations if role access revoked for the given user

        //attach locations to the given user------
        $user->attachLocations($locationIds);

        //set user active status-------------------
        $user->active = $active;

        $user->save();

        return $this->privilegeDetails($user->id);
    }

    /**
     * get privilege details for a given userId
     *
     * @param int $userId
     * @return array
     */
    public function privilegeDetails(int $userId)
    {
        $user = $this->find($userId);

        return [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'active' => $user->active,
            'role_ids' => $user->roles()->pluck('id')->toArray(),
            'role_groups_ids' => $user->roleGroups()->pluck('id')->toArray(),
            'location_ids' => $user->userLocations()->pluck('id')->toArray(),
        ];
    }

    /**
     * get the authenticated user profile and permissions lists
     *
     * @return mixed
     */
    public function getAuthUserProfile()
    {
        $user  = $this->find(Auth::id());

        $userType = $user->userType()->first();

        //attach the proper user type----------------------------------------
        $user->user_type = optional($userType)->name;

        $user->country_id = null;

        if (!is_null(optional($userType)->name)){
            if (
                $userType->name == config('mappings.user_types.back_office')
                or
                $userType->name == config('mappings.user_types.stockist_staff')
            ){
                $staff = $user->staff()->first();

                $user->staff = $staff;

                $user->country_id = $staff->country_id;

            }elseif ($userType->name == config('mappings.user_types.member')){
                $member = $user->member()->first();

                $user->member = $member;

                $user->country_id = $member->country_id;
            }elseif ($userType->name == config('mappings.user_types.stockist')){
                $stockist = $user->stockist()->first();

                $user->stockist = $stockist;

                $user->country_id = $stockist->country_id;
            }
        }

        //structure the rnp permissions -------------------------------------
        $roles = $user
            ->roles()
            ->with(['permissions.operation', 'permissions.module' ,'countries'])
            ->get();

        $permissionList = [];

        if ($roles->count() > 0)
        {
            foreach ($roles as $role){

                $countryObj = $role->countries->first();

                foreach ($role->permissions as $permission){

                    $moduleName = $permission->module->alias
                        .'.'.
                        $countryObj->id
                        .'.'.
                        strtolower($userType->name)
                        .'.module';

                    //add module name if not exists before.
                    if (!in_array($moduleName, $permissionList)){
                        $permissionList[] = $moduleName;
                    }

                    if ($permission->module->parent_id !=0){
                        //loop in the operations
                        $permissionList[] =
                            $permission->alias
                            .'.'.
                            $countryObj->id
                            .'.'.
                            strtolower($userType->name)
                            .'.'.
                            $permission->operation->name
                        ;
                    }else{
                        //loop in the operations
                        $permissionList[] = $permission->module->alias
                            .'.'.
                            $permission->alias
                            .'.'.
                            $countryObj->id
                            .'.'.
                            strtolower($userType->name)
                            .'.'.
                            $permission->operation->name
                        ;
                    }
                }
            }

            $user->permissions = $permissionList;
        }else{
            $user->permissions = null;
        }

        return $user;
    }

    /**
     * get locations list if user has access to location.list permission
     *
     * @param int $userId
     * @return array|mixed
     */
    public function checkUserRnpLocations(int $userId)
    {
        $user = $this->find($userId);

        $roles = $user
            ->roles()
            ->with('countries')
            ->whereHas('permissions', function ($query){
                $query->where('name', 'locations.list');
            })
            ->get();

        $data =  $countryIds = [];

        //TODO optimize the bellow part..................
        foreach ($roles as $role){
            $countryId = $role->countries()->pluck('id');

            if (!in_array($countryId, $countryIds)){
                $countryIds[] =  $countryId;

                $data[] = $this->countryObj
                    ->with('entity.locations')
                    ->whereIn('id', $countryId)
                    ->first()
                ;
            }
        }

        return ['data' => $data];
    }

    /**
     * get oauth access token
     *
     * @return array
     */
    public function getOauthAccessToken()
    {
        $authAccessToken = Auth::user()->Token()->id;

        return collect([
            'auth_access_token' => md5($authAccessToken)
        ]);
    }

    /**
     * Login As Guest
     *
     * @param null $referrer
     * @param null $medium
     * @return mixed
     * @throws \League\OAuth2\Server\Exception\OAuthServerException
     */
    public function loginAsGuest($referrer = null, $medium = null) {
        $user = $this->modelObj->where("email", Config::get('setting.guest.email'))->firstOrFail();

        Auth::guard('web')->login($user);

        $client = DB::table('oauth_clients')
            ->where('personal_access_client',1)
            ->first();

        // Make an internal request because personal_access grant_type is not enabled for security reasons
        $request = (new ServerRequest)->withParsedBody([
            'grant_type' => 'personal_access',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'user_id' => $user->id,
            'scope' => '*'
        ]);

        $response = $this->authorizationServer->respondToAccessTokenRequest(
            $request, new Response
        );

        return json_decode($response->getBody()->__toString(), true);
    }

    /**
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTokensReferrer() {
        return collect($this->guestModel
            ->where("token_id", Auth::user()->token()->id)
            ->with('referrer')
            ->firstOrFail()->referrer);
    }

    /**
     * get roles has location access
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Relations\BelongsToMany[]
     */
    private function getRoleHasLocationAccess(User $user)
    {
        return $user
            ->roles()
            ->with('countries')
            ->whereHas('permissions', function ($query){
                $query->where('name', 'locations.list');
            })
            ->get();
    }

    /**
     * revoke location access if roles does not have access
     *
     * @param User $user
     * @param array $roles
     * @param array $locationsIds
     */
    private function revokeLocationAccess(User $user, array $roles, array &$locationsIds)
    {
        $existingRoles = $user->roles()->pluck('id');

        $newRoles = collect($roles)->diff($existingRoles);

        $userLocations = $user->userLocations()->pluck('id');

        //if count== 0=> then no new roles assign so revoke all locations access
        if (count($roles) == 0){
            $user->userLocations()->detach($userLocations->toArray());

            $locationsIds = [];
        }
        else
        {
            $roleHasLocationAccess = $this->getRoleHasLocationAccess($user);

            if ($roleHasLocationAccess->count() == 0){
                $user->userLocations()->detach($userLocations->toArray());

                $locationsIds = [];
            }

            //TODO revoke the locations if roles countries not there
//            $revokedRoles = $existingRoles->diff($roles);
//
//            $newRoles->each(function ($role) use ($revokedRoles){
//            });
        }
    }

    /**
     * get back office dashboard data
     *
     * @param int $locationId
     * @param string $requestType
     * @param string $startDate
     * @param string $endDate
     * @return mixed
     * @throws \Exception
     */
    public function backOfficeDashBoard(
        int $locationId = 0,
        string $requestType = '',
        string $startDate = '',
        string $endDate = ''
    )
    {
        $data = [];

        if ($endDate == '')
        {
            $endDate = Carbon::now(config('app.timezone'))->addDay();
        }
        else
        {
            $endDate = Carbon::createFromTimestamp(strtotime($endDate.' 23:59:59'));
        }

        if ($startDate == '')
        {
            $startDate = Carbon::now(config('app.timezone'))->subDay(4);
        }
        else
        {
            $startDate = Carbon::createFromTimestamp(strtotime($startDate.' 00:00:00'));
        }

        $interval = new \DateInterval('P1D');

        $dateRange = new \DatePeriod($startDate, $interval, $endDate );

        foreach($dateRange as $date)
        {
            $recentSales['dates'][] = $date->format('d M Y');

            $recentSales['values'][$date->format('d M Y')] = $this->saleObj->where('transaction_location_id', $locationId)
                ->where('transaction_date', $date->format('Y/m/d'))
                ->sum('total_amount');
        }

        if(Auth::user()->isUserType('Stockist'))
        {
            $stockist = Auth::user()->stockist;

            if($startDate != '' && $endDate != '')
            {
                $cwSchedules = $this->cwScheduleModelObj->where('date_from', '>=', $startDate)->where('date_to', '<=', $endDate)->get()->toArray();
            }
            else
            {
                $currentCWName = $this->cwScheduleObj->getCwSchedulesList('current')->get('data')->toArray()[0]['cw_name'];

                $cwSchedules = $this->cwScheduleObj->getCwSchedulesList('custom_current_past', [
                    'custom_cw_name' => $currentCWName,
                    'sort' => 'id',
                    'order' => 'desc',
                    'offset' => 0,
                    'limit' => 5
                ])->get('data')->toArray();

                $cwSchedules = array_reverse($cwSchedules);
            }

            foreach ($cwSchedules as $cwSchedule)
            {
                $commissions['cw_names'][] = $cwSchedule['cw_name'];

                $stockistCommission = $this->stockistCommissionObj->where('cw_id', $cwSchedule['id'])->where('stockist_id', $stockist->id)->first();

                $commissions['values'][ $cwSchedule['cw_name'] ] = isset($stockistCommission) ? $stockistCommission->total_nett_amount : 0;
            }
        }

        if($requestType == 'recent_sales')
        {
            $data['recent_sales'] = $recentSales;
        }
        elseif ($requestType == 'commissions')
        {
            if(isset($commissions))
            {
                $data['commissions'] = $commissions;
            }
        }
        else
        {
            $data['recent_sales'] = $recentSales;

            if(isset($commissions))
            {
                $data['commissions'] = $commissions;
            }
        }

        return collect($data);
    }
}