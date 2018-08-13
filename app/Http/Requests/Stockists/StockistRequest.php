<?php
namespace App\Http\Requests\Stockists;

use Illuminate\Foundation\Http\FormRequest;

class StockistRequest extends FormRequest
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
            'stockist_data.details.id' => 'sometimes|required|integer|exists:stockists,id',
            'stockist_data.details.member_user_id' => (!empty($this->input('stockist_data.details.id'))) ?
                'required|integer|exists:members,user_id|unique:stockists,member_user_id,' .
                    $this->input('stockist_data.details.id') :
                        'required|integer|exists:members,user_id|unique:stockists,member_user_id',
            'stockist_data.details.stockist_user_id' => (!empty($this->input('stockist_data.details.id'))) ?
                'required|integer|exists:stockists,stockist_user_id|unique:stockists,stockist_user_id,' .
                    $this->input('stockist_data.details.id') : 'nullable',
            'stockist_data.details.stockist_number' => (!empty($this->input('stockist_data.details.id'))) ?
                'required|max:10|unique:stockists,stockist_number,' .
                    $this->input('stockist_data.details.id') :
                        'required|max:10|unique:stockists,stockist_number|unique:locations,code',
            'stockist_data.details.country_id' => 'required|integer|exists:countries,id',
            'stockist_data.details.stockist_type_id' => 'required|integer|exists:master_data,id',
            'stockist_data.details.status_id' => 'required|integer|exists:master_data,id',
            'stockist_data.details.name' => 'required',
            'stockist_data.details.stockist_ratio' => 'required|numeric',
            'stockist_data.details.ibs_online' => 'required|boolean',
            'stockist_data.details.effective_date' => 'required|date',
            'stockist_data.details.remark' => (!empty($this->input('stockist_data.details.id'))) ?
                'required' : 'nullable',

            'stockist_data.business_address.contact_person' => 'required',
            'stockist_data.business_address.mobile_1_country_code_id' => 'required|exists:countries,id',
            'stockist_data.business_address.mobile_1_num' => 'required|min:4|max:20',
            'stockist_data.business_address.mobile_2_country_code_id' => 'sometimes|nullable|exists:countries,id',
            'stockist_data.business_address.mobile_2_num' => 'nullable|min:4|max:20',
            'stockist_data.business_address.telephone_office_country_code_id' => 'sometimes|nullable|exists:countries,id',
            'stockist_data.business_address.telephone_office_num' => 'nullable|min:4|max:20',
            'stockist_data.business_address.fax_country_code_id' => 'sometimes|nullable|exists:countries,id',
            'stockist_data.business_address.fax_num' => 'nullable|min:4|max:20',
            'stockist_data.business_address.email_1' => (!empty($this->input('stockist_data.details.id'))) ?
                'required|email|unique:users,email,' .
                    $this->input('stockist_data.details.stockist_user_id') :
                        'required|email|unique:users,email',
            'stockist_data.business_address.email_2' => 'nullable|email',
            'stockist_data.business_address.addresses' => 'required',

            'stockist_data.deposits.minimum_initial_deposit' => 'required|numeric',
            'stockist_data.deposits.maximum_initial_deposit' => 'required|numeric',
            'stockist_data.deposits.minimum_top_up_deposit' => 'required|numeric',
            'stockist_data.deposits.maximum_top_up_deposit' => 'required|numeric',
            'stockist_data.deposits.minimum_capping' => 'required|numeric',
            'stockist_data.deposits.credit_limit_1' => 'required|numeric',
            'stockist_data.deposits.credit_limit_2' => 'required|numeric',
            'stockist_data.deposits.credit_limit_3' => 'required|numeric',

            'stockist_data.banks.bank_detail' => 'required',

            'stockist_data.gst_vat.gst_vat_detail' => 'required',

            'stockist_data.stockist_stock_location' => 'required|array',
            'stockist_data.stockist_stock_location.id' => 'required|exists:stock_locations,id'
        ];
    }
}
