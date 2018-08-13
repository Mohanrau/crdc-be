<?php
namespace App\Rules\Locations;

use App\Models\Locations\Country;
use Illuminate\Contracts\Validation\Rule;

class CountryRelationRule implements Rule
{
    private
        $countryObj,
        $notExistsRelation
    ;

    /**
     * CountryRelationRule constructor.
     *
     * @param Country $country
     */
    public function __construct(Country $country)
    {
        $this->countryObj = $country;
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
        $status = true;

        foreach($value as $relation) {
            if (strpos($relation, '.') !== false) {
               $relation = explode('.', $relation)[0];
            }

            if (!method_exists($this->countryObj,$relation)){
                $status =  false;

                $this->notExistsRelation = $relation;

                break;
            }
        }

         return $status;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __(
            'message.country.not-exists-relation',
            [
                'name' => $this->notExistsRelation
            ]
        );
    }
}
