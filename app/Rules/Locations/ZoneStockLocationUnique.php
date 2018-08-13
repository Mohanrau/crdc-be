<?php
namespace App\Rules\Locations;

use App\Models\Locations\ZoneStockLocation;
use Illuminate\Contracts\Validation\Rule;

class ZoneStockLocationUnique implements Rule
{
    private $zoneStockLocationObj, $zoneId, $effectiveDate, $expiryDate;

    /**
     * Create a new rule instance.
     * 
     * @param ZoneStockLocation $zoneStockLocation
     * @param int $zoneId
     * @return void
     */
    public function __construct(ZoneStockLocation $zoneStockLocation, int $zoneId)
    {
        $this->zoneStockLocationObj = $zoneStockLocation;

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
        $effectiveDates = [];

        if (!empty($value)) {
            foreach($value as $zoneStockLocation) {
                if (!empty($zoneStockLocation['id'])) {
                    $zoneStockLocationId = $zoneStockLocation['id'];
                }
                else {
                    $zoneStockLocationId = null;
                }
                if (in_array($zoneStockLocation['effective_date'], $effectiveDates)) {
                    $this->effectiveDate = $zoneStockLocation['effective_date'];
                    $this->expiryDate = $zoneStockLocation['expiry_date'];
                    return false;
                }
                else {
                    $recordCount = $this->zoneStockLocationObj
                        ->where('zone_id', $this->zoneId)
                        ->where('id', '!=', $zoneStockLocationId)
                        ->where('effective_date', $zoneStockLocation['effective_date'])
                        ->count(); 
                        
                    if ($recordCount > 0) {
                      $this->effectiveDate = $zoneStockLocation['effective_date'];
                      $this->expiryDate = $zoneStockLocation['expiry_date'];
                        return false;
                    }
                    else {
                        array_push($effectiveDates, $zoneStockLocation['effective_date']);
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
        return trans('message.zone.stock-location-duplicated', [
            "effectiveDate" => $this->effectiveDate,
            "expiryDate" => $this->expiryDate
        ]);
    }
}
