<?php
namespace App\Repositories\Staff;

use App\{
    Interfaces\Staff\StaffInterface,
    Models\Authorizations\RoleGroup,
    Models\Staff\Staff,
    Models\Users\User,
    Models\Users\UserType,
    Notifications\Users\StaffWelcomeNotification,
    Repositories\BaseRepository,
    Helpers\Classes\RandomPassword
};
use Illuminate\Database\Eloquent\Model;

class StaffRepository extends BaseRepository implements StaffInterface
{
    private
        $userObj,
        $userTypeObj,
        $roleGroupObj
    ;

    /**
     * StaffRepository constructor.
     *
     * @param Staff $model
     * @param User $user
     * @param UserType $userType
     * @param RoleGroup $roleGroup
     */
    public function __construct(
        Staff $model,
        User $user,
        UserType $userType,
        RoleGroup $roleGroup
    )
    {
        parent::__construct($model);

        $this->userObj = $user;

        $this->userTypeObj = $userType;

        $this->roleGroupObj = $roleGroup;
    }

    /**
     * register new staff
     *
     * @param array $data
     * @return array|mixed
     */
    public function registerStaff(array $data)
    {
        $randomPasswordClass = new RandomPassword();

        $password = $randomPasswordClass->generate(16);

        //create the user first---------------------------
        $user = $this->userObj->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($password),
            'first_time_login' => 0
        ]);

        //create staff user-------------------------------
        $staff = $user->staff()->create([
            'country_id' => $data['country_id'],
            'stockist_user_id' => isset($data['stockist_user_id']) ? $data['stockist_user_id'] : null ,
            'position' => (isset($data['position']) ? $data['position'] :  ''),
        ]);

        //attach user to the respective user type---------
        if (isset($data['stockist_user_id'])){
            $userType = $this->userTypeObj
                ->where('name', config('mappings.user_types.stockist_staff'))
                ->first();

            //attach role as same as his parent stockist
            $stockistUser = $this->userObj->find($data['stockist_user_id']);

            $user->syncRoles($stockistUser->roles()->pluck('id')->toArray());

        }else{
            $userType = $this->userTypeObj
                ->where('name', config('mappings.user_types.back_office'))
                ->first();

            //attach role groups to the user------------------
            if (count($data['role_groups_ids'])){
                $user->syncRoleGroups($data['role_groups_ids']);
            }

            //attach role to the user-------------------------
            if (count($data['role_ids'])){
                $user->syncRoles($data['role_ids']);
            }
        }

        $user->userType()->sync([$userType->id]);

        //notify the user --------------------------------
       $user->notify(new StaffWelcomeNotification($user, $password));

        return $this->staffDetails($staff->id);
    }

    /**
     * get staff details for a given id
     *
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->modelObj->findOrFail($id);
    }

    /**
     * get staff details for a given id
     *
     * @param int $staffId
     * @return array
     */
    public function staffDetails(int $staffId)
    {
        $staff = $this->find($staffId);

        $user = $staff->user()->first();

        $roleIds = $user->roles()->pluck('id')->toArray();

        $roleGroupsIds = $user->roleGroups()->pluck('id')->toArray();

        return [
            'id' => $staff->id,
            'stockist_user_id' => $staff->stockist_user_id,
            'country_id' => $staff->country_id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->email,
            'position' => $staff->position,
            'active' => $user->active,
            'role_groups_ids' => $roleGroupsIds,
            'role_ids' => $roleIds,
            'user' => $user
        ];
    }
}