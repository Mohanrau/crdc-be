<?php
namespace App\Rules\Locations;

use App\Models\Locations\ZonePostcode;
use Illuminate\Contracts\Validation\Rule;

class ZonePostcodeUnique implements Rule
{
    private $zonePostcodeObj, $zoneId, $postcode;

    /**
     * Create a new rule instance.
     * 
     * @param ZonePostcode $zonePostcode
     * @param int $zoneId
     * @return void
     */
    public function __construct(ZonePostcode $zonePostcode, int $zoneId)
    {
        $this->zonePostcodeObj = $zonePostcode;

        $this->zoneId = $zoneId;
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
        $postcodes = [];

        if (!empty($value)) {
            foreach($value as $zonePostcode) {
                if (!empty($zonePostcode['id'])) {
                    $zonePostcodeId = $zonePostcode['id'];
                }
                else {
                    $zonePostcodeId = null;
                }
                if (in_array($zonePostcode['postcode'], $postcodes)) {
                    $this->postcode = $zonePostcode['postcode'];
                    return false;
                }
                else {
                    $recordCount = $this->zonePostcodeObj
                        ->where('zone_id', $this->zoneId)
                        ->where('id', '!=', $zonePostcodeId)
                        ->where('postcode', $zonePostcode['postcode'])
                        ->count(); 
                        
                    if ($recordCount > 0) {
                        $this->postcode = $zonePostcode['postcode'];
                        return false;
                    }
                    else {
                        array_push($postcodes, $zonePostcode['postcode']);
                    }
                }
            }
        }
        
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('message.zone.postcode-duplicated', [
            "postcode" => $this->postcode
        ]);
    }
}
