<?php
namespace App\Policies\Settings;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Settings\Tax,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class TaxPolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * TaxPolicy constructor.
     *
     * @param Tax $model
     */
    public function __construct(Tax $model)
    {
        $this->modelObj = $model;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param Tax $model
     * @return bool
     */
    public function view(User $user, Tax $model)
    {
        return true;
    }
}
