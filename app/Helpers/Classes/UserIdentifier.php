<?php
namespace App\Helpers\Classes;

use Illuminate\{
    Support\Facades\Auth,
    Database\Eloquent\Model
};
use App\Models\Users\{
    Guest, User
};

class UserIdentifier
{
    public
        $identifier,
        $modelTable,
        $referrer
    ;

    /**
     * UserIdentifier constructor.
     * @param User|null $user
     * @param string|null $tokenId
     */
    public function __construct(
        User $user = null,
        string $tokenId = null
    )
    {
        // get user from auth facade if user is not given
        $user = $user ?? Auth::user();

        // only retrieve identity if user is not empty
        if (!is_null($user)) {
            if ($user->isGuest()) {
                $guestModel = new Guest();

                $guest = $guestModel->where('token_id', $tokenId ?? $user->token()->id)
                                    ->firstOrFail();

                $this->identifier = $guest->unique_id;

                $this->referrer = $guest->referrer_user_id;

                $this->modelTable = $guestModel->getTable();
            } else {
                $this->modelTable = $user->getTable();

                $this->identifier = $user->id;
            }
        }
    }

    /**
     * Creates a mock for the given identifier
     *
     * @param string|null $identifier
     * @param Model|null $model
     * @param string|null $referrer
     * @return UserIdentifier
     */
    public static function mockIdentity(
        string $identifier = null,
        Model $model = null,
        string $referrer = null
    )
    {
        $userIdentity = new UserIdentifier();

        $userIdentity->identifier = $identifier;

        $userIdentity->modelTable = $model->getTable();

        $userIdentity->referrer = $referrer;

        return $userIdentity;
    }

    /**
     * @return mixed
     */
    public function __toString()
    {
        return $this->identifier;
    }

    /**
     * get the current auth guest obj
     *
     * @return mixed
     */
    public function guest()
    {
        return Guest::where('token_id', Auth::user()->token()->id)
            ->firstOrFail();
    }
}