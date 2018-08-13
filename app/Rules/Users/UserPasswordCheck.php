<?php
namespace App\Rules\Users;

use App\Models\Users\User;

use Illuminate\{
    Contracts\Validation\Rule,
    Support\Facades\Hash
};

class UserPasswordCheck implements Rule
{
    private $userObj, $userId;

    /**
     * Create a new rule instance.
     *
     * @param User $user
     */
    public function __construct(User $user, int $userId = null)
    {
        $this->userObj = $user;
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
        if($this->userId)
        {
            $userInfo = $this->userObj->find($this->userId);

            if (Hash::check($value, $userInfo->password))
            {
                return $value;
            }
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
        return trans('validation.password_check');
    }
}
