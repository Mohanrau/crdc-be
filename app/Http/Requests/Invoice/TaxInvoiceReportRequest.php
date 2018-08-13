<?php
namespace App\Http\Requests\Invoice;

use Illuminate\{
    Foundation\Http\FormRequest,
    Support\Facades\Auth,
    Validation\Rule
};


class TaxInvoiceReportRequest extends FormRequest
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
            'country_id' => 'required',
            'location_ids' => 'present|array',
            'location_ids.*.location_id' => 'integer|exists:locations,id',
            'from_date' => 'required|date|before_or_equal:to_date',
            'to_date' => 'required|date',
            'from_cw' => 'integer|exists:cw_schedules,id',
            'to_cw' => 'integer|exists:cw_schedules,id|gte:from_cw',
            'ibo_ids' => 'present|array',
            'ibo_ids.*.ibo_id' => 'integer|exists:users,id'
        ];
    }

    public function messages()
    {
        return [
            'country_id' => 'country',
            'location_ids' => 'location',
            'from_date' => 'transaction date from',
            'to_date' => 'transaction date to',
            'from_cw' => 'Commission Week from',
            'to_cw' => 'Commission Week to',
            'ibo_ids' => 'IBO',
            'ibo_ids.*.ibo_id' => 'IBO'
        ];
    }
}