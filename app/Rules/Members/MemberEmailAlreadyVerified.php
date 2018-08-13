<?php
namespace App\Rules\Members;

use App\Models\Members\MemberContactInfo;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class MemberEmailAlreadyVerified implements Rule
{
    private $memberContactInfoObj;

    /**
     * Create a new rule instance.
     *
     * @param MemberContactInfo $memberContactInfo
     */
    public function __construct(MemberContactInfo $memberContactInfo)
    {
        $this->memberContactInfoObj = $memberContactInfo;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $memberContact = $this->memberContactInfoObj->where("user_id", Auth::id())
            ->where("email", $value)
            ->where('email_verified', 1);

        if ($memberContact->count())
        {
            return false;
        }

        return $value;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.member_email_validated');
    }
}
