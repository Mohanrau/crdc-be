<?php
namespace App\Rules\General;

use App\Interfaces\Settings\SettingsInterface;
use App\Models\Locations\Country;
use Illuminate\Contracts\Validation\Rule;

class CountryGIROTypesExists implements Rule
{
    private $countryObj, $settingsObj;

    /**
     * Create a new rule instance.
     *
     * @param Country $country
     * @param SettingsInterface $settings
     */
    public function __construct(Country $country, SettingsInterface $settings)
    {
        $this->countryObj = $country;

        $this->settingsObj = $settings;
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
        $countryCode = $this->countryObj->find($value)->code_iso_2;

        $giroTypes = collect(json_decode($this->settingsObj->getSettingDataByKey(['giro_type'])['giro_type'][0]->value));

        if($giroTypes->has($countryCode))
        {
            return $value;
        }
        else
        {
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
        return trans('validation.country_giro_types_exists');
    }
}
