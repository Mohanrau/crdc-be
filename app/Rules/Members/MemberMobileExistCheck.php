<?php
namespace App\Rules\Members;

use App\{
    Models\Locations\Country,
    Models\Users\User
};
use Illuminate\Contracts\Validation\Rule;

class MemberMobileExistCheck implements Rule
{
    private
        $userObj,
        $countryObj,
        $countryId;

    /**
     * Create a new rule instance.
     *
     * @param User $user
     * @param Country $country
     * @param int|null $countryId
     */
    public function __construct(
        User $user,
        Country $country,
        int $countryId = null)
    {
        $this->userObj = $user;

        $this->countryObj = $country;

        $this->countryId = $countryId;
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
        if (is_null($this->countryId)){
            return false;
        }else{
            $country = $this->countryObj->find($this->countryId);

            $mobile = $this->userObj->whereHas('member.contactInfo', function($query) use ($country, $value){
                $query->where('mobile_1_country_code_id', $country->id)
                    ->where('mobile_1_num', $value);
            })->first();

            if (is_null($mobile)) {
                return false;
            }

            return true;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.mobile.not_exists');
    }
}
