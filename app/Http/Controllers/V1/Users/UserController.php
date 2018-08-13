<?php
namespace App\Http\Controllers\V1\Users;

use App\{
    Http\Requests\Users\LoginRequest,
    Http\Requests\Users\UserPrivilegeRequest,
    Http\Requests\Users\UserRegisterRequest,
    Interfaces\Users\UserInterface,
    Http\Controllers\Controller,
    Models\Users\User,
    Rules\Users\UserLocationAccess,
    Rules\Users\UserPasswordCheck
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private
        $obj,
        $authorizedModel
    ;

    /**
     * UserController constructor.
     *
     * @param UserInterface $userRepository
     * @param User $model
     */
    public function __construct(UserInterface $userRepository, User $model)
    {
        $this->middleware('auth', ['except' => ['login', 'register', 'refreshToken', 'guest']]);

        $this->obj = $userRepository;

        $this->authorizedModel = $model;
    }

    /**
     * get the authenticated user
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return response($this->obj->getAuthUserProfile());
    }

    /**
     * register new user
     *
     * @param UserRegisterRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function register(UserRegisterRequest $request)
    {
        $this->authorize('create', [$this->authorizedModel]);

        $userData = $request->all();

        $password = $request->input('password');
        //unset plain password
        unset($userData['password']);

        return response($this->obj->create(
            array_merge($userData, ['password' => bcrypt($password)])
        ));
    }

    /**
     * user login
     *
     * @param LoginRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function login(LoginRequest $request)
    {
        try
        {
            return response($this->obj->login($request));
        }
        catch(\Exception $e)
        {
            return ($e->getCode() == 401) ?
             response(array('error'=>trans('message.login.failed')),401) :
             response(array('error'=>$e->getMessage()),422) ;
        }
    }

    /**
     *
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function changePassword(Request $request)
    {
        request()->validate([
            'user_id' => 'required|integer|exists:users,id',
            'current_password' => [
                'required',
                'string',
                'min:6',
                new UserPasswordCheck($this->authorizedModel, $request->input('user_id'))
            ],
            'new_password' => 'required|confirmed|string|min:6'
        ]);


        return response($this->obj->updatePassword(
            $request->input('user_id'),
            $request->input('new_password')
        ));
    }

    /**
     * get the refreshed token
     *
     * @param Request $request
     * @return mixed
     */
    public function refreshToken(Request $request)
    {
        request()->validate([
            'refresh_token' => 'required|string'
        ]);

        return $this->obj->refreshToken($request);
    }

    /**
     * list all user types
     *
     * @return mixed
     */
    public function getUserTypes()
    {
        return response($this->obj->listUserTypes());
    }

    /**
     * get list of products filtered by countryId and categoryId
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function filterUsers(Request $request)
    {
        //check the authorization
        $this->authorize('listing', [$this->authorizedModel]);

        request()->validate([
            'user_type_id' => 'sometimes|integer|exists:user_types,id',
        ]);

        return response(
            $this->obj->getUsersByFilters(
                ($request->has('search') ? $request->input('search') : null),
                ($request->has('user_type_id') ? $request->input('user_type_id') : 0),
                ($request->has('location_ids') ? $request->input('location_ids') : []),
                ($request->has('limit') ? $request->input('limit') : 20),
                ($request->has('sort') ? $request->input('sort') :  'id'),
                ($request->has('order') ? $request->input('order') : 'desc'),
                ($request->has('offset') ? $request->input('offset') :  0)
            )
        );
    }

    /**
     * update user privileges for a given user
     *
     * @param UserPrivilegeRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updatePrivileges(UserPrivilegeRequest $request)
    {
        //check the authorization
        $this->authorize('update', [$this->authorizedModel]);

        //TODO check the given locations belong to user countries enabled by rnp -JALALA

        return response($this->obj->updateUserPrivileges(
            $request->input('user_id'),
            $request->input('role_ids'),
            $request->input('active'),
            ($request->has('role_groups_ids') ? $request->input('role_groups_ids') : []),
            ($request->has('location_ids') ? $request->input('location_ids') : [])
        ));
    }

    /**
     * get user privilege details for a given userId
     *
     * @param int $userId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function userPrivilegeDetails(int $userId)
    {
        //check the authorization
        $this->authorize('view', [$this->authorizedModel]);

        return response($this->obj->privilegeDetails($userId));
    }

    /**
     * get locations lists if user given has access to location.list permission
     *
     * @param int $userId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function checkUserLocationsAccess(int $userId)
    {
        return response($this->obj->checkUserRnpLocations($userId));
    }

    /**
     * get oauth access token
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getOauthAccessToken()
    {
        return response($this->obj->getOauthAccessToken());
    }

    /**
     * Login as guest
     *
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function guest()
    {
        try
        {
            return response($this->obj->loginAsGuest());
        }
        catch(\Exception $e)
        {
            return ($e->getCode() == 401 || $e->getCode() == 0) ?
                response(array('error'=>trans('message.guest.failed')),401) :
                response(array('error'=>$e->getMessage()),422) ;
        }
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function referrer()
    {
        if ($referrer = $this->obj->getTokensReferrer()) {
            if (Auth::user()->isGuest()) {
                $referrer = $referrer->only('name');
            }
        }
        return response($referrer);
    }

    /**
     * get user dashboard data
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function userDashboard(Request $request)
    {
        request()->validate([
            'location_id' => 'sometimes|integer|nullable|exists:locations,id',
            'request_type' => 'sometimes|in:recent_sales,commissions|nullable',
            'start_date' => 'sometimes|date|nullable',
            'end_date' => 'sometimes|date|nullable'
        ]);

        return response($this->obj->backOfficeDashBoard(
            $request->has('location_id') ? $request->input('location_id') : 0,
            $request->has('request_type') ? $request->input('request_type') : '',
            $request->has('start_date') ? $request->input('start_date') : '',
            $request->has('end_date') ? $request->input('end_date') : ''
        ));
    }
}
