<?php
namespace App\Policies\Currency;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Currency\Currency,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class CurrencyPolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * CurrencyPolicy constructor.
     *
     * @param Currency $model
     */
    public function __construct(Currency $model)
    {
        $this->modelObj = $model;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param Currency $model
     * @return bool
     */
    public function view(User $user ,Currency $model)
    {
        return true;
    }
}
