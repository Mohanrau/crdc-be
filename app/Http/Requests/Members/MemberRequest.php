<?php
namespace App\Http\Requests\Members;

use App\{Helpers\Traits\AccessControl,
    Interfaces\Masters\MasterInterface,
    Models\Masters\MasterData,
    Rules\General\MasterDataIdExists,
    Rules\Members\ICPassportType};
use Illuminate\Foundation\Http\FormRequest;

class MemberRequest extends FormRequest
{
    use AccessControl;

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
	 * @param MasterInterface $masterRepository
	 * @param MasterData $masterData
	 * @return array
	 */
    public function rules(
        MasterInterface $masterRepository,
				MasterData $masterData
    )
    {
        $preferredContactPhone = $masterData->getIdByTitle( config('mappings.preferred_contact.phone'), 'preferred_contact');

        return [
            'member_data.details.user_id' => 'required|integer|exists:members,user_id',
            'member_data.details.avatar_image_path' => 'sometimes|nullable',
            'member_data.details.name' => 'required|min:2|max:191',
            'member_data.details.nationality_id' => 'required|integer|exists:countries,id',
            'member_data.details.ic_pass_type_id' => 'required|integer|exists:master_data,id',
            'member_data.details.ic_passport_number' => 'required',

            'member_data.information.user_id' => 'sometimes|required|exists:users,id|same:member_data.details.user_id',
            'member_data.information.language_id' => 'sometimes|required|exists:languages,id',
            'member_data.information.gender_id' => 'sometimes|required|exists:master_data,id',

            'member_data.information.spouse.spouse_elken_member' => 'sometimes|required|boolean',
            'member_data.information.spouse.spouse_ibo_id' => 'sometimes|required_if:member_data.information.spouse.spouse_elken_member,1|exists:users,old_member_id',
            'member_data.information.spouse.ic_pass_type_id' => 'sometimes|required_if:member_data.information.spouse.spouse_elken_member,1|nullable|integer|exists:master_data,id',
            'member_data.information.spouse.ic_pass_type_number' => 'sometimes|required_if:member_data.information.spouse.spouse_elken_member,1',

            'member_data.address.user_id' => 'sometimes|required|exists:users,id|same:member_data.details.user_id',
            'member_data.address.address_data' => 'sometimes|required',

            'member_data.verification.*.id' => 'sometimes|exists:members_ic_passport,id',
            'member_data.verification.*.user_id' => 'sometimes|required|exists:users,id|same:member_data.details.user_id',
            'member_data.verification.*.type' => ['sometimes','required', new ICPassportType()],
            'member_data.verification.*.image_path' => 'sometimes|required',

            'member_data.banking.user_id' => 'sometimes|required|exists:users,id|same:member_data.details.user_id',
            'member_data.banking.banking_data' => 'sometimes|required',

            'member_data.contact_info.user_id' => 'sometimes|required|exists:users,id|same:member_data.details.user_id',
            'member_data.contact_info.preferred_contact_id' => [
                "sometimes",
                "required",
                "integer",
                new MasterDataIdExists($masterRepository, 'preferred_contact')
            ],
            'member_data.contact_info.mobile_1_country_code_id' => [
                'sometimes',
                'required_if:member_data.contact_info.preferred_contact_id,' . $preferredContactPhone,
                'integer',
                'exists:countries,id'
            ],
            'member_data.contact_info.mobile_1_num' => [
                'sometimes',
                'required_if:member_data.contact_info.preferred_contact_id,' . $preferredContactPhone,
                'nullable',
                'min:4',
                'max:20'
            ],
            'member_data.contact_info.mobile_1_activated' => [
                'sometimes',
                'required_if:member_data.contact_info.preferred_contact_id,' . $preferredContactPhone,
                'boolean'
            ],
            'member_data.contact_info.tel_office_1_country_code_id' => 'sometimes|nullable|integer|exists:countries,id',
            'member_data.contact_info.tel_home_1_country_code_id' => 'sometimes|nullable|integer|exists:countries,id',



            //todo validate the empty string
//            'member_data.beneficiary.user_id' => 'sometimes|required|exists:users,id|same:member_data.details.user_id',
//            'member_data.beneficiary.beneficiary_name' => 'sometimes|nullable|required|min:2|max:191',
//            'member_data.beneficiary.beneficiary_type_id' => 'sometimes|nullable|required|exists:master_data,id',
//            'member_data.beneficiary.beneficiary_ic_num' => 'sometimes|nullable|required',

            'member_data.tax.user_id' => 'sometimes|required|exists:users,id|same:member_data.details.user_id',
          //  'member_data.tax.tax_data' => 'sometimes|required',
        ];
    }
}
