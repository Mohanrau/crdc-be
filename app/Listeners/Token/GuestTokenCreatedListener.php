<?php
namespace App\Listeners\Token;

use Laravel\Passport\Events\AccessTokenCreated;
use Illuminate\{
    Http\Request,
    Support\Facades\Auth
};
use App\{
    Repositories\Users\UserRepository,
    Models\Users\Guest,
    Models\Users\User
};

class GuestTokenCreatedListener
{
    private $request,
            $userRepository,
            $guestModel,
            $userModel
    ;

    /**
     * GuestTokenCreatedListener constructor.
     *
     * @param Request $request
     * @param UserRepository $userRepository
     * @param Guest $guestModel
     * @param User $userModel
     */
    public function __construct(
        Request $request,
        UserRepository $userRepository,
        Guest $guestModel,
        User $userModel
    )
    {
        $this->request = $request;
        $this->userRepository = $userRepository;
        $this->guestModel = $guestModel;
        $this->userModel = $userModel;
    }

    /**
     * When Access Token is created, if the request has referrer or medium of login, it will be saved
     *
     * If a login code is provided, the guest user will share the unique id with the login codes unique id.
     * in this case, both guests actions will be reflected in both sessions.
     *
     * @param AccessTokenCreated $event
     * @return void
     */
    public function handle(AccessTokenCreated $event)
    {
        // Guest login will have user object attached to auth
        if (Auth::guard('web')->check() && Auth::guard('web')->user()->isGuest()) {
            $referrer = $this->request->input('referrer') ?? null;

            $smsCode = $this->request->input('code') ?? '';

            $medium = $this->request->input('medium') ?? null;

            $referrerUser =  null;

            // user previous data if sms code is provided
            if (
                $guest = $this->guestModel
                ->where("unique_id", $event->tokenId)
                ->orWhere("login_code", $smsCode)
                ->first()
            ) {
                $uniqueId = $guest->unique_id;

                $medium = $guest->medium;

                $referrer = $guest->referrer_user_id;
            } else {
                $uniqueId = $event->tokenId;

                // Process referrer if set
                if (!is_null($referrer)) {
                    $referrerUser = $this->userModel
                        ->where("email", $referrer)
                        ->orWhere("old_member_id", $referrer)
                        ->first();

                    if ($referrerUser) {
                        $referrer = $referrerUser->id;
                    }
                }
            }

            $guest = new Guest;

            $guest ->token_id = $event->tokenId;

            $guest ->unique_id = $uniqueId;

            $guest->medium = $medium;

            $guest->referrer_user_id = $referrer;

            $guest->save();
        }
    }
}