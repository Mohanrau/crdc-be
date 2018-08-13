<?php
namespace App\Rules\Members;

use App\Interfaces\Members\MemberInterface;
use Illuminate\Contracts\Validation\Rule;

class CurrentHighestRankValidation implements Rule
{
    private $memberRepositoryObj, $userId;

    /**
     * CurrentHighestRankValidation constructor.
     *
     * @param MemberInterface $memberRepository
     * @param int $userId
     */
    public function __construct(MemberInterface $memberRepository, int $userId)
    {
        $this->memberRepositoryObj = $memberRepository;

        $this->userId = $userId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $member = $this->memberRepositoryObj->find($this->userId);

        return ($member->highest_rank_id == $value) ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.member-rank.different-highest-rank');
    }
}
