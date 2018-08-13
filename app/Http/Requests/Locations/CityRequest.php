<?php
namespace App\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;

class CityRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'country_id' =>'required|integer|exists:countries,id',
            'state_id' =>'required|integer|exists:states,id',
            'name' => 'required','min:3','max:191',
            'active' => 'sometimes|required|boolean'
        ];
    }
}