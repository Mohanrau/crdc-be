<?php
namespace App\Policies\Masters;

use App\{
    Helpers\Traits\AllowedPolicy,
    Models\Masters\MasterData,
    Models\Users\User
};
use Illuminate\Auth\Access\HandlesAuthorization;

class MasterDataPolicy
{
    use HandlesAuthorization, AllowedPolicy;

    private $modelObj;

    /**
     * MasterDataPolicy constructor.
     *
     * @param MasterData $model
     */
    public function __construct(MasterData $model)
    {
        $this->modelObj = $model;
    }

    /**
     * Determine whether the user can view the location.
     *
     * @param User $user
     * @param MasterData $model
     * @return bool
     */
    public function view(User $user, MasterData $model)
    {
        return true;
    }
}
