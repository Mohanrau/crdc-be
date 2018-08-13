<?php

namespace App\Models\Users;

use App\Models\{
    Authorizations\Role,
    Authorizations\RoleGroup,
    EWallets\EWallet,
    Locations\Country,
    Locations\Location,
    Members\Member,
    Shop\Favorites,
    Staff\Staff,
    Stockists\Stockist
};
use App\Notifications\Users\ResetPasswordNotification;
use App\Helpers\Classes\UserIdentifier;
use Illuminate\{
    Notifications\Notifiable,
    Foundation\Auth\User as Authenticatable,
    Support\Facades\Auth,
    Support\Facades\Config
};
use Laravel\Passport\HasApiTokens;
use Symfony\Component\Translation\Loader\IcuDatFileLoader;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'old_ibs_user_id',
        'unique_login_id',
        'old_member_id',
        'mobile',
        'name',
        'email',
        'password',
        'salt',
        'active',
        'login_count'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'salt',
    ];

    /**
     * Unique identifier for user
     */
    private $identifier = null;

    /**
     * get the member details for a given userObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function member()
    {
        return $this->hasOne(Member::class);
    }

    /**
     * get the staff details for a given userObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * get user stockist details for a given userObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stockist()
    {
        return $this->hasOne(Stockist::class, 'stockist_user_id');
    }

    /**
     * get the e-wallet details for a given userObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function eWallet()
    {
        return $this->hasOne(EWallet::class, "user_id", "id");
    }

    /**
     * Get user favorites
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function favorites()
    {
        return $this->hasMany(Favorites::class, "user_id");
    }

    /**
     * get user locations for a given userObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userLocations()
    {
        return $this->belongsToMany(Location::class, 'user_locations')
            ->withTimestamps();
    }

    /**
     * return the userType for a given userObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userType()
    {
        return $this->belongsToMany(UserType::class, 'user_type', 'user_id', 'user_type_id');
    }

    /**
     * get authenticated user role groups for a given userObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roleGroups()
    {
        return $this->belongsToMany(RoleGroup::class, 'role_group_user')
            ->withTimestamps();
    }

    /**
     * get authenticated user roles for a given userObj
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * return all the data created by the given userObj
     *
     * @param $className
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdBy($className)
    {
        return $this->hasMany($className, 'created_by');
    }

    /**
     * return all the data updated by the given userObj
     *
     * @param $className
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function updatedBy($className)
    {
        return $this->hasMany($className, 'updated_by');
    }

    /**
     * return all data verified by the given userObj
     *
     * @param $className
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function verifiedBy($className)
    {
        return $this->hasMany($className, 'verified_by');
    }

    /**
     * check if the user is root user
     *
     * @return bool
     */
    public function isRootUser(): bool
    {
        return (Auth::user()->userType()->first()->name == 'root') ? true : false;
    }

    /**
     * check user type is match the given type
     *
     * @param string $type
     * @return bool
     */
    public function isUserType(string $type): bool
    {
        return (Auth::user()->userType()->first()->name == $type) ? true : false;
    }

    /**
     * check user type is guest
     *
     * @return bool
     */
    public function isGuest(): bool
    {
        return ($this->email == Config::get('setting.guest.email'));
    }

    /**
     * get the user identifier
     *
     * @param User|null $user
     * @param string|null $tokenId
     * @return UserIdentifier
     */
    public function identifier(User $user = null, string $tokenId = null): UserIdentifier
    {
        if (!$this->identifier) {
            $this->identifier = new UserIdentifier($user ?? $this, $tokenId);
        }

        return $this->identifier;
    }

    /**
     * check if user has the given role
     *
     * @param $role
     * @param int $countryId
     * @return bool
     */
    public function hasRole($role, int $countryId = 0)
    {
        //do the check if the role given as string
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        //check if we have country, check if this role with
        // a given permission attached to this countryId
        if ($countryId > 0) {
            $countryExists = false;

            foreach ($role as $userRole) {
                $result = $userRole->countries()
                    ->where('id', $countryId)
                    ->count();

                if ($result > 0)
                    $countryExists = true;
            }

            return
                (
                    $countryExists
                    and
                    !!$role->intersect($this->roles)->count()
                );
        }

        return !!$role->intersect($this->roles)->count();
    }

    /**
     * Attach a given Role for a user
     *
     * @param Role $role
     * @return array
     */
    public function attachRole(Role $role)
    {
        return $this->roles()->sync($role);
    }

    /**
     * attach the given rolesIds for a given user
     *
     * @param array $roles
     * @return array
     */
    public function syncRoles(array $roles)
    {
        return $this->roles()->sync($roles);
    }

    /**
     * attach role groups to the given userObj
     *
     * @param array $roleGroups
     * @return array
     */
    public function syncRoleGroups(array $roleGroups)
    {
        return $this->roleGroups()->sync($roleGroups);
    }

    /**
     * attach the given locationsIds for a given user
     *
     * @param array $locations
     * @return array
     */
    public function attachLocations(array $locations)
    {
        return $this->userLocations()->sync($locations);
    }

    /**
     * Send the password reset notification.
     *
     * @param string $otpCode
     * @param bool $isMember
     * @param string $language
     */
    public function sendPasswordResetEmail(string $otpCode, bool $isMember = false, string $language = null)
    {
        $this->notify(new ResetPasswordNotification($otpCode, $this->email, $this->name, $isMember, $language));
    }

    /**
     * Set Remember Token
     *
     * @param string $token
     */
    public function setRememberToken($token)
    {
        $this->remember_token = $token;
    }

    /**
     * get user obj of given email/old_member_id
     *
     * @param $request
     * @return mixed
     */
    public function getUser($request)
    {
        if (isset($request['email']))
        {
            $email = $request['email'];
            if (filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $userDetail = $this->where('email', $email)
                    ->first();
            }
            else
            {
                $userDetail = $this->where('old_member_id', $email)
                    ->where('old_member_id', '>', 0)
                    ->first();
            }
        }
        else
        {
            $country = Country::find($request['mobile_country_code_id']);

            $mobile = $request['mobile_num'];

            $userDetail = $this->whereHas('member.contactInfo', function ($query) use ($country, $mobile) {
                $query->where('mobile_1_country_code_id', $country->id)
                    ->where('mobile_1_num', $mobile);
            })->first();
        }

        return $userDetail;
    }
}
