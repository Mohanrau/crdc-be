<?php
namespace App\Http\Requests\Enrollments;

use App\{
    Interfaces\Masters\MasterInterface,
    Models\Locations\Country,
    Models\Users\User,
    Rules\General\MasterDataIdExists,
    Rules\Users\MobileExistCheck
};
use Illuminate\Foundation\Http\FormRequest;

class EnrollmentRequest extends FormRequest
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
     *  Get the validation rules that apply to the request.
     *
     * @param MasterInterface $masterInterface
     * @param User $user
     * @param Country $country
     * @return array
     */
    public function rules(
        MasterInterface $masterInterface,
        User $user,
        Country $country
    )
    {
        return [
            'region.value' => 'required|integer|exists:countries,id',

            //validate user enrollment type -----------------------
            'enrolment_type' => 'required|integer|exists:enrollments_types,id',
            'without_sponsor' => 'required|boolean',

            //validate sponsor user id-----------------------------
            'sponsor_user_id' => 'required_if:without_sponsor,0|integer|exists:users,id',

            //validate member data----------------------------------
            'member_data' => 'required|present',
            'member_data.details.nationality_id' => 'required|integer|exists:countries,id',
            'member_data.details.name' => 'required|min:2|max:191',
            'member_data.details.ic_pass_type_id' => [
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'ic_passport_type'),
            ],
            'member_data.details.ic_passport_number' => 'required',
            'member_data.details.date_of_birth' => 'required|date',

            'member_data.contact_info.email' => 'sometimes|nullable|email|unique:users,email',

            //validating member information ----------------------------------
            'member_data.information' => 'required|present',
            'member_data.information.language_id' => 'required|exists:languages,id',
            'member_data.information.gender_id' => 'required|exists:master_data,id',
            'member_data.information.martial_status_id' => [
                'required',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'martial_status'),
            ],

            //validating member contact info----------------------------------
            'member_data.contact_info' => 'required|present',
            'member_data.contact_info.preferred_contact_id' => [
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'preferred_contact'),
            ],
            'member_data.contact_info.mobile_1_country_code_id' => 'sometimes|nullable|integer|exists:countries,id',
            'member_data.contact_info.mobile_1_num' => [
                'sometimes',
                'nullable',
                'min:4',
                'max:20',
                new MobileExistCheck(
                    $user,
                    $country,
                    (is_int($this->input('member_data.contact_info.mobile_1_country_code_id'))?
                        $this->input('member_data.contact_info.mobile_1_country_code_id') :
                        null
                    )
                )
            ],

            //validating member address data----------------------------------------
            'member_data.address.address_data' => 'sometimes|required',

            //validate placement----------------------------------------------------
            'placement' => 'required|present',
            'placement.placement_position' => 'required|integer|in:0,1,2'
        ];
    }

    /**
     * specify custom messages
     *
     * @return array
     */
    public function messages()
    {
        return [
          'sponsor_user_id.required_if' => trans('message.enrollment.sponsor_required'),
        ];
    }
}
