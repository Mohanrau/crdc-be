<?php

namespace App\Rules\Users;

use App\Models\Users\User;
use Illuminate\Contracts\Validation\Rule;

class UserExistCheck implements Rule
{
    protected $user;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        if(filter_var($value, FILTER_VALIDATE_EMAIL))
        {
            $userDetail = $this->user
                ->where('email', $value)
                ->first();
        }
        else
        {
            $userDetail = $this->user
                ->where('old_member_id', $value)
                ->where('old_member_id', '>', 0)
                ->first();
        }

        if($userDetail)
        {
            return $value;
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
        return trans('message.login.wrong-email');
    }
}
