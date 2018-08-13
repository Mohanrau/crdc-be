<?php
namespace App\Policies\Languages;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Languages\Language,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class LanguagePolicy
{
    use HandlesAuthorization, AllowedPolicy{
        before as oldBefore;
    }

    private $modelObj;

    /**
     * LanguagePolicy constructor.
     *
     * @param Language $model
     */
    public function __construct(Language $model)
    {
        $this->modelObj = $model;
    }

    /**
     * check if the user is superAdmin then no need to check the authorization
     *
     * @param $user
     * @param $ability
     * @return bool
     */
    public function before($user, $ability)
    {
        return true;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param Language $model
     * @return bool
     */
    public function view(User $user ,Language $model)
    {
        return true;
    }
}
