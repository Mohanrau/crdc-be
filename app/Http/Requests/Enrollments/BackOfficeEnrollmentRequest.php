<?php
namespace App\Http\Requests\Enrollments;

use Illuminate\Foundation\Http\FormRequest;
use App\Interfaces\Masters\MasterInterface;
use App\Models\{
    Locations\Country,
    Users\User
};
use App\Rules\{
    Users\MobileExistCheck,
    General\MasterDataIdExists
};

class BackOfficeEnrollmentRequest extends FormRequest
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
            /**
             * ---------------------------------------------------------------------------------------------------------
             * general section
             * ---------------------------------------------------------------------------------------------------------
             */
            'country_id' => 'required|integer|exists:countries,id',
            'sponsor_user_id' => 'required_if:without_sponsor,0|integer|exists:users,id',

            //validate user enrollment type -----------------------
            'enrolment_type_id' => 'required|integer|exists:enrollments_types,id',
            'without_sponsor' => 'required|boolean',

            /**
             * ---------------------------------------------------------------------------------------------------------
             * validating member obj
             * ---------------------------------------------------------------------------------------------------------
             */
            'member.data' => 'required|present',
            'member.data.details.nationality_id' => 'required|integer|exists:countries,id',
            'member.data.details.name' => 'required|min:2|max:191',
            'member.data.details.ic_pass_type_id' => [
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'ic_passport_type'),
            ],
            'member.data.details.ic_passport_number' => 'required',
            'member.data.details.date_of_birth' => 'required|date',

            //contact info
            'member.data.contact_info.email' => 'sometimes|nullable|email|unique:users,email',

            //validating member information ----------------------------------
            'member.data.information' => 'required|present',
            'member.data.information.language_id' => 'required|exists:languages,id',
            'member.data.information.gender_id' => 'required|exists:master_data,id',
            'member.data.information.martial_status_id' => [
                'required',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'martial_status'),
            ],

            //validating member contact info----------------------------------
            'member.data.contact_info' => 'required|present',
            'member.data.contact_info.preferred_contact_id' => [
                'required',
                'integer',
                'exists:master_data,id',
                new MasterDataIdExists($masterInterface, 'preferred_contact'),
            ],
            'member.data.contact_info.mobile_1_country_code_id' => 'sometimes|nullable|integer|exists:countries,id',
            'member.data.contact_info.mobile_1_num' => [
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
            'member.data.address.address_data' => 'sometimes|required',

            //validate placement----------------------------------------------------
            'member.placement' => 'required|present',
            'member.placement.placement_position' => 'required|integer|in:0,1,2'

            /**
             * ---------------------------------------------------------------------------------------------------------
             * validating sales obj
             * ---------------------------------------------------------------------------------------------------------
             */
            
        ];
    }
}
