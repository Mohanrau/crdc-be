<?php
namespace App\Http\Requests\Locations;

use App\Models\Locations\Zone;
use App\Models\Locations\ZonePostcode;
use App\Models\Locations\ZoneStockLocation;
use App\Rules\Locations\ZonePostcodeUnique;
use App\Rules\Locations\ZoneStockLocationUnique;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ZoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @param Zone $zone
     * @param ZonePostcode $zonePostcode
     * @param ZoneStockLocation $zoneStockLocation
     * @return array
     */
    public function rules(
        Zone $zone,
        ZonePostcode $zonePostcode,
        ZoneStockLocation $zoneStockLocation
    )
    {
        if (!empty($this->input('id'))) {
            $ignoredId = $this->input('id');
            $zoneId = $this->input('id');
        }
        else {
            $ignoredId = 'NULL';
            $zoneId = 0;
        }

        return [
            'id' => [
                'bail', 'sometimes', 'integer', 'nullable', 'exists:zones,id'
            ],
            'code' => 'required|string|min:3|max:10|unique:zones,code,' . $ignoredId . ',id',
            'name' => 'required|string|min:3|max:191',
            'countries' => 'sometimes|array',
            'states' => 'sometimes|array',
            'cities' => 'sometimes|array',
            'zone_postcodes' => [
                'sometimes', 'array', 
                new ZonePostcodeUnique($zonePostcode, $zoneId)
            ],
            'zone_stock_locations' => [
                'required', 'array', 'min:1',
                new ZoneStockLocationUnique($zoneStockLocation, $zoneId)
            ],
            'countries.*' => 'required|integer|exists:countries,id',
            'states.*' => 'required|integer|exists:states,id',
            'cities.*' => 'required|integer|exists:cities,id',
            'zone_postcodes.*.postcode' => 'required|string|min:3|max:191',
            'zone_stock_locations.*.effective_date' => 'required|date',
            'zone_stock_locations.*.expiry_date' => 'required|date',
            'zone_stock_locations.*.stock_location_id' => 'required|integer|exists:locations,id'
        ];
    }
}