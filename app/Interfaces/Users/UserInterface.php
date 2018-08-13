<?php
namespace App\Interfaces\Users;

use App\{
    Interfaces\BaseInterface,
    Models\Users\User
};
use Illuminate\Http\Request;

interface UserInterface extends BaseInterface
{
    /**
     * User login using laravel passport
     *
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request);

    /**
     * Validate and convert old ibs password to nibs password
     *
     * @param User $user
     * @param string $password
     * @return bool
     */
    public function validatePassword(User $user, string $password);

    /**
     * Change Password
     *
     * @param int $userId
     * @param string $password
     * @return mixed
     */
    public function updatePassword(int $userId, string $password);

    /**
     * user refresh token using laravel passport
     *
     * @param Request $request
     * @return mixed
     */
    public function refreshToken(Request $request);

    /**
     * list all user types
     *
     * @return mixed
     */
    public function listUserTypes();

    /**
     * get users by filters
     *
     * @param string|null $search
     * @param int $userTypeId
     * @param array $locationIds
     * @param int $paginate
     * @param string $orderBy
     * @param string $orderMethod
     * @param int $offset
     * @return mixed
     */
    public function getUsersByFilters(
        string $search = null,
        int $userTypeId = 0,
        array $locationIds = [],
        int $paginate = 20,
        string $orderBy = 'id',
        string $orderMethod = 'desc',
        int $offset = 0
    );

    /**
     * attach roles and role groups to a given userId
     *
     * @param int $userId
     * @param array $roles
     * @param array $roleGroups
     * @param array $locationIds
     * @param $active
     * @return mixed
     */
    public function updateUserPrivileges(
        int $userId,
        array $roles,
        $active,
        array $roleGroups = [],
        array $locationIds = []
    );

    /**
     * get privilege details for a given userId
     *
     * @param int $userId
     * @return mixed
     */
    public function privilegeDetails(int $userId);

    /**
     * get the authenticated user profile and permissions lists
     *
     * @return mixed
     */
    public function getAuthUserProfile();

    /**
     * check if user has access to locations listing permission
     *
     * @param int $userId
     * @return mixed
     */
    public function checkUserRnpLocations(int $userId);

    /**
     * get oauth access token
     *
     * @return array
     */
    public function getOauthAccessToken();

    /**
     * get back office dashboard data
     *
     * @param int $locationId
     * @param string $requestType
     * @param string $startDate
     * @param string $endDate
     * @return mixed
     */
    public function backOfficeDashBoard(
        int $locationId = 0,
        string $requestType = '',
        string $startDate = '',
        string $endDate = ''
    );

    /**
     * login as guest user
     *
     * @param null $referrer
     * @param null $medium
     * @return mixed
     */
    public function loginAsGuest($referrer = null, $medium = null);

    /**
     * get token for referrer
     *
     * @return mixed
     */
    public function getTokensReferrer();
}