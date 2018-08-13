<?php
namespace App\Http\Requests\Locations;

use App\Models\Locations\Country;
use App\Models\Locations\Entity;
use App\Rules\ForeignBelongTo;
use Illuminate\{
    Foundation\Http\FormRequest,
    Validation\Rule
};

class LocationRequest extends FormRequest
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
     * @param Entity $entity
     * @return array
     */
    public function rules(Entity $entity)
    {
        $id = isset($this->segments()[3])? $this->segments()[3] : '';

        return [
            'country_id' => 'required|integer|exists:countries,id',
            'entity_id' => [
                'required',
                'integer',
                'exists:entities,id',
                new ForeignBelongTo(
                    $entity,
                    'country_id',
                    $this->input('country_id'),
                    $this->input('entity_id')
                )
            ],
            'name' => ['required','min:3','max:255', Rule::unique('locations', 'name')->ignore($id),],
            'active' => 'required|boolean',
            'address.location_id' => 'sometimes|required|exists:locations,id|same:id',
            'address.address_data' => 'sometimes|required'
        ];
    }
}
