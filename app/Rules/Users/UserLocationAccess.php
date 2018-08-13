<?php
namespace App\Rules\Users;

use App\Interfaces\Users\UserInterface;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserLocationAccess implements Rule
{
    private
        $userRepositoryObj,
        $userId;

    /**
     * UserLocationAccess constructor.
     *
     * @param UserInterface $userInterface
     * @param int $userId
     */
    public function __construct(UserInterface $userInterface, int $userId)
    {
        $this->userRepositoryObj = $userInterface;

        $this->userId = $userId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if(empty($value))
            return true;

        //escape checking if root user
        if (Auth::user()->isRootUser()) {
            return true;
        }

        //check the access if locationIds not empty
        if (count(
                $this->userRepositoryObj
                    ->checkUserRnpLocations($this->userId)['data']) > 0){
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.user.locations_access_forbidden');
    }
}
