<?php
namespace App\Http\Requests\Members;

use Illuminate\{
    Validation\Rule,
    Foundation\Http\FormRequest
};
use App\{
    Interfaces\Members\MemberInterface,
    Rules\Members\CurrentHighestRankValidation,
    Rules\Members\CurrentEnrollementRankValidation
};

class MemberRanksUpdateRequest extends FormRequest
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
    public function rules(MemberInterface $memberRepository)
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'cw_id' => 'required|integer|exists:cw_schedules,id',
            'enrollment_rank_id' => [
                'required_without:highest_rank_id',
                'integer',
                'exists:enrollment_ranks,id',
                new CurrentEnrollementRankValidation($memberRepository, $this->input('user_id'))
            ],
            'highest_rank_id' => [
                'required_without:enrollment_rank_id',
                'integer',
                'exists:team_bonus_ranks,id',
                new CurrentHighestRankValidation($memberRepository, $this->input('user_id'))
            ],
            'case_reference_number' => 'required|unique:member_rank_transactions,case_reference_number'
        ];
    }
}
