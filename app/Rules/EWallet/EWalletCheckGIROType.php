<?php
namespace App\Rules\EWallet;

use App\Interfaces\Settings\SettingsInterface;
use Illuminate\Contracts\Validation\Rule;

class EWalletCheckGIROType implements Rule
{
    private $settingsObj;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(SettingsInterface $settings)
    {
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
        $allGiroTypes = collect(json_decode($this->settingsObj->getSettingDataByKey(['giro_type'])['giro_type'][0]->value));

        foreach ($allGiroTypes as $giroType)
        {
            if(collect($giroType)->contains($value))
            {
                return true;
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
        return trans('validation.in');
    }
}
