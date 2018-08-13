<?php
namespace App\Rules\Users;

use App\Models\{
    Locations\Country,
    Users\User
};
use Illuminate\Contracts\Validation\Rule;

class MobileExistCheck implements Rule
{
    private
        $userObj,
        $countryObj,
        $countryId,
        $userId;

    /**
     * MobileExistCheck constructor.
     *
     * @param User $user
     * @param Country $country
     * @param int $countryId
     * @param int $userId
     */
    public function __construct(
        User $user,
        Country $country,
        int $countryId = null,
        int $userId = null)
    {
        $this->userObj = $user;

        $this->countryObj = $country;

        $this->countryId = $countryId;

        $this->userId = $userId;
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
        if (is_null($this->countryId)) {
            return true;
        } else {
            $country = $this->countryObj->find($this->countryId);

            //ignore given userId
            if (!is_null($this->userId)) {
                $mobile = $this->userObj
                    ->whereNotIn('id', [$this->userId])
                    ->where('mobile', $country->call_code . $value)
                    ->first();
            } else {
                $mobile = $this->userObj
                    ->where('mobile', $country->call_code . $value)
                    ->first();
            }

            if (is_null($mobile)) {
                return true;
            }

            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.mobile.exists');
    }
}
