<?php
namespace App\Rules\Members;

use App\Interfaces\Members\MemberTreeInterface;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class DownlineUserValidation implements Rule
{
    private $memberTreeRepositoryObj, $userId;

    /**
     * DownlineUserValidation constructor.
     *
     * @param MemberTreeInterface $memberTreeRepository
     * @param mixed $userId
     */
    public function __construct(MemberTreeInterface $memberTreeRepository, $userId)
    {
        $this->memberTreeRepositoryObj = $memberTreeRepository;

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
        $currentUserId = Auth::id();

        if ($value == null) {
            return true;
        }
        else if ($value == $currentUserId) {
            return true;
        } else {
            $downlineUserIds = $this->memberTreeRepositoryObj
                ->getAllSponsorChildUserId($currentUserId, false);

            return in_array($value, $downlineUserIds);
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('message.member.not-sponsor-child', ['userId' => $this->userId]);
    }
}
