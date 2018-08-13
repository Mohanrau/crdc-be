<?php
namespace App\Http\Requests\Members;

use Illuminate\{
    Validation\Rule,
    Foundation\Http\FormRequest
};
use App\{
    Interfaces\Members\MemberInterface,
    Interfaces\Masters\MasterInterface,
    Rules\Members\CurrentMigrateCountryValidation,
    Rules\General\MasterDataIdExists
};

class MemberMigrateRequest extends FormRequest
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
     * @param MemberInterface $memberRepository
     * @param MasterInterface $masterRepository
     * @return array
     */
    public function rules(
        MemberInterface $memberRepository,
        MasterInterface $masterRepository
    )
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'country_id' => [
                'required',
                'integer',
                'exists:countries,id',
                new CurrentMigrateCountryValidation($memberRepository, $this->input('user_id'))
            ],
            'cw_id' => 'required|integer|exists:cw_schedules,id',
            'case_reference_number' => 'required|unique:members_migrates_transactions,case_reference_number',
            'reason_id' => [
                'required',
                new MasterDataIdExists($masterRepository, 'member_migrate_reason')
            ]
        ];
    }
}
