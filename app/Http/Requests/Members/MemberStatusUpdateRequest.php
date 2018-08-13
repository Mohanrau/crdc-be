<?php
namespace App\Http\Requests\Members;

use Illuminate\{
    Validation\Rule,
    Foundation\Http\FormRequest
};
use App\{
    Interfaces\Members\MemberInterface,
    Interfaces\Masters\MasterInterface,
    Rules\Members\CurrentStatusValidation,
    Rules\General\MasterDataIdExists
};

class MemberStatusUpdateRequest extends FormRequest
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
            'cw_id' => 'integer|exists:cw_schedules,id',
            'effective_date' => 'required|date',
            'bonus_payout_deferment' => 'required|boolean',
            'case_reference_number' => 'required|unique:members_status_transactions,case_reference_number',
            'status_id' => [
                'required',
                new MasterDataIdExists($masterRepository, 'member_status'),
                new CurrentStatusValidation($memberRepository, $this->input('user_id'))
            ],
            'reason_id' => [
                'required',
                new MasterDataIdExists($masterRepository, 'member_status_update_reason')
            ]
        ];
    }
}
